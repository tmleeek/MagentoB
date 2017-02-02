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
 * @package    AW_Affiliate
 * @version    1.0.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Affiliate_Model_Transaction_Profit extends Mage_Core_Model_Abstract
{
    private $affiliateId = NULL;
    private $campaignId = NULL;
    private $linkedEntityType = NULL;
    private $linkedEntityId = NULL;
    private $storeId = NULL;
    private $linkedEntityOrder = NULL;
    private $linkedEntityInvoice = NULL;
    private $type = NULL;

    public function _construct($params = array())
    {
        $this->_init('awaffiliate/transaction_profit');
        $this->_initTransaction($params);
    }

    protected function _initTransaction($params = array())
    {
        $this->affiliateId = !empty($params['affiliate_id']) ? $params['affiliate_id'] : $this->getData('affiliate_id');
        $this->campaignId = !empty($params['campaign_id']) ? $params['campaign_id'] : $this->getData('campaign_id');
        $this->linkedEntityType = !empty($params['linked_entity_type']) ? $params['linked_entity_type'] : $this->getData('linked_entity_type');
        $this->linkedEntityId = !empty($params['linked_entity_id']) ? $params['linked_entity_id'] : $this->getData('linked_entity_id');
        $this->storeId = !empty($params['store_id']) ? $params['store_id'] : NULL;
        $this->linkedEntityOrder = isset($params['linked_entity_order']) && !is_scalar($params['linked_entity_order']) ? $params['linked_entity_order'] : $this->getData('linked_entity_order');
        $this->linkedEntityInvoice = isset($params['linked_entity_invoice']) && !is_scalar($params['linked_entity_invoice']) ? $params['linked_entity_invoice'] : $this->getData('linked_entity_invoice');
        $this->type = !empty($params['type']) ? $params['type'] : $this->getData('type');
    }

    public function createTransaction()
    {
        if ($this->_isValidForCreation()) {
            /** @var $campaign AW_Affiliate_Model_Campaign */
            $campaign = $this->getCampaign();
            $affiliate = $this->getAffiliate();
            $profitModel = $campaign->getProfitModel();

            if (!$this->_canBeApplied($profitModel)) return $this;

            $rate = $profitModel->getRateForAffiliate($affiliate);
            if (is_null($rate)) {
                Mage::throwException($this->__('Rate cannot be calculated'));
            }
            $this->setData('rate', $rate);

            $attractedAmount = $this->_getAttractedAmount();
            if (is_null($attractedAmount)) {
                Mage::throwException($this->__('Attracted amount cannot be calculated'));
            }
            $this->setData('attracted_amount', $attractedAmount);

            $amount = $campaign->getProfitModel()->getAmountForAffiliate($affiliate, $attractedAmount);
            $this->setData('amount', $amount);

            $currencyCode = $this->_getCurrencyCode();
            if (is_null($currencyCode)) {
                Mage::throwException($this->__('Currency undefined'));
            }
            $this->setData('currency_code', $currencyCode);
            $this->save();
        } else {
            //Mage::throwException($this->__('Not valid for create transaction'));
        }
        return $this;
    }

    protected function _canBeApplied($profitModel)
    {
        switch ($profitModel->getType()) {
            case AW_Affiliate_Model_Source_Profit_Type::TIER_CUR:
            case AW_Affiliate_Model_Source_Profit_Type::FIXED_CUR:
                /** @var $order Mage_Sales_Model_Order */
                if ($order = $this->linkedEntityOrder) {
                    return $order->getInvoiceCollection()->getSize() == 0;
                } else {
                    return false;
                }
                break;
        }
        return true;
    }

    public function getAffiliate()
    {
        if (!is_null($this->affiliateId) && intval($this->affiliateId) > 0) {
            $_affiliate = Mage::getModel('awaffiliate/affiliate')->load($this->affiliateId);
            if (!is_null($_affiliate->getId())) {
                return $_affiliate;
            }
        }
        return null;
    }

    public function getCampaign()
    {
        if (!is_null($this->campaignId) && intval($this->campaignId) > 0) {
            $_campaign = Mage::getModel('awaffiliate/campaign')->load($this->campaignId);
            if (!is_null($_campaign->getId())) {
                return $_campaign;
            }
        }
        return null;
    }

    public function getLinkedEntity()
    {
        switch ($this->linkedEntityType) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM:
                $entityObject = $this->linkedEntityInvoice;
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM :
                $entityObject = Mage::getModel('sales/order_item')->load($this->linkedEntityId);
                break;
            default:
                return null;
        }
        return (isset($entityObject) && $entityObject) ? $entityObject : null;
    }

    protected function _isValidForCreation()
    {
        if (is_null($this->getAffiliate())) {
            return false;
        }
        if (is_null($this->getCampaign())) {
            return false;
        }
        if (is_null($this->getLinkedEntity())) {
            return false;
        }
        $type = Mage::getModel('awaffiliate/source_transaction_profit_type')->getOption($this->type);
        if ($type === FALSE) {
            return false;
        }
        return true;
    }

    protected function _getAttractedAmount()
    {
        $linkedEntity = $this->getLinkedEntity();
        if (!$linkedEntity) {
            return null;
        }
        $storeId = is_null($this->storeId) ? $linkedEntity->getData('store_id') : $this->storeId;
        $subtotalInclTax = $linkedEntity->getData('subtotal_incl_tax') ?: $linkedEntity->getSubtotalIncTax();
        $subtotal = $linkedEntity->getData('subtotal') ?: $linkedEntity->getSubtotal();
        $discountAmount = $linkedEntity->getData('discount_amount') ?: $linkedEntity->getDiscountAmount();
        $rowTotalInclTax = $linkedEntity->getData('row_total_incl_tax') ?: $linkedEntity->getRowTotalInclTax();
        $rowTotal = $linkedEntity->getData('row_total') ?: $linkedEntity->getRowTotal();

        $amount = null;
        switch ($this->linkedEntityType) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM:
                if (Mage::helper('awaffiliate/config')->isConsiderTax($storeId)) {
                    $amount = $subtotalInclTax;
                } else {
                    $amount = $subtotal;
                }
                if ($discountAmount) {
                    $amount = max(0, $amount - abs($discountAmount));
                }
                break;
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM :
                if (Mage::helper('awaffiliate/config')->isConsiderTax($storeId)) {
                    $amount = $rowTotalInclTax;
                } else {
                    $amount = $rowTotal;
                }
                break;
            default:
                return null;
        }
        return $amount;
    }

    protected function _getCurrencyCode()
    {
        $linkedEntity = $this->getLinkedEntity();
        if (!$linkedEntity) {
            return null;
        }
        $code = null;
        switch ($this->linkedEntityType) {
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::INVOICE_ITEM:
            case AW_Affiliate_Model_Source_Transaction_Profit_Linked::ORDER_ITEM:
                $code = $linkedEntity->getOrder()->getData('base_currency_code');
                break;
            default:
                return null;
        }
        return $code;
    }

    protected function _afterLoad()
    {
        if (is_string($this->getData('withdrawal_transaction_ids')))
            $this->setData('withdrawal_transaction_ids', @explode(',', $this->getData('withdrawal_transaction_ids')));
        return parent::_afterLoad();
    }

    protected function _beforeSave()
    {
        $res = parent::_beforeSave();
        if (is_null($this->getData('rate'))) {
            Mage::throwException(Mage::helper('awaffiliate')->__('Rate is not specified'));
        }
        if (is_null($this->getData('currency_code'))) {
            Mage::throwException(Mage::helper('awaffiliate')->__('Currency code is not specified'));
        }
        if ($this->getData('withdrawal_transaction_ids') !== null && is_array($this->getData('withdrawal_transaction_ids')))
            $this->setData('withdrawal_transaction_ids', @implode(',', $this->getData('withdrawal_transaction_ids')));
        return $res;
    }

    protected function _afterSave()
    {
        $res = parent::_afterSave();
        $affiliate = $this->getAffiliate();
        if (!is_null($affiliate)) {
            $affiliate->recollectBalances();
        }
        return $res;
    }
}
