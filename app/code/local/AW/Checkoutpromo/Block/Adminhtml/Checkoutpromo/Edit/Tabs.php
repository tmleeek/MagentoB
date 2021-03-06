<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Checkoutpromo
 * @version    1.2.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Checkoutpromo_Block_Adminhtml_Checkoutpromo_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('checkoutpromo_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Checkout Promo Rule'));
    }

    protected function _beforeToHtml() {
        $this->addTab('main_section', array(
            'label' => $this->__('Rule Information'),
            'title' => $this->__('Rule Information'),
            'content' => $this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit_tab_main')->toHtml(),
            'active' => true
        ));

        $this->addTab('conditions_section', array(
            'label' => $this->__('Conditions'),
            'title' => $this->__('Conditions'),
            'content' => $this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit_tab_conditions')->toHtml(),
        ));

        $this->addTab('processing_section', array(
            'label' => $this->__('Actions'),
            'title' => $this->__('Actions'),
            'content' => $this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit_tab_processing')->toHtml(),
        ));

        $this->addTab('mss', array(
            'label' => $this->__('Market Segmentation Suite'),
            'title' => $this->__('Market Segmentation Suite'),
            'content' => $this->getLayout()->createBlock('checkoutpromo/adminhtml_checkoutpromo_edit_tab_mss')->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
