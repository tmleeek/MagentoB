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


class Bilna_Wrappinggiftevent_Model_Total_Creditmemo_Wrapping extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{ 

    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {       
        $order = $creditmemo->getOrder();
        
        $wrappingPrice = $order->getWrappingPrice();
		$baseWrappingPrice = $order->getWrappingPrice();

		if ($wrappingPrice > 0) {
			$creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $wrappingPrice);
			$creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseWrappingPrice);

			$creditmemo->setWrappingPrice($wrappingPrice);
			$creditmemo->setBaseWrappingPrice($baseWrappingPrice);
		}
		return $this;
    } 
}