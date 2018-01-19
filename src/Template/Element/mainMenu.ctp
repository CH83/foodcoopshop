<?php
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

use Cake\Core\Configure;

$menu = [];
if (Configure::read('AppConfig.db_config_FCS_SHOW_PRODUCTS_FOR_GUESTS') || $appAuth->user()) {
    $menu[] = [
        'name' => 'Produkte', 'slug' => $this->Slug->getAllProducts(),
        'children' => $categoriesForMenu
    ];
}

if (!empty($manufacturersForMenu)) {
    $menu[] = [
        'name' => 'Hersteller', 'slug' => $this->Slug->getManufacturerList(),
        'children' => $manufacturersForMenu
    ];
}

$menu[] = [
    'name' => 'Aktuelles', 'slug' => $this->Slug->getBlogList()
];

$menu = array_merge($menu, $this->Menu->buildPageMenu($pagesForHeader));

echo $this->Menu->render($menu, ['id' => 'main-menu', 'class' => 'horizontal menu']);
