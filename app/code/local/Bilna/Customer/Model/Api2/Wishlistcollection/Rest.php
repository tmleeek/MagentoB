<?php
/**
 * Description of Bilna_Customer_Model_Api2_Wishlistcollection_Rest
 *
 * @author Bilna Development Team <development@bilna.com>
 */
abstract class Bilna_Customer_Model_Api2_Wishlistcollection_Rest extends Bilna_Customer_Model_Api2_Wishlistcollection
{
    /** 
     * 
     * Create new collection by customer with post method.
     * 
     * bodyParams:
     * {"name":"A1", "desc":"A1", "image_url":"", "visibility":"on", "username":"khairulazami", "preset_image":""}
     * 
     */
    protected function _create(array $data)
    {
        $data['customer_id'] = (int)$this->getRequest()->getParam('customer_id');
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = $this->_loadCustomerById($data['customer_id']);
        
        if ($customer->getId()) {
            try {
                //if user add new item from product list frontend
                //just add addnewitem in bodyparam
                if(isset($data['addnewitem'])) {
                    $data['customer_id'] = $customer->getId();
                    $this->addNewWishlistCollectionItem($data);
                    return TRUE;
                }
                die;
                if (!isset($data['name']) || $data['name'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide name.'));
                } 
                if (!isset($data['desc']) || $data['desc'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide description.'));
                } 
                if (!isset($data['username']) || $data['username'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide username.'));
                }
                
                $this->createNewCollection($data);
                
            } catch (Exception $exception) {
                $this->_error($exception->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        } else {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('No customer account specified.'));
        }
    }

    protected function _retrieve()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $customer = $this->_loadCustomerById($customerId);
        $data = [];
        if ($customer->getId()) {
            try {
                $collectionId = (int)$this->getRequest()->getParam('collection_id');
                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                if($wishlist){
                    $data = $wishlist;
                }
            } catch (Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        } else {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('No customer account specified.'));
        }
        
        return $data;
    }

    protected function _retrieveCollection() 
    {
        $collectionForRetrieve = $this->_getCollectionForRetrieve();
        if (!$collectionForRetrieve) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        return $collectionForRetrieve;
    }
    
    protected function _getCollectionForRetrieve()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');        
        $collection = $this->getWishlistCollection($customerId);
        
        return $collection;
    }
    
    /** 
     * Update collection by customer id and collection id.
     * 
     * bodyParams:
     * {"name":"TESTBARU","desc":"TESTBARU","image_url":"","collection_id":"","collection_id2":"35813","username":"m-khairul-azami-s-kom","preset_image":"","visibility":"on"}
     * 
     */
    protected function _update(array $data)
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $customer = $this->_loadCustomerById($customerId);

        if ($customer->getId()) {
            # Check if request is a post request
            try {

                $collectionId = (int)$this->getRequest()->getParam('collection_id');
                
                if (!isset($data['name']) || $data['name'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide name.'));
                } 
                if (!isset($data['desc']) || $data['desc'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide description.'));
                } 
                if (!isset($data['username']) || $data['username'] == "") {
                    throw Mage::exception('Mage_Core', Mage::helper('customer')->__('Please provide username.'));
                }

                # Populate sent data, validate & sanitize

                if(!$collectionId) $collectionId = $data['collection_id2'];
                $username = $data['username'];

                $desc = (isset($data['desc'])) ? $data['desc'] : null;
                $title = (isset($data['colname'])) ? $data['colname'] : null;

                $visibility = (isset($data['visibility'])) ? true : false;

                # TODO need to check if submitted collection is belong to logged user.

                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                $wishlist->setVisibility($visibility);

                #Get wishlist name
                $wlname = $wishlist->getName();

                # Check the cover image upload and update description
                $cover = Mage::helper('socialcommerce')->processCover();

                if ($cover) {
                    //$wishlist->setCloudCover($cover);
                    $wishlist->setCover($cover);
                }

                if ($preset_image = $_POST['preset_image']) {
                    $wishlist->setCover($preset_image);
                }

                $descupdate = $wishlist->setDesc($desc);

                if ($cover || $descupdate) {
                    $wishlist->save();
                }

            } catch (Exception $e) {
                $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            }
        } else {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('No customer account specified.'));
        }
    }

    /**
     * Delete collection
     */
    protected function _delete()
    {
        $customerId = (int)$this->getRequest()->getParam('customer_id');
        $customer = $this->_loadCustomerById($customerId);
        
        if ($customer->getId()) {
            $collectionId = (int)$this->getRequest()->getParam('collection_id');
            
            try {
                //delete item collection
                $username = $this->getRequest()->getParam('user');
                $proid = $this->getRequest()->getParam('proid');
                $wlid = $this->getRequest()->getParam('wlid');
                
                if($username != NULL && $proid != NULL && $wlid != NULL) {
                    $this->deleteWishlistCollectionItem();
                    return TRUE;
                }
                $wishlist = Mage::getModel('wishlist/wishlist')->load($collectionId);
                if($wishlist){
                    $viewupdate = $wishlist->setCustomerIdDel(0);
                }
            } catch (Mage_Core_Exception $e) {
                $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
            } catch (Exception $e) {
                $this->_critical(self::RESOURCE_INTERNAL_ERROR);
            }
        } else {
            throw Mage::exception('Mage_Core', Mage::helper('customer')->__('No customer account specified.'));
        }
    }
}
