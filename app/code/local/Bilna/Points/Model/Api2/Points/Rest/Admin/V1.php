<?php

/**
 * API2 class for points (AW) (admin)
 *
 * @category   Bilna
 * @package    Bilna_Points (custom)
 * @author     Development Team <development@bilna.com>
 */
class Bilna_Points_Model_Api2_Points_Rest_Admin_V1 extends Bilna_Points_Model_Api2_Points_Rest
{
    /**
     * "Minimum reward points balance to be available to redeem" from system config
     */
    const MINIMUM_POINTS_AMOUNT_TO_REDEEM = 'points/general/minimum_points_amount_for_spend';

    protected function _retrieve()
    {
        $customerId = $this->getRequest()->getParam('id');
        $quoteId = $this->getRequest()->getParam('quote_id');
        $customerIdDashboard = $this->getRequest()->getParam('customer_id_dashboard');
        $storeId = 1;

        try{
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getCustomer($customerId);
            $quote = $this->_getQuote($quoteId, $storeId);
            $_summaryForCustomer = Mage::getModel('points/summary')->loadByCustomer($customer);

            $moneyForPoints = Mage::getModel('points/rate')
                ->setCurrentCustomer($customer)
                ->setCurrentWebsite(Mage::getModel('core/store')->load(1)->getWebsite())
                ->loadByDirection(AW_Points_Model_Rate::POINTS_TO_CURRENCY)
                ->exchange($_summaryForCustomer->getPoints());
            $_moneyForPoints = Mage::app()->getStore()->convertPrice($moneyForPoints, true);

            $isAvailableToRedeem = Mage::helper('points')->isAvailableToRedeem($_summaryForCustomer->getPoints());

            $sum = $quote->getData('base_subtotal_with_discount');
            $limitedPoints = Mage::helper('points')->getLimitedPoints($sum, $customer, $storeId);

            $isAvailable =
                $_summaryForCustomer->getPoints()
                && $_moneyForPoints
                && $isAvailableToRedeem
                && $customer->getId();

            $canUseWithCoupon = Mage::helper('points/config')->getCanUseWithCoupon();

        } catch (Mage_Core_Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        }
        
        $minimumpoints = Mage::getStoreConfig(self::MINIMUM_POINTS_AMOUNT_TO_REDEEM, $storeId);
        
        return array(
            'is_available'      => $isAvailable,
            'canusewithcoupon'  => $canUseWithCoupon,
            'moneyforpoints'    => $_moneyForPoints,
            'moneyforpoints_int'=> $moneyForPoints,
            'limited_points'    => $limitedPoints, 
            'minimum_points_amount_for_spend' => Mage::app()->getStore()->convertPrice($minimumpoints, true), 
            'minimum_points_amount_for_spend_int' => $minimumpoints
        );

    }

}