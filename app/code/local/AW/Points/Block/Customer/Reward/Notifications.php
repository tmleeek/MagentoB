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
 * @package    AW_Points
 * @version    1.6.1
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Points_Block_Customer_Reward_Notifications extends Mage_Core_Block_Template {
    const SUBSCRIPTION_ACTION = "subscribe";

    public function getIsSubscribed() {
        $summary = Mage::getModel('points/summary')
                ->loadByCustomer(
                Mage::getSingleton('customer/session')
                ->getCustomer()
        );
        return (bool) (int) $summary->getBalanceUpdateNotification();
    }

    public function getIsSubscribedToExpireNotification() {
        $summary = Mage::getModel('points/summary')
                ->loadByCustomer(
                Mage::getSingleton('customer/session')
                ->getCustomer()
        );
        return (bool) $summary->getData('points_expiration_notification');
    }

    public function getAction() {
        return self::SUBSCRIPTION_ACTION;
    }

    public function isEnabled() {
        return Mage::helper('points/config')->getIsEnabledNotifications();
    }

    protected function _toHtml() {

        $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_14;

        if (Mage::helper('points')->magentoLess14())
            $magentoVersionTag = AW_Points_Helper_Data::MAGENTO_VERSION_13;

        $this->setTemplate("aw_points/customer/" . $magentoVersionTag . "/reward/notifications.phtml");

        $html = parent::_toHtml();
        return $html;
    }

}

?>
