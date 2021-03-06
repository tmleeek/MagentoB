<?php
/**
 * Description of Bilna_Paymethod_VtdirectController
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Paymethod_VtdirectController extends Mage_Core_Controller_Front_Action {
    protected $_code = 'vtdirect';
    protected $_typeTransaction = 'transaction';
    
    public function thankyouAction() {
        $session = Mage::helper('paymethod/vtdirect')->getCheckout();
        
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        
        if (!$lastQuoteId || (!$lastOrderId && empty ($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        /**
         * charge credit card
         */
        $responseCharge = array ();
        
        if ($this->getOrderId()) {
            $order = $this->getOrder();
            $items = $order->getAllItems();
            $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
            $url = Mage::getStoreConfig('payment/vtdirect/charge_transaction_url');
            
            $data = array ();
            $data['token_id'] = $this->getTokenId();
            $data['order_id'] = $this->maxChar($order->getIncrementId(), 20);
            $data['bins'] = $this->getBins($order, $paymentCode);
            $data['order_items'] = $this->getOrderItems($order, $items);
            $data['gross_amount'] = round($order->getGrandTotal());
            $data['email'] = $this->getCustomerEmail($order->getBillingAddress()->getEmail());
            $data['billing_address'] = $this->parseBillingAddress($order->getBillingAddress());
            $data['shipping_address'] = $this->parseBillingAddress($order->getBillingAddress());
            $data['bank'] = $this->getAcquiredBank($paymentCode);
            
            /**
             * check installment
             */
            if ($this->getInstallmentProcess($paymentCode) != 'manual') {
                $installmentId = $this->getInstallment($items);

                if ($installmentId) {
                    $data['type'] = 'installment';
                    $data['installment'] = array (
                        'bank' => $this->getInstallmentBank($paymentCode),
                        'term' => $installmentId,
                        //'term' => $this->getInstallmentTenor($paymentCode, $installmentId),
                        'type' => $this->getInstallmentTypeCodeBank($paymentCode)
                    );
                }
            }
            
            $threedsecure = $this->getThreedSecure($paymentCode);
            
            if ($threedsecure == true) {
                $data['3dsecure'] = $threedsecure;
                $data['3dsecure_callback_url'] = $this->getThreedSecureCallbackUrl($paymentCode);
                $data['3dsecure_notification_url'] = $this->getThreedSecureNotificationUrl($paymentCode);
            }
            
            $responseCharge = json_decode(Mage::helper('paymethod/vtdirect')->postRequest($url, $data));
            
            $contentRequest = sprintf("%s | request_vtdirect: %s", $order->getIncrementId(), json_encode($data));
            $contentResponse = sprintf("%s | response_vtdirect: %s", $order->getIncrementId(), json_encode($responseCharge));
            $this->writeLog($this->_typeTransaction, 'charge', $contentRequest);
            $this->writeLog($this->_typeTransaction, 'charge', $contentResponse);
            
            /**
             * processing order
             */
            $this->updateOrder($order, $paymentCode, $responseCharge);
        }
        else {
            $this->_redirect('checkout/cart');
            return;
        }
        
        /**
         * assign data to View
         */
        Mage::register('threedsecure', $threedsecure);
        Mage::register('response_charge', $responseCharge);
        
        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        Mage::dispatchEvent('checkout_onepage_controller_success_action', array ('order_ids' => array ($lastOrderId)));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Thankyou Page'));
        $this->renderLayout();
    }
    
    public function successAction() {
        $data = json_decode($this->getRequestData('vtdirect'), true);
        
        $contentRequest = sprintf("%s | request_vtdirect: %s", $data->data->order_id, json_encode($data));
        $this->writeLog($this->_typeTransaction, 'notification', $contentRequest);
        
        echo json_encode($data);
        exit;
    }
    
    public function notificationAction() {
        $notification = json_decode(file_get_contents('php://input'));
        $contentRequest = sprintf("%s | request_vtdirect: %s", $notification->data->order_id, json_encode($notification));
        $this->writeLog($this->_typeTransaction, 'notification', $contentRequest);
        
        /**
         * check order id
         */
        if (!isset ($notification->data->order_id)) {
            $this->writeLog($this->_typeTransaction, 'notification', 'transactionNo failed.');
            exit;
        }
        
        /**
         * check transaction already in process
         */
        if ($this->checkLock($notification->data->order_id)) {
            $this->writeLog($this->_typeTransaction, 'notification', 'Transaction already in process.');
            exit;
        }
        
        /**
         * create lock file
         */
        $this->createLock($notification->data->order_id);
        
        if ($this->getServerKey() == $notification->data->server_key) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($notification->data->order_id);
            
            if ($notification->status == 'success') {
                if ($order->canInvoice()) {
                    $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                    if ($invoice->getTotalQty()) {
                        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                        $invoice->setGrandTotal($order->getGrandTotal());
                        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                        $invoice->register();
                        $transactionSave = Mage::getModel('core/resource_transaction')
                            ->addObject($invoice)
                            ->addObject($invoice->getOrder());
                        $transactionSave->save();                            
                        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $notification->message, true)->save();
                        $invoice->sendEmail(true, '');

                        $contentLog = sprintf("%s | status_order: %s", $notification->data->order_id, Mage_Sales_Model_Order::STATE_PROCESSING);
                        $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                    }
                    else {
                        $contentLog = sprintf("%s | status_order: invoice cannot get total qty", $notification->data->order_id);
                        $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                    }
                }
                else {
                    $contentLog = sprintf("%s | status_order: cannot create invoice", $notification->data->order_id);
                    $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
                }
            }
            else {
                $order->addStatusHistoryComment($notification->message);
                $order->save();
            }
        }
        else {
            $contentLog = sprintf("%s | status_order: server key is not valid", $notification->data->order_id);
            $this->writeLog($this->_typeTransaction, 'notification', $contentLog);
        }
    }

    private function getOrderId() {
        return Mage::helper('paymethod/vtdirect')->getOrderId();
    }
    
    private function getOrder() {
        return Mage::getModel('sales/order')->load($this->getOrderId());
    }
    
    private function getTokenId() {
        $tokenId = Mage::getSingleton('core/session')->getVtdirectTokenId();
        
        /**
         * remove token_id session
         */
        Mage::getSingleton("core/session")->unsVtdirectTokenIdCreate();
        Mage::getSingleton("core/session")->unsVtdirectTokenId();
        
        return $tokenId;
    }
    
    private function getBins($order, $paymentCode) {
        $digit = 6;
        //$digit = ($paymentCode == 'othervisa' || $paymentCode == 'othermc') ? 1 : 6;
        $result = substr($order->getPayment()->getCcBins(), 0, $digit);
        
        return array ($result);
    }

    private function getOrderItems($order, $items) {
        $result = array ();
        
        //if (count($items) > 0) {
        //    foreach ($items as $itemId => $item) {
        //        $result[$itemId]['id'] = $this->maxChar($item->getProductId(), 20);
        //        $result[$itemId]['price'] = round($item->getPrice());
        //        $result[$itemId]['qty'] = $item->getQtyToInvoice();
        //        $result[$itemId]['name'] = $this->maxChar($this->removeSymbols($item->getName()), 20);
        //    }
        //}
        $result[0]['id'] = $order->getId();
        $result[0]['price'] = round($order->getGrandTotal());
        $result[0]['qty'] = 1;
        $result[0]['name'] = $this->maxChar('Item order ' . $order->getIncrementId(), 20);
        
        return $result;
    }
    
    private function getCustomerEmail($email) {
        if (Mage::getStoreConfig('payment/vtdirect/development_testing')) {
            return 'vt-testing@veritrans.co.id';
        }
        
        return $email;
    }
    
    private function parseShippingAddress($shippingAddress) {
        $firstname = $shippingAddress->getFirstname();
        $lastname = $shippingAddress->getFirstname();
        //$lastname = $shippingAddress->getLastname();
        
        $result = array (
            'first_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($firstname, true), 20),
            'last_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($lastname, true), 20),
            'address1' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getStreet(1)), 100),
            'address2' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getStreet(2)), 100),
            'city' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($shippingAddress->getCity()), 20),
            'postal_code' => $this->maxChar($this->getPostCode($shippingAddress->getPostcode()), 10),
            'phone' => $this->maxChar(Mage::helper('paymethod')->allowOnlyNumber($shippingAddress->getTelephone()), 19)
        );
        
        return $result;
    }
    
    private function parseBillingAddress($billingAddress) {
        $firstname = $billingAddress->getFirstname();
        $lastname = $billingAddress->getFirstname();
        //$lastname = $billingAddress->getLastname();
        
        $result = array (
            'first_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($firstname, true), 20),
            'last_name' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($lastname, true), 20),
            'address1' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getStreet(1)), 100),
            'address2' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getStreet(2)), 100),
            'city' => $this->maxChar(Mage::helper('paymethod/vtdirect')->filterAddress($billingAddress->getCity()), 20),
            'postal_code' => $this->maxChar($this->getPostCode($billingAddress->getPostcode()), 10),
            'phone' => $this->maxChar(Mage::helper('paymethod')->allowOnlyNumber($billingAddress->getTelephone()), 19)
        );
        
        return $result;
    }
    
    private function getPostCode($postCode) {
        $result = Mage::helper('paymethod')->allowOnlyNumber($postCode);
        
        if (empty ($result) || $result == '') {
            $result = Mage::helper('paymethod')->allowOnlyNumber($this->getPostCodeSession());
        }
        
        return $result;
    }
    
    private function getPostCodeSession() {
        return Mage::getSingleton('core/session')->getVtdirectZipCode();
    }
    
    private function getAcquiredBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/bank_acquired');
    }
    
    private function getInstallmentProcess($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_process');
    }
    
    private function getInstallment($items) {
        foreach ($items as $itemId => $item) {
            $installmentType = $item->getInstallmentType();
            
            if ($installmentType > 1) {
                return $installmentType;
            }
        }
        
        return false;
    }
    
    private function getInstallmentBank($paymentCode) {
		if (strtolower($paymentCode) == 'bnikartinivisa' || strtolower($paymentCode) == 'bnikartinimc') {
			return 'bni';
		}
		
        $result = '';
        
        if (substr($paymentCode, -4) == 'visa') {
            $result = substr($paymentCode, 0, -4);
        }
        else if (substr($paymentCode, -2) == 'mc') {
            $result = substr($paymentCode, 0, -2);
        }
        else {
            //do nothing
        }
        
        return $result;
    }
    
    private function getInstallmentTenor($paymentCode, $installmentId) {
        return Mage::helper('paymethod')->getInstallmentOption($paymentCode, $installmentId, 'tenor');
    }
    
    private function getInstallmentTypeCodeBank($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/installment_type_code');
    }
    
    private function getThreedSecure($paymentCode) {
        $result = false;
        
        if (Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure')) {
            if (Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure') == 1) {
                $result = true;
            }
        }
        
        return $result;
    }
    
    private function getThreedSecureCallbackUrl($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure_callback_url');
    }
    
    private function getThreedSecureNotificationUrl($paymentCode) {
        return Mage::getStoreConfig('payment/' . $paymentCode . '/threedsecure_notification_url');
    }

    private function getServerKey() {
        return Mage::getStoreConfig('payment/vtdirect/server_key');
    }

    private function updateOrder($order, $paymentCode, $responseCharge) {
        if ($responseCharge->status == 'success') {
            if ($this->getThreedSecure($paymentCode)) {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', 'Pending for 3D Secure Validation', true)->save();
            }
            else {
                // check order status if processing/complete then ignore
                if (in_array($order->getStatus(), Mage::helper('paymethod/vtdirect')->getStatusOrderIgnore())) {
                    return true;
                }
                
                $transactionStatus = $responseCharge->data->transaction_status;
                $message = "Transaction status: " . $transactionStatus . ". ";
                $message .= $this->getDefaultResponseMessage($responseCharge->status, $responseCharge->message);
                
                if ($transactionStatus == 'deny') {
                    //$order->addStatusHistoryComment($message);
                    $history = $order->addStatusHistoryComment($message);
                    $history->setIsCustomerNotified(true);
            
                    if ($order->canCancel()) {
                        $order->cancel();
                    }
            
                    $order->save();
                            
                    return true;
                }
                else if ($transactionStatus == 'cancel') {
                    //$order->addStatusHistoryComment($message);
                    $history = $order->addStatusHistoryComment($message);
                    $history->setIsCustomerNotified(true);
            
                    if ($order->canCancel()) {
                        $order->cancel();
                    }
            
                    $order->save();
                    
                    return true;
                }
                else if ($transactionStatus == 'challenge') {
                    $order->setState(Mage_Sales_Model_Order::STATE_NEW, 'cc_verification', $message, true);
                    $order->save();
                    
                    return true;
                }
                else if ($transactionStatus == 'authorize') {
                    if ($order->canInvoice()) {
                        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();

                        if ($invoice->getTotalQty()) {
                            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                            $invoice->setGrandTotal($order->getGrandTotal());
                            $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
                            $invoice->register();
                            $transaction = Mage::getModel('core/resource_transaction')
                                ->addObject($invoice)
                                ->addObject($invoice->getOrder());
                            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message, true);
                            $order->save();
                            $transaction->save();
                            $invoice->sendEmail(true, '');

                            return true;
                        }
                    }
                }
                else if ($transactionStatus == 'settlement') {
                    $order->addStatusHistoryComment($message);
                    $order->save();
                    
                    return true;
                }
                else {
                    //do nothing
                }
            }

            return false;
        }
        else if ($responseCharge->status == 'failure') {
            $message = "Transaction status: " . $responseCharge->status . ". ";
            $message .= $this->getDefaultResponseMessage($responseCharge->status, $responseCharge->message);
            $history = $order->addStatusHistoryComment($message);
            $history->setIsCustomerNotified(true);
            
            if ($order->canCancel()) {
                $order->cancel();
            }
            
            $order->save();
            
            return true;
        }
        else {
            /**
             * failed get response or timeout
             */
            $order->addStatusHistoryComment('failed get response or timeout from Veritrans');
            $order->save();
            
            /**
             * write log to process confirmation
             */
            $this->createLog($this->maxChar($order->getIncrementId(), 20), 'confirmation', $order->getIncrementId() . "|Response charge is null");
            
            return true;
        }
    }
    
    protected function getDefaultResponseMessage($status, $message) {
        $result = '';
        
        if ($message) {
            $result = $message;
        }
        else {
            if ($status == 'success') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_success');
            }
            else if ($status == 'failure') {
                $result = Mage::getStoreConfig('payment/vtdirect/default_response_message_failure');
            }
            else {
                $result = Mage::getStoreConfig('payment/vtdirect/charge_timeout_message');
            }
        }
        
        return $result;
    }
    
    protected function getStatusOrderIgnore() {
        $orders = Mage::getStoreConfig('payment/vtdirect/update_order_status');
        $ordersArr = explode(',', $orders);
        
        return $ordersArr;
    }

    protected function getRequestData($key, $type = 'POST') {
        $result = '';
        
        if ($type == 'POST') {
            if ($this->getRequest()->getPost($key)) {
                $result = $this->getRequest()->getPost($key);
            }
        }
        else {
            if ($this->getRequest()->getParam($key)) {
                $result = $this->getRequest()->getParam($key);
            }
        }
        
        return $result;
    }

    private function maxChar($text, $maxLength = 10) {
        if (empty ($text)) {
            return '';
        }
        
        return substr($text, 0, $maxLength);
    }
    
    private function removeSymbols($text, $removeNumber = false) {
        $result = Mage::helper('paymethod/vtdirect')->removeSymbols($text);
        $number = array (1,2,3,4,5,6,7,8,9,0);
        
        if ($removeNumber) {
            $result = str_replace($number, ' ', $result);
        }
        
        //TODO: REMOVE THIS LINE
        //DIRTY CODE
        $result = preg_replace('/[^\d\sa-z]/i', ' ' , $result);
        
        return $result;
    }
    
    protected function writeLog($type, $logFile, $content) {
        $tdate = date('Ymd', Mage::getModel('core/date')->timestamp(time()));
        $filename = sprintf("%s_%s.%s", $this->_code, $logFile, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content);
    }
    
    protected function createLock($filename) {
        return Mage::helper('paymethod')->createLockFile($this->_code, $filename);
    }
    
    protected function checkLock($filename) {
        return Mage::helper('paymethod')->checkLockFile($this->_code, $filename);
    }
    
    protected function createLog($filename, $type, $content) {
        $tdate = date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp(time()));
        $content = sprintf("%s|%s", $content, $tdate);
        
        return Mage::helper('paymethod')->writeLogFile($this->_code, $type, $filename, $content, 'normal');
    }
}
