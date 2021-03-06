<?php
/**
 * Description of Bilna_Wholesale_Model
 *
 * @author  Bilna Development Team
 * @email   development@bilna.com
 */
class Bilna_Wholesale_Model_Observer
{
    public function cancelOrderItem($observer)
    {
        $item = $observer->getEvent()->getItem();

        if ($item->getIsWholesale()) {
            return $this;
        }

        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            Mage::getSingleton('cataloginventory/stock')->backItemQty($productId, $qty);
        }

        return $this;
    }

    public function wholesaleCartAddItem(Varien_Event_Observer $observer)
    {
        $items = $observer->getEvent()->getItems();
        foreach ($items as $item) {
            $stockItem = $item->getProduct()->getStockItem();
            if ( $stockItem->isWholesaleQty($item->getQty()) ) {
                $item->setIsWholesale(1);
                $item->getQuote()->setIsWholesale(1);
            }
        }

        return $this;
    }

    public function wholesaleQuoteRemoveItem(Varien_Event_Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $quoteItemCollections = Mage::getModel('sales/quote_item')->getCollection()->addFieldToFilter('is_wholesale', 1)->addFieldToFilter('quote_id', $item->getQuoteId());

        if ($item->getIsWholesale() && $item->getQuote()->getIsWholesale() && count($quoteItemCollections->getData()) <= 1) {
            $item->getQuote()->setIsWholesale(0);
        }

        return $this;
    }

    public function wholesaleConvertQuoteToOrder(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();
        $order->setIsWholesale($quote->getIsWholesale());

        return $this;
    }

    public function wholesaleConvertQuoteToOrderItem(Varien_Event_Observer $observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();
        $orderItem->setIsWholesale($quoteItem->getIsWholesale());

        return $this;
    }
}

