<?php

use Admin\Controller\AdminAppController;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;

/**
 * ActionLogsController
 *
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
class ActionLogsController extends AdminAppController
{

    public function beforeFilter(Event $event)
    {
        $this->ActionLog = TableRegistry::get('ActionLogs');
        $this->Customer = TableRegistry::get('Customers');
        $this->Product = TableRegistry::get('Products');
        parent::beforeFilter($event);
    }

    public function index()
    {
        $conditions = [];

        $dateFrom = date('d.m.Y', strtotime('-6 day'));
        if (! empty($this->params['named']['dateFrom'])) {
            $dateFrom = $this->params['named']['dateFrom'];
        }
        $this->set('dateFrom', $dateFrom);

        $dateTo = date('d.m.Y');
        if (! empty($this->params['named']['dateTo'])) {
            $dateTo = $this->params['named']['dateTo'];
        }
        $this->set('dateTo', $dateTo);

        $conditions[] = 'DATE_FORMAT(ActionLog.date, \'%Y-%m-%d\') >= \'' . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($dateFrom) . '\'';
        $conditions[] = 'DATE_FORMAT(ActionLog.date, \'%Y-%m-%d\') <= \'' . Configure::read('AppConfig.timeHelper')->formatToDbFormatDate($dateTo) . '\'';

        $customerId = '';
        if (! empty($this->params['named']['customerId'])) {
            $customerId = $this->params['named']['customerId'];
        }
        $this->set('customerId', $customerId);

        if ($customerId != '') {
            $conditions['ActionLogs.customer_id'] = $customerId;
        }

        $productId = '';
        if (! empty($this->params['named']['productId'])) {
            $productId = $this->params['named']['productId'];
        }
        $this->set('productId', $productId);

        if ($productId != '') {
            $conditions['ActionLogs.object_type'] = "products";
            $conditions['ActionLogs.object_id'] = $productId;
        }

        // manufacturers should only see their own product logs
        if ($this->AppAuth->isManufacturer()) {
            $conditions[] = '( (BlogPosts.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Products.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Payments.id_manufacturer = ' . $this->AppAuth->getManufacturerId() .
                ' OR Manufacturers.id_manufacturer = ' . $this->AppAuth->getManufacturerId() . ') '.
              ' OR (ActionLogs.customer_id = ' .$this->AppAuth->getUserId().') )';
        }

        // customers are only allowed to see their own data
        if ($this->AppAuth->isCustomer()) {
            $tmpCondition  =  '(';
                $tmpCondition .= 'Customers.id_customer = '.$this->AppAuth->getUserId();
                // order of first and lastname can be changed Configure::read('AppConfig.customerMainNamePart')
                $customerNameForRegex = $this->AppAuth->user('firstname') . ' ' . $this->AppAuth->user('lastname');
            if (Configure::read('AppConfig.customerMainNamePart') == 'lastname') {
                $customerNameForRegex = $this->AppAuth->user('lastname') . ' ' . $this->AppAuth->user('firstname');
            }
                $tmpCondition .= ' OR ActionLogs.text REGEXP \'' . $customerNameForRegex . '\'';
            $tmpCondition .= ')';
            $conditions[] = $tmpCondition;
            // never show cronjob logs for customers
            $conditions[] = 'ActionLogs.type NOT REGEXP \'^cronjob_\'';
        }

        $type = '';
        if (! empty($this->params['named']['type'])) {
            $type = $this->params['named']['type'];
            $conditions[] = 'ActionLogs.type = \'' . $type . '\'';
        }
        $this->set('type', $type);

        $this->Paginator->settings = array_merge($this->Paginator->settings, [
            'conditions' => $conditions,
            'order' => [
                'ActionLogs.date' => 'DESC'
            ]
        ]);
        $actionLogs = $this->Paginator->paginate('ActionLogs');
        foreach ($actionLogs as &$actionLog) {
            $manufacturer = $this->Customer->getManufacturerRecord($actionLog);
            if ($manufacturer) {
                $actionLog['Customers']['Manufacturers'] = $manufacturer['Manufacturers'];
            }
        }
        $this->set('actionLogs', $actionLogs);

        $this->set('actionLogModel', $this->ActionLog);

        if ($this->AppAuth->isSuperadmin() || $this->AppAuth->isAdmin() || $this->AppAuth->isManufacturer()) {
            $this->set('customersForDropdown', $this->Customer->getForDropdown(true));
        }

        $titleForLayout = 'Aktivitäten';
        if (isset($this->ActionLog->types[$type])) {
            $titleForLayout .= ' | ' . $this->ActionLog->types[$type]['de'];
        }
        $this->set('title_for_layout', $titleForLayout);
    }
}
