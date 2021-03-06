<?php
/**
 * Description of Bilna_Cod_Block_Adminhtml_Sales_Order_View_Tab_Invoices
 *
 * @author Bilna Development Team <development@bilna.com>
 */

class Bilna_Cod_Block_Adminhtml_Sales_Order_View_Tab_Invoices extends Mage_Adminhtml_Block_Sales_Order_View_Tab_Invoices {
    private $order;
    private $payCode;
    
    public function __construct() {
        parent::__construct();
        
        $this->setId('order_invoices');
        $this->setUseAjax(true);
        
        $this->order = $this->getOrder();
        $this->payCode = $this->order->getPayment()->getMethodInstance()->getCode();
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass() {
        return 'sales/order_invoice_grid_collection';
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel($this->_getCollectionClass())
            ->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToSelect('order_id')
            ->addFieldToSelect('increment_id')
            ->addFieldToSelect('state')
            ->addFieldToSelect('grand_total')
            ->addFieldToSelect('base_grand_total')
            ->addFieldToSelect('store_currency_code')
            ->addFieldToSelect('base_currency_code')
            ->addFieldToSelect('order_currency_code')
            ->addFieldToSelect('billing_name')
            ->setOrderFilter($this->getOrder())
        ;
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('increment_id', array (
            'header' => Mage::helper('sales')->__('Invoice #'),
            'index' => 'increment_id',
            'width' => '120px',
        ));

        $this->addColumn('billing_name', array (
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('created_at', array (
            'header' => Mage::helper('sales')->__('Invoice Date'),
            'index' => 'created_at',
            'type' => 'datetime',
        ));

        $this->addColumn('state', array (
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'state',
            'type' => 'options',
            'options' => Mage::getModel('sales/order_invoice')->getStates(),
        ));

        $this->addColumn('base_grand_total', array (
            'header' => Mage::helper('customer')->__('Amount'),
            'index' => 'base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code',
        ));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder() {
        return Mage::registry('current_order');
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/sales_order_invoice/view',
            array (
                'invoice_id'=> $row->getId(),
                'order_id'  => $row->getOrderId()
            )
        );
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/invoices', array ('_current' => true));
    }


    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel() {
        return Mage::helper('sales')->__($this->getInvoicesTabLabel());
    }

    public function getTabTitle() {
        return Mage::helper('sales')->__('Order Invoices');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }
    
    private function getInvoicesTabLabel() {
        $result = 'Invoices';
        
        if ($this->payCode == 'cod') {
            $result = 'Invoices COD';
        }
        
        return $result;
    }
}
