<?php

namespace App\Model\Table;

use Cake\Validation\Validator;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class TimebasedCurrencyPaymentsTable extends AppTable
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->belongsTo('Manufacturers', [
            'foreignKey' => 'id_manufacturer'
        ]);
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator)
    {
        return $validator;
    }
    
    /**
     * @param int $customerId
     * @return float
     */
    public function getSum($customerId)
    {
        $conditions = [
            'TimebasedCurrencyPayments.id_customer' => $customerId,
            'TimebasedCurrencyPayments.status' => APP_ON
        ];
        
        $query = $this->find('all', [
            'conditions' => $conditions
        ]);
        $query->select(
            ['SumTime' => $query->func()->sum('TimebasedCurrencyPayments.time')]
        );
        return $query->toArray()[0]['SumTime'];
    }

}
