<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Shipment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Initialization
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_shipment_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_shipment_grid_collection';
    }

    /**
     * Prepare and set collection of grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $collection->getSelect()
            ->joinLeft(
                array('sales_flat_shipment_track' => Mage::getSingleton('core/resource')->getTableName('sales/shipment_track') ),
                "main_table.entity_id = sales_flat_shipment_track.parent_id",
                array(
                    "tracking_number"        => "GROUP_CONCAT(track_number)"
                )
            )
            /*->joinInner(
                array('sales_flat_order_status_history' => new Zend_Db_Expr('(SELECT * FROM (SELECT * FROM sales_flat_order_status_history ORDER BY created_at DESC)x GROUP BY parent_id)') ),
                "main_table.order_id = sales_flat_order_status_history.parent_id",
                array(
                    "is_customer_notified" => "IF(sales_flat_order_status_history.is_customer_notified =1, 'Yes', 'No')" 
                )
            )*/
            ->joinLeft(
                array('sales_flat_shipment_comment' => new Zend_Db_Expr('(SELECT parent_id, MAX(is_customer_notified) AS is_customer_notified FROM sales_flat_shipment_comment GROUP BY parent_id)') ),
                "main_table.entity_id = sales_flat_shipment_comment.parent_id",
                array(
                    "is_customer_notified2" => "IF(sales_flat_shipment_comment.is_customer_notified =1, 'Yes', 'No')" 
                )
            );
        $collection->getSelect()->group(array('sales_flat_shipment_track.parent_id'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare and add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Shipment #'),
            'index'     => 'increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Date Shipped'),
            'index'     => 'created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            'type'  => 'number',
        ));

        $this->addColumn('tracking_number', array(
            'header' => Mage::helper('sales')->__('Track Number'),
            'index' => 'tracking_number',
            'type'  => 'text',
        ));

        /*$this->addColumn('is_customer_notified', array(
            'header' => Mage::helper('sales')->__('Customer Notified (Order Status)'),
            'index' => 'is_customer_notified',
            'type'  => 'text',
        ));*/

        $this->addColumn('is_customer_notified2', array(
            'header' => Mage::helper('sales')->__('Customer Notified (Shipment Prosess)'),
            'index' => 'is_customer_notified2',
            'type'  => 'text',
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('sales')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('sales')->__('View'),
                        'url'     => array('base'=>'*/sales_shipment/view'),
                        'field'   => 'shipment_id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * Get url for row
     *
     * @param string $row
     * @return string
     */
    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/shipment')) {
            return false;
        }

        return $this->getUrl('*/sales_shipment/view',
            array(
                'shipment_id'=> $row->getId(),
            )
        );
    }

    /**
     * Prepare and set options for massaction
     *
     * @return Mage_Adminhtml_Block_Sales_Shipment_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shipment_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('PDF Packingslips'),
             'url'  => $this->getUrl('*/sales_shipment/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('print_shipping_label', array(
             'label'=> Mage::helper('sales')->__('Print Shipping Labels'),
             'url'  => $this->getUrl('*/sales_order_shipment/massPrintShippingLabel'),
        ));

        return $this;
    }

    /**
     * Get url of grid
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true));
    }

}
