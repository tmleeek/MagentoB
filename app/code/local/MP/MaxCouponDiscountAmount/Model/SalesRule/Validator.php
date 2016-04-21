<?php

/**
 * @category    MP
 * @package     MP_MaxCouponDiscountAmount
 * @copyright   MagePhobia (http://www.magephobia.com)
 */
class MP_MaxCouponDiscountAmount_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{
    protected $_maxDiscountAmount = null;

    protected $_subTotals = null;

    protected $_countItems = null;

    protected $_sumDiscount = null;

    protected $_countForeach = null;

    protected $_sumMaxDiscount = array();

    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        $quote      = $item->getQuote();
        $address    = $this->_getAddress($item);
        $itemPrice              = $this->_getItemPrice($item);
        $baseItemPrice          = $this->_getItemBasePrice($item);
        $itemOriginalPrice      = $this->_getItemOriginalPrice($item);
        $baseItemOriginalPrice  = $this->_getItemBaseOriginalPrice($item);

        if ($itemPrice < 0) {
            return $this;
        }

        $appliedRuleIds = array();
        $this->_stopFurtherRules = false;
        foreach ($this->_getRules() as $rule) {

            /*echo "RULE NAME : " . $rule->getName() . "<br />";*/

            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                // check whether this rule will stop further rules processing
                if ($rule->getStopRulesProcessing()) {
                    $this->_stopFurtherRules = true;
                    break;
                }
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                // check whether this rule will stop further rules processing
                if ($rule->getStopRulesProcessing()) {
                    $this->_stopFurtherRules = true;
                    break;
                }
                continue;
            }

            $qty = $this->_getItemQty($item, $rule);
            $rulePercent = min(100, $rule->getDiscountAmount());

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            //discount for original price
            $originalDiscountAmount = 0;
            $baseOriginalDiscountAmount = 0;

            switch ($rule->getSimpleAction()) {
                case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                //no break;
                case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $_rulePct = $rulePercent/100;
                    $discountAmount    = ($qty * $itemPrice - $item->getDiscountAmount()) * $_rulePct;
                    $baseDiscountAmount = ($qty * $baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct;
                    //get discount for original price
                    $originalDiscountAmount    = ($qty * $itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct;
                    $baseOriginalDiscountAmount =
                        ($qty * $baseItemOriginalPrice - $item->getDiscountAmount()) * $_rulePct;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;
                case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty * ($itemPrice-$quoteAmount);
                    $baseDiscountAmount = $qty * ($baseItemPrice-$rule->getDiscountAmount());
                    //get discount for original price
                    $originalDiscountAmount    = $qty * ($itemOriginalPrice-$quoteAmount);
                    $baseOriginalDiscountAmount = $qty * ($baseItemOriginalPrice-$rule->getDiscountAmount());
                    break;

                case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $qty * $quoteAmount;
                    $baseDiscountAmount = $qty * $rule->getDiscountAmount();
                    break;

                case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
                case MP_MaxCouponDiscountAmount_Model_Rule::CART_RANDOM_ACTION:
                    if (empty($this->_rulesItemTotals[$rule->getId()])) {
                        Mage::throwException(Mage::helper('salesrule')->__('Item totals are not set for rule.'));
                    }

                    /**
                     * prevent applying whole cart discount for every shipping order, but only for first order
                     */
                    if ($quote->getIsMultiShipping()) {
                        $usedForAddressId = $this->getCartFixedRuleUsedForAddress($rule->getId());
                        if ($usedForAddressId && $usedForAddressId != $address->getId()) {
                            break;
                        } else {
                            $this->setCartFixedRuleUsedForAddress($rule->getId(), $address->getId());
                        }
                    }
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        if (MP_MaxCouponDiscountAmount_Model_Rule::CART_RANDOM_ACTION == $rule->getSimpleAction()) {

                            // set discount code to modulo 100 of the quote id
                            $cartRules[$rule->getId()] = $quote->getId() % 100;
                        } else {
                            $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                        }
                    }

                    if ($cartRules[$rule->getId()] > 0) {
                        if ($this->_rulesItemTotals[$rule->getId()]['items_count'] <= 1) {
                            $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                            $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
                        } else {
                            $discountRate = $baseItemPrice * $qty /
                                $this->_rulesItemTotals[$rule->getId()]['base_items_price'];
                            $maximumItemDiscount = $rule->getDiscountAmount() * $discountRate;
                            $quoteAmount = $quote->getStore()->convertPrice($maximumItemDiscount);

                            $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                            $this->_rulesItemTotals[$rule->getId()]['items_count']--;
                        }

                        $discountAmount = min($itemPrice * $qty, $quoteAmount);
                        $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);

                        //get discount for original price
                        $originalDiscountAmount = min($itemOriginalPrice * $qty, $quoteAmount);
                        $baseOriginalDiscountAmount = $quote->getStore()->roundPrice($baseItemOriginalPrice);

                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);

                    break;

                case Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION:
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y > $x) {
                        break;
                    }
                    $buyAndDiscountQty = $x + $y;

                    $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
                    $freeQty  = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

                    $discountQty = $fullRuleQtyPeriod * $y;
                    if ($freeQty > $x) {
                        $discountQty += $freeQty - $x;
                    }

                    $discountAmount    = $discountQty * $itemPrice;
                    $baseDiscountAmount = $discountQty * $baseItemPrice;
                    //get discount for original price
                    $originalDiscountAmount    = $discountQty * $itemOriginalPrice;
                    $baseOriginalDiscountAmount = $discountQty * $baseItemOriginalPrice;
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            ));

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $percentKey = $item->getDiscountPercent();
            /**
             * Process "delta" rounding
             */
            if ($percentKey) {
                $delta      = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
                $baseDelta  = isset($this->_baseRoundingDeltas[$percentKey])
                    ? $this->_baseRoundingDeltas[$percentKey]
                    : 0;
                $discountAmount += $delta;
                $baseDiscountAmount += $baseDelta;

                $this->_roundingDeltas[$percentKey]     = $discountAmount -
                    $quote->getStore()->roundPrice($discountAmount);
                $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount -
                    $quote->getStore()->roundPrice($baseDiscountAmount);
                $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            } else {
                $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            }

            /**
             * We can't use row total here because row total not include tax
             * Discount can be applied on price included tax
             */

            $itemDiscountAmount = $item->getDiscountAmount();
            $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

            $discountAmount     = min($itemDiscountAmount + $discountAmount, $itemPrice * $qty);
            $baseDiscountAmount = min($itemBaseDiscountAmount + $baseDiscountAmount, $baseItemPrice * $qty);

            $this->_maxDiscountAmount = $rule->getMaxDiscountAmount();

            /*
            echo "SUM DISCOUNT : " . $this->_sumDiscount . "<br />";
            echo "MAX DISCOUNT AMOUNT : " . $this->_maxDiscountAmount . "<br />";
            */

            if ($this->_maxDiscountAmount != 0 && $this->_sumDiscount >= $this->_maxDiscountAmount) {

                $discount = $this->_getDiscountSum($itemPrice, $qty);

                /*echo "DISCOUNT : " . $discount . "<br />";*/

                $item->setDiscountAmount($discount);
                $item->setBaseDiscountAmount($discount);

                $item->setOriginalDiscountAmount($discount);
                $item->setBaseOriginalDiscountAmount($discount);

                $this->_addNoticeMaxCoupon();
            } else {
                $item->setDiscountAmount($discountAmount);
                $item->setBaseDiscountAmount($baseDiscountAmount);

                $item->setOriginalDiscountAmount($discountAmount);
                $item->setBaseOriginalDiscountAmount($baseDiscountAmount);
            }

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            $this->_maintainAddressCouponCode($address, $rule);
            $this->_addDiscountDescription($address, $rule);

            if ($rule->getStopRulesProcessing()) {
                $this->_stopFurtherRules = true;
                break;
            }
        }

        $item->setAppliedRuleIds(join(',',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }

    public function setDataDiscount($maxDiscountAmount, $subTotals, $countItems, $sumDiscount)
    {
        $this->_maxDiscountAmount = $maxDiscountAmount;
        $this->_subTotals = $subTotals;
        $this->_countItems = $countItems;
        $this->_sumDiscount = $sumDiscount;
    }

    public function countForeach($count)
    {
        $this->_countForeach = $count;
    }

    protected function _getDiscountSum($itemPrice, $qty)
    {
        if ($this->_countForeach == $this->_countItems) {
            $percent = round($this->_maxDiscountAmount - array_sum($this->_sumMaxDiscount), 4);
            $discount = $percent;
        } else {
            $percent = round((100 * $itemPrice * $qty) / $this->_subTotals, 4);
            $discount = round(($this->_maxDiscountAmount / 100) * $percent, 4);
            $this->_sumMaxDiscount[] = $discount;
        }

        return $discount;
    }

    protected function _addNoticeMaxCoupon()
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        if (!$checkoutSession->getDoNotShowMaxDiscountAmountNotice()) {
            Mage::getSingleton('core/session')->addNotice('Maximum discount amount of '
                . Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol()
                . number_format($this->_maxDiscountAmount, 2) . ' reached.');
            $checkoutSession->setDoNotShowMaxDiscountAmountNotice(true);
        }
    }

    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_SalesRule_Model_Validator
     */
    public function initTotals($items, Mage_Sales_Model_Quote_Address $address)
    {
        $address->setCartFixedRules(array());

        if (!$items) {
            return $this;
        }

        foreach ($this->_getRules() as $rule) {
            if ((Mage_SalesRule_Model_Rule::CART_FIXED_ACTION == $rule->getSimpleAction()
                || MP_MaxCouponDiscountAmount_Model_Rule::CART_RANDOM_ACTION == $rule->getSimpleAction())
                && $this->_canProcessRule($rule, $address)) {

                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    $qty = $this->_getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->_getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->_getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = array(
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count' => $validItemsCount,
                );
            }
        }

        return $this;
    }
}
