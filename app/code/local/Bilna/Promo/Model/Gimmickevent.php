<?php

class Bilna_Promo_Model_Gimmickevent extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('bilnapromo/gimmickevent');
    }
}