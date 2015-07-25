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

abstract class Unirgy_Dropship_Model_Vendor_Statement_Abstract extends Mage_Core_Model_Abstract implements Unirgy_Dropship_Model_Vendor_Statement_Interface
{

    abstract public function getAdjustmentPrefix();

    public function getVendor()
    {
        return Mage::helper('udropship')->getVendor($this->getVendorId());
    }

    public function initOrder($po)
    {
		$hlp = Mage::helper('udropship');
		//For get Cod Fee added some lin By Dileswar on Dated 13-02-2013
		$shipmentId = $po->getIncrementId();
		$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
		$codFee = $shipment->getCodFee();
		
		
		$order = array(
            'po_id' => $po->getId(),
            'date' => $hlp->getPoOrderCreatedAt($po),
            'id' => $hlp->getPoOrderIncrementId($po),
            //'com_percent' => $po->getCommissionPercent(),
			//Added by Gayatri on dated 16-01-2014
        	'com_percent' => '20',
			'adjustments' => $po->getAdjustments(),
            'order_id' => $po->getOrderId(),
            'po_id' => $po->getId(),
            'order_created_at' => $hlp->getPoOrderCreatedAt($po),
            'order_increment_id' => $hlp->getPoOrderIncrementId($po),
            'po_increment_id' => $shipmentId,
            'po_created_at' => $po->getCreatedAt(),
            'base_shipping_amount' => $po->getBaseShippingAmount(),
			'itemised_total_shippingcost' => $po->getItemisedTotalShippingcost(),
            'udropship_vendor' => $po->getUdropshipVendor (),
            'po_statement_date' => $po->getStatementDate(),
			'cod_fee' => $codFee,
			'po_type' => $po instanceof Unirgy_DropshipPo_Model_Po ? 'po' : 'shipment'
        );
	//print_r($order);exit;	
		
		 $order['amounts'] = array_merge($this->_getEmptyTotals(), array(
        	'subtotal' => $this->getVendor()->getStatementSubtotalBase() == 'cost' ? $po->getTotalCost() : $po->getBaseTotalValue(),
            'shipping' => $po->getShippingAmount(),
            'tax' => $po->getBaseTaxAmount(),
            'handling' => $po->getBaseHandlingFee(),
        	'trans_fee' => $po->getTransactionFee(),
            'adj_amount' => $po->getAdjustmentAmount(),
        ));
        
        $order = array();
        return $order;
    }

    public function calculateOrder($order)
    {  
	//print_r($order); exit;
	           // $total_amount1 = $shipmentpayout_report1_val['subtotal'];
				//$total_amount = $shipmentpayout_report1_val['subtotal'];
		//***start*******Below Line Added By Manoj sir & Dileswar for get Commision amount on Pdf on dated 13-02-2013**************//		
			/*	$total_amount1 = $order['amounts']['subtotal'];
				$total_amount = $order['amounts']['subtotal'];
				
				$_liveDate = "2012-08-21 00:00:00";
				$_order = Mage::getModel('sales/order')->loadByIncrementId($order['id']);
				$_orderCurrencyCode = $_order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($order['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $order['amounts']['subtotal']/1.5;

		    	$commission_amount = $order['com_percent'];
				
		    	$itemised_total_shippingcost = $order['itemised_total_shippingcost'];
				$order['base_shipping_amount'];
		    	$vendors = Mage::helper('udropship')->getVendor($order['udropship_vendor']);
		    	
		    		
		    	//$gen_random_number = "K".$this->gen_rand();

    			if($order['order_created_at']<='2012-07-02 23:59:59')
		    	{
		    		if($vendors->getManageShipping() == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
						$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$order['cod_fee'];
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
						$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
			    	}
		    	}
		    	else {
		    		if($vendors->getManageShipping() == "imanage")
			    	{
						
			    		$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$order['cod_fee'])*1.00 - $vendor_amount;
			    	}
			    	else {
						
						$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//echo 'kribha amount'.$kribha_amount = ((($total_amount1+$itemised_total_shippingcost+$order['cod_fee'])*1.00) - $vendor_amount).'<br>';
						/*$kribha_amount = ((($total_amount1+$order['cod_fee']+$order['base_shipping_amount'])*1.00) - $vendor_amount);
			    		
					}
		    	}
	
        if (is_null($order['com_percent'])) {
            $order['com_percent'] = $this->getVendor()->getCommissionPercent();
        }
		
        $order['com_percent'] *= 1;
		
        if (is_null($order['amounts']['trans_fee'])) {
            $order['amounts']['trans_fee'] = $this->getVendor()->getTransactionFee();
        }
		
        
		$order['amounts']['com_amount'] = $kribha_amount/1.1236;
		
		 $order['amounts']['total_payout'] = $vendor_amount;
		
        return $order;*/
    //***End*******Below Line Added By Manoj sir & Dileswar for get Commision amount on Pdf on dated 13-02-2013**************//	
	
	
			$base_shipping_amount = $order['base_shipping_amount']; 
			$total_amount1 = $order['amounts']['subtotal'];
			$total_amount = $order['amounts']['subtotal'];
			$_liveDate = "2012-08-21 00:00:00";
			$_order = Mage::getModel('sales/order')->loadByIncrementId($order['id']);
		   	$orderBaseShippingamount = $_order->getBaseShippingAmount();
			$orderid = $_order->getEntityId();
			$discountAmountCoupon = 0;
			$disCouponcode = '';
			$_orderCurrencyCode = $_order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($order['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $order['amounts']['subtotal']/1.5;
            $vendorId = $order['udropship_vendor'];
			$readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
			$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$orderid."' AND `address_type` = 'shipping'")->fetch();
			$getCountryResult = $getCountryOf['country_id'];
			$lastFinalbaseshipamt = $this->baseShippngAmountByOrder($orderid,$orderBaseShippingamount);
		    $commission_amount = $order['com_percent'];
		    $itemised_total_shippingcost = $order['itemised_total_shippingcost'];
			$vendors = Mage::helper('udropship')->getVendor($order['udropship_vendor']);
		    $couponCodeId = Mage::getModel('salesrule/coupon')->load($_order->getCouponCode(), 'code');
			$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
			$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $_order->getBaseDiscountAmount();
					$disCouponcode = $_order->getCouponCode();
				}
		       if($order['order_created_at']<='2012-07-02 23:59:59')
		    	{
		    		if($vendors->getManageShipping() == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
						$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$order['cod_fee'];
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
						$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
			    	}
		    	}
		    	else {
		    			if($vendors->getManageShipping() == "imanage")
			    		{
						
			    		$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
					
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$order['cod_fee'])*1.00 - $vendor_amount;
						}
			    	   else {
						   $vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1236)));
					
						if($getCountryResult == 'IN')
						{	
							$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)*1.00) - $vendor_amount);
						}
						else
						{
							$kribha_amount = ((($total_amount1+$lastFinalbaseshipamt+$discountAmountCoupon)*1.00) - $vendor_amount);
						}
							}
		    	 	 }
					if (is_null($order['com_percent'])) {
						//$order['com_percent'] = $this->getVendor()->getCommissionPercent();
						$order['com_percent'] = 20;
					}
					
					$order['com_percent'] *= 1;

					if (is_null($order['amounts']['trans_fee'])) {
            					$order['amounts']['trans_fee'] = $this->getVendor()->getTransactionFee();
       				 }
					        
			$order['amounts']['com_amount'] = $kribha_amount/1.1236;
            $order['amounts']['total_payout'] = $vendor_amount;
            return $order;					
		
	}
	
	public function baseShippngAmountByOrder($orderId,$orderBaseShippingamount)
		{
			$readOrder = Mage::getSingleton('core/resource')->getConnection('core_read');
			$adjustquery = $readOrder->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
			$shipcnt = $adjustquery['cntshipments'];
			$lastFinalshipamt = $orderBaseShippingamount/$shipcnt;
			return $lastFinalshipamt;
		}	

    public function isMyAdjustment($adjustment)
    {
        return 0 === strpos($adjustment->getAdjustmentId(), $this->getAdjustmentPrefix());
    }

    public function isOrderAdjustment($adjustment)
    {
        return 0 === strpos($adjustment->getAdjustmentId(), Mage::helper('udropship')->getAdjustmentPrefix('po_comment'))
            || 0 === strpos($adjustment->getAdjustmentId(), Mage::helper('udropship')->getAdjustmentPrefix('shipment_comment'));
    }

    public function finishStatement()
    {
        $this->_calculateTotalDue();
        $this->_formatOrders();
        $this->_formatTotals();
        $this->_refreshExtraAdjustments();
        $this->_compactTotals();
        return $this;
    }

    protected function _cleanAdjustments()
    {
        $adjCol = $this->getAdjustmentsCollection();
        foreach ($adjCol as $adj) {
            if (!$this->isMyAdjustment($adj)) {
                $adjCol->removeItemByKey($adj->getAdjustmentId());
            }
        }
        return $this;
    }

    protected function _calculateAdjustments()
    {
        $this->initTotals();
        $calculate = array();
        foreach ($this->getAdjustmentsCollection() as $adj) {
            if ($this->isMyAdjustment($adj)) {
                $calculate[] = $adj;
            }
        }
        $this->_accumulateAdjustments($calculate);
        return $this;
    }

    protected function _calculateTotalDue()
    {
        $this->initTotals();
        $this->_totals_amount['total_paid'] = $this->getTotalPaid();
        $this->_totals_amount['total_due']  = $this->_totals_amount['total_payout'] - $this->_totals_amount['total_paid'];
        $this->setTotalAdjustment($this->_totals_amount['adj_amount']);
        $this->setTotalPayout($this->_totals_amount['total_payout']);
        $this->setTotalDue($this->_totals_amount['total_due']);
        return $this;
    }

    public function accumulateOrder($order, $totals_amount)
    {
        foreach ($this->_getEmptyTotals() as $k => $v) {
            if (isset($order['amounts'][$k])) $totals_amount[$k] += $order['amounts'][$k];
        }
        return $totals_amount;
    }

    protected function _accumulateAdjustments($adjustments)
    {
        $this->initTotals();
        foreach ($adjustments as $adj) {
            $this->_totals_amount['total_payout'] += $adj->getAmount();
            $this->_totals_amount['adj_amount']   += $adj->getAmount();
        }
        return $this;
    }

    protected $_adjustmentClass;
    public function getAdjustmentClass()
    {
        if (is_null($this->_adjustmentClass)) {
            $this->_adjustmentClass = Mage::getConfig()->getModelClassName('udropship/vendor_statement_adjustment');
        }
        return $this->_adjustmentClass;
    }

    public function createAdjustment($amount, $comment='')
    {
        $adjClass = $this->getAdjustmentClass();
        $adjustment = new $adjClass(array(
            'amount' => (float)$amount,
            'comment' => $comment,
            'created_at' => now()
        ));
        return $adjustment;
    }

    protected function _addAdjustment($adjustment, $comment='')
    {
        if (!is_object($adjustment)) {
            $adjustment = $this->createAdjustment($adjustment, $comment);
        }
        try {
            $this->getAdjustmentsCollection()->load()->addItem($adjustment);
            $this->_accumulateAdjustments(array($adjustment));
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    public function addAdjustment($adjustment, $comment='')
    {
        $this->_addAdjustment($adjustment, $comment);
        return $this;
    }

    public function addPaidAmount($amount)
    {
        $this->setTotalPaid($this->getTotalPaid()+$amount);
        $this->finishStatement();
        return $this;
    }

    public function addOrders($orders, $calculate=true, $reset=false)
    {
        if (!is_array($orders)) return $this;

        $this->initTotals();
        if ($reset) $this->_resetOrders();
        $this->_resetTotals(true);

        foreach ($orders as $sId => $order) {
            $this->_orders[$sId] = $order;
        }

        foreach ($this->_orders as &$order) {
            if ($calculate) $order = $this->calculateOrder($order);
            $this->_totals_amount = $this->accumulateOrder($order, $this->_totals_amount);
        }
        unset($order);

        return $this;
    }

    protected function _getEmptyTotals($format=false)
    {
        return Mage::helper('udropship')->getStatementEmptyTotalsAmount($format);
    }

    protected function _getEmptyCalcTotals($format=false)
    {
        return Mage::helper('udropship')->getStatementEmptyCalcTotalsAmount($format);
    }

    protected $_totalsInit = false;
    public function initTotals()
    {
        if (!$this->_totalsInit) {
            $this->_resetOrders();
            $this->_resetTotals();
            $this->_extractTotals();
            $this->_totalsInit = true;
        }
        return $this;
    }

    protected function _resetOrders()
    {
        $this->_orders = array();
        return $this;
    }

    protected function _resetTotals($soft=false)
    {
        if ($soft) {
            $this->_totals = array_merge($this->_totals, $this->_getEmptyTotals(true));
            $this->_totals_amount = array_merge($this->_totals_amount, $this->_getEmptyTotals());
        } else {
            $this->_totals = $this->_getEmptyTotals(true);
            $this->_totals_amount = $this->_getEmptyTotals();
        }
        return $this;
    }

    protected function _formatOrders()
    {
        $this->initTotals();
        foreach ($this->_orders as &$order) {
            if (!empty($order['amounts'])) Mage::helper('udropship')->formatAmounts($order, $order['amounts'], 'merge');
        }
        unset($order);
        return $this;
    }
    protected function _formatTotals()
    {
        $this->initTotals();
        Mage::helper('udropship')->formatAmounts($this->_totals, $this->_totals_amount, 'merge');
        return $this;
    }
    protected function _refreshExtraAdjustments()
    {
        $this->_extra_adjustments = array();
        foreach ($this->getAdjustmentsCollection() as $adj) {
            if ($this->isOrderAdjustment($adj)) continue;
            $this->_extra_adjustments[] = $adj->getData();
        }
    }
    protected function _compactTotals()
    {
        $this->initTotals();
        $this->setOrdersData(Zend_Json::encode(array(
            'orders' => $this->getOrders(),
            'totals' => $this->getTotals(),
        	'totals_amount' => $this->getTotalsAmount(),
        	'extra_adjustments' => $this->getExtraAdjustments(),
        	'payouts' => $this->getPayouts(),
        )));
        $this->setTotalOrders(count($this->getOrders()));
        return $this;
    }

    protected $_totalsExtracted = false;
    protected function _extractTotals()
    {
        if (!$this->_totalsExtracted) {
            $ordersData = $this->getOrdersData();
            if (strpos($ordersData, 'a:')===0) {
                $ordersData = @unserialize($ordersData);
            } elseif (strpos($ordersData, '{')===0) {
                $ordersData = Zend_Json::decode($ordersData);
            }
            if (!empty($ordersData['totals'])) {
                $this->_totals = $ordersData['totals'];
            }
            if (!empty($ordersData['totals_amount'])) {
                $this->_totals_amount = $ordersData['totals_amount'];
            }
            if (!empty($ordersData['orders'])) {
                $this->_orders = $ordersData['orders'];
            }
            if (!empty($ordersData['extra_adjustments'])) {
                $this->_extra_adjustments = $ordersData['extra_adjustments'];
            }
            if (!empty($ordersData['payouts'])) {
                $this->_payouts = $ordersData['payouts'];
            }
            $this->_totalsExtracted = true;
        }
        return $this;
    }

    protected $_orders;
    protected $_totals;
    protected $_totals_amount;
    protected $_extra_adjustments;
    protected $_payouts;

    public function getTotals()
    {
        $this->initTotals();
        return $this->_totals;
    }
    public function getTotalsAmount()
    {
        $this->initTotals();
        return $this->_totals_amount;
    }
    public function getOrders()
    {
        $this->initTotals();
        return $this->_orders;
    }
    public function getExtraAdjustments()
    {
        $this->initTotals();
        return $this->_extra_adjustments;
    }
    public function getPayouts()
    {
        $this->initTotals();
        return $this->_payouts;
    }

    protected function _beforeSave()
    {
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt(now());
        }
        $this->setUpdatedAt(now());
        parent::_beforeSave();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->initTotals();
        return $this;
    }

    protected $_withhold;
    protected $_withholdOptions;

    public function getWithholdOptions()
    {
        if (is_null($this->_withholdOptions)) {
            $this->_withholdOptions = Mage::getSingleton('udropship/source')->setPath('statement_withhold_totals')->toOptionHash();
        }
        return $this->_withholdOptions;
    }
    public function getWithhold()
    {
        if (is_null($this->_withhold)) {
            $this->_withhold = array_flip((array)$this->getVendor()->getStatementWithholdTotals());
        }
        return $this->_withhold;
    }
    public function hasWithhold($key)
    {
        return array_key_exists($key, $this->getWithhold());
    }

    protected $_adjustmentsCollection;
    public function resetAdjustmentCollection()
    {
        $this->_adjustmentsCollection = null;
        return $this;
    }
    protected function _initAdjustmentsCollection($reload=false)
    {
        if (is_null($this->_adjustmentsCollection) || $reload) {
            $this->getResource()->initAdjustmentsCollection($this);
        }
        return $this;
    }
    public function setAdjustmentsCollection($collection)
    {
        $this->_adjustmentsCollection = $collection;
        return $this;
    }
    public function getAdjustmentsCollection($reload=false)
    {
        $this->_initAdjustmentsCollection($reload);
        return $this->_adjustmentsCollection;
    }

    public function getAdjustments($prefix='')
    {
        $adjustments = array();
        foreach ($this->getAdjustmentsCollection() as $adj) {
            if (empty($prefix) || strpos($adj->getAdjustmentId(), $prefix) === 0) $adjustments[$adj->getAdjustmentId()] = $adj;
        }
        return $adjustments;
    }

    static public function processPos($pos, $subtotalBase)
    {
        $poItemsToLoad = array();
        $subtotalKey = $subtotalBase == 'cost' ? 'total_cost' : 'base_total_value';
        foreach ($pos as $id=>$po) {
            foreach (array($subtotalKey, 'base_tax_amount') as $k) {
                if (abs($po->getData($k))<.001) {
                    $poItemsToLoad[$id][$k] = true;
                }
            }
        }
        if ($poItemsToLoad) {
            if ($pos instanceof Varien_Data_Collection) {
                $samplePo = $pos->getFirstItem();
            } else {
                reset($pos);
                $samplePo = current($pos);
            }
            $poType = $samplePo instanceof Unirgy_DropshipPo_Model_Po ? 'po' : 'shipment';
            $baseCost = Mage::helper('udropship')->hasMageFeature('order_item.base_cost');
            if (Mage::helper('udropship')->isSalesFlat()) {
                $poItems = $poType == 'po' ? Mage::getModel('udpo/po_item')->getCollection() : Mage::getModel('sales/order_shipment_item')->getCollection();
                $fields = array('base_row_total', 'base_tax_amount');
                if ($baseCost) $fields[] = 'base_cost';
                $poItems->getSelect()
                    ->join(array('i'=>$poItems->getTable('sales/order_item')), 'i.item_id=order_item_id', $fields)
                    ->where('order_item_id<>0 and parent_id in (?)', array_keys($poItemsToLoad))
                ;
            } else {
                $fields = array('base_row_total'=>'base_row_total', 'base_tax_amount'=>'base_tax_amount');
                if ($baseCost) $fields['base_cost'] = 'base_cost';
                $poItems = Mage::getModel('sales/order_shipment_item')->getCollection()
                    ->addAttributeToFilter('order_item_id', array('neq'=>0))
                    ->joinTable('sales/order_item', 'item_id=order_item_id', $fields)
                    ->addAttributeToFilter('parent_id', array('in'=>array_keys($poItemsToLoad)))
                ;
            }
            $itemTotals = array();
            foreach ($poItems as $item) {
                $id = $item->getParentId();
                if (empty($itemTotals[$id])) {
                    $itemTotals[$id] = array($subtotalKey=>0, 'base_tax_amount'=>0);
                }
                $itemTotals[$id][$subtotalKey] += $subtotalBase == 'cost' ? $item->getBaseCost()*$item->getQty() : $item->getBaseRowTotal();
                $itemTotals[$id]['base_tax_amount'] += $item->getBaseTaxAmount();
            }
            foreach ($itemTotals as $id=>$total) {
                foreach ($total as $k=>$v) {
                    if (!empty($poItemsToLoad[$id][$k])) {
                        $pos->getItemById($id)->setData($k, $v);
                    }
                }
            }
        }
    }

}
