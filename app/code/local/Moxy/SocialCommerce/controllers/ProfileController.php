<?php

class Moxy_SocialCommerce_ProfileController
extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $username = $this->getRequest()->getParam('u');

        $profiler = Mage::getModel('socialcommerce/profile')->load($username, 'username');

        if ($profiler->getCustomerId()) {

            $profilerCustomerId = $profiler->getCustomerId();
            $customer = Mage::getModel('customer/customer')->load($profilerCustomerId);

            # Check if the user activate her public profile
            if ($profiler->getStatus()) {

                $this->loadLayout();

                $this->_initLayoutMessages('customer/session');

                # Assign profile and customer data
                $block = $this->getLayout()->getBlock('user_content');

                # Assign profile and customer data
                $block->setCustomer($customer);
                $block->setProfiler($profiler);

                # Set page title
                $pageTitle = $this->__('Customer Profile');

                $this->getLayout()->getBlock('head')->setTitle($pageTitle);

                $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');

                $this->renderLayout();

                return;
            }
        }

        # 404 if no user found, or disabled her public profile
        $this->norouteAction();

    }

    public function collectionAction() {

        $wishlistId = $this->getRequest()->getParam('id');

        if ($wishlistId) { # Need to sanitize dan validate

            # Extract the ID
            $wishlistId = explode("-", $wishlistId, 2)[0];

            # Get wishlist
            $wishlist = Mage::getModel('wishlist/wishlist')->load($wishlistId);
            $wishlistCollection = $wishlist->getItemCollection();

            $this->loadLayout();

            $this->_initLayoutMessages('customer/session');

            # Assign data
            $block = $this->getLayout()->getBlock('user_content');
            $block->setWishlistCollection($wishlistCollection);
            $block->setWishlist($wishlist);

            # Set page title
            $pageTitle = $this->__('Collection Detail');

            $this->getLayout()->getBlock('head')->setTitle($pageTitle);

            $this->getLayout()->getBlock('root')->setTemplate('page/1column.phtml');

            $this->renderLayout();

            return;
        }

        # 404 for anything else
        $this->norouteAction();

    }


}

