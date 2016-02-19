<?php
/**
 * Unicode Systems
 * @category   Uni
 * @package    Uni_Banner
 * @copyright  Copyright (c) 2010-2011 Unicode Systems. (http://www.unicodesystems.in)
 * @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
class Bilna_Promo_Block_Adminhtml_Giftvoucher_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('banner')->__('Event Information'));
    }

    protected function _beforeToHtml() {
        $this->addTab('form_section', array(
            'label' => Mage::helper('banner')->__('Gift Voucher Event'),
            'alt' => Mage::helper('banner')->__('Gift Voucher Event'),
            'content' => $this->getLayout()->createBlock('Bilna_Promo_Block_Adminhtml_Giftvoucher_Edit_Tab_Form')->toHtml(),
        ));        
        return parent::_beforeToHtml();
    }

}