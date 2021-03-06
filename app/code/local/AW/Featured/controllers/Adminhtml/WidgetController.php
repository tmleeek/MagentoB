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
 * @package    AW_Featured
 * @version    3.5.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Featured_Adminhtml_WidgetController extends Mage_Adminhtml_Controller_Action
{
    public function blockchooserAction()
    {
        $uniqId = $this->getRequest()->getParam('uniq_id');
        $blocksGrid = $this->getLayout()->createBlock(
            'awfeatured/adminhtml_widget_blockchooser', '',
            array('id' => $uniqId)
        );
        $_blockCSS = $this->getLayout()->createBlock('adminhtml/template');
        $_blockCSS->setTemplate('aw_featured/widget/blocks.phtml')
            ->setGridId($uniqId)
        ;
        $this->getResponse()->setBody($blocksGrid->toHtml() . $_blockCSS->toHtml());
    }
}
