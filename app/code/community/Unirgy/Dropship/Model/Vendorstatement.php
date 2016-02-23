<?php
class Unirgy_Dropship_Model_Vendorstatement extends Unirgy_Dropship_Model_Abstract
{
    public function exportshipmentspdf($shipment = null)
    {
        $this->_beforeGetPdf();
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        
        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
        $pdf->pages[] = $page;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.9));
        $page->setLineWidth(0);
        $this->x = 0;
        $this->y = $page->getHeight();
        $this->xdpi = $page->getWidth()/$this->_pageWidth;
        $this->ydpi = $page->getHeight()/$this->_pageHeight;

        $this->setPage($page)
            ->fillColor(0)
            ->lineColor(0)
            ->setUnits('inch')
            ->setAlign('left')
            ->setLineHeight($this->_lineHeight)
            ->font('normal', 10)
            ->setMarginBottom($this->_marginBottom)
        ;
        $hlp = Mage::helper('udropship');
        $this->rectangle(9.2, .4, .9, .9);
            $this->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 10)
                ->setAlign('left')
                //->text($hlp->__("Date"));
                ->text($hlp->__("Shipment#"));
            	$this->moveRel(.7, 0)->text($hlp->__("Gross"));
                $this->moveRel(.6, 0)->text($hlp->__("Shipping"));
				$this->moveRel(.8, 0)->text($hlp->__("Discount"));
                $this->moveRel(0.7, 0)->text($hlp->__("PayoutStatus"));
                $this->moveRel(1.07, 0)->text($hlp->__("Payment"));
                $this->moveRel(0.8, 0)->text($hlp->__("Payout Type"));
                $this->moveRel(1.06, 0)->text($hlp->__("Payout Date"));
                $this->moveRel(1.03, 0)->text($hlp->__("Comm (%)"))
                    ->moveRel(1.02, 0)->text($hlp->__("Svc Tax"))
                    ->movePop(0, .3);
        
                $rs = "Rs. ";
                $core = Mage::helper('core');
                $totalPayment       = 0;
                $totalCommisionAmt  = 0;
                $totalTransaction   = 0;
            foreach($shipment as $ship):
				$order = Mage::getModel('sales/order')->loadByIncrementId($ship->getOrderId());
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				$_shipmentBaseTotal = $ship->getBaseTotalValue();
				$_liveDate = "2012-08-21 00:00:00";
				if(($_orderCurrencyCode != 'INR') && (strtotime($order->getOrderCreatedAt()) >= strtotime($_liveDate)))
					$_shipmentBaseTotal = $ship->getBaseTotalValue()/1.5;
                $this->checkPageOverflow()
                    ->setMaxHeight(0)
                    ->font('normal', 8)
                    ->movePush()
                        ->setAlign('left')
                            //->text($core->formatDate($ship->getCreatedAt(), 'short'));
                        
                    ->text($ship->getIncrementId());
                        $this->moveRel(.8, 0)->text($rs.round($_shipmentBaseTotal));
						$vendorData = Mage::helper('udropship')->getVendor($ship->getUdropshipVendor());
						if($vendorData->getManageShipping() != 'imanage'){
							$_shipmentItemsedShipping = $ship->getItemisedTotalShippingcost();
						} else{
							$_shipmentItemsedShipping = 0;
						}
                        $vendorData = Mage::helper('udropship')->getVendor($ship->getUdropshipVendor());
                        if($vendorData->getManageShipping() != 'imanage'):
                            
                            $itemisedTotal = $rs.round($ship->getItemisedTotalShippingcost(),0);
                            $this->moveRel(0.7, 0)->text("{$itemisedTotal}");
							$_shipmentItemsedShipping = $ship->getItemisedTotalShippingcost();
                        else:
                            $this->moveRel(0.7, 0)->text("Rs. 0");
							$_shipmentItemsedShipping = 0;
                        endif;
						
                    if($ship->getShipmentpayoutStatus() == 0):
                        $status = "Unpaid";
                    else:
                        $status = "Paid";
					endif;
                    if(!$ship->getShipmentpayoutUpdateTime()):
                        $payoutUpdateDate = "-";
                    else:
                        $payoutUpdateDate = $core->formatDate($ship->getShipmentpayoutUpdateTime());
                    endif;
					$vendoramount = $this->commissionamount($_shipmentBaseTotal,$_shipmentItemsedShipping,$ship);
					$commisionAmount    = $vendoramount[0];
					$this->moveRel(0.7, 0)->text("Rs. ".$vendoramount[1]);
                    $this->moveRel(0.8, 0)->text($status);
                    $this->moveRel(0.9, 0)->text("Rs. ".$ship->getPaymentAmount());
                    $this->moveRel(1.00, 0)->text($ship->getType());
                    $this->moveRel(0.9, 0)->text($payoutUpdateDate);
                   
                    
					
                    $totalCommisionAmt += $commisionAmount;
                    $rsCommisionAmount  = "-".$rs.$commisionAmount;
                    $commisionPercent   = "-".'20';
                    $transactionFee     = "-".$rs.round($ship->getTransactionFee());
                    $totalTransaction  += round($ship->getTransactionFee());
                    //$totalPayout        = $rs.round($_shipmentBaseTotal+($_shipmentItemsedShipping-$commisionAmount-$ship->getTransactionFee()));
					$totaltax = round($commisionAmount*0.1236);
                    $totalPayment      += round($_shipmentBaseTotal+($_shipmentItemsedShipping-$commisionAmount-$ship->getTransactionFee()));
                    $this->moveRel(1.01, 0)->text("{$rsCommisionAmount} ({$commisionPercent}%)")
                        ->setAlign('left')
                            ->moveRel(1.0, 0)->text("Rs. ".$totaltax);

                        
                        $this->movePop(0, $this->getMaxHeight()+5, 'point')
                    ->moveRel(-.1, 0)
                    ->line(8.2, 0, .7)
                    ->moveRel(.1, .1);
            endforeach;
            
            $core = Mage::helper('core');
            $hlp = Mage::helper('udropship');

            /*foreach (array('trans_fee','com_amount') as $_k) {
                $totals[$_k] = strpos($totals[$_k], '-') === 0
                    ? substr($totals[$_k], 1)
                    : '-'.$totals[$_k];
            }*/
            /************** Added By Mandar on 18-05-2012*****************/
            $service_tax            = round($totalCommisionAmt*12/100);
            $education_tax          = round($service_tax*2/100,2);
            $highereducation_tax    = round($service_tax*1/100,2);
            $totalsFinalAmt         = round($totalPayment-round($service_tax+$education_tax+$highereducation_tax));
            /************************************************/
            $adjustmentAmt  = 0;
/*Below lines Coomented (Start) By Dileswar On dated 17-04-2013 for remove the total payment*/
				/*$this->checkPageOverflow(1.5)
					->moveRel(-.1, 0)
					->rectangle(8.2, .05, .8, .8)
					->moveRel(6.5, .2)
					->movePush()
						->setAlign('right')
						->text($hlp->__("Total Commission"), 'down')
						->text($hlp->__("Total Transaction Fees"), 'down')
						->text($hlp->__("Total Adjustments"), 'down')
						->text($hlp->__("Service Tax"), 'down')
						->text($hlp->__("Education Tax"), 'down')
						->text($hlp->__("Secondary & Higher Education Tax"), 'down')
	
					->movePop(1.7, 0)
					->text("Rs. ".$totalCommisionAmt, 'down')
					->text("Rs. ".$totalTransaction, 'down')
					->text("Rs. ".$adjustmentAmt, 'down')
					->text("Rs. ".$service_tax, 'down')
					->text("Rs. ".$education_tax, 'down')
					->text("Rs. ".$highereducation_tax, 'down')
					->movePush()
						->moveRel(-3.5, .1);
				$stTotalHeight = $this->getTextHeight();
				if ($hlp->isUdpayoutActive()) {
					$stTotalHeight += $this->getTextHeight()*2;
				}
				$this->font('bold', 14);
				$stTotalRectPad = $this->getTextHeight()*.4;
				$stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
				$this->rectangle(3.6, $stTotalHeightOut, .8, .8)
					->movePop(-1.7, .15)
						->text($hlp->__("Total Payment"))
					->moveRel(1.7, 0)->text("Rs. ".$totalsFinalAmt, 'down');*/

/*lines Coomented (End) By Dileswar On dated 17-04-2013 for remove the total payment*/

        $this->_afterGetPdf();
        return $pdf;
    }
    
    public function exportCsvShipment($shipment = null,$commission = null,$manage = null) 
    {
        $fileName = 'shipment_export_'.date("Y-m-d").'.csv';
        $fp = fopen(Mage::getBaseDir('export').'/'.$fileName, 'w');
        $this->writeHeadRow($fp);
        foreach ($shipment as $order=>$k) {
            $payoutStatus   = $k->getShipmentpayoutStatus();
            if($payoutStatus == 0):
                $status = "Unpaid";
            else:
                $status = "Paid";
            endif;
                
            $commissionAmt  = round($k->getBaseOriginalPrice()*$commission/100);
            if($manage == 'vmanage'):
                $shipmentCost   = $k->getTotalShippingcost();
            else:
                $shipmentCost   = 0;
            endif;
            $netAmount      = round($k->getBaseOriginalPrice()+$shipmentCost-$commissionAmt);
            $data           = array($k->getCreatedAt(),$k->getIncrementId(),$k->getSku(),$k->getName(),$k->getQtyOrdered(),$k->getBaseOriginalPrice(),$shipmentCost,$status,$k->getShipmentpayoutUpdateTime(),$commissionAmt,$netAmount);
            fputcsv($fp, $data, self::DELIMITER, self::ENCLOSURE);
        }
        fclose($fp);
        return $fileName;
    }
	
	public function commissionamount($_shipmentBaseTotal,$_shipmentItemsedShipping,$ship)
	{
		    $_liveDate = "2012-08-21 00:00:00";
            $hlp = Mage::helper('udropship');
            $commission_amount = $hlp->getVendorCommission($ship->getUdropshipVendor(), $ship->getIncrementId());
            $service_tax = $hlp->getServicetaxCv($ship->getIncrementId());
			//$commission_amount = 20;
     	   	$discountAmountCoupon = 0;
			$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($ship->getIncrementId());
			$orderid = $shipment->getOrderId();
			$total_amount = $ship->getBaseTotalValue();
			$total_amount1 = $ship->getBaseTotalValue();
			$vendorData = $ship->getUdropshipVendor();
			$order = Mage::getModel('sales/order')->loadByIncrementId($ship->getOrderId());
			$_orderCurrencyCode = $order->getOrderCurrencyCode();
			$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
			$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
			
			$couponVendorId = $_resultCoupon->getVendorid();
		  	 $orderBaseShippingAmount = $ship->getBaseShippingAmount();
			$_orderCurrencyCode = $order->getOrderCurrencyCode();
			$lastFinalbaseshipamt = $this->baseShippngAmountByOrder($orderid,$orderBaseShippingAmount);
			$readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
			$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$orderid."' AND `address_type` = 'shipping'")->fetch();
			$getCountryResult = $getCountryOf['country_id'];
				if($couponVendorId == $vendorData)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
				if(($_orderCurrencyCode != 'INR') && (strtotime($order->getOrderCreatedAt()) >= strtotime($_liveDate)))
					$total_amount = $ship->getBaseTotalValue()/1.5;
					
	     		$vendor_amount = (($total_amount+$_shipmentItemsedShipping+$discountAmountCoupon)*(1-($commission_amount/100)*(1+$service_tax)));
       		   if($getCountryResult == 'IN')
						{	
							$kribha_amount = ((($total_amount1+$orderBaseShippingAmount+$discountAmountCoupon)*1.00) - $vendor_amount);
							 $kribha_amount = $kribha_amount/1.1236;
						}
						else
						{
							$kribha_amount = ((($total_amount1+$lastFinalbaseshipamt+$discountAmountCoupon)*1.00) - $vendor_amount);
							 $kribha_amount = $kribha_amount/1.1236;
						}
		  
		   $vendorinfo = array(floor($kribha_amount),$discountAmountCoupon);
		   return $vendorinfo;

	}
	
	public function baseShippngAmountByOrder($orderId,$orderBaseShippingAmount)
		{
		$readOrder = Mage::getSingleton('core/resource')->getConnection('core_read');
		//echo $query = "select count(*) from `sales_flat_shipment`  where  sfs.`order_id` = '".$orderId."'";exit;
		$adjustquery = $readOrder->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
		$shipcnt = $adjustquery['cntshipments'];
		$lastFinalshipamt = $orderBaseShippingAmount/$shipcnt;
		return $lastFinalshipamt;
		}
    
}
?>
