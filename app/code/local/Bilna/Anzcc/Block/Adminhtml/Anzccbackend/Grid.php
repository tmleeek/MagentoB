<?php
class Bilna_Anzcc_Block_Adminhtml_Anzccbackend_Grid extends Mage_Adminhtml_Block_Widget_Grid {
    protected $mainAliasName = 'main_table';
    protected $addressAliasName = 'shipping_o_a';
    protected $paymentAliasName = 'payment';
    protected $itemAliasName = 'item';
    
    public function __construct() {
        parent::__construct();

        $this->setId('anzreport_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }
    
    protected function _prepareCollection() {
        $orderPaymentAllowed = $this->_preparePaymentOrderOption();
        $orderStatusAllowed = $this->_prepareStatusOrderOption();
        
        $collection = Mage::getResourceModel('anzcc/grid_collection');
        $collection->addAddressFields();
        $collection->getSelect()->joinLeft(
            array ($this->paymentAliasName => Mage::getSingleton('core/resource')->getTableName('sales/order_payment')),
            "main_table.entity_id = {$this->paymentAliasName}.parent_id",
            array ("{$this->paymentAliasName}.method", "{$this->paymentAliasName}.cc_bins")
        )->joinLeft(
            array ($this->itemAliasName => Mage::getSingleton('core/resource')->getTableName('sales/order_item')),
            "main_table.entity_id = {$this->itemAliasName}.order_id",
            array ("{$this->itemAliasName}.installment_type")
        )->group('main_table.entity_id');
        $collection
            ->addFieldToFilter("{$this->paymentAliasName}.method", array ('in' => $orderPaymentAllowed))
            ->addFieldToFilter('main_table.status', array ('in' => $orderStatusAllowed));
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('increment_id', array (
            'header' => Mage::helper('anzcc')->__('Order #'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'increment_id',
            'filter_index' => $this->mainAliasName . '.increment_id'
        ));
	  
        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => $this->mainAliasName . '.created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
	  
        $this->addColumn('billing_name', array (
            'header' => Mage::helper('anzcc')->__('Bill to Name'),
            'align' => 'left',
            'index' => 'billing_name'
        ));
        
        $this->addColumn('telephone', array (
            'header' => Mage::helper('anzcc')->__('Telephone'),
            'align' => 'left',
            'index' => 'telephone',
            'filter_index' => $this->addressAliasName . '.telephone',
            'width' => '130px'
        ));
        
        $this->addColumn('base_grand_total', array (
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'filter_index' => $this->mainAliasName . '.base_grand_total',
            'type' => 'currency',
            'currency' => 'base_currency_code'
        ));

        $this->addColumn('grand_total', array (
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'filter_index' => $this->mainAliasName . '.grand_total',
            'type' => 'currency',
            'currency' => 'order_currency_code'
        ));
        
        $this->addColumn('method', array(
            'header' => Mage::helper('anzcc')->__('Payment Method'),
            'index' => 'method',
            'filter_index' => $this->paymentAliasName . '.method',
            'type'  => 'options',
            'width' => '100px',
            'options' => $this->_preparePaymentOption()
        ));
        
        $this->addColumn('status', array(
            'header' => Mage::helper('anzcc')->__('Status'),
            'index' => 'status',
            'filter_index' => $this->mainAliasName . '.status',
            'type'  => 'options',
            'width' => '95px',
            'options' => $this->_prepareStatusOption()
        ));
        
        $this->addColumn('cc_bins', array (
            'header' => Mage::helper('anzcc')->__('BINS Number'),
            'align' => 'center',
            'width' => '70px',
            'index' => 'cc_bins',
            'filter_index' => $this->paymentAliasName . '.cc_bins'
        ));
        
        $this->addColumn('installment_type', array(
            'header' => Mage::helper('anzcc')->__('Payment Type'),
            'index' => 'installment_type',
            'filter_index' => $this->itemAliasName . '.installment_type',
            'width' => '80px',
            'filter' => false,
            'renderer' => 'Bilna_Anzcc_Block_Adminhtml_Renderer_Column_Installment'
        ));
        
        $this->addExportType('*/*/exportCsv', Mage::helper('anzcc')->__('CSV'));
	  
        return parent::_prepareColumns();
    }
    
    protected function _preparePaymentOrderOption() {
        $orderPaymentAllowed = Mage::getStoreConfig('payment/anzcc/payment_allowed');
        $orderPaymentAllowedArr = explode(',', $orderPaymentAllowed);
        
        return $orderPaymentAllowedArr;
    }
    
    protected function _prepareStatusOrderOption() {
        $orderStatusAllowed = Mage::getStoreConfig('payment/anzcc/order_status_cicilan');
        $orderStatusAllowedArr = explode(',', $orderStatusAllowed);
        
        return $orderStatusAllowedArr;
    }
    
    protected function _preparePaymentOption() {
        $paymentCollection = $this->_preparePaymentOrderOption();
        $paymentOption = array ();
       
        foreach ($paymentCollection as $key => $value) {
            $paymentOption[$value] = $this->helper('anzcc')->getPaymentmentOptionLabel($value);
        }
        
        return $paymentOption;
    }
    
    protected function _prepareStatusOption() {
        $status = explode(',', Mage::getStoreConfig('payment/anzcc/order_status_cicilan'));
        $result = array ();
        
        foreach ($status as $key => $value) {
            $result[$value] = $this->_getStatusLabel($value);
        }
        
        return $result;
    }
    
    protected function _getStatusLabel($status) {
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        
        foreach ($statuses as $key => $value) {
            if ($status == $key) {
                return $value;
            }
        }
        
        return '';
    }
    
    /**
     * Grid with Ajax Request
     */
    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array ('_current' => true));
    }
}
