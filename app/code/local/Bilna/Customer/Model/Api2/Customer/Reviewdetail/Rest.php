<?php
/**
 * Description of Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Customer_Reviewdetail_Rest extends Bilna_Customer_Model_Api2_Customer_Reviewdetail
{/**
     *
     */
    protected function _getCustomer($customerId)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')
            ->load($customerId);
        if (!$customer->getId()) {
            throw Mage::throwException('Customer Not Exists');
        }
        return $customerId;
    }

    /**
     * Retrieve detail of review rating from table: 
     * - review detail
     * - rating_option_vote
     *
     * @return array
     */
    protected function _retrieve()
    {
        $reviewId = $this->getRequest()->getParam('review_id');
        $customerId = $this->_getCustomer($this->getRequest()->getParam('customer_id'));
        
        $reviewRating = $this->getRatingOptionRate($reviewId);
        $reviewDetail = $this->getReviewDetail($reviewId, $customerId);
        
        $productId = $reviewRating['entity_pk_value'];
        
        $product = $this->getProductDetail($productId);
        
        return array(
            'review_rating' => $reviewRating, 
            'review_detail' => $reviewDetail,
            'product' => $product
        );
    }
}
