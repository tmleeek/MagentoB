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
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Customerattributes
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Customerattributes_CustomerController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();
        $loginUrl = Mage::helper('customer')->getLoginUrl();
        if (!Mage::getSingleton('customer/session')->authenticate($this, $loginUrl)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function downloadAttachmentAction()
    {
        $attributeCode = $this->getRequest()->getParam('attribute_code', '');
        $attribute = Mage::getModel('aw_customerattributes/attribute')->load($attributeCode, 'code');
        $customer = Mage::helper('customer')->getCustomer();
        if (!$attribute->getId() || !$customer->getId()) {
            return $this->norouteAction();
        }

        $file = Mage::helper('aw_customerattributes/image')->viewFile($attribute, $customer);

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Content-type', $file['header']['content_type'], true)
            ->setHeader('Content-Length', $file['header']['content_length'])
            ->setHeader('Last-Modified', $file['header']['content_modified']);
        if (isset($file['filename'])) {
            $this->getResponse()->setHeader(
                'Content-Disposition', 'attachment; filename="' . $file['filename'] . '"', true
            );
        }
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        while (false !== ($buffer = $file['content_stream']->streamRead())) {
            echo $buffer;
        }
        exit();
    }
}