<?php
/**
 * Description of Bilna_Sales_Model_Api2_Order_Rest_Admin_V1
 * 
 * @path   app/code/local/Bilna/Sales/Model/Api2/Order/Rest/Admin/V1.php
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Sales_Model_Api2_Order_Rest_Admin_V1 extends Bilna_Sales_Model_Api2_Order_Rest {
    protected function _retrieve() {
        $orderId = $this->getRequest()->getParam('id');
        $collection = $this->_getCollectionForSingleRetrieve($orderId);

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        
        $this->_addTaxInfo($collection);
        $order = $collection->getItemById($orderId);

        if (!$order) {
            $this->_critical(self::RESOURCE_NOT_FOUND);
        }
        
        $orderData = $order->getData();
        
        $addresses = $this->_getAddresses(array ($orderId));
        $items = $this->_getItems(array ($orderId));
        $comments = $this->_getComments(array ($orderId));

        if ($addresses) {
            $orderData['addresses'] = $addresses[$orderId];
        }
        
        if ($items) {
            $orderData['order_items'] = $items[$orderId];
        }
        
        if ($comments) {
            $orderData['order_comments'] = $comments[$orderId];
        }
        
        if ($orderData) {
            $orderData['status_label'] = $order->getStatusLabel();
            $orderData['payment_title'] = $this->_getPaymentTitle($order);
            $orderData['additional_info'] = $this->_getAdditionalInfo($order);
        }
        
        return $orderData;
    }
    
    protected function _retrieveCollection() {
        $collection = $this->_getCollectionForRetrieve();

        if ($this->_isPaymentMethodAllowed()) {
            $this->_addPaymentMethodInfo($collection);
        }
        
        if ($this->_isGiftMessageAllowed()) {
            $this->_addGiftMessageInfo($collection);
        }
        
        $this->_addTaxInfo($collection);
        $ordersData = array ();

        foreach ($collection->getItems() as $order) {
            $ordersData[$order->getId()] = $order->toArray();
            $ordersData[$order->getId()]['status_label'] = $order->getStatusLabel();
            $ordersData[$order->getId()]['payment_title'] = $this->_getPaymentTitle($order);
            $ordersData[$order->getId()]['additional_info'] = $this->_getAdditionalInfo($order);
        }
        
        if ($ordersData) {
            foreach ($this->_getAddresses(array_keys($ordersData)) as $orderId => $addresses) {
                $ordersData[$orderId]['addresses'] = $addresses;
            }
            
            foreach ($this->_getItems(array_keys($ordersData)) as $orderId => $items) {
                $ordersData[$orderId]['order_items'] = $items;
            }
            
            foreach ($this->_getComments(array_keys($ordersData)) as $orderId => $comments) {
                $ordersData[$orderId]['order_comments'] = $comments;
            }
        }
        
        return $ordersData;
    }
}
