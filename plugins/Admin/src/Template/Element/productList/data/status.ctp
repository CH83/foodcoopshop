<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.2.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, http://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

echo '<td class="status">';

    if ($product->active == 1) {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('accept.png')), [
            'class' => 'set-state-to-inactive change-active-state',
            'id' => 'change-active-state-' . $product->id_product,
            'title' => __d('admin', 'deactivate')
        ], 'javascript:void(0);');
    }
    
    if ($product->active == '') {
        echo $this->Html->getJqueryUiIcon($this->Html->image($this->Html->getFamFamFamPath('delete.png')), [
            'class' => 'set-state-to-active change-active-state',
            'id' => 'change-active-state-' . $product->id_product,
            'title' => __d('admin', 'activate')
        ], 'javascript:void(0);');
    }

echo '</td>';

?>