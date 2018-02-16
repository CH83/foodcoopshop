<?php

namespace App\Model\Table;

use App\Lib\Error\Exception\InvalidParameterException;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use Cake\Utility\Hash;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class ProductsTable extends AppTable
{

    public function initialize(array $config)
    {
        $this->setTable('product');
        parent::initialize($config);
        $this->setPrimaryKey('id_product');
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->belongsTo('ProductLangs', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('ProductShops', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('StockAvailables', [
            'foreignKey' => 'id_product'
        ]);
        $this->belongsTo('Taxes', [
            'foreignKey' => 'id_tax'
        ]);
        $this->belongsTo('CategoryProducts', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasOne('DepositProducts', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasOne('Images', [
            'foreignKey' => 'id_product',
            'order' => [
                'Images.id_image' => 'DESC'
            ]
        ]);
        $this->hasMany('ProductAttributes', [
            'foreignKey' => 'id_product'
        ]);
        $this->hasMany('CategoryProducts', [
            'foreignKey' => 'id_product'
        ]);
        
    }

    public function __construct($id = false, $table = null, $ds = null)
    {
        parent::__construct($id, $table, $ds);
        $this->Configuration = TableRegistry::get('Configurations');
    }

    /**
     * @param int $productId
     * @param int $manufacturerId
     * @return boolean success
     */
    public function isOwner($productId, $manufacturerId)
    {

        $found = $this->find('count', [
            'conditions' => [
                'Products.id_product' => $productId,
                'Products.id_manufacturer' => $manufacturerId
            ]
        ]);
        return (boolean) $found;
    }

    /**
     *
     * @param string $productId
     *            (eg. 4 or '4-10' or '4'
     * @return array ids (productId, attributeId)
     */
    public function getProductIdAndAttributeId($productId)
    {
        $attributeId = 0;
        $explodedProductId = explode('-', $productId);
        if (count($explodedProductId) == 2) {
            $productId = $explodedProductId[0];
            $attributeId = $explodedProductId[1];
        }
        return [
            'productId' => $productId,
            'attributeId' => $attributeId
        ];
    }

    /**
     * @param array $products
     * Array
     *   (
     *       [0] => Array
     *           (
     *               [productId] => (int) status
     *           )
     *   )
     * @throws InvalidParameterException
     * @return boolean $success
     */
    public function changeStatus($products)
    {

        $products2save = [];

        foreach ($products as $product) {
            $productId = key($product);
            $ids = $this->getProductIdAndAttributeId($productId);
            if ($ids['attributeId'] > 0) {
                throw new InvalidParameterException('change status is not allowed for product attributes');
            }
            $status = $product[$ids['productId']];
            $whitelist = [APP_OFF, APP_ON];
            if (!in_array($status, $whitelist, true)) { // last param for type check
                throw new InvalidParameterException('Products.active for product ' .$ids['productId'] . ' needs to be ' .APP_OFF . ' or ' . APP_ON.'; was: ' . $status);
            } else {
                $products2save[] = [
                    'id_product' => $ids['productId'],
                    'active' => $status
                ];
            }
        }

        $success = false;
        if (!empty($products2save)) {
            $entities = $this->newEntities($products2save);
            $result = $this->saveMany($entities);
            $success = !empty($result);
        }

        return $success;
    }

    /**
     * @param string $quantity
     * @return boolean / int
     */
    public function getQuantityAsInteger($quantity)
    {
        $quantity = trim($quantity);

        if (!is_numeric($quantity)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $quantity = (int) ($quantity);

        return $quantity;
    }

    /**
     * @param string $price
     * @return boolean / float
     */
    public function getPriceAsFloat($price)
    {
        $price = trim($price);
        $price = str_replace(',', '.', $price);

        if (!is_numeric($price)) {
            return -1; // do not return false, because 0 is a valid return value!
        }
        $price = floatval($price);

        return $price;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (float) deposit
     *          )
     *  )
     * @return boolean $success
     */
    public function changeDeposit($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $this->getPriceAsFloat($product[$productId]);
            if ($deposit < 0) {
                throw new InvalidParameterException('Eingabeformat von Pfand ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $deposit = $this->getPriceAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                $oldDeposit = $this->DepositProducts->find('all', [
                    'conditions' => [
                        'id_product_attribute' => $ids['attributeId']
                    ]
                ])->first();

                if (empty($oldDeposit)) {
                    $entity = $this->DepositProducts->newEntity();
                } else {
                    $this->DepositProducts->setPrimaryKey('id_product_attribute');
                    $entity = $this->DepositProducts->get($oldDeposit->id_product_attribute);
                }
                
                $deposit2save = [
                    'id_product_attribute' => $ids['attributeId'],
                    'deposit' => $deposit
                ];
            } else {
                // deposit is set for productId
                $oldDeposit = $this->DepositProducts->find('all', [
                    'conditions' => [
                        'id_product' => $productId
                    ]
                ])->first();

                if (empty($oldDeposit)) {
                    $entity = $this->DepositProducts->newEntity();
                } else {
                    $entity = $this->DepositProducts->get($oldDeposit->id_product);
                }

                $deposit2save = [
                    'id_product' => $productId,
                    'deposit' => $deposit
                ];
            }

            $this->DepositProducts->setPrimaryKey('id');
            $result = $this->DepositProducts->save(
                $this->DepositProducts->patchEntity($entity, $deposit2save)
            );
            $this->DepositProducts->setPrimaryKey('id_product');
            $success |= is_object($result);
        }

        return $success;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (float) price
     *          )
     *  )
     * @return boolean $success
     */
    public function changePrice($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $price = $this->getPriceAsFloat($product[$productId]);
            if ($price < 0) {
                throw new InvalidParameterException('Eingabeformat von Preis ist nicht korrekt: '.$product[$productId]);
            }
        }

        $success = false;
        foreach ($products as $product) {
            $productId = key($product);
            $price = $this->getPriceAsFloat($product[$productId]);

            $ids = $this->getProductIdAndAttributeId($productId);

            $netPrice = $this->getNetPrice($ids['productId'], $price);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $success = $this->ProductAttributes->ProductAttributeShops->updateAll([
                    'price' => $netPrice
                ], [
                    'id_product_attribute' => $ids['attributeId']
                ]);
            } else {
                $product2update = [
                    'price' => $netPrice
                ];
                $entity = $this->ProductShops->get($ids['productId']);
                $result = $this->ProductShops->save(
                    $this->ProductShops->patchEntity($entity, $product2update)
                );
                $success |= is_object($result);
            }
        }

        return $success;
    }

    /**
     * @param array $products
     *  Array
     *  (
     *      [0] => Array
     *          (
     *              [productId] => (int) quantity
     *          )
     *  )
     * @return boolean $success
     */
    public function changeQuantity($products)
    {

        foreach ($products as $product) {
            $productId = key($product);
            $quantity = $this->getQuantityAsInteger($product[$productId]);
            if ($quantity < 0) {
                throw new InvalidParameterException('Eingabeformat von Anzahl ist nicht korrekt: '.$product[$productId]);
            }
        }

        foreach ($products as $product) {
            $productId = key($product);
            $quantity = $product[$productId];

            $ids = $this->getProductIdAndAttributeId($productId);

            if ($ids['attributeId'] > 0) {
                // update attribute - updateAll needed for multi conditions of update
                $this->StockAvailables->updateAll([
                    'quantity' => $quantity
                ], [
                    'id_product_attribute' => $ids['attributeId'],
                    'id_product' => $ids['productId']
                ]);
                $this->StockAvailables->updateQuantityForMainProduct($ids['productId']);
            } else {
                $product2update = [
                    'quantity' => $quantity
                ];
                $entity = $this->StockAvailables->get($ids['productId']);
                $this->StockAvailables->save($this->StockAvailables->patchEntity($entity, $product2update));
            }
        }
    }

    /**
     * @param int $manufacturerId
     * @param boolean $useHolidayMode
     * @return array
     */
    public function getCountByManufacturerId($manufacturerId, $useHolidayMode = false)
    {
        $productCount = $this->find('all', [
            'conditions' => [
                'Products.active' => APP_ON,
                $useHolidayMode ? $this->getManufacturerHolidayConditions() : null,
                'Products.id_manufacturer' => $manufacturerId
            ],
            'contain' => [
                'Manufacturers'
            ]
        ])->count();
        return $productCount;
    }

    public function isNew($date)
    {
        $showAsNewExpirationDate = date('Y-m-d', strtotime($date . ' + ' . Configure::read('appDb.FCS_DAYS_SHOW_PRODUCT_AS_NEW') . ' days'));
        if (strtotime($showAsNewExpirationDate) > strtotime(date('Y-m-d'))) {
            return true;
        }
        return false;
    }

    /**
     * @param array $products
     * @return array $preparedProducts
     */
    public function prepareProductsForBackend($pParams, $addProductNameToAttributes = false)
    {

        $quantityIsZeroFilterOn = false;
        $priceIsZeroFilterOn = false;
        foreach ($pParams['conditions'] as $condition) {
            if (preg_match('/'.$this->getIsQuantityZeroCondition().'/', $condition)) {
                $this->association('ProductAttributes')->setConditions(
                    [
                        'StockAvailables.quantity' => 0
                    ]
                );
                $quantityIsZeroFilterOn = true;
            }
            if (preg_match('/'.$this->getIsPriceZeroCondition().'/', $condition)) {
                $this->association('ProductAttributeShops')->setConditions(
                    [
                        'ProductAttributeShop.price' => 0
                    ]
                );
                $priceIsZeroFilterOn = true;
            }
        }

        $products = $this->find('all', $pParams)->toArray();

        $i = 0;
        $preparedProducts = [];
        foreach ($products as $product) {
            $product->category = (object) [
                'names' => [],
                'allProductsFound' => false
            ];
            foreach ($product->category_products as $category) {
                if ($category->id_category == 2) {
                    continue; // do not consider category "produkte" - why was it needed???
                }

                // assignment to "all products" has to be checked... otherwise show error message
                if ($category->id_category == Configure::read('app.categoryAllProducts')) {
                    $product->category->allProductsFound = true;
                } else {
                    // check if category was assigned to product but deleted afterwards
                    if (!empty($category->name)) {
                        $product->category->name[] = $category->name;
                    }
                }
            }
            $product->selectedCategories = Hash::extract($product->category_products, '{n}.id_category');
            $product->deposit = 0;

            $product->is_new = $this->isNew($product->product_shop->date_add);
            
            $product->gross_price = $this->getGrossPrice($product->id_product, $product->product_shop->price);

            $rowClass = [];
            if (! $product->active) {
                $rowClass[] = 'deactivated';
            }

            @$products->deposit = $product->deposit_product->deposit;
            if (!empty($product->tax)) {
                $product->tax->rate = 0;
            }

            $rowClass[] = 'main-product';
            $rowIsOdd = false;
            if ($i % 2 == 0) {
                $rowIsOdd = true;
                $rowClass[] = 'custom-odd';
            }
            $product->rowClass = join(' ', $rowClass);

            $preparedProducts[] = $products[$i];
            $i ++;

            if (! empty($product->product_attributes)) {
                $currentPreparedProduct = count($preparedProducts) - 1;
                $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] = 0;

                foreach ($product->product_attributes as $attribute) {
                    if (($quantityIsZeroFilterOn && empty($attribute->stock_available)) || ($priceIsZeroFilterOn && empty($attribute->product_attribute_shops))) {
                        $preparedProducts[$currentPreparedProduct]['AttributesRemoved'] ++;
                        continue;
                    }

                    $grossPrice = 0;
                    if (! empty($attribute->product_attribute_shops->price)) {
                        $grossPrice = $this->getGrossPrice($product->id_product, $attribute->product_attribute_shops->price);
                    }

                    $rowClass = [
                        'sub-row'
                    ];
                    if (! $product->active) {
                        $rowClass[] = 'deactivated';
                    }

                    if ($rowIsOdd) {
                        $rowClass[] = 'custom-odd';
                    }

                    $preparedProduct = [
                        'id_product' => $product->id_product . '-' . $attribute->id_product_attribute,
                        'gross_price' => $grossPrice,
                        'active' => - 1,
                        'rowClass' => join(' ', $rowClass),
                        'product_lang' => [
                            'name' => ($addProductNameToAttributes ? $product->product_lang->name . ' : ' : '') . $attribute->product_attribute_combination->attribute->name,
                            'description_short' => '',
                            'description' => '',
                            'unity' => ''
                        ],
                        'manufacturer' => [
                            'name' => $product->manufacturer->name
                        ],
                        'product_attribute_shop' => [
                            'default_on' => $attribute->product_attribute_shop->default_on
                        ],
                        'stock_available' => [
                            'quantity' => $attribute->stock_available->quantity
                        ],
                        'deposit' => !empty($attribute->deposit_product_attributes) ? $attribute->deposit_product_attributes->deposit : 0,
                        'category' => [
                            'names' => [],
                            'allProductsFound' => true
                        ],
                        'image' => null
                    ];
                    
                    $preparedProduct = json_decode(json_encode($preparedProduct), FALSE); // convert array recursively into object
                    
                    $preparedProducts[] = $preparedProduct;
                }
            }
        }

        // price zero filter is difficult to implement, because if there are attributes assigned to the product, the product's price is always 0
        // which would lead to always showing the main product of attributes if price zero filter is set
        // this is not the case for quantity zero filter, because the main product's quantity is the sum of the associated attribute quantities
        if ($priceIsZeroFilterOn) {
            foreach ($preparedProducts as $key => $preparedProduct) {
                if (isset($preparedProducts[$key]['AttributesRemoved']) && $preparedProducts[$key]['AttributesRemoved'] == count($preparedProducts[$key]['ProductAttributes'])) {
                    unset($preparedProducts[$key]);
                }
            }
        }
        return $preparedProducts;
    }

    public function getForDropdown($appAuth, $manufacturerId)
    {
        $conditions = [];

        if ($appAuth->isManufacturer()) {
            $manufacturerId = $appAuth->getManufacturerId();
        }

        if ($manufacturerId > 0) {
            $conditions['Manufacturers.id_manufacturer'] = $manufacturerId;
        }

        // ->find('list') a does not return associated model data
        $products = $this->find('all', [
            'conditions' => $conditions,
            'contain' => [
                'ProductLangs',
                'Manufacturers',
            ],
            'order' => [
                'Products.active' => 'DESC',
                'ProductLangs.name' => 'ASC'
            ]
        ]);

        $offlineProducts = [];
        $onlineProducts = [];
        foreach ($products as $product) {
            $productNameForDropdown = $product->product_lang->name . (!empty($product->manufacturer) ? ' - ' . $product->manufacturer->name : '');
            if ($product->active == 0) {
                $offlineProducts[$product->id_product] = $productNameForDropdown;
            } else {
                $onlineProducts[$product->id_product] = $productNameForDropdown;
            }
        }

        $productsForDropdown = [];
        if (! empty($onlineProducts)) {
            $onlineCount = count($onlineProducts);
            $productsForDropdown['online-' . $onlineCount] = $onlineProducts;
        }

        if (! empty($offlineProducts)) {
            $offlineCount = count($offlineProducts);
            $productsForDropdown['offline-' . $offlineCount] = $offlineProducts;
        }

        return $productsForDropdown;
    }

    /**
     * @param float $grossPrice (for all units)
     * @param float $netPrice (for one unit)
     * @param int $quantity
     * @return float
     */
    public function getUnitTax($grossPrice, $netPrice, $quantity)
    {
        if ($quantity == 0) {
            return 0;
        }
        return round(($grossPrice - ($netPrice * $quantity)) / $quantity, 2);
    }

    private function getTaxJoins()
    {
        // leave "t.active IN (0,1)" condition because 0% tax does not have a record in tax table
        $taxJoins = 'FROM '.$this->tablePrefix.'product p
             LEFT JOIN '.$this->tablePrefix.'tax t ON t.id_tax = p.id_tax
             WHERE t.active IN (0,1)
               AND p.id_product = :productId';
        return $taxJoins;
    }

    /**
     * needs to be called AFTER taxId of product was updated
     */
    public function getNetPriceAfterTaxUpdate($productId, $oldNetPrice, $oldTaxRate)
    {

        // if old tax was 0, $oldTaxRate === null (tax 0 has no record in table tax) and would reset the price to 0 €
        if (is_null($oldTaxRate)) {
            $oldTaxRate = 0;
        }

        $sql = 'SELECT ROUND(:oldNetPrice / ((100 + t.rate) / 100) * (1 + :oldTaxRate / 100), 6) as new_net_price ';
        $sql .= $this->getTaxJoins();
        $params = [
            'oldNetPrice' => $oldNetPrice,
            'oldTaxRate' => $oldTaxRate,
            'productId' => $productId
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');
        
        // if tax == 0 %, tax is empty
        if (empty($rate)) {
            $newNetPrice = $oldNetPrice * (1 + $oldTaxRate / 100);
        } else {
            $newNetPrice = $rate[0]['new_net_price'];
        }

        return $newNetPrice;
    }

    public function getGrossPrice($productId, $netPrice)
    {
        $productId = (int) $productId;
        $sql = 'SELECT ROUND(:netPrice * (100 + t.rate) / 100, 2) as gross_price ';
        $sql .= $this->getTaxJoins();
        $params = [
            'netPrice' => $netPrice,
            'productId' => $productId
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');

        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $grossPrice = $netPrice;
        } else {
            $grossPrice = $rate[0]['gross_price'];
        }

        return $grossPrice;
    }

    public function getNetPrice($productId, $grossPrice)
    {
        $grossPrice = str_replace(',', '.', $grossPrice);

        if (! $grossPrice > - 1) { // allow 0 as new price
            return false;
        }

        $sql = 'SELECT ROUND(:grossPrice / (100 + t.rate) * 100, 6) as net_price ';
        $sql .= $this->getTaxJoins();
        $params = [
            'productId' => $productId,
            'grossPrice' => $grossPrice
        ];
        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($params);
        $rate = $statement->fetchAll('assoc');
        
        // if tax == 0% rate is empty...
        if (empty($rate)) {
            $netPrice = $grossPrice;
        } else {
            $netPrice = $rate[0]['net_price'];
        }

        return $netPrice;
    }

    private function getIsQuantityZeroCondition()
    {
        return 'StockAvailables.quantity = 0';
    }

    private function getIsPriceZeroCondition()
    {
        return 'ProductShops.price = 0';
    }

    public function getProductParams($appAuth, $productId, $manufacturerId, $active, $category = '', $isQuantityZero = 0, $isPriceZero = 0)
    {
        $conditions = [];
        $group = [];

        if ($manufacturerId != 'all') {
            $conditions['Products.id_manufacturer'] = $manufacturerId;
        } else {
            // do not show any non-associated products that might be found in database
            $conditions[] = 'Products.id_manufacturer > 0';
        }

        if ($productId != '') {
            $conditions['Products.id_product'] = $productId;
        }

        if ($active != 'all') {
            $conditions['Products.active'] = $active;
        }

        if ($category != '') {
            $conditions['CategoryProducts.id_category'] = (int) $category;
        }

        if ($isQuantityZero != '') {
            $conditions[] = $this->getIsQuantityZeroCondition();
        }

        if ($isPriceZero != '') {
            $conditions[] = $this->getIsPriceZeroCondition();
        }

        $contain = [
            'CategoryProducts',
            'ProductShops',
            'ProductLangs',
            'DepositProducts',
            'Images',
            'Taxes',
            'Manufacturers',
            'StockAvailables',
            'ProductAttributes',
            'ProductAttributes.StockAvailables',
            'ProductAttributes.ProductAttributeShops',
            'ProductAttributes.DepositProductAttributes',
            'ProductAttributes.ProductAttributeCombinations.Attributes'
        ];

        if ($manufacturerId == '') {
            $contain[] = 'Manufacturers';
        }

        $pParams = [
            'conditions' => $conditions,
            'order' => [
                'Products.active' => 'DESC',
                'ProductLangs.name' => 'ASC'
            ],
            'contain' => $contain,
            'group' => $group
        ];

        return $pParams;
    }

    public function changeDefaultAttributeId($productId, $productAttributeId)
    {
        $productAttributes = $this->ProductAttributes->find('all', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ]);
        $productAttributeIds = Hash::extract('{n}.ProductAttributes.id_product_attribute', $productAttributes);

        // first set all associated attributes to 0
        $this->ProductAttributes->ProductAttributeShop->updateAll([
            'ProductAttributeShop.default_on' => 0
        ], [
            'id_product_attribute IN (' . join(', ', $productAttributeIds) . ')',
            'id_shop' => 1
        ]);

        // then set the new one
        $this->ProductAttributes->ProductAttributeShop->updateAll([
            'ProductAttributeShop.default_on' => 1
        ], [
            'ProductAttributeShop.id_product_attribute' => $productAttributeId,
            'ProductAttributeShop.id_shop' => 1
        ]);
    }

    public function deleteProductAttribute($productId, $attributeId, $oldProduct)
    {

        $pac = $this->ProductAttributes->ProductAttributeCombination->find('all', [
            'conditions' => [
                'ProductAttributeCombination.id_product_attribute' => $attributeId
            ]
        ])->first();
        $productAttributeId = $pac['ProductAttributeCombinations']['id_product_attribute'];

        $this->ProductAttributes->deleteAll([
            'ProductAttributes.id_product_attribute' => $productAttributeId
        ], false);

        $this->ProductAttributes->ProductAttributeCombination->deleteAll([
            'ProductAttributeCombination.id_product_attribute' => $productAttributeId
        ], false);

        $this->ProductAttributes->ProductAttributeShop->deleteAll([
            'ProductAttributeShop.id_product_attribute' => $productAttributeId
        ], false);

        // deleteAll can only get primary key as condition
        $this->StockAvailable->primaryKey = 'id_product_attribute';
        $this->StockAvailable->deleteAll([
            'StockAvailables.id_product_attribute' => $attributeId
        ], false);

        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function addProductAttribute($productId, $attributeId)
    {
        $defaultQuantity = 999;

        $productAttributesCount = $this->ProductAttributes->find('count', [
            'conditions' => [
                'ProductAttributes.id_product' => $productId
            ]
        ]);

        $this->ProductAttributes->save([
            'id_product' => $productId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0
        ]);
        $productAttributeId = $this->ProductAttributes->getLastInsertID();

        // INSERT in ProductAttributeCombination tricky because of set primary_key
        $this->getConnection()->query('INSERT INTO '.$this->tablePrefix.'product_attribute_combination (id_attribute, id_product_attribute) VALUES(' . $attributeId . ', ' . $productAttributeId . ')');

        $this->ProductAttributes->ProductAttributeShop->save([
            'id_product_attribute' => $productAttributeId,
            'default_on' => $productAttributesCount == 0 ? 1 : 0,
            'id_shop' => 1,
            'id_product' => $productId
        ]);

        // set price of product back to 0 => if not, the price of the attribute is added to the price of the product
        $this->ProductShop->id = $productId;
        $this->ProductShop->save([
            'price' => 0
        ]);

        // avoid Integrity constraint violation: 1062 Duplicate entry '64-232-1-0' for key 'product_sqlstock'
        // with custom sql
        $this->getConnection()->query('INSERT INTO '.$this->tablePrefix.'stock_available (id_product, id_product_attribute, quantity) VALUES(' . $productId . ', ' . $productAttributeId . ', ' . $defaultQuantity . ')');

        $this->StockAvailable->updateQuantityForMainProduct($productId);
    }

    public function add($manufacturer)
    {
        $defaultQuantity = 999;

        $defaultTaxId = $this->Manufacturer->getOptionDefaultTaxId($manufacturer['Manufacturers']['default_tax_id']);

        // INSERT PRODUCT
        $this->save([
            'id_manufacturer' => $manufacturer['Manufacturers']['id_manufacturer'],
            'id_category_default' => Configure::read('app.categoryAllProducts'),
            'id_tax' => $defaultTaxId,
            'unity' => '',
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);
        $newProductId = $this->getLastInsertID();

        // INSERT PRODUCT_SHOP
        $this->ProductShop->save([
            'id_product' => $newProductId,
            'id_shop' => 1,
            'id_category_default' => Configure::read('app.categoryAllProducts'),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s')
        ]);

        // INSERT CATEGORY_PRODUCTS
        $this->CategoryProducts->save([
            'id_category' => Configure::read('app.categoryAllProducts'),
            'id_product' => $newProductId
        ]);

        // INSERT PRODUCT_LANG
        $name = StringComponent::removeSpecialChars('Neues Produkt von ' . $manufacturer['Manufacturers']['name']);
        $this->ProductLang->save([
            'id_product' => $newProductId,
            'id_lang' => 1,
            'id_shop' => 1,
            'name' => $name,
            'description' => '',
            'description_short' => '',
            'unity' => ''
        ]);

        // INSERT STOCK AVAILABLE
        $this->StockAvailable->save([
            'id_product' => $newProductId,
            'id_shop' => 1,
            'quantity' => $defaultQuantity
        ]);

        $newProduct = $this->find('all', [
            'conditions' => [
                'Products.id_product' => $newProductId
            ]
        ])->first();
        return $newProduct;
    }
}
