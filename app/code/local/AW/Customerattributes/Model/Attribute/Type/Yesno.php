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
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Customerattributes_Model_Attribute_Type_Yesno extends AW_Customerattributes_Model_Attribute_TypeAbstract
{
    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Grid_Yesno
     */
    protected function _getBackendGridRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Grid_Yesno();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Backend_Form_Yesno
     */
    protected function _getBackendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Backend_Form_Yesno();
    }

    /**
     * @return AW_Customerattributes_Block_Widget_Frontend_Form_Yesno
     */
    protected function _getFrontendFormRenderer()
    {
        return new AW_Customerattributes_Block_Widget_Frontend_Form_Yesno();
    }

    public function getValueType()
    {
        return AW_Customerattributes_Model_Resource_Value::INT_TYPE;
    }

    public function getAvailableOptionArray()
    {
        $helper = Mage::helper('aw_customerattributes');
        return array(
            array('value' => 0, 'label' => $helper->__('---Please Select---')),
            array('value' => 1, 'label' => $helper->__('Yes')),
            array('value' => 2, 'label' => $helper->__('No'))
        );
    }

    /**
     * @param mixed $value
     *
     * @throws Exception
     */
    public function validate($value)
    {
        $helper = Mage::helper('aw_customerattributes');
        $storeId = Mage::app()->getStore()->getId();
        $label = $this->getAttribute()->getLabel($storeId);
        $label = Mage::helper('core')->escapeHtml($label);
        if (strlen(trim($value)) < 1 || $value == 0) {
            if ($this->getAttribute()->getData('validation_rules/is_required')) {
                throw new Exception($helper->__('%s is required', $label));
            }
        }
        $options = $this->getAvailableOptionArray();
        $isFounded = false;
        foreach ($options as $item) {
            if ($item['value'] == $value) {
                $isFounded = true;
                break;
            }
        }
        if (!$isFounded) {
            throw new Exception($helper->__('%s has incorrect selected option', $label));
        }
        return;
    }
}