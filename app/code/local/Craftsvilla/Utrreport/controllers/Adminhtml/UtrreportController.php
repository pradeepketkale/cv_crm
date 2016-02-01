<?php

class Craftsvilla_Utrreport_Adminhtml_UtrreportController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('utrreport/items')
		->_addBreadcrumb(Mage::helper('adminhtml')->__('UTR Details'), Mage::helper('adminhtml')->__('UTR Details'));
		
		return $this;
	}   

	public function indexAction() {
		$this->_initAction()
		->renderLayout();
	}
/**
     * Product grid for AJAX request

     */
public function gridAction()
{
	$this->loadLayout();
	$this->getResponse()->setBody(
		$this->getLayout()->createBlock('utrreport/adminhtml_utrreport_grid')->toHtml()
		);
}

public function editAction() {
	$id     = $this->getRequest()->getParam('id');
	$model  = Mage::getModel('utrreport/utrreport')->load($id);

	if ($model->getId() || $id == 0) {
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register('utrreport_data', $model);

		$this->loadLayout();
		$this->_setActiveMenu('utrreport/items');

		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

		$this->_addContent($this->getLayout()->createBlock('utrreport/adminhtml_utrreport_edit'))
		->_addLeft($this->getLayout()->createBlock('utrreport/adminhtml_utrreport_edit_tabs'));

		$this->renderLayout();
	} else {
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('utrreport')->__('Item does not exist'));
		$this->_redirect('*/*/');
	}
}

public function newAction() {
	$this->_forward('edit');
}

public function saveAction() {
	if ($data = $this->getRequest()->getPost()) {

		$model = Mage::getModel('utrreport/utrreport');		
		$model->setData($data)
		->setId($this->getRequest()->getParam('id'));

		try {
			if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
				$model->setCreatedTime(now())
				->setUpdateTime(now());
			} else {
				$model->setUpdateTime(now());
			}	

			$model->save();
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('utrreport')->__('Item was successfully saved'));
			Mage::getSingleton('adminhtml/session')->setFormData(false);

			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', array('id' => $model->getId()));
				return;
			}
			$this->_redirect('*/*/');
			return;
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			Mage::getSingleton('adminhtml/session')->setFormData($data);
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			return;
		}
	}
	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('utrreport')->__('Unable to find item to save'));
	$this->_redirect('*/*/');
}

public function deleteAction() {
	if( $this->getRequest()->getParam('id') > 0 ) {
		try {
			$model = Mage::getModel('utrreport/utrreport');

			$model->setId($this->getRequest()->getParam('id'))
			->delete();

			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
			$this->_redirect('*/*/');
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		}
	}
	$this->_redirect('*/*/');
}

public function massDeleteAction() {
	$utrreportIds = $this->getRequest()->getParam('utrreport');
	if(!is_array($utrreportIds)) {
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
	} else {
		try {
			foreach ($utrreportIds as $utrreportId) {
				$utrreport = Mage::getModel('utrreport/utrreport')->load($utrreportId);
				$utrreport->delete();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(
				Mage::helper('adminhtml')->__(
					'Total of %d record(s) were successfully deleted', count($utrreportIds)
					)
				);
		} catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		}
	}
	$this->_redirect('*/*/index');
}

public function massStatusAction()
{
	$utrreportIds = $this->getRequest()->getParam('utrreport');
	if(!is_array($utrreportIds)) {
		Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
	} else {
		try {
			foreach ($utrreportIds as $utrreportId) {
				$utrreport = Mage::getSingleton('utrreport/utrreport')
				->load($utrreportId)
				->setStatus($this->getRequest()->getParam('status'))
				->setIsMassupdate(true)
				->save();
			}
			$this->_getSession()->addSuccess(
				$this->__('Total of %d record(s) were successfully updated', count($utrreportIds))
				);
		} catch (Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		}
	}
	$this->_redirect('*/*/index');
}
public function assignAction()
{	
	//echo ("yo"); exit;
	$utrreportIds = $this->getRequest()->getParam('utrreport');
	$utrreport = Mage::getSingleton('utrreport/utrreport')->load($utrreportIds);
	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
	$shipmentpayout_report1->getSelect()
	->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('order_entid'=>'order_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
	->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
	->joinLeft('sales_flat_order_payment','b.order_id = sales_flat_order_payment.parent_id','method')
				//->where('main_table.type = "Adjusted Against Refund"');
				->where('main_table.citibank_utr = "" AND (a.updated_at < DATE_SUB(NOW(),INTERVAL 8 DAY)) AND  main_table.shipmentpayout_status=0 AND a.udropship_status IN (1,17) AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard","avenues_standard","payucheckout_shared","paypal_standard","free")');// a.udropship_status IN (1,15,17)
				
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)

      	//echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		//exit();
				
				//$closingbalance = intval($closingbalance);

				$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
				$utrAmount = $utrreport->getAmount();
				$utrNum = $utrreport->getUtrno();
				$utrBalance = $utrreport->getBalance();
				$strTest = "";
				$readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
				foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
				{
					$merchantIdnew = $this->getMerchantIdCv($shipmentpayout_report1_val['udropship_vendor']);
					$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
					$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
					$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
					$collectionVendorQuery = "select `closing_balance` FROM `udropship_vendor` WHERE `vendor_id` = '".$vendorId."'";//Mage::getModel('udropship/vendor')->load($vendorId);
					$collectionVendor = $readOrderCntry->query($collectionVendorQuery)->fetch();
					$closingbalance = $collectionVendor['closing_balance'];

					if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($merchantIdnew != ''))
					{	
						$commission_amount = 20;
						unset($vendor_amount);
						unset($total_amount);
						unset($adjustmentAmount);
						$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
						$orderBaseShippingAmount = $order->getBaseShippingAmount();
						$disCouponcode = '';
						$discountAmountCoupon = 0;
						//$lastFinalbaseshipamt = $this->baseShippngAmountByOrderUtr($shipmentpayout_report1_val['order_entid'],$orderBaseShippingAmount);
						
						$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$shipmentpayout_report1_val['order_entid']."' AND `address_type` = 'shipping'")->fetch();
						$getCountryResult = $getCountryOf['country_id'];
						$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
						$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
						$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
						$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
						$couponVendorId = $_resultCoupon->getVendorid();
						$actualDiscountamount = $order->getBaseDiscountAmount();
						if($couponVendorId == $vendorId)
						{
							$discountAmountCoupon = $actualDiscountamount;
							$disCouponcode = $order->getCouponCode();
						}
						else
						{
							$lastFinaldiscountamt = $this->baseDiscountAmountByOrderUtr($shipmentpayout_report1_val['order_entid'],$actualDiscountamount);
							$discountAmountCoupon = $lastFinaldiscountamt; 
						}
						// Modifying By Ankit due to latest shipping amount policy
						// if($getCountryResult == 'IN')
						// {
							$total_amount = $shipmentpayout_report1_val['subtotal']+$shipmentpayout_report1_val['base_shipping_amount']+$discountAmountCoupon;
						// }
						// else
						// {
						// 	$total_amount = $shipmentpayout_report1_val['subtotal']+$lastFinalbaseshipamt+$discountAmountCoupon;
						// }

						if($shipmentpayout_report1_val['order_created_at']<='2012-07-02 23:59:59')
						{
							if($vendors->getManageShipping() == "imanage")
							{
								$vendor_amount = ($total_amount*(1-$commission_amount/100));								
							}
							else {
								$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));								
							}
						}
						else {
							if($vendors->getManageShipping() == "imanage")
							{
								$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));							
							}
							else {
								//$vendor_amount = (($total_amount+$base_shipping_amount+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1450)));
								$vendor_amount = (($total_amount)*(1-($commission_amount/100)*(1+0.1450)));
							}

						}
							// if($total_amount == 0){///////////////////////////////////////////////
							// 	continue;
							// }
							
							if( (($vendor_amount+$closingbalance) <= 0) &&  $utrBalance >= $vendor_amount )
							{
								$strTest.= "\nUTRAmout:$utrBalance\tTotalAmout:$total_amount\tVendorAmount:$vendor_amount\tClosingBalance:$closingbalance\tAdjustmentAmt:$adjustmentAmount";////////////////////
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){
									//$utrBalance = $utrBalance - $vendor_amount;
									$vendor_amount = 0;
								}	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									//$utrBalance = $utrBalance - $vendor_amount;
									//$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`payment_amount`= '".$adjustmentAmount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	

									$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');	
									$queryUtrupdate = "update utrreport set `balance` = '".$utrBalance."' WHERE `utrno` = '".$utrNum."'";
									$writeUtr->query($queryUtrupdate);
									$this->_getSession()->addSuccess($this->__('Total of %d record(s) UTR successfully Assigned'.$shipmentpayout_report1_val['shipment_id']));

							} else if ($utrBalance >= $total_amount) {
								$strTest.= "\nUTRAmout:$utrBalance\tTotalAmout:$total_amount\tVendorAmount:$vendor_amount\tClosingBalance:$closingbalance\tAdjustmentAmt:$adjustmentAmount";////////////////////	
								$utrBalance = $utrBalance - $total_amount;
								$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
								$queryUpdate = "update shipmentpayout set `citibank_utr` = '".$utrNum."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
								$write->query($queryUpdate);
								$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');	
								$queryUtrupdate = "update utrreport set `balance` = '".$utrBalance."' WHERE `utrno` = '".$utrNum."'";
								$writeUtr->query($queryUtrupdate);
								$this->_getSession()->addSuccess($this->__('Total of %d record(s) UTR successfully Assigned'.$shipmentpayout_report1_val['shipment_id']));
							}else{
								$this->_getSession()->addError($this->__('Total Amount is greater than Utr Balance,So UTR Not Updated'.$shipmentpayout_report1_val['shipment_id']));
							}
					}
				}
				$filename = "UtrReport_".date("Ymd");
				$filePathOfCsv = Mage::getBaseDir('media').DS.'misreport'.DS.$filename.'.txt';
			    $fp=fopen($filePathOfCsv,'a');
			    fputs($fp, "\n\n**********************************************************************");
			    fputs($fp, $strTest);
			    fclose($fp);
				$this->_redirect('*/*/index');
			}

			public function calculatePaidAction()
			{
				$utrreportIds = $this->getRequest()->getParam('utrreport');
				$utrreport = Mage::getSingleton('utrreport/utrreport')->load($utrreportIds);
				$utrNum = $utrreport->getUtrno();
				$alreadyUtrpaidamnt = 0;
				$readUtr = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_read');	
				$shipmentpayoututrQuery ="SELECT SUM(`payment_amount` + `commission_amount`) as totalutrpaid FROM `shipmentpayout` where `citibank_utr` = '".$utrNum."'";
				$shpmentUtrresult = $readUtr->fetchAll($shipmentpayoututrQuery);

				$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');	
				$queryUtrUpdate = "update `utrreport` set `utrpaid` = '".$shpmentUtrresult[0]['totalutrpaid']."' WHERE `utrno` = '".$utrNum."'";
				$writeUtr->query($queryUtrUpdate);
				$this->_getSession()->addSuccess($this->__('Calcutlation Updated For This Utr '.$utrNum));
				$this->_redirect('*/*/index');
			}
			public function complianceReportAction()
			{
				$utrreportIds = $this->getRequest()->getParam('utrreport');
				$utrreport = Mage::getSingleton('utrreport/utrreport')->load($utrreportIds);
				$utrNum = $utrreport->getUtrno();

				$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
				$shipmentpayout_report1->getSelect()
				->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
				->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
				->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where("main_table.citibank_utr ='".$utrNum."'");

      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
      	exit();*/

      	$shpmentComplianceresult = $shipmentpayout_report1->getData();
      	$filename = "ComplianceReport"."_".$utrNum;
      	$output = "";

      	$fieldlist = array("MERCHANT","AMOUNT","PAYINDATE","PAYOUTDATE","UTR_NUMBER","NARRATION");

      	$numfields = sizeof($fieldlist);

		// *********************   NOW START BUILDING THE CSV

		// Create the column headers

      	for($k =0; $k < $numfields;  $k++) { 
      		$output .= $fieldlist[$k];
      		if ($k < ($numfields-1)) $output .= ", ";
      	}
      	$output .= "\n";
      	foreach($shpmentComplianceresult as $shpmentComplianceresultValue)
      	{
      		$vendors = Mage::helper('udropship')->getVendor($shpmentComplianceresultValue['udropship_vendor']);
      		$difference = (strtotime($shpmentComplianceresultValue['shipmentpayout_update_time']) - strtotime($utrreport->getPayinDate()));
      		$datediff   = floor($difference / (60*60*24));
      		for($m =0; $m < sizeof($fieldlist); $m++) {

      			$fieldvalue = $fieldlist[$m];
      			if($fieldvalue == "MERCHANT")
      			{
      				$output .= $vendors->getCheckPayTo();
      			}
      			if($fieldvalue == "AMOUNT")
      			{
      				$output .= str_replace(',','',number_format($shpmentComplianceresultValue['payment_amount'],2));
      			}

      			if($fieldvalue == "PAYINDATE")
      			{
      				$output .= $utrreport->getPayinDate();
      			}
      			if($fieldvalue == "PAYOUTDATE")
      			{
      				$output .= $shpmentComplianceresultValue['shipmentpayout_update_time'];
      			}
      			if($fieldvalue == "UTR_NUMBER")
      			{
      				$output .= $utrNum;
      			}
      			if($fieldvalue == "NARRATION")
      			{
      				$output .= $datediff;
      			}
      			if($m < ($numfields-1))
      			{
      				$output .= ",";
      			}
      			else {
      				$output .= "\n";
      				$output .= 'Kribha Handicrafts Pvt Ltd';
      				$output .= ",";
      				$output .= str_replace(',','',number_format($shpmentComplianceresultValue['commission_amount'],2));;
      				$output .= ",";
      				$output .= $utrreport->getPayinDate();
      				$output .= ",";
      				$output .= $shpmentComplianceresultValue['shipmentpayout_update_time'];
      				$output .= ",";
      				$output .= $utrNum;
      				$output .= ",";
      				$output .= $datediff;
      				$output .= ",";
      			}
      		}
      		$output .= "\n";

      	}
			// Send the CSV file to the browser for download

      	header("Content-type: text/x-csv");
      	header("Content-Disposition: attachment; filename=$filename.csv");
      	echo $output;
      	exit;
      }

      public function baseShippngAmountByOrderUtr($orderId,$orderBaseShippingAmount)
      {
      	$readOrder = Mage::getSingleton('core/resource')->getConnection('core_read');
			//echo $query = "select count(*) from `sales_flat_shipment`  where  sfs.`order_id` = '".$orderId."'";exit;
      	$adjustquery = $readOrder->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
      	$shipcnt = $adjustquery['cntshipments'];
      	$lastFinalshipamt = $orderBaseShippingAmount/$shipcnt;
      	return $lastFinalshipamt;
      }

      public function baseDiscountAmountByOrderUtr($orderId,$actualDiscountamount)
      {
      	$readOrderR = Mage::getSingleton('core/resource')->getConnection('custom_db');
			//echo $query = "select count(*) from `sales_flat_shipment`  where  sfs.`order_id` = '".$orderId."'";exit;
      	$adjustqueryR = $readOrderR->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
      	$readOrderR->closeConnection();
      	$shipcnt = $adjustqueryR['cntshipments'];
      	$lastFinaldiscountamt = $actualDiscountamount/$shipcnt;
      	return $lastFinaldiscountamt;
      }	

      public function getMerchantIdCv($vendorIdc)
      {
      	$readVd = Mage::getSingleton('core/resource')->getConnection('core_read');
      	$MerchantIdQuery = "SELECT `merchant_id_city` FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorIdc."'";
      	$resultMerchant = $readVd->query($MerchantIdQuery)->fetch();
      	$readVd->closeConnection();	
      	return $resultMerchant['merchant_id_city']; 
      }
  }
