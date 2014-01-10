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
 * @package    AW_Ajaxlogin
 * @version    1.0.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * 
 */
class AW_Ajaxlogin_Block_AuthorizationFormElementButtonsetRecovery extends AW_Ajaxlogin_Block_Template {
    
    /**
     * 
     */
    private $__backtologinbuttonUID      = null;
    private $__showBacktologinbuttonFlag = true;
    
    
    /**
     *
     */
    public function hideForgotpasswordbutton() {
        $this->__showBacktologinbuttonFlag = false;
    }
    
    
    /**
     * 
     */
    public function shouldDisplayBacktologinbutton() {
        return $this->__showBacktologinbuttonFlag;
    }
    
    
    /**
     * 
     */
    public function getBacktologinbuttonHtmlId() {
        if ( !$this->__backtologinbuttonUID ) {
            $this->__backtologinbuttonUID = Mage::helper('ajaxlogin/data')->getUniqueID();
        }
        
        return 'node-uid-' . $this->__backtologinbuttonUID;
    }
}