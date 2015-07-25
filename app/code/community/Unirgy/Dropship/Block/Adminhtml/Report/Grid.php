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

class Unirgy_Dropship_Block_Adminhtml_Report_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('reportGrid');
        $this->setDefaultSort('order_created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function t($table)
    {
        return Mage::getSingleton('core/resource')->getTableName($table);
    }

    protected $_couponCodeColumn;
    
    protected function _getFlatExpressionColumn($key, $bypass=true)
    {
    	$result = $bypass ? $key : null;
    	switch ($key) {
            case 'tracking_price':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(st.final_price,0)) from {$this->t('sales_flat_shipment_track')} st where parent_id=main_table.entity_id)");
    			break;
    		case 'tracking_ids':
    			$result = new Zend_Db_Expr("(select group_concat(concat(st.".Mage::helper('udropship')->trackNumberField().", ' (', IFNULL(round(st.final_price,2),'N/A'), ')') separator '\n') from {$this->t('sales_flat_shipment_track')} st where parent_id=main_table.entity_id)");
    			break;
    		case 'tax_amount':
    			$result = new Zend_Db_Expr("(select sum(tax_amount) from {$this->t('sales_flat_order_item')} oi where oi.order_id=main_table.order_id)");
    			break;
    		/*Start....
    		*Craftsvilla Comment
            **Added extra field base_discount_amount, base_amount_refunded
            **added by suresh on 28-05-2012
            */
    		case 'base_discount_amount':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(si.base_discount_amount,0)) from {$this->t('sales_flat_shipment_item')} si where si.parent_id=main_table.entity_id)");
    			break;
    		case 'base_amount_refunded':
    			$result = new Zend_Db_Expr("(select sum(IFNULL(si.base_amount_refunded,0)) from {$this->t('sales_flat_shipment_item')} si where si.parent_id=main_table.entity_id)");
    			break;
    		case 'telephone':
    			$result = new Zend_Db_Expr("(select soa.telephone from {$this->t('sales_flat_order_address')} soa where soa.parent_id=main_table.order_id AND soa.address_type='billing')");
    			break;
    		case 'shipmentpayout_status':
    			$result = new Zend_Db_Expr("(select s1.shipmentpayout_status from {$this->t('shipmentpayout')} s1 where main_table.increment_id=s1.shipment_id and main_table.order_increment_id=s1.order_id)");
    			break;	
    		case 'citibank_utr':
    			$result = new Zend_Db_Expr("(select s2.citibank_utr from {$this->t('shipmentpayout')} s2 where main_table.increment_id=s2.shipment_id and main_table.order_increment_id=s2.order_id)");
    			break;	
    		case 'shipmentpayout_update_time':
    			$result = new Zend_Db_Expr("(select s3.shipmentpayout_update_time from {$this->t('shipmentpayout')} s3 where main_table.increment_id=s3.shipment_id and main_table.order_increment_id=s3.order_id)");
    			break;
    		case 'shipmentpayout_created_time':
    			$result = new Zend_Db_Expr("(select s4.shipmentpayout_created_time from {$this->t('shipmentpayout')} s4 where main_table.increment_id=s4.shipment_id and main_table.order_increment_id=s4.order_id)");
    			break;				
    		/*End....
    		*Craftsvilla Comment
            **Added extra field base_discount_amount, base_amount_refunded
            **added by suresh on 28-05-2012
            */			
    		case 'coupon_codes':
		    	if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
					$result = new Zend_Db_Expr("concat(
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), ''),
						IF(o.giftcert_code is not null and o.giftcert_code!='', 
							CONCAT(
								IF(o.coupon_code is not null and o.coupon_code!='', '\n', ''),
								concat('Giftcert: ',o.giftcert_code)
							),
							'')
					)");
				} else {
					$result = new Zend_Db_Expr("
						IF(o.coupon_code is not null and o.coupon_code!='', concat('Coupon: ',o.coupon_code), '')
					");
				}
				break;
    	}
    	return $result;
    }
    
    protected function _prepareCollection()
    {
        if (Mage::helper('udropship')->isSalesFlat()) {
            $res = Mage::getSingleton('core/resource');

            $collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
            
            /*Craftsvilla Comment
            **Added extra field commission_percent and changed tax_amount to base_tax_amount, shipping_amount to base_shipping_amount
            **Commented tax_amount and added in shipment flat table.
            **added by suresh on 24-05-2012
            */
            
            $collection->getSelect()
                ->join(array('t'=>$res->getTableName('sales/shipment')), 't.entity_id=main_table.entity_id', array('udropship_vendor', 'udropship_available_at', 'udropship_method', 'udropship_method_description', 'udropship_status', 'shipping_amount'=>'base_shipping_amount', 'subtotal'=>'base_total_value', 'total_cost'=>'total_cost', 'tax_amount'=>'base_tax_amount', 'commission_percent','updated_at'))
                ->join(array('o'=>$res->getTableName('sales/order')), 'o.entity_id=main_table.order_id', array('base_grand_total', 'order_status'=>'o.status', 'email'=>'customer_email'))
                ->join(array('a'=>$res->getTableName('sales/order_address')), 'a.parent_id=o.entity_id and a.address_type="shipping"', array('region_id', 'country_id', 'domestic_international'=>('IF(a.country_id!="IN", "International", "Domestic")')))
                ->join(array('b'=>$res->getTableName('sales/order_payment')), 'b.parent_id=o.entity_id', array('b.method'))
                //->join(array('c'=>'shipmentpayout'), 'main_table.increment_id=c.shipment_id', array('c.shipmentpayout_status', 'c.citibank_utr', 'c.shipmentpayout_update_time', 'c.shipmentpayout_created_time'))
                //->joinLeft(array('c'=>'shipmentpayout'), 'main_table.increment_id=c.shipment_id and main_table.order_increment_id=c.order_id', array('c.shipmentpayout_status', 'c.citibank_utr', 'c.shipmentpayout_update_time', 'c.shipmentpayout_created_time'))
                ->columns(array(
                    'tracking_price'=>$this->_getFlatExpressionColumn('tracking_price'),
                    'tracking_ids'=>$this->_getFlatExpressionColumn('tracking_ids'),
                    //'subtotal'=>$subtotal,
                    //'tax_amount'=>$this->_getFlatExpressionColumn('tax_amount'),
                    'base_discount_amount'=>$this->_getFlatExpressionColumn('base_discount_amount'),
                    'base_amount_refunded'=>$this->_getFlatExpressionColumn('base_amount_refunded'),
                	'telephone'=>$this->_getFlatExpressionColumn('telephone'),
                	'shipmentpayout_status'=>$this->_getFlatExpressionColumn('shipmentpayout_status'),
                	'citibank_utr'=>$this->_getFlatExpressionColumn('citibank_utr'),
                	'shipmentpayout_update_time'=>$this->_getFlatExpressionColumn('shipmentpayout_update_time'),
                	'shipmentpayout_created_time'=>$this->_getFlatExpressionColumn('shipmentpayout_created_time'),
                	'coupon_codes' => $this->_getFlatExpressionColumn('coupon_codes')
                ));

        } else {
            $eav = Mage::getSingleton('eav/config');
            $sioAttr = $eav->getAttribute('shipment_item', 'order_item_id');
            $stnAttr = $eav->getAttribute('shipment_track', 'number');
            $stfpAttr = $eav->getAttribute('shipment_track', 'final_price');
            $oarAttr = $eav->getAttribute('order_address', 'region_id');
            $oatAttr = $eav->getAttribute('order_address', 'address_type');
            $oacAttr = $eav->getAttribute('order_address', 'country_id');
            $oaeAttr = $eav->getAttribute('order', 'customer_email');

            $subtotal = "(select sum(base_row_total) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";

            $taxAmount = "(select sum(base_tax_amount) from {$this->t('sales_flat_order_item')} oi inner join {$this->t('sales_order_entity_int')} sio on sio.value=oi.item_id and sio.attribute_id={$sioAttr->getId()} inner join {$this->t('sales_order_entity')} si on si.entity_id=sio.entity_id where si.parent_id=e.entity_id)";

            $trackingIds = "(select group_concat(concat(stn.value, ' (', IFNULL(round(_stfp.value,2),'N/A'), ')') separator '\n') from {$this->t('sales_order_entity_text')} stn inner join {$this->t('sales_order_entity')} st on st.entity_id=stn.entity_id left join {$this->t('sales_order_entity_decimal')} _stfp on st.entity_id=_stfp.entity_id where stn.attribute_id={$stnAttr->getId()} and _stfp.attribute_id={$stfpAttr->getId()} and st.parent_id=e.entity_id)";

            $trackingPrice = "(select sum(IFNULL(stfp.value,0)) from {$this->t('sales_order_entity_decimal')} stfp inner join {$this->t('sales_order_entity')} st on st.entity_id=stfp.entity_id where stfp.attribute_id={$stfpAttr->getId()} and st.parent_id=e.entity_id)";
            
   			$base_discount_amount = new Zend_Db_Expr("(select sum(IFNULL(si.base_discount_amount,0)) from {$this->t('sales_flat_shipment_item')} si where si.parent_id=main_table.entity_id)");


   			$base_amount_refunded = new Zend_Db_Expr("(select sum(IFNULL(si.base_amount_refunded,0)) from {$this->t('sales_flat_shipment_item')} si where si.parent_id=main_table.entity_id)");
   			
   			$telephone = new Zend_Db_Expr("(select telephone from {$this->t('sales_flat_order_address')} sfoa where sfoa.entity_id=main_table.order_id)");

            $collection = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('increment_id')
                ->addAttributeToSelect('udropship_status')
                ->addAttributeToSelect('udropship_vendor')
                ->addAttributeToSelect('base_shipping_amount')
                ->addAttributeToSelect('commission_percent')
                ->addAttributeToSelect('total_cost')
                ->addAttributeToSelect('total_qty')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                ->joinAttribute('order_status', 'order/status', 'order_id')
                ->joinAttribute('order_coupon_code', 'order/coupon_code', 'order_id', null, 'left')
                ->joinAttribute('base_grand_total', 'order/base_grand_total', 'order_id')
                ->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
                ->addExpressionAttributeToSelect('tracking_ids', $trackingIds, 'entity_id')
                ->addExpressionAttributeToSelect('tracking_price', $trackingPrice, 'entity_id')
                ->addExpressionAttributeToSelect('subtotal', $subtotal, 'entity_id')
                ->addExpressionAttributeToSelect('tax_amount', $taxAmount, 'entity_id')
                ->addExpressionAttributeToSelect('base_discount_amount', $base_discount_amount, 'entity_id')
                ->addExpressionAttributeToSelect('base_amount_refunded', $base_amount_refunded, 'entity_id')
                ->addExpressionAttributeToSelect('telephone', $telephone, 'entity_id')
            ;
            
            if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
            	$collection->joinAttribute('order_giftcert_code', 'order/giftcert_code', 'order_id', null, 'left');
            }
            
            if (Mage::helper('udropship')->isModuleActive('Unirgy_Giftcert')) {
				$couponCodesExpr = "concat(
					IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', concat('Coupon: ',{{order_coupon_code}}), ''),
					IF({{order_giftcert_code}} is not null and {{order_giftcert_code}}!='', 
						CONCAT(
							IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', '\n', ''),
							concat('Giftcert: ',{{order_giftcert_code}})
						),
						'')
				)";
                $couponCodesExprAttrs = array('order_giftcert_code');
                if ($collection->getAttribute('order_coupon_code')->getBackend()->isStatic()) {
                    $couponCodesExpr = str_replace('{{order_coupon_code}}', '_table_order_coupon_code.coupon_code', $couponCodesExpr);
                } else {
                    $couponCodesExprAttrs[] = 'order_coupon_code';
                }
			} else {
				$couponCodesExpr = "
					IF({{order_coupon_code}} is not null and {{order_coupon_code}}!='', concat('Coupon: ',{{order_coupon_code}}), '')
				";
                $couponCodesExprAttrs = array();
			}
            if ($collection->getAttribute('order_coupon_code')->getBackend()->isStatic()) {
                $couponCodesExpr = str_replace('{{order_coupon_code}}', '_table_order_coupon_code.coupon_code', $couponCodesExpr);
            } else {
                $couponCodesExprAttrs[] = 'order_coupon_code';
            }
            $collection->addExpressionAttributeToSelect('coupon_codes', new Zend_Db_Expr($couponCodesExpr), $couponCodesExprAttrs);

            $collection->getSelect()
                ->join(array('oa'=>$this->t('sales_order_entity')), 'oa.parent_id=_table_order_increment_id.entity_id', array())
                ->join(array('oat'=>$this->t('sales_order_entity_varchar')), "oat.entity_id=oa.entity_id and oat.attribute_id=".$oatAttr->getId()." and oat.value='shipping'", array())
                ->joinLeft(array('oar'=>$this->t('sales_order_entity_int')), 'oar.entity_id=oa.entity_id and oar.attribute_id='.$oarAttr->getId(), array('region_id'=>'value'))
                ->joinLeft(array('oar'=>$this->t('sales_order_entity_int')), 'oar.entity_id=oa.entity_id and oar.attribute_id='.$oacAttr->getId(), array('country_id'=>'value'))
                ->joinLeft(array('oar'=>$this->t('sales_order_entity_int')), 'oar.entity_id=oa.entity_id and oar.attribute_id='.$oaeAttr->getId(), array('email'=>'value'))
            ;
        }

        //$collection->getSelect()->joinLeft(array('c'=>'shipmentpayout'), 'main_table.increment_id=c.shipment_id', array('c.shipmentpayout_status', 'c.citibank_utr', 'c.shipmentpayout_update_time', 'c.shipmentpayout_created_time'));
        
        /*echo $collection->getSelect()->__tostring();
        exit();*/
        
         //->joinTable('sales_order', 'entity_id=order_id', array('order_status'=>'status'), null , 'left')
        
        $this->setCollection($collection);
        
		/*echo "<pre>";
        print_r($collection->getdata());
        exit();*/
			
        return parent::_prepareCollection();
}

    protected function _prepareColumns()
    {
        $flat = Mage::helper('udropship')->isSalesFlat();

        $poStr = Mage::helper('udropship')->isUdpoActive() ? 'Shipment' : 'PO';
        
        $hlp = Mage::helper('udropship');
		
        $this->addColumn('order_increment_id', array(
            'header'    => $hlp->__('Order #'),
            'index'     => 'order_increment_id',
            'type'      => 'number',
        ));

        $this->addColumn('order_created_at', array(
           	'header'    =>  $hlp->__('Order Date'),
            'index'     => 'order_created_at',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('order_status', array(
            'header'    => $hlp->__('Order Status'),
            'index'     => 'order_status',
            'filter_index' => !$flat ? null : 'o.status',
            'type' => 'options',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));
        
        $this->addColumn('base_grand_total', array(
            'header' => $hlp->__('Order Total'),
            'index' => 'base_grand_total',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('increment_id', array(
            'header'    => $hlp->__("$poStr #"),
            'index'     => 'increment_id',
            'filter_index' => !$flat ? null : 'main_table.increment_id',
            'type'      => 'number',
        ));

        $this->addColumn('created_at', array(
            'header'    => $hlp->__("$poStr Date"),
            'index'     => 'created_at',
            'filter_index' => !$flat ? null : 'main_table.created_at',
            'type'      => 'datetime',
        ));
		//added By Dileswar on dated 05-12-2012
		$this->addColumn('updated_at', array(
            'header'    => $hlp->__("$poStr ShipmentUpdated"),
            'index'     => 'updated_at',
            'filter_index' => !$flat ? null : 't.updated_at',
            'type'      => 'datetime',
        ));
		
		$this->addColumn('delay_status', array(
            'header' => $hlp->__('Delay Status'),
        	'renderer'  => 'Unirgy_Dropship_Block_Adminhtml_Report_RenderSet',
        	'filter' => false
        ));
        
        $this->addColumn('udropship_status', array(
            'header' => $hlp->__("$poStr Status"),
            'index' => 'udropship_status',
            'filter_index' => !$flat ? null : 't.udropship_status',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash(),
        ));

        $this->addColumn('subtotal', array(
            'header' => $hlp->__("$poStr Subtotal"),
            'index' => 'subtotal',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
         
        
		$this->addColumn('total_cost', array(
            'header' => $hlp->__("$poStr Total Cost"),
            'index' => 'total_cost',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
		
		$this->addColumn('tax_amount', array(
            'header' => $hlp->__("$poStr Tax Amount"),
            'index' => 'tax_amount',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tax_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
		
		
        $this->addColumn('shipping_amount', array(
            'header' => $hlp->__("$poStr Shipping Price"),
            'index' => 'shipping_amount',
            'filter_index' => !$flat ? null : 't.shipping_amount',
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('total_qty', array(
            'header'    => $hlp->__("$poStr Total Qty"),
            'index'     => 'total_qty',
        	'filter_index' => !$flat ? null : 't.total_qty',
            'type'      => 'number',
        ));
        
        $this->addColumn('udropship_vendor', array(
            'header' => $hlp->__('Vendor'),
            'index' => 'udropship_vendor',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash(),
            'filter' => 'udropship/vendor_gridColumnFilter'
        ));
		        
        $this->addColumn('tracking_ids', array(
            'header' => $hlp->__('Tracking #'),
            'index' => 'tracking_ids',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tracking_ids'),
        ));

        $this->addColumn('tracking_price', array(
            'header' => $hlp->__('Tracking Total'),
            'index' => 'tracking_price',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('tracking_price'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));

        $this->addColumn('region_id', array(
            'header' => $hlp->__('Tax State'),
            'index' => 'region_id',
            'type' => 'options',
            'options' => Mage::getSingleton('udropship/source')->getTaxRegions(),
            'filter'    => false,
            'sortable'  => false,
        ));
        
        $this->addColumn('coupon_codes', array(
            'header' => $hlp->__('Order coupon codes'),
            'index' => 'coupon_codes',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('coupon_codes'),
        	'type' => 'text',
        	'nl2br' => true,
        ));
        
        $this->addColumn('commission_percent', array(
            'header' => $hlp->__('Commission Percentage'),
            'index' => 'commission_percent',
            'filter_index' => !$flat ? null : 't.commission_percent',
            'type'      => 'number',
       ));
       
       /*Start....
       *Craftsvilla Comment
       **Added extra field base_discount_amount, base_amount_refunded
       **added by suresh on 28-05-2012
       */
       $this->addColumn('base_discount_amount', array(
            'header' => $hlp->__('Discount Amount'),
            'index' => 'base_discount_amount',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('base_discount_amount'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('base_amount_refunded', array(
            'header' => $hlp->__('Refunded Amount'),
            'index' => 'base_amount_refunded',
        	'filter_index' => !$flat ? null : $this->_getFlatExpressionColumn('base_amount_refunded'),
            'type'  => 'price',
            'currency' => 'base_currency_code',
            'currency_code' => Mage::getStoreConfig('currency/options/base'),
        ));
        
        $this->addColumn('method', array(
            'header'    => Mage::helper('sales')->__('Payment Method Name'),
            'index'     => 'method',
			'type'		=> 'options',
			'options'   => array('checkmo' => 'Cash On Delivery Old', 'secureebs_standard' => 'EBS', 'paypal_standard'=>'PayPal Website Payments Standard', 'purchaseorder' =>'EBS-B', 'gharpay_standard' => 'Cash In Advance','cashondelivery' => 'Cash On Delivery','avenues_standard' => 'Ccavenue Payment','ccavenue_standard' => 'Ccavenue Old','m2epropayment' => 'E-Bay Payment','payucheckout_shared' => 'PayU Checkout'),
        ));
        
        $this->addColumn('country_id', array(
            'header' => Mage::helper('sales')->__('Country'),
            'index' => 'country_id',
        ));
        
        $this->addColumn('telephone', array(
            'header' => Mage::helper('sales')->__('Telephone'),
            'index' => 'soa.telephone',
            
        ));
        
        $this->addColumn('email', array(
            'header' => Mage::helper('sales')->__('Email'),
            'index' => 'email',
            
        ));
        
        $this->addColumn('domestic_international', array(
            'header' => Mage::helper('sales')->__('International/Domestic'),
            'index' => 'domestic_international',
        	'filter' => false,
        ));
 //?? Add a extra field name Refunded By dileswar On dated 19-02*-2013 ??//       
        $this->addColumn('shipmentpayout_status', array(
            'header' => Mage::helper('shipmentpayout')->__('Shipmentpayout Status'),
            'index' => 'shipmentpayout_status',
        	'type'	=> 'options',
			'options'  => array('2' => 'Refunded','1' => 'Paid', '0' => 'Unpaid'),
        ));
        
        $this->addColumn('shipmentpayout_created_time', array(
            'header'    => $hlp->__('Shipped Date'),
            'index'     => 'shipmentpayout_created_time',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('shipmentpayout_update_time', array(
            'header'    => $hlp->__('Shipmentpayout Date'),
            'index'     => 'shipmentpayout_update_time',
            'type'      => 'datetime',
        ));
        
        $this->addColumn('citibank_utr', array(
            'header' => Mage::helper('sales')->__('Citibank UTR No.'),
            'index' => 'citibank_utr',
            
        ));
        
       
        
        /*End....
       *Craftsvilla Comment
       **Added extra field base_discount_amount, base_amount_refunded
       **added by suresh on 28-05-2012
       */

        $this->addExportType('*/*/exportCsv', Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('adminhtml')->__('XML'));
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}
