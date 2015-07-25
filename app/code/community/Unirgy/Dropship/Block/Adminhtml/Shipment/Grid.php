<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Adminhtml_Shipment_Grid
    extends Mage_Adminhtml_Block_Sales_Shipment_Grid
{
    
	protected function _getFlatExpressionColumn($key, $bypass=true)
    {
    	$result = $bypass ? $key : null;
    	switch ($key) {
    		case 'base_discount_amount':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(si.base_discount_amount,0)) from sales_flat_shipment_item si where si.parent_id=main_table.entity_id)");
    			break;
    		case 'base_amount_refunded':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(si.base_amount_refunded,0)) from sales_flat_shipment_item si where si.parent_id=main_table.entity_id)");
    			break;
    		case 'country_id':
    			$result = new Zend_Db_Expr("(select country_id from sales_flat_order_address sa where sa.parent_id=main_table.order_id and sa.address_type='shipping')");
    			break;	
    	}
    	return $result;
    }
    
	protected function _prepareCollection()
    {
        if (Mage::helper('udropship')->isSalesFlat()) {
            $res = Mage::getSingleton('core/resource');
            $collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
            $collection->getSelect()->join(
                array('t'=>$res->getTableName('sales/shipment')), 
                't.entity_id=main_table.entity_id', 
                /**Start..
                ***commented by suresh on 22-05-2012
                ***To add grand_total to grid
                ***'subtotal'=>'total_value'
                **/
                /*array('udropship_vendor', 'udropship_available_at', 'udropship_method', 
                    'udropship_method_description', 'udropship_status', 'shipping_amount'*/
                 array('updated_at','udropship_vendor', 'udropship_available_at', 'udropship_method', 
                    'udropship_method_description', 'udropship_status', 'shipping_amount', 'subtotal'=>'base_total_value' 
                )
            );
            
            
            //$collection->getSelect()->join(array('k'=>$res->getTableName('sales/order_address')), 'k.parent_id=main_table.order_id and k.address_type="shipping"', array('k.country_id'));
            
           /*$collection->getSelect()->columns(array(
      						'base_discount_amount'=>$this->_getFlatExpressionColumn('base_discount_amount'),
            				'base_amount_refunded'=>$this->_getFlatExpressionColumn('base_amount_refunded'),
            				'country_id'=>$this->_getFlatExpressionColumn('country_id')
      			));*/
            
            //$collection->getSelect()->join(array('l'=>$res->getTableName('sales/shipment_item')), 'main_table.entity_id=l.parent_id', array('base_discount_amount1'=>'SUM(l.base_discount_amount)', 'base_amount_refunded1'=>'SUM(l.base_amount_refunded)'));
            
            //$collection->getSelect()->group('main_table.entity_id');
          
            /*echo $collection->getSelect()->__toString();
            exit();*/
            
            /**End..
            ***commented by suresh on 22-05-2012
            ***To add grand_total to grid
            ***'subtotal'=>'total_value'
            **/ 
        } else {
        	/**Start..
            ***commented by suresh on 22-05-2012
            ***To add grand_total to grid
            ***$subtotal
            **/
        	$subtotal = "(select sum(row_total) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";
        	
        	//$base_discount_amount = "(select sum(base_discount_amount) from {$this->l('sales_flat_shipment_item')} oi inner join {$this->l('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";
        	
        	//$base_amount_refunded = "(select sum(base_amount_refunded) from {$this->l('sales_flat_shipment_item')} oi inner join {$this->l('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";
        	
            /**End..
            ***commented by suresh on 22-05-2012
            ***To add grand_total to grid
            ***$subtotal
            **/
            $collection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('created_at')
                ->addAttributeToSelect('total_qty')
                ->addAttributeToSelect('udropship_status')
                ->addAttributeToSelect('udropship_vendor')
				//->addAttributeToSelect('udropship_method_description')
                //->addAttributeToSelect('shipping_amount')
                //->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
                //->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left')
                //->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
                ->addExpressionAttributeToSelect('subtotal', $subtotal, 'entity_id')

                //->addExpressionAttributeToSelect('base_discount_amount', $base_discount_amount, 'entity_id')
               // ->addExpressionAttributeToSelect('base_amount_refunded', $base_amount_refunded, 'entity_id')
            ;
        }
        
        /**Start..
        ***commented by suresh on 22-05-2012
        ***To add payment method and billing telephone to grid
        **/
        $collection->getSelect()
                ->joinLeft('sales_flat_order_payment', 'main_table.order_id = sales_flat_order_payment.parent_id','method')
                ->joinLeft('sales_flat_order_address', 'main_table.order_id = sales_flat_order_address.parent_id AND address_type = "shipping"',array('country' => 'country_id','postcode' => 'postcode'))
                //->where("sales_flat_order_address.address_type='billing'") ;
  				//below condition added By dileswar on dated 30-01-2014 for showing shipment details of last 6 months only
				->where('main_table.created_at > DATE_SUB(NOW(),INTERVAL 180 DAY)');
                
        /**End..
        ***commented by suresh on 22-05-2012
        ***To add payment method and billing telephone to grid
        **/         
        
        //echo $collection->getSelect()->__toString();
        //exit();
                
        /*echo "TOTAL:".$collection->count();
        exit();   */    
                
        /*echo "<pre>";
        print_r($collection->getData());
        exit();*/
        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $flat = Mage::helper('udropship')->isSalesFlat();
        
        $this->addColumn('action',	
        array(
                        'header'    => Mage::helper('sales')->__('Action'),
                        'width'     => '50px',
                        'type'      => 'action',
                        'getter'     => 'getId',
                        'actions'   => array(
        array(
                                'caption' => Mage::helper('sales')->__('View'),
                                'url'     => array('base'=>'*/*/view'),
                                'field'   => 'shipment_id'
        )
        ),
                        'filter'    => false,
                        'sortable'  => false,
                        'is_system' => true
        ));
        
        
        $this->addColumn('increment_id', array(
            'header'    => Mage::helper('sales')->__('Shipment #'),
            'index'     => 'increment_id',
            'filter_index' => !$flat ? null : 'main_table.increment_id',
            'type'      => 'number',
        ));
		$this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Date Shipped'),
            'index'     => 'created_at',
            'filter_index' => !$flat ? null : 'main_table.created_at',
            'type'      => 'datetime',
        ));
		$this->addColumn('updated_at', array(
            'header'    => Mage::helper('sales')->__('Date Updated'),
            'index'     => 'updated_at',
            'filter_index' => !$flat ? null : 't.updated_at',
            'type'      => 'datetime',
        ));
		$this->addColumn('order_increment_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'number',
        ));
		$this->addColumn('udropship_status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));
		$this->addColumn('udropship_vendor', array(
            'header' => Mage::helper('udropship')->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));
		$this->addColumn('country', array(
            'header'    => Mage::helper('sales')->__('Country'),
            'index'     => 'country',
            'type'      => 'text',
			'filter_index' => 'sales_flat_order_address.country_id'
        ));
		$this->addColumn('postcode', array(
            'header'    => Mage::helper('sales')->__('Pincode'),
            'index'     => 'postcode',
            'type'      => 'text',
			'filter_index' => 'sales_flat_order_address.postcode'
        ));
		$this->addColumn('method', array(
            'header'    => Mage::helper('sales')->__('Payment Method Name'),
            'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','ccavenue_standard' => 'Ccavenue Payment','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout'),
        ));
		$this->addColumn('sku', array(
            'header'    => Mage::helper('udropship')->__('Sku'),
            'index'     => 'sku',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'udropship/renderer_productdetailshipment',
        ));
		$this->addColumn('total_qty', array(
            'header' => Mage::helper('sales')->__('Total Qty'),
            'index' => 'total_qty',
            //'filter_index' => !$flat ? null : 'main_table.total_qty',
			'filter'    => false,
            'sortable'  => false,
            'type'  => 'number',
        ));
		$this->addColumn('courier', array(
            'header'    => Mage::helper('udropship')->__('Courier'),
            'index'     => 'courier',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'udropship/renderer_courier',
        ));
		$this->addColumn('tracking_code', array(
            'header'    => Mage::helper('udropship')->__('Tracking Code'),
            'index'     => 'tracking_code',
            'filter'    => false,
            'sortable'  => false,
            'renderer' => 'udropship/renderer_trackingcode',
        ));
		
        

        

        /*$this->addColumn('order_created_at', array(
            'header'    => Mage::helper('sales')->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));

        if (Mage::helper('udropship')->isSalesFlat()) {
            $this->addColumn('shipping_name', array(
                'header' => Mage::helper('sales')->__('Ship to Name'),
                'index' => 'shipping_name',
            ));
        } else {
            $this->addColumn('shipping_firstname', array(
                'header' => Mage::helper('sales')->__('Ship to First name'),
                'index' => 'shipping_firstname',
            ));

            $this->addColumn('shipping_lastname', array(
                'header' => Mage::helper('sales')->__('Ship to Last name'),
                'index' => 'shipping_lastname',
            ));
        }*/

        

        /*$this->addColumn('shipping_amount', array(
            'header' => Mage::helper('sales')->__('Shipping Price'),
            'index' => 'shipping_amount',
            'type'  => 'number',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));*/

        
		
        

        /*$this->addColumn('udropship_method_description', array(
            'header' => Mage::helper('udropship')->__('Method'),
            'index' => 'udropship_method_description',
        ));*/
		
		$this->addColumn('subtotal', array(
            'header' => Mage::helper('udropship')->__("Grand Total"),
            'index' => 'subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
			'filter'    => false,
            'sortable'  => false,
        ));
        
        
                
        
        
        /*$this->addColumn('telephone', array(
            'header' => Mage::helper('sales')->__('Telephone'),
            'index' => 'telephone',
            
        ));
        
        $this->addColumn('base_discount_amount', array(
            'header' => Mage::helper('udropship')->__("Discount"),
            'index' => 'base_discount_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('base_amount_refunded', array(
            'header' => Mage::helper('udropship')->__("Refund"),
            'index' => 'base_amount_refunded',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
		$this->addColumn('country_id', array(
            'header' => Mage::helper('sales')->__('Country'),
            'index' => 'country_id',
        ));*/
	
        
        
       // $this->addExportType('*/*/exportCsv', Mage::helper('udropship')->__('CSV'));
        //$this->addExportType('*/*/exportExcel', Mage::helper('udropship')->__('Excel XML'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
    
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

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('shipment_ids');
        $this->getMassactionBlock()->setUseSelectAll(false);

            $this->getMassactionBlock()->addItem('pdfshipments_order', array(
                 'label'=> Mage::helper('sales')->__('PDF Packingslips'),
                 'url'  => $this->getUrl('*/sales_shipment/pdfshipments'),
            ));
            
            $this->getMassactionBlock()->addItem('multipleprint_order', array(
                 'label'=> Mage::helper('sales')->__('Print'),
                 'url'  => $this->getUrl('*/sales_order_shipment/multipleprint'),
            ));
			 $this->getMassactionBlock()->addItem('downloadexcel', array(
                 'label'=> Mage::helper('sales')->__('Download COD Shipments'),
                 'url'  => $this->getUrl('*/sales_order_shipment/codshipments'),
            ));
            
			$this->getMassactionBlock()->addItem('downloadexcelmanifest', array(
                 'label'=> Mage::helper('sales')->__('COD Delayed Pickup Shipments'),
                 'url'  => $this->getUrl('*/sales_order_shipment/codshipmentManifest'),
            ));
			$this->getMassactionBlock()->addItem('requestpikuparamex', array(
                 'label'=> Mage::helper('sales')->__('Request Pickup'),
                 'url'  => $this->getUrl('*/sales_order_shipment/requestpikuparamex'),
            ));

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/*', array('_current' => true));
    }
   
}
