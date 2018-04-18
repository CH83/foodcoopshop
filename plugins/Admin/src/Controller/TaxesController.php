<?php

namespace Admin\Controller;

use Cake\Core\Configure;
use Cake\Network\Exception\NotFoundException;
use Cake\ORM\TableRegistry;

/**
 * TaxesController
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
class TaxesController extends AdminAppController
{

    public function isAuthorized($user)
    {
        return $this->AppAuth->isSuperadmin();
    }

    public function add()
    {
        $this->Tax = TableRegistry::get('Taxes');
        $tax = $this->Tax->newEntity(
            [
                'rate' => 0,
                'active' => APP_ON,
            ],
            ['validate' => false]
        );
        $this->set('title_for_layout', 'Steuersatz erstellen');
        $this->_processForm($tax, false);

        if (empty($this->request->getData())) {
            $this->render('edit');
        }
    }

    public function edit($taxId)
    {
        if ($taxId === null) {
            throw new NotFoundException;
        }

        $this->Tax = TableRegistry::get('Taxes');
        $tax = $this->Tax->find('all', [
            'conditions' => [
                'Taxes.id_tax' => $taxId
            ]
        ])->first();

        if (empty($tax)) {
            throw new NotFoundException;
        }
        $this->set('title_for_layout', 'Steuersatz bearbeiten');
        $this->_processForm($tax, true);
    }

    private function _processForm($tax, $isEditMode)
    {

        $this->setFormReferer();
        $this->set('isEditMode', $isEditMode);

        if (empty($this->request->getData())) {
            $this->set('tax', $tax);
            return;
        }

        $this->loadComponent('Sanitize');
        $this->request->data = $this->Sanitize->trimRecursive($this->request->getData());
        $this->request->data = $this->Sanitize->stripTagsRecursive($this->request->getData());

        $tax = $this->Tax->patchEntity($tax, $this->request->getData());
        if (!empty($tax->getErrors())) {
            $this->Flash->error('Beim Speichern sind Fehler aufgetreten!');
            $this->set('tax', $tax);
            $this->render('edit');
        } else {
            $tax = $this->Tax->save($tax);

            if (!$isEditMode) {
                $messageSuffix = 'erstellt';
                $actionLogType = 'tax_added';
            } else {
                $messageSuffix = 'geändert';
                $actionLogType = 'tax_changed';
            }

            $this->ActionLog = TableRegistry::get('ActionLogs');
            $message = 'Der Steuersatz <b>' . Configure::read('app.htmlHelper')->formatAsPercent($tax->rate) . '</b> wurde ' . $messageSuffix . '.';
            $this->ActionLog->customSave($actionLogType, $this->AppAuth->getUserId(), $tax->id_tax, 'taxes', $message);
            $this->Flash->success($message);

            $this->request->getSession()->write('highlightedRowId', $tax->id_tax);
            $this->redirect($this->request->getData('referer'));
        }

        $this->set('tax', $tax);
    }

    public function index()
    {
        $conditions = [
            'Taxes.active > ' . APP_DEL
        ];

        $this->Tax = TableRegistry::get('Taxes');
        $query = $this->Tax->find('all', [
            'conditions' => $conditions
        ]);
        $taxes = $this->paginate($query, [
            'sortWhitelist' => [
                'Taxes.rate', 'Taxes.position'
            ],
            'order' => [
                'Taxes.rate' => 'ASC'
            ]
        ]);

        $this->set('taxes', $taxes);

        $this->set('title_for_layout', 'Steuersätze');
    }
}