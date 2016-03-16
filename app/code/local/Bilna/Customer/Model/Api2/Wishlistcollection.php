<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection
 *
 * @author Bilna Development Team <development@bilna.com>
 */
class Bilna_Customer_Model_Api2_Wishlistcollection extends Mage_Api2_Model_Resource
{    
    const DEFAULT_STORE_ID = 1;

    public function _construct()
    {
        Mage::app()->getStore()->setStoreId(self::DEFAULT_STORE_ID);
    }

    public function createNewCollection($data)
    {
        $visibility = ($data['visibility'] === 'on' ? 1 : 0);
        $wishlistName = (isset($data['name'])) ? $data['name'] : null;
        $username = (isset($data['username'])) ? $data['username'] : null;
        $desc = (isset($data['desc'])) ? $data['desc'] : null;        
        $customerId = $data['customer_id'];

        try {
            $this->_createNewCollection($customerId, $wishlistName, $visibility, $desc);
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        
        return TRUE;
    }

    protected function _createNewCollection($customerId, $wishlistName, $visibility, $desc)
    {
        $wishlist = Mage::getModel('wishlist/wishlist');

        $cover = Mage::helper('socialcommerce')->processCover();
        
        $wishlist->setCustomerId($customerId)
            ->setName($wishlistName)
            ->setVisibility($visibility)
            ->setDesc($desc)
            ->generateSharingCode()
            //->setCloudCover($cover)
            ->setCover($cover)
            ->save();

        if ($preset_image = $_POST['preset_image']) {
            $wishlist->setCover($preset_image);
            $wishlist->save();
        }
        
        return $wishlist;
    }
    
    public function getWishlistCollection($customerId = null) 
    {
        $username = $this->_getUsername($customerId);        
        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');
        $result = [];
        
        if ($profiler->getCustomerId()) {

            $profilerCustomerId = $profiler->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($profilerCustomerId);
            
            # Get wishlist collection
            $wishlistCollection = Mage::getModel('wishlist/wishlist')->getCollection();
            $wishlistCollection->addFieldToFilter('customer_id', $customer->getId()); 
            $hasCollection = $wishlistCollection->count() < 1 ? false : true;
            
            # Check if the user activate her public profile
            if ($profiler->getStatus()) {
                $result['profile'] = $profiler;
            }
            
            //still not working to get collection
            /*if ($wishlistCollection && $hasCollection) {
                foreach($wishlistCollection as $key => $value) {
                    $result['wishlist_collection'][$key] = $value->getData();
                }
            }
            
            return $result;*/
            
            return $profiler;
        }
        
        return FALSE;
    }

    /**
     * Load customer by id
     *
     * @param int $id
     * @throws Mage_Api2_Exception
     * @return Mage_Customer_Model_Customer
     */
    protected function _loadCustomerById($id)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        
        $customer = Mage::getModel('customer/customer')->load($id);
        
        if (!$customer->getId()) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $customer;
    } 
    
    protected function _getUsername($customerId = null) 
    {
        $customer = $this->_loadCustomerById($customerId);
        $customerData = $customer->getData();
        
        $username = null;

        if (!isset($customerData['entity_id'])) {
            $this->_critical('No customer account specified.');
        }

        $customerProfile = Mage::getModel('socialcommerce/profile')->load($customerData['entity_id'], 'customer_id');
        $customerProfileData = $customerProfile->getData();

        if (!isset($customerProfileData['entity_id'])) {
            $username = $this->createTemporaryProfile($customerId);
        } else {
            $username = $customerProfileData['username'];
        }
        
        return $username;
    }
    
    /** 
     * Add new item into collection.
     * 
     * bodyParams:
     * {"wishlist_id":"35824","name":"","desc":"","collection_id":"","image_url":"","visibility":"on","preset_image":"","product_id":"62978"}
     * 
     */
    public function addNewWishlistCollectionItem($data = array()) 
    {
        $customer = $this->_loadCustomerById($data['customer_id']);
        
        $wishlistId = (isset($data['wishlist_id'])) ? $data['wishlist_id'] : null;
        $itemDescription = (isset($data['item_description'])) ? $data['item_description'] : null;
        $productId = (isset($data['product_id'])) ? $data['product_id'] : null;
        $wishlistName = (isset($data['name'])) ? $data['name'] : null;
        $wishlistDescription = (isset($data['desc'])) ? $data['desc'] : null;
        unset($data['addnewitem']);
        unset($data['customer_id']);
        try {

            # She want to create a new collection first
            if ($wishlistName) {
                $visibility = ($data['visibility'] === 'on' ? 1 : 0);
                $wishlist = $this->_createNewCollection($customer->getId(), $wishlistName, $visibility, $wishlistDescription);
            } else {
                $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistId);
            }
            
            $product = Mage::getModel('catalog/product')->load($productId);
            
            $buyRequest = new Varien_Object(array(
                //'description' => $itemDescription,
            ));
            
            $item = Mage::getModel('wishlist/item');
            $item->setProductId($product->getId())
                ->setWishlistId($wishlist->getId())
                ->setAddedAt(now())
                ->setStoreId(self::DEFAULT_STORE_ID)
                ->setOptions($product->getCustomOptions())
                ->setProduct($product)
                ->setQty(1)
                ->save();
            
        } catch (Exception $e) {
            $this->_critical($e->getMessage());
        }
        
        return $wishlist->getData();
    }
    
    /**
     * Delete item collection bu request params.
     * 
     * paramters:
     * user=m-khairul-azami-s-kom&wlid=35823&proid=62978&url=/
     */
    public function deleteWishlistCollectionItem()
    {
        # Get value from query string
        if ($this->getRequest()->getParam('user')) {

            try {

                # Populate sent data, validate & sanitize

                $customerId = $this->getRequest()->getParam('customer_id');

                $username = $this->getRequest()->getParam('user');
                $proid = $this->getRequest()->getParam('proid');
                $wlid = $this->getRequest()->getParam('wlid');

                $wishlist = Mage::getModel('wishlist/wishlist')->load($wlid);
                $name = $wishlist->getName();
                $urlname = Mage::getModel('catalog/product_url')->formatUrlKey($name);
                
                $w = Mage::getSingleton('core/resource')->getConnection('core_write');
                $result = $w->query('DELETE FROM wishlist_item WHERE wishlist_id ='.$wlid.' and product_id ='.$proid);
                
                return $result;
                
            } catch (Exception $e) {
                $this->_critical($e->getMessage());
            }
        }
        
        return FALSE;
    }
    
    public function createTemporaryProfile($customerId = null) 
    {

        $customer = $this->_loadCustomerById($customerId);

        # Temporary username
        $username = Mage::getModel('catalog/product_url')->formatUrlKey($customer->getName());
        $profile = Mage::getModel('socialcommerce/profile')->load($username, 'username')->getData();

        # If username exists, improvise
        if ($profile) {

            for ($i = 1; $i < 101; $i++) {
                $slug = $username . '-' . substr(uniqid(), 7);
                $profile = Mage::getModel('socialcommerce/profile')->load($slug, 'username')->getData();

                if (empty($profile)) {
                    $username = $slug;
                    break;
                }
            }
        }

        # Create new customer profile
        $profile = Mage::getModel('socialcommerce/profile');

        # Assign data
        $profile->setCustomerId($customer->getId());
        $profile->setStatus(1);
        $profile->setWishlist(1);
        $profile->setTemporary(1);
        $profile->setUsername($username);

        $profile->save();

        return $username;

    }
}
