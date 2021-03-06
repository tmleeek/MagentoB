<?php
/**
 * @package    Bilna Static Area Manager
 **/

class Bilna_Staticarea_Block_Adminhtml_Manage_Edit_Tab_Contents extends Mage_Adminhtml_Block_Widget_Grid {
    
    public function __construct()
    {
        parent::__construct();
        $this->setId('staticarea_contents')
            ->setSaveParametersInSession(true)
            ->setDefaultSort('sort_order', 'ASC')
            ->setUseAjax(true);
    }

    protected function _prepareColumns() {
        /*$this->addColumn('location', array(
            'header' => $this->__('Image preview'),
            'index' => 'location',
            'width' => '150px',
            'sortable' => false,
            'filter' => false,
            'renderer' => 'AW_Islider_Block_Widget_Grid_Column_Renderer_Imagepreview'
        ));*/

        $this->addColumn('content', array(
            'header' => $this->__('Content'),
            'index' => 'content',
            'type' => 'textarea',
            'width' => '200px',
            'sortable' => false
        ));

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage::getModel('staticarea/source_status')->toShortOptionArray(),
            'width' => '200px',
            'sortable' => false
        ));

        $this->addColumn('url', array(
            'header' => $this->__('URL'),
            'index' => 'url',
            'sortable' => false
        ));

        $this->addColumn('url_action', array(
            'header' => $this->__('URL Action'),
            'index' => 'url_action',
            'sortable' => false
        ));

        $this->addColumn('active_from', array(
            'header' => $this->__('Active From'),
            'index' => 'active_from',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'Bilna_Staticarea_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('active_to', array(
            'header' => $this->__('Active To'),
            'index' => 'active_to',
            'type' => 'date',
            'sortable' => false,
            'renderer' => 'Bilna_Staticarea_Block_Widget_Grid_Column_Renderer_Date'
        ));

        $this->addColumn('order', array(
            'header' => $this->__('Sort Order'),
            'index' => 'order',
            'width' => '150px'
        ));


        return parent::_prepareColumns();
    }

    protected function _prepareCollection()
    {
        $_collection = Mage::getModel('staticarea/contents')->getCollection();

        if($this->getRequest()->getParam('id')) {
            $_collection->addContentFilter($this->getRequest()->getParam('id'));
        } else {
            $_collection->addContentFilter(-1);
        }


        $this->setCollection($_collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl() {
        return $this->getUrl('*/*/grid', array('id' => $this->getRequest()->getParam('id'), '_current' => true));
    }

    public function getRowUrl($row) {
        return 'javascript:bilnaISAjaxForm.showForm(null, '.$row->getId().');';
    }

}
