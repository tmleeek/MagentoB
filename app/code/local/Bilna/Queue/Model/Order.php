<?php
/**
 * Description of Bilna_Queue_Model_Order
 *
 * @author Bilna Development Team <development@bilna.com>
 * @date 12-Nov-2015
 */

class Bilna_Queue_Model_Order extends Mage_Core_Model_Abstract {
    public function _construct() {
        $this->_init('bilna_queue/order');
    }
}
