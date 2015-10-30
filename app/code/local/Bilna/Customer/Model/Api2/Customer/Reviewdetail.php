<?php
/**
 * Description of Reviewdetail
 *
 * @author Bilna Development Team <development@bilna.com>
 * 
 * @link http://mandagreen.com/showing-all-reviews-and-ratings-on-a-page-in-magento/
 * @link https://wiki.magento.com/display/m1wiki/Using+Magento+1.x+collections
 * @link http://devdocs.magento.com/guides/m1x/magefordev/mage-for-dev-8.html
 * @link https://www.creare.co.uk/blog/magento/magento-product-collection-bible
 * 
 */

class Bilna_Customer_Model_Api2_Customer_Reviewdetail extends Mage_Api2_Model_Resource
{
    protected function getRatingOptionRate($reviewId) 
    {
        $collection = Mage::getModel('rating/rating_option_vote')->getCollection();
        $collection->addFieldToFilter('review_id', $reviewId);

        //to check query log
        //$collection->printLogQuery(true);
        //exit;
        
        $data = $collection->getData();
        
        return $data[0];
    }
    
    /** 
     * @link http://fishpig.co.uk/magento/tutorials/direct-sql-queries/
     * @link http://www.vortexcommerce.com/blog/magento-direct-sql
     * 
     * @param integer $reviewId
     * @param integer $customerId
     * @return array
     */
    protected function getReviewDetail($reviewId, $customerId) 
    {
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');  
        /**
	 * Retrieve our table name
	 */
	$table = $resource->getTableName('review/review_detail');
        $query = 'SELECT * FROM ' . $table . ' WHERE review_id = '
			. (int)$reviewId . ' AND customer_id = '
                        . (int)$customerId. ' LIMIT 1';
        
        return $read->fetchRow($query);
    }
    
    protected function getProductDetail($productId) 
    {
        /** @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        $product = $productHelper->getProduct($productId, Mage::app()->getStore()->getId());
        if (!($product->getId())) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        // check if product belongs to website current
        if (Mage::app()->getStore()->getId()) {
            $isValidWebsite = in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds());
            if (!$isValidWebsite) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
        }
        // Check display settings for customers & guests
        if ($this->getApiUser()->getType() != Mage_Api2_Model_Auth_User_Admin::USER_TYPE) {
            // check if product assigned to any website and can be shown
            if ((!Mage::app()->isSingleStoreMode() && !count($product->getWebsiteIds()))
                || !$productHelper->canShow($product)
            ) {
                $this->_critical(self::RESOURCE_NOT_FOUND);
            }
        }
        
        return $product->getData();
    }

}
