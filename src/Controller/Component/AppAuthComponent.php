<?php

namespace App\Controller\Component;

use App\Network\AppSession;
use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\ORM\TableRegistry;

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
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class AppAuthComponent extends AuthComponent
{

    public $components = [
        'Flash',
        'RequestHandler',
        'Cart'
    ];

    public $manufacturer;

    public function flash($message)
    {
        $this->Flash->error($message);
    }

    /**
     * @return boolean
     */
    public function termsOfUseAccepted()
    {
        $formattedAcceptedDate = $this->user('terms_of_use_accepted_date')->i18nFormat(Configure::read('DateFormat.Database'));
        return $formattedAcceptedDate >= Configure::read('app.termsOfUseLastUpdate');
    }

    public function getUserId()
    {
        return $this->user('id_customer');
    }

    public function getUserFirstname()
    {
        return $this->user('firstname');
    }

    public function getUsername()
    {
        return $this->user('name');
    }

    public function getEmail()
    {
        return $this->user('email');
    }

    public function getAbbreviatedUserName()
    {
        return $this->user('firstname') . ' ' . substr($this->user('lastname'), 0, 1) . '.';
    }

    public function getGroupId()
    {
        return $this->user('id_default_group');
    }

    public function getLastOrderDetailsForDropdown()
    {
        $this->OrderDetail = TableRegistry::getTableLocator()->get('OrderDetails');
        $dropdownData = $this->OrderDetail->getLastOrderDetailsForDropdown($this->getUserId());
        return $dropdownData;
    }

    private function setManufacturer()
    {
        if (!empty($this->manufacturer)) {
            return;
        }

        $mm = TableRegistry::getTableLocator()->get('Manufacturers');
        $this->manufacturer = $mm->find('all', [
            'conditions' => [
                'AddressManufacturers.email' => $this->user('email'),
                'AddressManufacturers.id_manufacturer > ' . APP_OFF
            ],
            'contain' => [
                'AddressManufacturers'
            ]
        ])->first();
    }

    public function isSuperadmin()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_SUPERADMIN) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isManufacturer()
    {
        $this->setManufacturer();

        if (! empty($this->manufacturer)) {
            return true;
        }

        return false;
    }

    public function getManufacturerId()
    {
        if (! $this->isManufacturer()) {
            throw new Exception('logged user is no manufacturer');
        }

        if (! empty($this->manufacturer)) {
            return $this->manufacturer->id_manufacturer;
        }

        return 0;
    }

    public function getManufacturerName()
    {
        if (! $this->isManufacturer()) {
            throw new Exception('logged user is no manufacturer');
        }

        if (! empty($this->manufacturer)) {
            return $this->manufacturer->name;
        }

        return '';
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_ADMIN) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isCustomer()
    {
        if ($this->isManufacturer()) {
            return false;
        }
        if ($this->user('id_default_group') == CUSTOMER_GROUP_MEMBER) {
            return true;
        }
        return false;
    }

    public function getCreditBalance()
    {
        $c = TableRegistry::getTableLocator()->get('Customers');
        return $c->getCreditBalance($this->getUserId());
    }
    
    public function isInstantOrderMode()
    {
        $session = new AppSession();
        return $session->check('Auth.instantOrderCustomer');
    }

    public function isSelfServiceModeByUrl()
    {
        $result = $this->_registry->getController()->request->getPath() == '/' . __('route_self_service');
        if (!empty($this->_registry->getController()->request->getQuery('redirect'))) {
            $result |= preg_match('`' . '/' . __('route_self_service') . '`', $this->_registry->getController()->request->getQuery('redirect'));
        }
        return $result;
    }
    
    public function isSelfServiceModeByReferer()
    {
        $result = false;
        $serverParams = $this->_registry->getController()->request->getServerParams();
        $requestUriWhitelist = [
            '/' . __('route_cart') . '/ajaxAdd/',
            '/' . __('route_cart') . '/ajaxRemove/'
        ];
        if (isset($serverParams['HTTP_REFERER'])) {
            $result = preg_match('`' . preg_quote(Configure::read('app.cakeServerName')) . '/' . __('route_self_service') . '`', $serverParams['HTTP_REFERER']);
        }
        if (!in_array($serverParams['REQUEST_URI'], $requestUriWhitelist)) {
            $result = false;
        }
        return $result;
    }
    
    public function getCartType()
    {
        $cart = TableRegistry::getTableLocator()->get('Carts');
        $cartType = $cart::CART_TYPE_WEEKLY_RHYTHM;
        if ($this->isInstantOrderMode()) {
            $cartType = $cart::CART_TYPE_INSTANT_ORDER;
        }
        if ($this->isSelfServiceModeByUrl() || $this->isSelfServiceModeByReferer()) {
            $cartType = $cart::CART_TYPE_SELF_SERVICE;
        }
        return $cartType;
    }
    
    public function setCart($cart)
    {
        $this->Cart->cart = $cart;
    }

    public function getCart()
    {
        if (! $this->user()) {
            return null;
        }
        
        $cartType = $this->getCartType();
        
        $cart = TableRegistry::getTableLocator()->get('Carts');
        return $cart->getCart($this->getUserId(), $cartType);
    }

    public function isTimebasedCurrencyEnabledForManufacturer()
    {
        return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->isManufacturer() && $this->manufacturer->timebased_currency_enabled;
    }

    public function isTimebasedCurrencyEnabledForCustomer()
    {
        return Configure::read('appDb.FCS_TIMEBASED_CURRENCY_ENABLED') && $this->user('timebased_currency_enabled');
    }

}
