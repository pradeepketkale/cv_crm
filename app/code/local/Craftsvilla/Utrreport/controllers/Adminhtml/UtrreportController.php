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
	->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('order_entid'=>'order_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount','entity_id'=>'entity_id'))
	->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
	->joinLeft('sales_flat_order_payment','b.order_id = sales_flat_order_payment.parent_id','method')
				//->where('main_table.type = "Adjusted Against Refund"');
				->where('main_table.citibank_utr = "" AND (a.updated_at < DATE_SUB(NOW(),INTERVAL 16 DAY)) AND  main_table.shipmentpayout_status=0 AND a.udropship_status IN (1,17) AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard","avenues_standard","payucheckout_shared","free")');
				//->limit(100);// a.udropship_status IN (1,15,17)
				//->where('b.increment_id' = '100001562');
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)

      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();*/

				//$closingbalance = intval($closingbalance);

				$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
				//var_dump($shipmentpayout_report1_arr);exit;
				$utrAmount = $utrreport->getAmount();
				$utrNum = $utrreport->getUtrno();
				$utrBalance = $utrreport->getBalance();
				$strTest = "";
				$_liveDate = "2012-08-21 00:00:00";
				$readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
				foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
				{	/**********Check Payment Privilege of the Vendor*********/
			    	$readObj = Mage::getSingleton('core/resource')->getConnection('core_read');
					$sqlQuery = "SELECT * FROM `vendor_info_craftsvilla` WHERE `payment_privileges` = '0' AND `vendor_id` = " .$shipmentpayout_report1_val['udropship_vendor'];
					$result = $readObj->query($sqlQuery)->fetch();
					$readObj->closeConnection();
					if($result){
						continue;
					}
					/**********Privilege Check Done***********/
					$merchantIdnew = $this->getMerchantIdCv($shipmentpayout_report1_val['udropship_vendor']);
					$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
					$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
					$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
					$collectionVendorQuery = "select `closing_balance` FROM `udropship_vendor` WHERE `vendor_id` = '".$vendorId."'";//Mage::getModel('udropship/vendor')->load($vendorId);
					$collectionVendor = $readOrderCntry->query($collectionVendorQuery)->fetch();
					$closingbalance = $collectionVendor['closing_balance'];
					//echo (date("Y-m-d", strtotime($shipmentpayout_report1_val['created_at'])));
					//var_dump($shipmentpayout_report1_val);exit;

					if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($merchantIdnew != ''))
					{	//echo "yo";exit;
						$hlp = Mage::helper('udropship');
				        $commission_amount = $hlp->getVendorCommission($vendorId, $shipmentpayout_report1_val['shipment_id']);
				        $service_tax = $hlp->getServicetaxCv($shipmentpayout_report1_val['shipment_id']);
						//$commission_amount = 20;
						unset($vendor_amount);
						unset($total_amount);
						unset($adjustmentAmount);
						unset($shipmentType);
						unset($resultSendd);
						$isSenddFlag = "false";
						$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
						$orderBaseShippingAmount = $order->getBaseShippingAmount();
						$disCouponcode = '';
						$discountAmountCoupon = 0;
						//$lastFinalbaseshipamt = $this->baseShippngAmountByOrderUtr($shipmentpayout_report1_val['order_entid'],$orderBaseShippingAmount);

						/*$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$shipmentpayout_report1_val['order_entid']."' AND `address_type` = 'shipping'")->fetch();
						$getCountryResult = $getCountryOf['country_id'];*/
						$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
						$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
						$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
						$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
						$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
						$couponVendorId = $_resultCoupon->getVendorid();
						$actualDiscountamount = $order->getBaseDiscountAmount();
						if($couponVendorId == $vendorId)
						{
							$discountAmountCoupon = $actualDiscountamount;
							$disCouponcode = $order->getCouponCode();
						}
						// else
						// {
						// 	$lastFinaldiscountamt = $this->baseDiscountAmountByOrderUtr($shipmentpayout_report1_val['order_entid'],$actualDiscountamount);
						// 	$discountAmountCoupon = $lastFinaldiscountamt;
						// }
						//Modifying By Ankit due to latest shipping amount policy
						$_orderCurrencyCode = $order->getOrderCurrencyCode();
						if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate))){
							$subTotal = $shipmentpayout_report1_val['subtotal']/1.5 ;
						} else {
							$subTotal = $shipmentpayout_report1_val['subtotal'];
						}
						$total_amount = $subTotal+$shipmentpayout_report1_val['base_shipping_amount']+$discountAmountCoupon;
						/**** Code for deduction of Bank wise persentage from Craftsvilla Commission */
						$sqlQuery = "SELECT `pg_type`, `mode` FROM `payu_payment_details` WHERE `increment_id` =" .$shipmentpayout_report1_val['order_id'];
						$result = $readOrderCntry->query($sqlQuery)->fetch();
						$payuChargePercent = 0;
						if($result){
							$mode = $result['mode'];
							$pg = $result['pg_type'];
							if($mode == 'NB'){
								if ($pg == 'HDFCNB' or $pg == 'ICICI'){
									$payuChargePercent = 1.55; //For HDFC and ICICI Net banking
								}else {
									$payuChargePercent = 1.25; //Pre approved Net banking
								}
							} else if($mode == 'CC'){
								if($pg != 'UBIPG' or $pg != 'SBIPG'){
									$payuChargePercent = 1.75; //For UBI and SBI Payment gateway
								}else if ($pg == 'INDUS'){
									$payuChargePercent = 2.00; // For INDUS PG
								}else if ($pg == 'AMEX'){
									$payuChargePercent = 2.60; //AMEX Charge
								}else {
									$payuChargePercent = 1.90; //Any other PG
								}
							} else if($mode == 'DC'){
								if($total_amount >2000){
									$payuChargePercent = 1;
								} else {
									$payuChargePercent = 0.75;
								}
							} else if($pg == 'PAISA'){
								$payuChargePercent = 1.80; // Payu Wallet PG
							} else {
								$payuChargePercent = 1.50; // Any other Default percent deduction
							}
						} else {
							$payuChargePercent = 1.50;
							$pg = 'nill';
						}
						/****Code for deduction of Bank wise persentage from Craftsvilla Commission ENDS*/
						$payu_commission = $total_amount * ($payuChargePercent/100) ;
						/***********SEND SHIPPING CHECK**************/
						$senddLogisticAmount = (75*(1+$service_tax));
						$sqlSenddCheckQuery = "select `result_extra` from `sales_flat_shipment_track` where `result_extra` = 'sendd_prepaid' AND parent_id = " .$shipmentpayout_report1_val['entity_id'];
						$resultSendd = $readOrderCntry->query($sqlSenddCheckQuery)->fetch();
						/***********SEND SHIPPING CHECK**************/
						if($shipmentpayout_report1_val['order_created_at']<='2012-07-02 23:59:59')
						{
							if($vendors->getManageShipping() == "imanage")
							{
								$vendor_amount = ($total_amount*(1-$commission_amount/100));
								$kribha_amount = ($subTotal - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
							}
							else {
								$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
								$kribha_amount = (($subTotal+$itemised_total_shippingcost) - $vendor_amount);
							}
						}
						else {
							if($vendors->getManageShipping() == "imanage")
							{
								$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
								$kribha_amount = ($total_amount+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'])*1.00 - $vendor_amount;
							}
							else {
								//$vendor_amount = (($total_amount+$base_shipping_amount+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1450)));
								if($_orderCurrencyCode == 'INR'){
									$sqlQuery = "select `courier_name`,`result_extra` from `sales_flat_shipment_track` where LOWER(`courier_name`) = 'dhl_int' AND parent_id = " .$shipmentpayout_report1_val['entity_id'];
									$result = $readOrderCntry->query($sqlQuery)->fetch();
									if($result){
										$total_amount1 = $total_amount - $shipmentpayout_report1_val['base_shipping_amount'];
										$vendor_amount = (($total_amount1)*(1-($commission_amount/100)*(1+$service_tax)));
										if($resultSendd){
											$vendor_amount = $vendor_amount - $senddLogisticAmount;
											$isSenddFlag = "true";
										}
										$kribha_amount = ((($total_amount)*1.00) - $vendor_amount);
										$shipmentType = "KYC";
									}else {
										$vendor_amount = (($total_amount)*(1-($commission_amount/100)*(1+$service_tax)));
										if($resultSendd){
											$vendor_amount = $vendor_amount - $senddLogisticAmount;
											$isSenddFlag = "true";
										}
										$kribha_amount = ((($total_amount)*1.00) - $vendor_amount) - $payu_commission;
										$shipmentType = "IN";
									}
								}else{
									$sqlQuery = "select `courier_name`,`result_extra` from `sales_flat_shipment_track` where LOWER(`courier_name`) = 'dhl_int' AND parent_id = " .$shipmentpayout_report1_val['entity_id'];
									$result = $readOrderCntry->query($sqlQuery)->fetch();
									if($result){
										$total_amount1 = $total_amount - $shipmentpayout_report1_val['base_shipping_amount'];
										$vendor_amount = (($total_amount1)*(1-($commission_amount/100)*(1+$service_tax)));
										if($resultSendd){
											$vendor_amount = $vendor_amount - $senddLogisticAmount;
											$isSenddFlag = "true";
										}
										$total_amount = ($subTotal*1.5)+$shipmentpayout_report1_val['base_shipping_amount']+$discountAmountCoupon; //For 1.5 Factor recovery
										$kribha_amount = ((($total_amount)*1.00) - $vendor_amount);
										$shipmentType = "KYC";
									}else {
										$vendor_amount = (($total_amount)*(1-($commission_amount/100)*(1+$service_tax)));
										if($resultSendd){
											$vendor_amount = $vendor_amount - $senddLogisticAmount;
											$isSenddFlag = "true";
										}
										$total_amount = ($subTotal*1.5) + $shipmentpayout_report1_val['base_shipping_amount'] +$discountAmountCoupon; //For 1.5 Factor recovery
										$kribha_amount = ((($total_amount)*1.00) - $vendor_amount);
										$shipmentType = "Non IN KYC";
									}
								}
							}

						}
							// if($total_amount == 0){///////////////////////////////////////////////
							// 	continue;
							// }
							if( (($vendor_amount+$closingbalance) < 0) &&  $utrBalance >= $vendor_amount )
							{
								$strTest.= $shipmentpayout_report1_val['shipment_id'].",".$utrBalance.",".$total_amount.",".$vendor_amount.",".$kribha_amount.",".$closingbalance.",";////////////////////
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){
									//$utrBalance = $utrBalance - $vendor_amount;
									$vendor_amount = 0;
								}
									$adjustmentAmount = $adjustmentAmount + $vendor_amount;
									$closingbalance = $closingbalance + $vendor_amount;
									//$utrBalance = $utrBalance - $vendor_amount;
									//$vendor_amount = 0;
									$strTest.= "$adjustmentAmount,$commission_amount,$service_tax,$shipmentType,$payu_commission,$pg,$isSenddFlag\n";
									$neft = 'Adjusted Against Refund';
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`todo_payment_amount`= '".$adjustmentAmount."',`todo_commission_amount`= '".$kribha_amount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);
									$write->closeConnection();

									$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');
									$queryUtrupdate = "update utrreport set `balance` = '".$utrBalance."' WHERE `utrno` = '".$utrNum."'";
									$writeUtr->query($queryUtrupdate);
									$writeUtr->closeConnection();
									$this->_getSession()->addSuccess($this->__('Total of %d record(s) UTR successfully Assigned'.$shipmentpayout_report1_val['shipment_id']));

							} else if ($utrBalance >= $total_amount) {
								$strTest.= $shipmentpayout_report1_val['shipment_id'].",".$utrBalance.",".$total_amount.",".$vendor_amount.",".$kribha_amount.",".$closingbalance.",nill,$commission_amount,$service_tax,$shipmentType,$payu_commission,$pg,$isSenddFlag\n";////////////////////
								$utrBalance = $utrBalance - ($total_amount - $payu_commission);
								$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
								$queryUpdate = "update shipmentpayout set `citibank_utr` = '".$utrNum."', `todo_payment_amount`= '".$vendor_amount."',`todo_commission_amount`= '".$kribha_amount."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
								$write->query($queryUpdate);
								$write->closeConnection();
								$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');
								$queryUtrupdate = "update utrreport set `balance` = '".$utrBalance."' WHERE `utrno` = '".$utrNum."'";
								$writeUtr->query($queryUtrupdate);
								$writeUtr->closeConnection();
								$this->_getSession()->addSuccess($this->__('Total of %d record(s) UTR successfully Assigned'.$shipmentpayout_report1_val['shipment_id']));
							}else{
								$this->_getSession()->addError($this->__('Total Amount is greater than Utr Balance,So UTR Not Updated'.$shipmentpayout_report1_val['shipment_id']));
							}
					}
				}

				/************Functionality to Adjust Small balance Amount*********************/
				if($utrBalance <= 50){
					$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
					$queryUtrShipmentCount = "select shipment_id , todo_commission_amount from shipmentpayout where citibank_utr = '".$utrNum."'";
					$resultUtrShipment = $readQuery->query($queryUtrShipmentCount)->fetchAll();
					$readQuery->closeConnection();
					$unitCommissionAmount = $utrBalance / count($resultUtrShipment);
					foreach ($resultUtrShipment as $value) {
						$tempShipmentId = $value['shipment_id'];
						$originalTodoCommissionAmount = $value['todo_commission_amount'] ;
						$finalTodoCommissionAmount = $originalTodoCommissionAmount + $unitCommissionAmount;
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
						$queryUpdate = "update shipmentpayout set `todo_commission_amount`= '".$finalTodoCommissionAmount."' WHERE shipment_id = '".$tempShipmentId."'";
						$write->query($queryUpdate);
						$write->closeConnection();
						$utrBalance = $utrBalance - $unitCommissionAmount;
						$writeUtr = Mage::getSingleton('core/resource')->getConnection('utrreport_write');
						$queryUtrupdate = "update utrreport set `balance` = '".$utrBalance."' WHERE `utrno` = '".$utrNum."'";
						$writeUtr->query($queryUtrupdate);
						$writeUtr->closeConnection();
					}
				}
				/********************END***********************************************************/

				$filename = "UtrReport_".date("Ymd");
				$filePathOfCsv = Mage::getBaseDir('media').DS.'misreport'.DS.$filename.'.txt';
			    $fp=fopen($filePathOfCsv,'a');
			    $strHead = "Shipment Id,Utr Balance,Total Amount,Vendor Amount,Commission Amount,Closing Balance, Adjustment Amount,Commission Percent,Service Tax,Shipment Type,PayU Commission,PG,SenddFlag\n";
			    fputs($fp, $strHead);
			    fputs($fp, $strTest);
			    fputs($fp, $strHead);
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
