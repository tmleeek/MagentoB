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
 * This package designed for Magento community edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento community edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Points
 * @version    1.6.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product {

    protected function _addSpecialAttributes(array &$attributes) {
        parent::_addSpecialAttributes($attributes);
        $helper = Mage::helper('points');
        $attributes['quote_item_qty'] = $helper->__('Quantity in cart');
        $attributes['quote_item_price'] = $helper->__('Price in cart');
        $attributes['quote_item_row_total'] = $helper->__('Row total in cart');
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object) {
        $product = Mage::getModel('catalog/product')
                ->load($object->getProductId())
                ->setQuoteItemQty($object->getQty())
                ->setQuoteItemPrice($object->getPrice())
                ->setQuoteItemRowTotal($object->getRowTotal());

        return parent::validate($product);
    }

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validateOnCheck(Varien_Object $object, $pointToBeRewarded) {
        $product = Mage::getModel('catalog/product')
                ->load($object->getProductId())
                ->setQuoteItemQty($object->getQty())
                ->setQuoteItemPrice($object->getPrice())
                ->setQuoteItemRowTotal($object->getRowTotal());

        $productPoint = array();
        if(parent::validate($product) == true){
        	if($pointToBeRewarded->getApplyOn() == "by_percent" || $pointToBeRewarded->getApplyOn() == "by_percent_product"){
        		$productPoint['productId'] = $object->getProductId();
        		$productPoint['points'] = ($pointToBeRewarded->getPointsChange() * $object->getPrice() / 100) * $object->getQty();

        		return $productPoint;
        	}else{
        		$productPoint['productId'] = $object->getProductId();
        		$productPoint['points'] = $pointToBeRewarded->getPointsChange() * $object->getQty();

        		return $productPoint;
        	}
        }else{
        	return false;
        }
    }
}
