<?php
class Mage_Adminhtml_Block_Sales_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('increment_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $aweekago = date('Y-m-d',strtotime('-90 days'));
        $today = date('Y-m-d H:i:s');
		$this->setCollection($collection);
		$collection->addFieldToFilter('created_at',array("from" =>  $aweekago, "to" =>  $today, "datetime" => true));
		//added mehul for payment method on view page
                //added by saurabh for search by sku at grid
		$collection->getSelect()
                ->joinLeft('sales_flat_order_payment', 'main_table.entity_id = sales_flat_order_payment.parent_id','method')
				
				;
                //->joinLeft('sales_flat_order_address', 'main_table.entity_id = sales_flat_order_address.parent_id',array('telephone' => 'telephone'))
                //->where("sales_flat_order_address.address_type='billing'") ; 
                //->joinLeft('sales_flat_order_item', 'main_table.entity_id = sales_flat_order_item.order_id','sku');
         //echo $collection->getSelect();               
        		//echo '<pre>';print_r($collection);exit;
		return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {

        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased From (Store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
		));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));
		
		$this->addColumn('country_id', array(
            'header' => Mage::helper('sales')->__('Country'),
            'renderer' => 'adminhtml/renderer_country',
            ));
            
        /*$this->addColumn('escalate', array(
            'header' => Mage::helper('sales')->__('ESCALATE'),
        	'renderer'  => 'Mage_Adminhtml_Block_Renderer_Escalate',
        	'filter' => false
       ));*/
		
		
		$this->addColumn('method', array(
            'header'    => Mage::helper('sales')->__('Payment Method Name'),
            'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout' ,'retailpay' => 'Retail Pay'),
        ));
		
		/*$this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));*/

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

           
	    /*$this->addColumn('telephone', array(
            'header' => Mage::helper('sales')->__('Telephone'),
            'index' => 'telephone',
            
        ));*/
        
        
        
        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

       /* $this->addColumn('sku', array(
            'header' => Mage::helper('sales')->__('SKU'),
            'index' => 'sku',
        ));*/
        
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/sales_order/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action1',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',

                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('ViewUltraLite'),
                            'url'     => array('base'=>'*/sales_order/viewulite'),
                            'field'   => 'order_id',
                            'target'    => '_blank',
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }


        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        //$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        //$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('order_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/cancel')) {
            $this->getMassactionBlock()->addItem('cancel_order', array(
                 'label'=> Mage::helper('sales')->__('Cancel'),
                 'url'  => $this->getUrl('*/sales_order/massCancel'),
            ));
        }
		
		if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/massUndoAction')) {
        $this->getMassactionBlock()->addItem('changestatus_order', array(
        'label'=> Mage::helper('sales')->__('Undo Cancel'),
        'url' => $this->getUrl('*/*/massUndo'),
        ));
		}

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/hold')) {
            $this->getMassactionBlock()->addItem('hold_order', array(
                 'label'=> Mage::helper('sales')->__('Hold'),
                 'url'  => $this->getUrl('*/sales_order/massHold'),
            ));
        }

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/unhold')) {
            $this->getMassactionBlock()->addItem('unhold_order', array(
                 'label'=> Mage::helper('sales')->__('Unhold'),
                 'url'  => $this->getUrl('*/sales_order/massUnhold'),
            ));
        }

        $this->getMassactionBlock()->addItem('pdfinvoices_order', array(
             'label'=> Mage::helper('sales')->__('Print Invoices'),
             'url'  => $this->getUrl('*/sales_order/pdfinvoices'),
        ));

        $this->getMassactionBlock()->addItem('pdfshipments_order', array(
             'label'=> Mage::helper('sales')->__('Print Packingslips'),
             'url'  => $this->getUrl('*/sales_order/pdfshipments'),
        ));

        $this->getMassactionBlock()->addItem('pdfcreditmemos_order', array(
             'label'=> Mage::helper('sales')->__('Print Credit Memos'),
             'url'  => $this->getUrl('*/sales_order/pdfcreditmemos'),
        ));

        $this->getMassactionBlock()->addItem('pdfdocs_order', array(
             'label'=> Mage::helper('sales')->__('Print All'),
             'url'  => $this->getUrl('*/sales_order/pdfdocs'),
        ));
        
        return $this;
    }

    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/sales_order/view', array('order_id' => $row->getId()));
        }
        return false;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
