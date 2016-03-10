<?php
/**
 * Shipment Tracking module
 * Author : Saurabh Sharma*/
class Craftsvilla_Shipmentpayout_Adminhtml_ImportpaytypeController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Constructor
     */
    protected function _construct()
    {        
        $this->setUsedModuleName('Shipmentpayout');
    }

    /**
     * Main action : show import form
     */
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('report/shipmentpayout/importpaytype')
            ->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_import_formpaytype'))
            ->renderLayout();
    }

    /**
     * Import Action
     */
    public function importpaytypeAction()
    {
        if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmentpaytype_file']['tmp_name'])) {
            try {
                //$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importPayTypeFile($_FILES['import_shipmentpaytype_file']['tmp_name']);
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Invalid file upload attempttttt'));
            }
        }
        else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Importation logic
     * @param string $fileName
    
     */
    protected function _importPayTypeFile($fileName)
    {
        /**
         * File handling
         **/
		 
        ini_set('auto_detect_line_endings', true);
        $csvObject = new Varien_File_Csv();
        $csvData = $csvObject->getData($fileName);
		
        /**
         * File expected fields
         */
        $expectedCsvFields  = array(
            0   => $this->__('Shipment Id'),
            1   => $this->__('Payment Amount'),
			2   => $this->__('Commission Amount'),
            3   => $this->__('Payment Type'),
            4   => $this->__('Date')
        );

       
        /**
         * $k is line number
         * $v is line content array
         */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        
        foreach ($csvData as $k => $v) {

            /**
             * End of file has more than one empty lines
             */
            if (count($v) <= 1 && !strlen($v[0])) {
                continue;
            }

            /**
             * Check that the number of fields is not lower than expected
             */
            if (count($v) < count($expectedCsvFields)) {
                $this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
                continue;
            }

            /**
             * Get fields content
			 extra column 'Commision' added By Dileswar on dated 03-04-2013
             */
            $shipmentId = $v[0];
            $paymentAmount=$v[1];
			$commissionAmount=$v[2];
            $paymentType=$v[3];
            $dateCsv=$v[4];
			$date=date('Y-m-d ',strtotime($dateCsv));
			$date1=date('d-m-Y ',strtotime($dateCsv));
			
			$queryShipmentUtr = "update shipmentpayout set payment_amount='".$paymentAmount."',commission_amount='".$commissionAmount."',type='".$paymentType."',shipmentpayout_update_time='".$date."',shipmentpayout_status='1' WHERE shipment_id = '".$shipmentId."'";  
            $write->query($queryShipmentUtr);
			
           $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
           $vendorId = $shipment['udropship_vendor'];
           $hlp = Mage::helper('udropship');
	        $commission_amount = $hlp->getVendorCommission($vendorId, $shipmentId);
	        $service_tax = $hlp->getServicetaxCv($shipmentId);
		  //$commission_amount = 20;
		  $total_value = $shipment['base_total_value'];
		   $order_id = $shipment['order_id'];
		  $order = Mage::getModel('sales/order')->load($order_id);
		  $_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR'))
				{
					$total_value = $shipment['base_total_value']/1.5;
				}
		  //$itemised_total_shippingcost = $shipment['itemised_total_shippingcost'];
		  $itemised_total_shippingcost = $shipment['base_shipping_amount'];
		  
		  
		  
		 
		  //$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				$discountAmountCoupon = 0;
				$disCouponcode = '';
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
			
           
			$read = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_read');
			$payment = "select * from  sales_flat_order_payment where parent_id = '".$order_id."'";
			$paymentrd = $read->fetchAll($payment);
			$method = $paymentrd[0]['method'];
			if($method=='cashondelivery')
			{
				$method1 = 'COD';
			}
			else
			{
				$method1 = 'Prepaid';
			}
			$shipmentlogisticcharge = "select * from shipmentpayout where shipment_id = '".$shipmentId."'";
			$sqlred = $read->fetchAll($shipmentlogisticcharge);
			$logisticcharge1 = $sqlred[0]['intshipingcost'].'<br>';
			$logisticcharge = $logisticcharge1*(1+$service_tax);
			//echo $commissionAmount = $sqlred[0]['intshipingcost'];
			//$querysmsemail = "SELECT sfs.`increment_id` as shipment_id,uv.`email` as email,uv.`telephone`,uv.`vendor_name` as vendor_name FROM `udropship_vendor` as uv,sales_flat_shipment as sfs where sfs.`udropship_vendor` = uv.`vendor_id` and sfs.`increment_id` = '".$shipmentId."'";

			$querysmsemail = "SELECT email,`telephone`,`vendor_name` FROM `udropship_vendor` where `vendor_id` = '".$vendorId."'";

			$sql = $read->fetchAll($querysmsemail);
			$smsTelephone = $sql[0]['telephone'];
			$smsEmail = $sql[0]['email'];
			$vendorName = $sql[0]['vendor_name'];
			$servicetax = 1+$service_tax;
			$totalpayment = ($total_value + $itemised_total_shippingcost + $discountAmountCoupon)*(1-($commission_amount/100)*(1+$service_tax));//- ($logisticcharge - $servicetax));
			$totalpayment = $totalpayment-$logisticcharge;
			$cvcommission = ($total_value + $itemised_total_shippingcost + $discountAmountCoupon)*(0.2);
			$cvsttax = $cvcommission*$service_tax;
		
			$shipmentvalue = "<table border='0' width='auto'><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Shipment Value: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".floor($total_value)."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Discount Amount: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$discountAmountCoupon."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShippingCost: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$itemised_total_shippingcost."</td></tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Logistic Charge: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. -".$logisticcharge."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Service Tax: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. -".floor($cvsttax)."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Commission Amount:</strong> </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. -".floor($cvcommission)."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><strong>Total Payment:</strong> </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><strong>Rs. ".floor($totalpayment)."</strong></td></tr></table>";
			$storeId = Mage::app()->getStore()->getId();
		 	$templatePayid = 'shipmentpayments_to_vendors';
         	$sender = Array('name'  => 'Craftsvilla Finance',
				'email' => 'finance@craftsvilla.com');
		  //   $translate  = Mage::getSingleton('core/translate');
		//				$translate->setTranslateInline(false);
		 	$varSms = Array('shipmentId' =>	$shipmentId,
						'paymentAmount' => $paymentAmount,
						'paymentType' => $paymentType,
						'date' => $date1,
						'vendorName' => $vendorName,
						'shipmentvalue' => $shipmentvalue,
						'totalpayment' => $totalpayment,
						'method' => $method1,
						
			);
		
		$emailPayout = Mage::getModel('core/email_template')->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId));
		$emailPayout->sendTransactional($templatePayid, $sender, $smsEmail,$vendorName,$varSms, $storeId);
		//$emailPayout->sendTransactional($templatePayid, $sender, 'dileswar@craftsvilla.com',$vendorName,$varSms, $storeId);		
		//$translate->setTranslateInline(true);
		
	//// Added For SMS To Vendor by Dileswar On Dated (08-02-2013)//////    --------------------//////////////	
		
			$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
			$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
			$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
			$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		// Send SMS to Vendor
		$customerMessage = 'Craftsvilla: Deposited in Your Bank Amount Rs.'.$paymentAmount.' For Shipment# '.$shipmentId.', On Date'.$date1.' Via '.$paymentType.'.';
		$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$smsTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
           //    $this->_getSession()->addError($this->__('Can Not Save UTR Number For Order Number - '.$orderId));
         
		 //  Send email to Vendor....................
		 
		}
           $this->_getSession()->addSuccess($this->__('Payment Amount And Payment Types are successfully saved & SMS & Email sent to respective Vendors!!!'));
        
    }
	
}
