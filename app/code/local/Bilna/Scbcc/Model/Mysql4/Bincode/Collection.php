<?php
class Bilna_Scbcc_Model_Mysql4_Bincode_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    public function _construct() {
        parent::_construct();
        $this->_init('scbcc/bincode');
    }
}
