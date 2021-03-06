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
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Invoice_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_invoice_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Retrieve collection class
     *
     * @return string
     */
    protected function _getCollectionClass()
    {
        return 'sales/order_invoice_grid_collection';
    }

    protected function _prepareCollection()
    {
		$orderTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
		$invoiceTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_invoice');
		$orderAddressTable = Mage::getSingleton('core/resource')->getTableName('sales_flat_order_address');
        $collection = Mage::getResourceModel($this->_getCollectionClass());
		
		$collection->getSelect()->joinLeft( array('order'=>$orderTable), "main_table.order_id=order.entity_id" ,array())
								->joinLeft( array('orderAddress'=>$orderAddressTable), "orderAddress.entity_id=order.shipping_address_id" , array('shipping_address' => 'street','shipping_name' => 'CONCAT(firstname, " ", lastname)' , 'postcode' => 'postcode' , 'telephone' => 'telephone'))
								->joinLeft( array('invoice'=>$invoiceTable), "main_table.increment_id=invoice.increment_id" ,array('qty' => 'total_qty'))
								->order('main_table.created_at DESC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Invoice #'),
            'index'     => 'increment_id',
            'type'      => 'text',
			'filter_index' => 'main_table.increment_id'
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Invoice Date'),
            'index'     => 'created_at',
            'type'      => 'datetime',
			'filter_index' => 'main_table.created_at'
        ));

        $this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'text',
			'filter_index' => 'main_table.order_increment_id'
        ));

        $this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
			'filter_index' => 'main_table.order_created_at'
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
			'filter_index' => 'main_table.billing_name'
        ));

		$this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
			'filter'    => false,
            'sortable'  => false,
        ));

		$this->addColumn('shipping_address', array(
            'header' => Mage::helper('sales')->__('Ship to Address'),
            'index' => 'shipping_address',
			'filter_index' => 'orderAddress.street'
        ));

		$this->addColumn('telephone', array(
            'header' => Mage::helper('sales')->__('Telephone'),
            'index' => 'telephone',
			'filter_index' => 'orderAddress.telephone'
        ));

		$this->addColumn('postcode', array(
            'header' => Mage::helper('sales')->__('Postal Code'),
            'index' => 'postcode',
			'filter_index' => 'orderAddress.postcode'
        ));

        $this->addColumn('state', array(
            'header'    => Mage::helper('sales')->__('Status'),
            'index'     => 'state',
            'type'      => 'options',
            'options'   => Mage::getModel('sales/order_invoice')->getStates(),
			'filter_index' => 'main_table.state'
        ));

        $this->addColumn('grand_total', array(
            'header'    => Mage::helper('customer')->__('Amount'),
            'index'     => 'grand_total',
            'type'      => 'currency',
            'align'     => 'right',
            'currency'  => 'order_currency_code',
			'filter_index' => 'main_table.grand_total'
        ));

		$this->addColumn('qty', array(
            'header'    => Mage::helper('customer')->__('Total Qty'),
            'index'     => 'qty',
            'align'     => 'right',
			'filter_index' => 'invoice.total_qty'
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
                        'url'     => array('base'=>'*/sales_invoice/view'),
                        'field'   => 'invoice_id'
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

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('invoice_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('PDF Invoices'),
             'url'  => $this->getUrl('*/sales_invoice/pdfinvoices'),
        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/invoice')) {
            return false;
        }

        return $this->getUrl('*/sales_invoice/view',
            array(
                'invoice_id'=> $row->getId(),
            )
        );
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

}
