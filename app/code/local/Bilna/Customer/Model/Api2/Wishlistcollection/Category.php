<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Category
 *
 * @path    app/code/local/Bilna/Customer/Model/Api2/Wishlistcollection/Category.php
 * @author  Bilna Development Team <development@bilna.com>
 */

class Bilna_Customer_Model_Api2_Wishlistcollection_Category extends Bilna_Customer_Model_Api2_Wishlistcollection {
    protected function _getWishlistCollectionListByCategoryId($categoryId) {
        $collection = Mage::getModel('wishlist/wishlist')->getCollection();
        $collection->getSelect()->limit(4);
        $collection->load();

        return $this->_getCollectionListItems($collection);
    }

    protected function _getCollectionListByUrlKey($urlkey) {

    }
    
    protected function _getCollectionListItems($collection) {
        $result = [];
        $totalRecord = $collection->getSize();

        if ($totalRecord > 0) {
            foreach ($collection as $key => $row) {
                $id = $row->getId();
                $name = $row->getName();
                $customerId = $row->getCustomerId();

                $result[$key] = $row->getData();
                $result[$key]['slug'] = $this->_getCollectionSlug($id, $name);
                $result[$key]['avatar'] = $this->_getCollectionAvatar($customerId);
                $result[$key]['gender'] = $this->_getCollectionGender($customerId);
            }
        }

        return $result;
    }

    protected function _getCollectionSlug($id, $name) {
        return $id . '-' . Mage::getModel('catalog/product_url')->formatUrlKey($name);
    }

    protected function _getCollectionAvatar($customerId) {
        $profile = Mage::getModel('socialcommerce/profile')->load($customerId, 'customer_id');

        return $profile->getAvatar();
    }

    protected function _getCollectionGender($customerId) {
        $customer = $this->_loadCustomerById($customerId);

        return $customer->getGender();
    }
}
