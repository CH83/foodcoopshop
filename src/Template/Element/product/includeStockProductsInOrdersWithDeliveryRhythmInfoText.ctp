<?php
/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 2.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */

use Cake\Core\Configure;

if (!$appAuth->user() || !Configure::read('appDb.FCS_CART_ENABLED') || !$showInfoText) {
    return;
}
?>

<div class="line">
	<p>
	<?php
	if (Configure::read('appDb.FCS_SELF_SERVICE_MODE_FOR_STOCK_PRODUCTS_ENABLED') && Configure::read('app.serviceModeTestingEnabled')) {
            echo __('Stock_product:_order_possible_only_by_{0}.', [
                $this->Html->link(__('Self_service'), $this->Slug->getSelfService($keyword))
            ]);
        } else {
            echo __('Stock_product:_order_possible_only_with_instant_order_on_pick_up.');
        }
	 ?></p>
</div>
