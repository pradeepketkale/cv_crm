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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales orders grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class EM_DeleteOrder_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{

    
     public function __construct()
    {
        parent::__construct();
        $this->setId('sales_order_grid');
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
  /* protected function _getCollectionClass()
    {
        return 'sales/order_grid_collection';
    }*/

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
      //  echo '<pre>';print_r($collection->getData());exit;
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

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

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
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

       // $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
       // $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

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
		//$this->getMassactionBlock()->addItem('delete_order', array(
        //     'label'=> Mage::helper('sales')->__('Delete order'),
        //     'url'  => $this->getUrl('*/sales_order/deleteorder'),
        //));	
        $this->getMassactionBlock()->addItem('sendEbslink', array(
             'label'=> Mage::helper('sales')->__('Send Ebslink'),
             'url'  => $this->getUrl('*/sales_order/sendEbslink'),
        ));
		$this->getMassactionBlock()->addItem('checkEbspayment', array(
             'label'=> Mage::helper('sales')->__('EBS Payment Status'),
             'url'  => $this->getUrl('*/sales_order/checkEbspayment'),
        ));
		
		$this->getMassactionBlock()->addItem('captureEbspayment', array(
             'label'=> Mage::helper('sales')->__('Capture EBS Payment'),
             'url'  => $this->getUrl('*/sales_order/captureEbspayment'),
        ));
		$this->getMassactionBlock()->addItem('invInternational', array(
             'label'=> Mage::helper('sales')->__('International Invoice'),
             'url'  => $this->getUrl('*/sales_order/invInternational'),
		      'target' => '_blank',
			'additional' => array(
                    'visibility' =>array(
                         'name' => 'goodsdescription',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Full Description Of Goods'),
						 ),
                    
                  array(
                         'name' => 'qty',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Qty'),
						 ),
                  
                    array(
                         'name' => 'unitprice',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Unit Value'),
						 ),
                      
              
                   array(
                         'name' => 'grossweight',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Gross Weight'),
						 ),
                      )
                
        ));
		
		$this->getMassactionBlock()->addItem('suspectfraud', array(
             'label'=> Mage::helper('sales')->__('Suspected Fraud'),
             'url'  => $this->getUrl('*/sales_order/suspectfraud'),
        ));
		
		$this->getMassactionBlock()->addItem('changePaymentMethod', array(
             'label'=> Mage::helper('sales')->__('Change Method To EBS'),
             'url'  => $this->getUrl('*/sales_order/changePaymentMethod'),
        ));
        
      /* $this->getMassactionBlock()->addItem('printorder', array(
             'label'=> Mage::helper('sales')->__('printorder'),
              'url' => $this->getUrl('*///sales_order/massPrintorder'),
        	   //'target'    => '_blank'
     		 
        //));*/
         $this->getMassactionBlock()->addItem('printorder', array(
             'label'=> Mage::helper('sales')->__('Print To And From'),
             'url'  => $this->getUrl('*/sales_order/massPrintorder', array('_current'=>true)),
             'target' => '_blank'
           )       
        );
        
          $this->getMassactionBlock()->addItem('getawb', array(
             'label'=> Mage::helper('sales')->__('GetDHLAwb'),
             'url'  => $this->getUrl('*/sales_order/getawb', array('_current'=>true)),
             'target' => '_blank',
             'additional' => array(
                    'visibility' =>array(
                         'name' => 'unitprice',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Declared Value'),
						 )
                      
           )       
        ));
        
		$this->getMassactionBlock()->addItem('sentpayuInvoice', array(
             'label'=> Mage::helper('sales')->__('Send Payu Invoice'),
             'url'  => $this->getUrl('*/sales_order/sentpayuInvoice'),
        ));
        
		$this->getMassactionBlock()->addItem('lowerShipingPrice', array(
             'label'=> Mage::helper('sales')->__('Send New Offer Link'),
             'url'  => $this->getUrl('*/sales_order/lowerShipingPrice'),
			 'additional' => array(
                    'visibility' =>array(
                         'name' => 'newshipping_amount',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Discount Amount'),
						 )
             )
        ));

		$this->getMassactionBlock()->addItem('editshippingaddress', array(
             'label'=> Mage::helper('sales')->__('ShippingAddressChange'),
             'url'  => $this->getUrl('*/sales_order/editshippingaddress'),
			 'additional' => array(
                    'visibility' =>array(
                         'name' => 'firstname',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Firstname'),
						 ),
					array(
                         'name' => 'lastname',
						 'align' => 'center',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Lastname'),
						 ),
					array(
						 'name' => 'street',
						 'type'  => 'text',
						 'label' => Mage::helper('sales')->__('street'),
						 ),
					array(
						 'name' => 'mobile',
						 'type'  => 'text',
						 'label' => Mage::helper('sales')->__('mobile'),
						 ),
					array(
						 'name' => 'city',
						 'type'  => 'text',
						'align' => 'left',
						'label'     => 'city',
			        	 ),					
					array(
						 'name' => 'zip',
						 'type'  => 'text',
						'align' => 'left',
						 'label' => Mage::helper('sales')->__('zip'),
						 ),

					array(
						 'name' => 'country',
						 'type'  => 'select',
						'align' => 'left',
						'label'     => 'Country',
			        	'values'    => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
						 ),
					array(
						 'name' => 'state',
						 'type'  => 'text',
						'align' => 'left',
						'label'     => 'state',
			        	 ),
					
             )
        ));
	$this->getMassactionBlock()->addItem('checkcoupon', array(
             'label'=> Mage::helper('sales')->__('Check Coupon'),
             'url'  => $this->getUrl('*/sales_order/checkcoupon'),
			 'additional' => array(
                    'visibility' =>array(
                         'name' => 'coupon_code',
                         'type'  => 'text',
						 'label' => Mage::helper('sales')->__('Coupon Code'),
						 )
             )
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
