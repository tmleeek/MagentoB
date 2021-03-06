<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Header extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('page/html/header.phtml');
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
    }

    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }

    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }

    /**
     * Retrieve page welcome message
     *
     * @deprecated after 1.7.0.2
     * @see Mage_Page_Block_Html_Welcome
     * @return mixed
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->escapeHtml(Mage::getSingleton('customer/session')->getCustomer()->getName()));
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }
    
    public $_categoryPath;
    public function getCategoryActive() {
        $path = array ();
        $categoryActive = array ();
        
        if (Mage::getSingleton('cms/page')->getIdentifier() == 'home' && Mage::app()->getFrontController()->getRequest()->getRouteName() == 'cms') {
            return false;
        }
            
        if ($category = $this->getCategory()) {
            $pathInStore = $category->getPathInStore();
            $pathIds = array_reverse(explode(',', $pathInStore));
            $categories = $category->getParentCategories();
            $x = 0;

            // add category path breadcrumb
            foreach ($pathIds as $categoryId) {
                if (isset ($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                    $path['category' . $categoryId] = array (
                        'id' => $categoryId,
                        'group' => $this->getCategoryGroup($x)
                    );
                    $x++;
                }
            }
            
            $this->_categoryPath = $path;
        }
        
        if (count($this->_categoryPath) > 0) {
            foreach ($this->_categoryPath as $categoryPath) {
                $categoryActive[$categoryPath['group']] = $categoryPath['id'];
            }
        }
        
        return $categoryActive;
    }
    
    public function getCategoryGroup($categoryLevel) {
        $group = array (
            0 => 'category',
            1 => 'subcategory',
            2 => 'subsubcategory'
        );
        
        return $group[$categoryLevel];
    }

    public function getCategory() {
        return Mage::registry('current_category');
    }
    
    public function getProduct() {
        return Mage::registry('current_product');
    }
    
    public function _isCategoryLink($categoryId) {
        if ($this->getProduct()) {
            return true;
        }
        
        if ($categoryId != $this->getCategory()->getId()) {
            return true;
        }
        
        return false;
    }
}
