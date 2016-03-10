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

class Unirgy_Dropship_Model_Vendor_Statement extends Unirgy_Dropship_Model_Vendor_Statement_Abstract
{
    protected $_eventPrefix = 'udropship_vendor_statement';
    protected $_eventObject = 'statement';

    protected function _construct()
    {
        $this->_init('udropship/vendor_statement');
        parent::_construct();
    }

    protected function _getPoCollection()
    {

		$stPoStatuses = $this->getVendor()->getData('statement_po_status');
		$stPoStatuses[] = 7;
        if (!is_array($stPoStatuses)) {
            $stPoStatuses = explode(',', $stPoStatuses);
        }
        $poType = $this->getVendor()->getStatementPoType();
		$this->getResource()->fixStatementDate($this->getVendor(), $poType, $stPoStatuses, $this->getOrderDateFrom(), $this->getOrderDateTo());
        if (Mage::helper('udropship')->isSalesFlat()) {
			
            $res = Mage::getSingleton('core/resource');
            $pos = $poType == 'po' ? Mage::getResourceModel('udpo/po_grid_collection') : Mage::getResourceModel('sales/order_shipment_grid_collection');
            $pos->getSelect()->join(
                array('t'=>$poType == 'po' ? $res->getTableName('udpo/po') : $res->getTableName('sales/shipment')),
                't.entity_id=main_table.entity_id'/*,
                array('udropship_vendor', 'udropship_available_at', 'udropship_method',
                    'udropship_method_description', 'udropship_status', 'shipping_amount'
                )*/
            )
			->join(array('b'=>'sales_flat_order_payment'), 'b.parent_id=main_table.order_id', array('b.method'))
            ->where("(t.udropship_status = 1 AND b.method!='cashondelivery') OR (t.udropship_status = 7 AND b.method='cashondelivery')")
            ->where("t.udropship_vendor=?", $this->getVendorId())
            ->where("t.created_at IS NOT NULL")
            ->where("t.created_at!='0000-00-00 00:00:00'")
            ->where("t.created_at>=?", $this->getOrderDateFrom())
            ->where("t.created_at<=?", $this->getOrderDateTo())
            ->where("(main_table.statement_id=? OR main_table.statement_id IS NULL OR main_table.statement_id='')", $this->getStatementId())
            ->order('main_table.entity_id asc');
			
        } else {
			
            $pos = Mage::getModel('sales/order_shipment')->getCollection()
			    ->addAttributeToSelect('*')
                ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                ->addAttributeToFilter('udropship_vendor', $this->getVendorId())
                ->addAttributeToFilter('created_at', array('notnull'=>true))
                ->addAttributeToFilter('created_at', array('neq'=>'0000-00-00 00:00:00'))
                ->addAttributeToFilter('created_at', array(
                    'date' => true,
                    'from' => $this->getOrderDateFrom(),
                    'to' => $this->getOrderDateTo(),
                ))
                ->addAttributeToSort('po_id', 'asc')
                ->addAttributeToFilter('udropship_status', array('in'=>$stPoStatuses))
                ->addAttributeToFilter('statement_id', array($this->getStatementId(), array('null'=>true), ''), 'left')
            ;
			//$pos->getSelect()->join(array('b'=>'sales_flat_order_payment'), 'b.parent_id=main_table.order_id', array('b.method'));
			
			}
			
        return $pos;
    }
    
    protected $_poCollection;
    public function getPoCollection($reload=false)
    {
        if (is_null($this->_poCollection) || $reload) {
            $this->_poCollection = $this->_getPoCollection();
            self::processPos($this->_poCollection, $this->getVendor()->getStatementSubtotalBase());
        }
        return $this->_poCollection;
    }
    
    public function addPayout($payout)
    {
        $this->_payouts[] = $payout->getData();
    }
    
    public function fetchOrders()
    {
		$hlp = Mage::helper('udropship');
        $core = Mage::helper('core');
        $vendor = $this->getVendor();
		
        $this->setPoType($vendor->getStatementPoType());
		$pos = $this->getPoCollection();
        if(($pos->getData()) == NULL)
			{
				return NULL;
			}
		else
		{
		$this->_resetOrders();
        $this->_resetTotals();
        $this->_cleanAdjustments();
        $this->_payouts = array();
        $this->setTotalPaid(0);
		
        
		
        $hlp->collectPoAdjustments($pos, true);
        
        Mage::dispatchEvent('udropship_vendor_statement_pos', array(
            'statement'=>$this,
            'pos'=>$pos
        ));
        
        $totals_amount = $this->_totals_amount;
        
        foreach ($pos as $id=>$po) {
			$order = $this->initOrder($po);

            Mage::dispatchEvent('udropship_vendor_statement_row', array(
                'statement'=>$this,
                'po'=>$po,
                'order'=>&$order
            ));

            $order = $this->calculateOrder($order);
            $totals_amount = $this->accumulateOrder($order, $totals_amount);
            
            $this->_orders[$id] = $order;
        }

        Mage::dispatchEvent('udropship_vendor_statement_totals', array(
            'statement'=>$this,
        	'totals'=>&$totals_amount,
            'totals_amount'=>&$totals_amount
        ));
        
        $this->_totals_amount = $totals_amount;
        
        Mage::dispatchEvent('udropship_vendor_statement_collect_payouts', array(
            'statement'=>$this,
        ));
        
        $this->_calculateAdjustments();
        $this->finishStatement();
        
        return $this;
		}
    }
    
    public function getAdjustmentPrefix()
    {
        return Mage::helper('udropship')->getAdjustmentPrefix('statement');
    }
    
    public function getPdf()
    {
        return Mage::getModel('udropship/pdf_statement')
            ->before()->addStatement($this)->after()->getPdf();
    }
    
    public function createPayout()
    {
        if (!Mage::helper('udropship')->isUdpayoutActive()) Mage::throwException('Payout module is inactive or not installed');
        if ($this->getTotalDue()<=0) Mage::throwException('Statement "total due" must be positive');
        $payout = Mage::helper('udpayout')->createPayout(
                $this->getVendor(), 
                Unirgy_DropshipPayout_Model_Payout::STATUS_PROCESSING, 
                Unirgy_DropshipPayout_Model_Payout::TYPE_STATEMENT
            )
            ->setPoType($this->getPoType())
            ->addOrders($this->getUnpaidOrders(), false)
            ->setStatementId($this->getStatementId())
            ->finishPayout();
        if (abs($this->getTotalDue()-$payout->getTotalDue())>0.001) {
            $payout->addAdjustment(
                $payout->createAdjustment(
                    $this->getTotalDue()-$payout->getTotalDue(), 
                    Mage::helper('udropship')->__('Internal adjustment to sync payout with statement total due')
                )
                ->setForcedAdjustmentPrefix(Mage::helper('udropship')->getAdjustmentPrefix('statement:payout'))
            );
            $payout->finishPayout();
        }
        return $payout;
    }

    public function pay()
    {
        if (!Mage::helper('udropship')->isUdpayoutActive()) Mage::throwException('Payout module is inactive or not installed');
        if ($this->getTotalDue()<=0) Mage::throwException('Statement "total due" must be positive');
        $payout = $this->createPayout();
        $payout->pay();
        $this->completePayout($payout);
    }
    
    public function completePayout($payout)
    {
        $this->addPaidAmount($payout->getTotalPaid());
        $this->markPosPaid()->save();
        $ptCol = Mage::getResourceModel('udpayout/payout_collection')
            ->addFieldToFilter('statement_id', $this->getStatementId())
            ->addFieldToFilter('payout_status', Unirgy_DropshipPayout_Model_Payout::STATUS_HOLD);
        foreach ($ptCol as $pt) {
            $pt->cancel();
        }
    }
   /* public function getRefundedOrders()
    {
        return $this->_getFilteredOrders(false);
    }*/
    public function getUnpaidOrders()
    {
        return $this->_getFilteredOrders(false);
    }
    
    public function getPaidOrders()
    {
        return $this->_getFilteredOrders(true);
    }
    
    protected function _getFilteredOrders($paid=false)
    {
        $filtered = array();
        $this->initTotals();
        foreach ($this->_orders as $sId => $order) {
            if (!empty($order['paid']) == $paid) $filtered[$sId] = $order;
        }
        return $filtered;
    }
    
    public function markPosPaid()
    {
        $this->getResource()->markPosPaid($this);
        $this->initTotals();
        foreach ($this->_orders as &$order) {
            $order['paid'] = true;
        }
        unset($order);
        $this->_compactTotals();
        return $this;
    }

    public function send()
    {
        $hlp = Mage::helper('udropship');
        $core = Mage::helper('core');
        $vendor = $this->getVendor();
        $data = array();
        //print_r($this);exit;
		$statementid = $this->getStatementId();
        $store = Mage::app()->getDefaultStoreView();

        $hlp->setDesignStore($store);
		$dateMonth = date('M',strtotime($this->getOrderDateFrom()));
		$dateYear = date('Y',strtotime($this->getOrderDateFrom()));
		$vendorId = $vendor->getId();
		$url = Mage::getBaseUrl('media').'statementreport/'.$vendorId.'/invoice-'.$vendorId.'-'.$statementid.'.pdf';
		
	$data= array(
            'statement'  => $this,
            'vendor'     => $vendor,
            'store'      => $store,
            'date_from'  => Mage::app()->getLocale()->date($core->formatDate($this->getOrderDateFrom()))->toString('dd-MM-yy'),
            'date_to'    => Mage::app()->getLocale()->date($core->formatDate($this->getOrderDateTo()))->subDay(1)->toString('dd-MM-yy'),
			'url_report' => $url
        );
        $template = $vendor->getStatementEmailTemplate();
        if (!$template) {
            $template = Mage::getStoreConfig('udropship/statement/email_template');
        }
        $identity = Array('name'  => 'Craftsvilla Finance',
					'email' => 'finance@craftsvilla.com');
        Mage::getModel('udropship/email')->sendTransactional(
            $template,
            $identity,
            $vendor->getBillingEmail(),
            $vendor->getVendorName(),
            $data
        );
        $hlp->setDesignStore();

        if (!$this->getEmailSent()) {
            $this->setEmailSent(1)->save();
        }

        return $this;
    }
    
    
    public function getCommission($shipmentId)
	{ 
		
		$hlp = Mage::helper('udropship');
        $actualServiceTax = $hlp->getServicetaxCv($shipmentId);
		$po = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
		$order = array(
            'id' => $hlp->getPoOrderIncrementId($po),
			'com_percent' => $hlp->getVendorCommission($po->getUdropshipVendor(),$shipmentId),
            'order_id' => $po->getOrderId(),
            'po_id' => $po->getId(),
            'order_created_at' => $hlp->getPoOrderCreatedAt($po),
            'order_increment_id' => $hlp->getPoOrderIncrementId($po),
            'po_increment_id' => $shipmentId,
            'itemised_total_shippingcost' => $po->getItemisedTotalShippingcost(),
			'base_shipping_amount' => $po->getBaseShippingAmount(),
            'udropship_vendor' => $po->getUdropshipVendor(),
			'cod_fee' => $po->getCodFee(),
        	'subtotal' =>  $po->getBaseTotalValue(),
        );
      
	   $base_shipping_amount = $order['base_shipping_amount'];
	  	$total_amount1 = $order['subtotal'];
			$total_amount = $order['subtotal'];
			
			$_order = Mage::getModel('sales/order')->loadByIncrementId($order['id']);
		 
		   	$orderBaseShippingamount = $_order->getBaseShippingAmount();
		   $orderid = $_order->getEntityId();
		   $discountAmountCoupon = 0;
		   $disCouponcode = '';
			$_orderCurrencyCode = $_order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') )
				$total_amount = $order['subtotal']/1.5;
            	$commission_amount = $order['com_percent'];
		    	$vendorId = $order['udropship_vendor'];
		    	$couponCodeId = Mage::getModel('salesrule/coupon')->load($_order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $_order->getBaseDiscountAmount();
					$disCouponcode = $_order->getCouponCode();
				}
		    		
		    	$vendor_amount = (($total_amount+$base_shipping_amount+$discountAmountCoupon)*(1-($commission_amount/100)*(1+$actualServiceTax)));			 
				$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)*1.00) - $vendor_amount);
					$order['com_percent'] *= 1;
					//$order['com_amount'] = $kribha_amount/1.1400;
					$order['com_amount'] = $kribha_amount/(1+$actualServiceTax);
					return number_format($order['com_amount'],2);
		
	}
    public function getCommissionLogistic($shipmentId){

        $actualServiceTax = Mage::helper('udropship')->getServicetaxCv($shipmentId);
        $idreadcon = Mage::getSingleton('core/resource')->getConnection('custom_db');
        $shipmentlogistic = "SELECT `intshipingcost` as logisticcharge FROM `shipmentpayout`  WHERE `shipment_id` = '".$shipmentId."'";
        $resultLogistic = $idreadcon->query($shipmentlogistic)->fetch();
        $actuallogisticcharge = number_format(($resultLogistic['logisticcharge']),2);
        return $actuallogisticcharge;



    }
    
}
