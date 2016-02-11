<?php

class Craftsvilla_Shipmentpayout_Adminhtml_ShipmentpayoutController extends Mage_Adminhtml_Controller_action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('shipmentpayout/shipmentpayout')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipmentpayout Manager'), Mage::helper('adminhtml')->__('Shipmentpayout Manager'));
		
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
        $this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_grid')->toHtml()
        );
    }


	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('shipmentpayout/shipmentpayout')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('shipmentpayout_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('shipmentpayout/shipmentpayout');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipmentpayout Manager'), Mage::helper('adminhtml')->__('Shipmentpayout Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Shipmentpayout News'), Mage::helper('adminhtml')->__('shipmentpayout News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_edit'))
				->_addLeft($this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_edit_tabs'));
				
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shipmentpayout')->__('Shipmentpayout does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
				
		if ($data = $this->getRequest()->getPost()) {
		$adjustamount = $data['adjustment'];
		$id = $this->getRequest()->getParam('id');
		$shipmentidadjust = Mage::getModel('shipmentpayout/shipmentpayout')->loadByPayoutId($id)->getShipmentId();
		$adjustSave = Mage::getModel('shipmentpayout/shipmentpayout')->loadByPayoutId($id)->getAdjustment();
		if($adjustamount != NULL)
			{
			if(number_format($adjustSave,0) != number_format($adjustamount,0))
				{
				$read = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_read');
				$adjustquery = $read->query("select * from `sales_flat_shipment` as sfs,`udropship_vendor` as uv where sfs.`udropship_vendor` = uv.`vendor_id` and sfs.`increment_id` = '".$shipmentidadjust."'")->fetch();
				$closing_balance = $adjustquery['closing_balance'];
				$vendor_Id = $adjustquery['udropship_vendor'];
				$closing_balance = $closing_balance+$adjustamount;
				$writeAd = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
				$queyVendorAd = "update `udropship_vendor` set `closing_balance` = '".$closing_balance."' WHERE `vendor_id` = '".$vendor_Id."'";
				$writeAd->query($queyVendorAd);
				Mage::getSingleton('adminhtml/session')->addSuccess($this->__("Note: Adjustment Amount & Closing Balance was Changed"));
				}
			
					
			}
			
			/*if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/// Starting upload 	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['filename'] = $_FILES['filename']['name'];
			}*/
	  			
	  			
			$model = Mage::getModel('shipmentpayout/shipmentpayout');
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				/*if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}*/	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('shipmentpayout')->__('Shipmentpayout was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shipmentpayout')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('shipmentpayout/shipmentpayout');
				 
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
	
	 /**
     * Export product grid to CSV format
     */
     public function exportCsvAction()
     {
        
		//For get email when someone xport 
			$user = Mage::getSingleton('admin/session');
			$userId = $user->getUser()->getUserId();
			$userEmail = $user->getUser()->getEmail();
			$userFirstname = $user->getUser()->getFirstname();
			$date = date("d-m-Y h:i:s",Mage::getModel('core/date')->timestamp(time()));
			$storeId = Mage::app()->getStore()->getId();
       		$translate  = Mage::getSingleton('core/translate');
			$translate->setTranslateInline(false);
			$vars = array();
			$templateId ='shipmentpayout_export_csv';
					$translate  = Mage::getSingleton('core/translate');
					$mailSubject = 'Shipmentpayout Csv exported By User:'.$userFirstname.' On dated:'.$date.' From ip add'.$_SERVER['REMOTE_ADDR'];
					$sender = Array('name'  => 'Craftsvilla',
									'email' => 'places@craftsvilla.com');
		
        	$mailTemplate=Mage::getModel('core/email_template');
        	$vars['firstname']=$userFirstname;
			$mailTemplate->setTemplateSubject($mailSubject)
						 ->sendTransactional($templateId, $sender,'manoj@craftsvilla.com',$vars,$storeId);
			$translate->setTranslateInline(true);
		
		$fileName   = 'shipmentpayout.csv';
        $content    = $this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_grid')
            				->getCsv();

        $this->_sendUploadResponse($fileName, $content);
     }

     /**
     * Export product grid to XML format
     */
     public function exportXmlAction()
     {
        $fileName   = 'shipmentpayout.xml';
        $content    = $this->getLayout()->createBlock('shipmentpayout/adminhtml_shipmentpayout_grid')
           			 ->getXml();

        $this->_sendUploadResponse($fileName, $content);
     }
     
	 protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
     {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');

        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);

        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
     }     
     

    //public function massDeleteAction() {
    //    $webIds = $this->getRequest()->getParam('web');
   //     if(!is_array($webIds)) {
   //		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
   //     } else {
   //         try {
   //             foreach ($webIds as $webId) {
   //                 $web = Mage::getModel('web/web')->load($webId);
   //                 $web->delete();
   //             }
   //             Mage::getSingleton('adminhtml/session')->addSuccess(
   //                 Mage::helper('adminhtml')->__(
   //                     'Total of %d record(s) were successfully deleted', count($webIds)
   //                 )
   //             );
   //         } catch (Exception $e) {
   //             Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
   //         }
   //     }
   //     $this->_redirect('*/*/index');
   // }
	
    public function massStatusAction()
    {
        $shipmentpayoutIds = $this->getRequest()->getParam('shipmentpayout');
        if(!is_array($shipmentpayoutIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($shipmentpayoutIds as $shipmentpayoutId) {
                    if($shipmentpayoutId)
                    {
						$shipmentpayout = Mage::getSingleton('shipmentpayout/shipmentpayout')
                        ->load($shipmentpayoutId)
                        ->setShipmentpayoutStatus($this->getRequest()->getParam('status'))
                        ->setShipmentpayoutUpdateTime($this->getRequest()->getParam('updatedate'))
                        ->setCitibankUtr($this->getRequest()->getParam('utrno'))
                        ->setIsMassupdate(true)
                        ->save();                    
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($shipmentpayoutIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    public function report1Action()
    {
	
	$selected_date_val = $this->getRequest()->getParam('selected_date');
 
    	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('order_entid'=>'order_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				//->joinLeft('sales_flat_order', 'main_table.order_id = sales_flat_order.increment_id ',array('coupon_code','discount'=>'base_discount_amount'))
				//->joinLeft('salesrule', 'a.udropship_vendor = salesrule.vendorid ')
				//->joinLeft('salesrule_coupon', 'salesrule.rule_id = salesrule_coupon.rule_id','code')
				
				//->where('main_table.type = "Adjusted Against Refund"');
				->where('main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status IN (1,17) AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard","avenues_standard","payucheckout_shared","gharpay_standard","free")');// a.udropship_status IN (1,15,17)
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)
      	
      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();*/
			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "NodalReport"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("ACCOUNT_NO","SHIPMENT_ID","ORDER_ID","ORDER_DATE","ME_ID","MERCHANT","TXN_TYPE","BENE_AC_NO","IFSC_CODE","TID","AMOUNT","NARRATION","UTR_NUMBER");
    	
		$numfields = sizeof($fieldlist);
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
		/*echo "<pre>";
		print_r($shipmentpayout_report1_arr);
		exit();*/
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
			
	$merchantIdnew = $this->getMerchantIdCv($shipmentpayout_report1_val['udropship_vendor']);		
			$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
	    	if(($shipmentpayout_report1_val['udropship_vendor'] != '' && ($merchantIdnew!= '')) )
    		{
		    
				unset($total_amount);
		    	unset($commission_amount);
		    	unset($vendor_amount);
		    	unset($kribha_amount);
		    	unset($gen_random_number);
		    	unset($itemised_total_shippingcost);
		    	//unset($base_shipping_amount);
		    	$total_amount1 = $shipmentpayout_report1_val['subtotal'];
				$total_amount = $shipmentpayout_report1_val['subtotal'];
				
				$_liveDate = "2012-08-21 00:00:00";
		    	$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
				$orderBaseShippingAmount = $order->getBaseShippingAmount();
				// Below Two lines added By Dileswar for Adding Discount coupon on dated 25-07-2013
				$disCouponcode = '';
				$discountAmountCoupon = 0;
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	//	$commission_amount = $shipmentpayout_report1_val['commission_percent'];
		    	$commission_amount = 20;
				$lastFinalbaseshipamt = $this->baseShippngAmountByOrder($shipmentpayout_report1_val['order_entid'],$orderBaseShippingAmount);
				
				$readOrderCntry = Mage::getSingleton('core/resource')->getConnection('core_read');
				$getCountryOf = $readOrderCntry->query("SELECT `country_id` FROM `sales_flat_order_address` WHERE `parent_id` = '".$shipmentpayout_report1_val['order_entid']."' AND `address_type` = 'shipping'")->fetch();
				$getCountryResult = $getCountryOf['country_id'];
				
				
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$vendor_amount = $shipmentpayout_report1_val['todo_payment_amount'];
				$kribha_amount = $shipmentpayout_report1_val['todo_commission_amount'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
		    	// Added By Dileswar On dated 25-07-2013 For get the Value of coupon id & vendorid
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
				/*if($commission_amount>0)
		    	{
		    		$kribha_amount = (($total_amount*$commission_amount)/100);
		    	}
		    	else {
		    		$kribha_amount = 0;
		    	}*/
		    		
		    	//$vendor_amount = ($total_amount-$kribha_amount);
		    	
		    	//$vendors = Mage::getModel('udropship/vendor')->getCollection();
		    	//$vendors->getSelect()->where('vendor_id = '.$shipmentpayout_report1_val['udropship_vendor']);
		    	//$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
		    	/*echo "Query:".$vendors->getSelect()->__toString();
		    	exit();*/
		    		
		    	//$vendors_arr = $vendors->getData();
		    		//echo "<pre>"; print_r($vendors_arr); exit;
		    	//$new_vendor_obj = json_decode($vendors_arr[0]['custom_vars_combined']);
		    		
		    	$gen_random_number = "K".$this->gen_rand();

    	// 		if($shipmentpayout_report1_val['order_created_at']<='2012-07-02 23:59:59')
		   //  	{
		   //  		if($vendors->getManageShipping() == "imanage")
			  //   	{
			  //   		$vendor_amount = ($total_amount*(1-$commission_amount/100));
					// 	$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
			  //   	}
			  //   	else {
			  //   		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
					// 	$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
			  //   	}
		   //  	}
		   //  	else {
		   //  		if($vendors->getManageShipping() == "imanage")
			  //   	{

			  //   		$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
					// 	//$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
					// 	//change to accomodate 3% Payment gateway charges on dated 20-12-12
					// 	$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'])*1.00 - $vendor_amount;
			  //   	}
			  //   	else {
						
			  //   		//$vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1236)));
			  //   		$vendor_amount = (($total_amount+$base_shipping_amount+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1450)));
					// 	//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
					// 	//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// // Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
					// 	//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
					// 	//if($getCountryResult == 'IN')
					// //	{	
					// 		$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)*1.00) - $vendor_amount);
					// //	}
					// //	else
					// //	{
					// //		$kribha_amount = ((($total_amount1+$lastFinalbaseshipamt+$discountAmountCoupon)*1.00) - $vendor_amount);
					// //	}
			  //   	}
					
		   //  	}
				
				/*if(($vendor_amount+$closingbalance) > 0)
						{
							$vendor_amount = $vendor_amount+$closingbalance;
							$adjustmentAmount = $adjustmentAmount - $closingbalance; 
							$closingbalance = 0;
							$utr = $shipmentpayout_report1_val['citibank_utr'];
							if($closingbalance != 0)
							{$utr = $utr.'-Adjusted';}
							
						}
					else{	*/
					
					//Below lines for to update the value in shipmentpayout table ...
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
						if($disCouponcode){
						$queryUpdateDiscount = "update shipmentpayout set `discount` ='".$discountAmountCoupon."',`couponcode` = '".$disCouponcode."' WHERE `shipment_id` = '".$shipmentpayout_report1_val['shipment_id']."'";
					
						$write->query($queryUpdateDiscount);
						}	
					$utr = $shipmentpayout_report1_val['citibank_utr'];
					$neft = 'NEFT';
						if(($vendor_amount+$closingbalance) <= 0)
							{
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){$vendor_amount = 0;}
								
								else{	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`payment_amount`= '".$adjustmentAmount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	
								
								}
							}	
							
							
						
		    	for($m =0; $m < sizeof($fieldlist); $m++) {
		    		
		    		$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "ACCOUNT_NO")
		    		{
		    			$output .= '712097027';
		    		}
		    		
		    		if($fieldvalue == "SHIPMENT_ID")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "ORDER_ID")
		    		{
		    			$output .= $shipmentpayout_report1_val['order_id'];
		    		}
		    		
		    		if($fieldvalue == "ORDER_DATE")
		    		{
		    			$output .= $shipmentpayout_report1_val['order_created_at'];
		    		}
		    			
		    		if($fieldvalue == "ME_ID")
		    		{
		    			$output .= $merchantIdnew;
		    		}
		    			
		    		if($fieldvalue == "MERCHANT")
		    		{
		    			$output .= $vendors->getCheckPayTo();
		    		}
		    			
		    		if($fieldvalue == "TXN_TYPE")
		    		{
		    			$output .= $neft;
		    		}
		    			
		    		if($fieldvalue == "BENE_AC_NO")
		    		{
		    			$output .= $vendors->getBankAcNumber();
		    		}
		    			
		    		if($fieldvalue == "IFSC_CODE")
		    		{
		    			$output .= $vendors->getBankIfscCode();
		    		}
		    			
		    		if($fieldvalue == "TID")
		    		{
		    			$output .= $gen_random_number;
		    		}
		    		
		    		if($fieldvalue == "AMOUNT")
		    		{
		    			$output .= str_replace(',','',number_format($vendor_amount,2));
		    		}
		    		
		    		if($fieldvalue == "NARRATION")
		    		{
		    			$output .= "PAYOUT TO SELLERS";
		    		}
		    			
		    		if($fieldvalue == "UTR_NUMBER")
		    		{
		    			$output .= $utr;
		    		}
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    		else {
		    			$output .= "\n";
			    		$output .= '712097027';
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['shipment_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_created_at'];
			    		$output .= ",";
			    		$output .= 'NEFTCRAFTS001';
			    		$output .= ",";
			    		$output .= 'Craftsvilla Handicrafts Pvt Ltd';
			    		$output .= ",";
			    		$output .= $neft;
			    		$output .= ",";
			    		$output .= '0712097019';
			    		$output .= ",";
			    		$output .= 'CITI0100000';
			    		$output .= ",";
			    		$output .= $gen_random_number;
			    		$output .= ",";
			    		$output .= str_replace(',','',number_format($kribha_amount,2));
			    		$output .= ",";
			    		$output .= "CORRESPONDING COMMISSION";
			    		$output .= ",";
			    		$output .= $utr;
			    		$output .= ",";
		    		}
		    	}
		    	$output .= "\n";
    		}
	    }

    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    }
    
	public function report2Action()
    {
    	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost', 'base_shipping_amount'=>'base_shipping_amount'))
      			
      			->where('main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0');
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();	
    	
		$filename = "CraftsvillaReport"."_".date("Y-m-d");
		$output = "";
	
		$fieldlist = array("ME_ID","TID","AMOUNT","UTR_NUMBER","DATE");
    	
		$numfields = sizeof($fieldlist);
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
	    	/*echo "<pre>";
	    	print_r($shipmentpayout_report1_val);*/
	    	if($shipmentpayout_report1_val['udropship_vendor'] != '')
    		{
		    	unset($total_amount);
		    	unset($commission_amount);
		    	unset($vendor_amount);
		    	unset($kribha_amount);
		    	unset($gen_random_number);
		    	unset($itemised_total_shippingcost);
		    	
		    	$total_amount = $shipmentpayout_report1_val['subtotal'];
		    	$commission_amount = $shipmentpayout_report1_val['commission_percent'];
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	
		    	//$vendor_amount = ($total_amount-$kribha_amount);
		    	
		    	$vendors = Mage::getModel('udropship/vendor')->getCollection();
		    	$vendors->getSelect()->where('vendor_id = '.$shipmentpayout_report1_val['udropship_vendor']);
		    	
		    	$vendors_arr = $vendors->getData();
		    		
		    	$new_vendor_obj = json_decode($vendors_arr[0]['custom_vars_combined']);
		    		
		    	$gen_random_number = "K".$this->gen_rand();
		    	
		    	/*if($vendors_arr[0]['manage_shipping'] == 'imanage')
		    	{
		    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
		    	}
		    	else {
		    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));	
		    	}

		    	$kribha_amount = ($total_amount-$vendor_amount);*/
		    	
    			if($shipmentpayout_report1_val['order_created_at']<='2012-07-02 23:59:59')
		    	{
		    		if($vendors_arr[0]['manage_shipping'] == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-$commission_amount/100));
			    	}
			    		
			    	$kribha_amount = ($total_amount - $vendor_amount);	
		    	}
		    	else {
		    		if($vendors_arr[0]['manage_shipping'] == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
			    	}
			    		
			    	$kribha_amount = ($total_amount - $vendor_amount);
		    	}
		    		
		    	for($m =0; $m < sizeof($fieldlist); $m++) {
		    		$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "ME_ID")
		    		{
		    			$output .= $vendors_arr[0]['merchant_id_city'];
		    		}
		    			
		    		if($fieldvalue == "TID")
		    		{
		    			$output .= $gen_random_number;
		    		}
		    		
		    		if($fieldvalue == "AMOUNT")
		    		{
		    			$output .= $vendor_amount;
		    		}
		    			
		    		if($fieldvalue == "UTR_NUMBER")
		    		{
		    			$output .= $shipmentpayout_report1_val['citibank_utr'];
		    		}
		    		
		    		if($fieldvalue == "DATE")
		    		{
		    			$output .= date("d-m-Y", strtotime($shipmentpayout_report1_val['shipmentpayout_update_time']));
		    		}
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    		else {
		    			$output .= "\n";
			    		$output .= 'A2A';
			    		$output .= ",";
			    		$output .= $gen_random_number;
			    		$output .= ",";
			    		$output .= $kribha_amount;
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['citibank_utr'];
			    		$output .= ",";
			    		$output .= date("d-m-Y", strtotime($shipmentpayout_report1_val['shipmentpayout_update_time']));
			    		$output .= ",";
		    		}
		    	}
		    	$output .= "\n";
    		}
	    }
    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    }
    
	public function gen_rand() {
   		$t = "123456789";
   		$r = "";
   		for ($i = 0; $i < 14; $i++) {
      		$r .= rand(1, 9);
   		}
   		return $r;
	}
	
	public function atxtreportAction()
		{
		$selected_date_val = $this->getRequest()->getParam('selected_date');
		$dateOpen = date('Ymd',strtotime($selected_date_val));
 
    	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status IN (1,17) AND `sales_flat_order_payment`.method IN ("secureebs_standard","purchaseorder","ccavenue_standard","avenues_standard","payucheckout_shared","gharpay_standard","paypal_standard","free")');// a.udropship_status IN (1,15,17)
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND `sales_flat_order_payment`.method IN ("cashondelivery","gharpay_standard","paypal_standard")');// a.udropship_status IN (1,15,17)
      	
      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();*/
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "NONNodalReport"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("Debit Account Number","Value Date","Customer Reference No","Beneficiary Name","Payment Type","Bene Account Number","Bank Code","Account type","Amount","Payment Details 1","Payment Details 2","Payment Details 3","Payment Details 4","Payable Location Code *","Payable Location Name *","Print Location Code *","Print Location Name *","Beneficiary Address 1","Beneficiary Address 2","Beneficiary Address 3","Beneficiary Address 4","Delivery Method","Cheque Number","Bene E-mail ID","Instrument Detail 1","Instrument Detail 2","Craftsvilla Commission");
    	
		$numfields = sizeof($fieldlist);
		$i = 1;
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
		/*echo "<pre>";
		print_r($shipmentpayout_report1_arr);
		exit();*/
		
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
			$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
			//if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($vendors->getMerchantIdCity() != ''))
			if($shipmentpayout_report1_val['udropship_vendor'] != '')
    		{
		    	unset($total_amount);
		    	unset($commission_amount);
		    	unset($vendor_amount);
		    	unset($kribha_amount);
		    	unset($gen_random_number);
		    	unset($itemised_total_shippingcost);
		    
				$total_amount1 = $shipmentpayout_report1_val['subtotal'];
				$total_amount = $shipmentpayout_report1_val['subtotal'];
				
				$_liveDate = "2012-08-21 00:00:00";
		    	$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
				
				// Below Two lines added By Dileswar for Adding Discount coupon on dated 25-07-2013
				$disCouponcode = '';
				$discountAmountCoupon = 0;
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	//$commission_amount = $shipmentpayout_report1_val['commission_percent'];
				$commission_amount = 20;
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
		    	
				// Added By Dileswar On dated 25-07-2013 For get the Value of coupon id & vendorid
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
				
				//$gen_random_number = "K".$this->gen_rand();

    			if($shipmentpayout_report1_val['order_created_at']<='2012-07-02 23:59:59')
		    	{
		    		if($vendors->getManageShipping() == "imanage")
			    	{
			    		$vendor_amount = ($total_amount*(1-$commission_amount/100));
						$kribha_amount = ($total_amount1 - $vendor_amount)+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'];
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
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'])*1.00 - $vendor_amount;
			    	}
			    	else {
						
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)*1.00) - $vendor_amount);
			    	}
					
					
		    	}
				
				//Below lines for to update the value in shipmentpayout table ...
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
					$queryUpdateDiscount = "update shipmentpayout set `discount` ='".$discountAmountCoupon."',`couponcode` = '".$disCouponcode."' WHERE `shipment_id` = '".$shipmentpayout_report1_val['shipment_id']."'";
					$write->query($queryUpdateDiscount);
				
					$utr = $shipmentpayout_report1_val['citibank_utr'];
					$neft = 'EFT';
						if(($vendor_amount+$closingbalance) <= 0)
							{
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){$vendor_amount = 0;}
								
								else{	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									//$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									//$queryUpdate = "update shipmentpayout set `adjustment` ='".$adjustmentAmount."',`type` = '".$neft."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`payment_amount`= '".$adjustmentAmount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	
								
								}
							}	
							
				for($m =0; $m < sizeof($fieldlist); $m++) {
					$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "Debit Account Number")
		    		{
		    			$output .= '710607028';
		    		}
		    		
		    		if($fieldvalue == "Value Date")
		    		{
		    			$output .= $dateOpen;
		    		}
		    		
		    		if($fieldvalue == "Customer Reference No")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Beneficiary Name")
		    		{
		    			$output .= $vendors->getCheckPayTo();
		    		}
		    			
		    		if($fieldvalue == "Payment Type")
		    		{
		    			$output .= $neft;
		    		}
		    			
		    		if($fieldvalue == "Bene Account Number")
		    		{
		    			$output .= $vendors->getBankAcNumber();
		    		}
		    			
		    		if($fieldvalue == "Bank Code")
		    		{
		    			$output .= $vendors->getBankIfscCode();
		    		}
		    			
		    		if($fieldvalue == "Account type")
		    		{
		    			$output .= '2';
		    		}
		    			
		    		if($fieldvalue == "Amount")
		    		{
		    			$output .= str_replace(',','',number_format($vendor_amount,2));
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',substr(strtoupper($vendors->getVendorName()),0,30)));		    		}
		    		
		    		if($fieldvalue == "Payment Details 3")
		    		{
		    			$output .= "";
		    		}
		    			
		    		if($fieldvalue == "Payment Details 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 3")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Delivery Method")
		    		{
		    				$output .= "";
		    		}
					if($fieldvalue == "Cheque Number")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Bene E-mail ID")
		    		{
		    			$output .= $vendors->getEmail();
		    		}
					if($fieldvalue == "Instrument Detail 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Instrument Detail 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Craftsvilla Commission")
		    		{
		    			$output .= $kribha_amount;
		    		}
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    	
				}
		    	$output .= "\n";
				
    		}
	    }
		
    	// Send the CSV file to the browser for download
	
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=$filename.csv");
		echo $output;
		exit;
    $i++;
	}
	public function refundedamountAction()
		{
			$shipmentpayoutIds = $this->getRequest()->getParam('shipmentpayout');
			$refundedAmount = $this->getRequest()->getParam('refunded_amount');		
			$orderIdr = Mage::getModel('shipmentpayout/shipmentpayout')->load($shipmentpayoutIds);
			$refundTodo = $orderIdr->getRefundedAmount(); 
			$amtordId = $orderIdr->getOrderId();
			if(number_format($refundTodo) > number_format($refundedAmount))
					{
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The 'Amount' is Greater Than 'To be Refund Amount'."));
					}
			else
				{
				$url = 'https://secure.ebs.in/api/1_0';
				$myvar1 = 'statusByRef';
				$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();
				$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();
				$myvar4 = $amtordId;
				$myvar5 = 'capture';
				$myvar10 = 'refund';
				$myvar11 = $refundedAmount;
				
				$fields = array(
							'Action' => urlencode($myvar1),
							'AccountID' => urlencode($myvar2),
							'SecretKey' => urlencode($myvar3),
							'RefNo' => urlencode($myvar4)
						);
				$fields_string = '';
				//url-ify the data for the POST
				foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				rtrim($fields_string, '&');
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_VERBOSE, 1); 
				curl_setopt($ch,CURLOPT_URL, $url);
				curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
				curl_setopt( $ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
				$response = curl_exec( $ch );
				if(curl_errno($ch))
				{		
					print curl_error($ch);
				}
				else
				{
					$xml = @simplexml_load_string($response);
					$myvar6 = $xml['amount'];
					$myvar7 = $xml['paymentId'];
					$myvar8 = $xml['transactionType'];
					$myvar9 = $xml['isFlagged'];
	
					if($myvar8 == 'Authorized')
					{
							if($myvar9 == 'NO')
								{
									Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is Not flagged :-".$myvar4));
								}
							else
								{
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Flagged :-".$myvar4));
								}
					}
					else
					{
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Order Payment is not Authorized :-".$myvar4));
					}
				}
				curl_close($ch);
	

				if($myvar8 == 'Authorized')
					{
				
					$fields2 = array(
						'Action' => urlencode($myvar10),
						'AccountID' => urlencode($myvar2),
						'SecretKey' => urlencode($myvar3),
						'Amount' => urlencode($myvar11),
						'PaymentID' =>urlencode($myvar7)
					);
					$fields_string2 = '';
					//url-ify the data for the POST
					foreach($fields2 as $key=>$value) { $fields_string2 .= $key.'='.$value.'&'; }
					rtrim($fields_string2, '&');
					$ch2 = curl_init();
					curl_setopt($ch2, CURLOPT_VERBOSE, 1); 
					curl_setopt($ch2,CURLOPT_URL, $url);
					curl_setopt($ch2,CURLOPT_POSTFIELDS, $fields_string2);
					curl_setopt( $ch2, CURLOPT_POST, 1);
					curl_setopt( $ch2, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt( $ch2, CURLOPT_HEADER, 0);
					curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
					$response2 = curl_exec( $ch2 );
					if(curl_errno($ch2))
						{		
						print curl_error($ch2);
						}
					else
						{
						$xml2 = @simplexml_load_string($response2);
					
					/*echo '<pre>';
					print_r($xml2);*/
							if($xml2['errorCode'] == '24')
							{
								Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('This payment is not captured'));
							}
							else
							{
								$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
								$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$myvar11."' WHERE `order_id` = '".$myvar4."'";
								$write->query($queryRefamt);
								Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11));			
							
							}
					
					}
					curl_close($ch2);
			
				}
			else
				{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is not ready to Refunded :-".$myvar4));
				}
			}
	$this->_redirect('*/*/');
	}
	
	
	public function autorefundAction()
		{
			$shipmentpayoutIds = $this->getRequest()->getParam('shipmentpayout');
			if(!is_array($shipmentpayoutIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			} else {
				try {
					foreach ($shipmentpayoutIds as $shipmentpayoutId) {
						if($shipmentpayoutId)
						{
							$shipmentpayoutdetails = Mage::getSingleton('shipmentpayout/shipmentpayout')->load($shipmentpayoutId);
							$shipmentId = $shipmentpayoutdetails->getShipmentId();                    
							$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
					$shipmentpayout_report1->getSelect()
							->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('entity_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
							->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
							->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
							->where('sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder")')// a.udropship_status IN (1,15,17)
							->where("main_table.shipment_id = '".$shipmentId."'");
					//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)
			
				//echo "<pre>Query:".$shipmentpayout_report1->getSelect()->__toString();
			//	exit();
				
		  	$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();			
			$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
			
			foreach($shipmentpayout_report1_arr as $row)
			{
			//print_r($row);exit;	
			//$orderID = $row['order_id'];
			if($row['refundtodo'] != "")
			{
			$id = $row['entity_id'];
			$refundedAmount = $row['refundtodo'];
			$amtordId = $row['order_id'];
			//$shipmentId = $row['shipment_id'];
			$payoutStatus = $row['shipmentpayout_status'];
			$payoutAdjust = $row['adjustment'];
			$total_amount1 = $row['subtotal'];
			$total_amount = $row['subtotal'];
			$vendorId = $row['udropship_vendor'];
			$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
			$closingbalance = $collectionVendor->getClosingBalance();
			$shipment = Mage::getModel('sales/order_shipment')->load($id);
			$vendors = Mage::helper('udropship')->getVendor($row['udropship_vendor']);
			$commentText = 'Refunded By system of amount '.$refundedAmount.'';
						
					
			 
					$url = 'https://api.secure.ebs.in/api/1_0';
					$myvar1 = 'statusByRef';
					$myvar2 = Mage::getSingleton('secureebs/config')->getAccountId();//'7509';
					$myvar3 = Mage::getSingleton('secureebs/config')->getSecretKey();//'694862a59816baf0c4b165baf4a6b3cf';exit;
					$myvar4 = $amtordId;
					$myvar5 = 'capture';
					$myvar10 = 'refund';
					$myvar11 = $refundedAmount;
					
					$fields = array(
								'Action' => urlencode($myvar1),
								'AccountID' => urlencode($myvar2),
								'SecretKey' => urlencode($myvar3),
								'RefNo' => urlencode($myvar4)
							);
					$fields_string = '';
					//url-ify the data for the POST
					foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
					rtrim($fields_string, '&');
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_VERBOSE, 1); 
					curl_setopt($ch,CURLOPT_URL, $url);
					curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
					curl_setopt( $ch, CURLOPT_POST, 1);
					curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt( $ch, CURLOPT_HEADER, 0);
					curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
					$response = curl_exec( $ch );
					//var_dump($response);exit;
					if(curl_errno($ch))
					{		
						print curl_error($ch);
					}
					else
					{
						$xml = @simplexml_load_string($response);
						$myvar6 = $xml['amount'];
						$myvar7 = $xml['paymentId'];
						$myvar8 = $xml['transactionType'];
						$myvar9 = $xml['isFlagged'];
		
						if($myvar8 == 'Authorized')
						{
								if($myvar9 == 'NO')
									{
										//echo "The Payment For this Order is Not flagged :-".$myvar4;
										Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is Not flagged :-".$myvar4));
									}
								else
									{
										//echo "The Payment For this Order is Flagged :-".$myvar4;
										Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Flagged :-".$myvar4));
									}
						}
						else
						{
						//echo "This Order Payment is not Authorized :-".$myvar4;
						Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("This Order Payment is not Authorized :-".$myvar4));
						}
					}
					curl_close($ch);
		
	
					if($myvar8 == 'Authorized')
						{
					
						$fields2 = array(
							'Action' => urlencode($myvar10),
							'AccountID' => urlencode($myvar2),
							'SecretKey' => urlencode($myvar3),
							'Amount' => urlencode($myvar11),
							'PaymentID' =>urlencode($myvar7)
						);
						$fields_string2 = '';
						//url-ify the data for the POST
						foreach($fields2 as $key=>$value) { $fields_string2 .= $key.'='.$value.'&'; }
						rtrim($fields_string2, '&');
						$ch2 = curl_init();
						curl_setopt($ch2, CURLOPT_VERBOSE, 1); 
						curl_setopt($ch2,CURLOPT_URL, $url);
						curl_setopt($ch2,CURLOPT_POSTFIELDS, $fields_string2);
						curl_setopt( $ch2, CURLOPT_POST, 1);
						curl_setopt( $ch2, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt( $ch2, CURLOPT_HEADER, 0);
						curl_setopt( $ch2, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
						$response2 = curl_exec( $ch2 );
						if(curl_errno($ch2))
							{		
							print curl_error($ch2);
							}
						else
							{
							$xml2 = @simplexml_load_string($response2);
						
						/*echo '<pre>';
						print_r($xml2);*/
								if($xml2['errorCode'] == '24')
								{
									Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('This payment is not captured'));
								}
								else
								{
									$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$myvar11."' WHERE `order_id` = '".$myvar4."'";
									$write->query($queryRefamt);
									//echo "The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11;
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11));			
							//query for change the status of refund intiated		
									$writeshipment = Mage::getSingleton('core/resource')->getConnection('core_write');	
									$queryShipmentUpdate = "update `sales_flat_shipment` set `udropship_status` = '12' WHERE `increment_id` = '".$shipmentId."'";
									$writeshipment->query($queryShipmentUpdate);
									//echo "The shipmentno.".$shipmentId." status has changes to refund initiated";
									Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The shipmentno.".$shipmentId." status has changes to refund initiated'));			
									$comment = Mage::getModel('sales/order_shipment_comment')
									->setParentId($id)
									->setComment($commentText)
									->setCreatedAt(NOW())
									->setUdropshipStatus(@$statuses[12])
									->save();
									$shipment->addComment($comment);		
				
				//for adjustment copied from shipment controller.php
									
				
				if($payoutStatus == 1)
					{
						$order = Mage::getModel('sales/order')->loadByIncrementId($row['order_id']);
						$_orderCurrencyCode = $order->getOrderCurrencyCode();
						if($_orderCurrencyCode != 'INR') 
						$total_amount = $row['subtotal']/1.5.'<br>';
						$commission_amount = $row['commission_percent'].'<br>';
						$itemised_total_shippingcost = $row['itemised_total_shippingcost'].'<br>';
						$base_shipping_amount = $row['base_shipping_amount'];
					
								if($vendors->getManageShipping() == "imanage")
								{
				
									$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
									$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$row['cod_fee'])*0.97 - $vendor_amount;
								}
								else
								   {
									$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
									$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
									}
	
							
						$payoutAdjust = $payoutAdjust-$vendor_amount;
						
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
						
						$closingbalance = $closingbalance - $vendor_amount;
						$queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
						$write->query($queyVendor);
						
						/*$comment = Mage::getModel('sales/order_shipment_comment')
									->setParentId($id)
									->setComment($commentText)
									->setCreatedAt(NOW())
									->setUdropshipStatus(@$statuses[12])
									->save();
						$shipment->addComment($comment);*/
					//echo '<pre>';
					//print_r($comment->getData());
					//$shipment->addComment($comment);
					//print_r($shipment->getData());exit;
					//print_r($shipment);
					
					}
					else
						{
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
							
						}
			
					}
				}
				curl_close($ch2);
				
					}
				else
					{
					echo "The Payment For this Order is not ready to Refunded :-".$myvar4;
					//Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("The Payment For this Order is not ready to Refunded :-".$myvar4));
					}
			}
		
	}
						}
					}
					$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully Refunded', count($shipmentpayoutIds))
					);
				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}
		
		
		public function codreportAction()
		{
			
		$selected_date_val = $this->getRequest()->getParam('selected_date'); //Get Selected date
		$selected_date_val1 = new DateTime($selected_date_val); //Convert to date time object
		$datetemp =  date('Y-m-d');//date_create("2016-01-30"); //Create Today Temp date
		$datetemp = new DateTime($datetemp);
		$diff = date_diff($selected_date_val1,$datetemp ); //Calulate date difference
		$date_diff  = $diff->days;
		$finaldate = '';

		if($date_diff >=30){
			//echo ">30";
			$finaldate = date_format($selected_date_val1,'Y-m-d') ;
		}else {
			//echo "<30";
			$finaldate = date_sub($datetemp, date_interval_create_from_date_string('30 days')); //30 Days difference
			$finaldate = date_format($finaldate,'Y-m-d');
		}
		//echo( "Difference :".$date_diff . " Finale Date: ". $finaldate); exit;
		

		$dateOpen = date('Ymd',strtotime($selected_date_val));
 
    	$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
      	$shipmentpayout_report1->getSelect()
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.shipmentpayout_status=0 AND a.udropship_status IN (7) AND `sales_flat_order_payment`.method = "cashondelivery" AND main_table.citibank_utr != "" and a.updated_at <= \''. $finaldate.'\'');      	
      /*	echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
      	echo ($selected_date_val);
		exit();*/
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "CODReport"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("Debit Account Number","Value Date","Customer Reference No","Beneficiary Name","Payment Type","Bene Account Number","Bank Code","Account type","Amount","Payment Details 1","Payment Details 2","Payment Details 3","Payment Details 4","Payable Location Code *","Payable Location Name *","Print Location Code *","Print Location Name *","Beneficiary Address 1","Beneficiary Address 2","Beneficiary Address 3","Beneficiary Address 4","Delivery Method","Cheque Number","Bene E-mail ID","Instrument Detail 1","Instrument Detail 2","Craftsvilla Commission");
    	
		$numfields = sizeof($fieldlist);
		$i = 1;
	
		// *********************   NOW START BUILDING THE CSV
	
		// Create the column headers
	
		for($k =0; $k < $numfields;  $k++) { 
			$output .= $fieldlist[$k];
			if ($k < ($numfields-1)) $output .= ", ";
		}
		$output .= "\n";
		
		/*echo "<pre>";
		print_r($shipmentpayout_report1_arr);
		exit();*/
		
    	foreach($shipmentpayout_report1_arr as $shipmentpayout_report1_val)
	    {
			$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
			//var_dump($vendors);exit;
	    	//Checking Valid Account number
	    	//$accountnumber = $vendors->getBankAcNumber();

			//$bankaccountno = $this->checkbanknumber($accountnumber);
			//if($bankaccountno === false){
			//	continue;
			//}

			//if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($vendors->getMerchantIdCity() != ''))
			if($shipmentpayout_report1_val['udropship_vendor'] != '')
    		{
		    	unset($total_amount);
		    	unset($commission_amount);
		    	unset($vendor_amount);
		    	unset($kribha_amount);
		    	unset($gen_random_number);
		    	unset($itemised_total_shippingcost);
		    
				$total_amount1 = $shipmentpayout_report1_val['subtotal'];
				$total_amount = $shipmentpayout_report1_val['subtotal'];
				$logisticamount = $shipmentpayout_report1_val['intshipingcost'];
				//$logisticamount = 125;
				$_liveDate = "2012-08-21 00:00:00";
		    	$order = Mage::getModel('sales/order')->loadByIncrementId($shipmentpayout_report1_val['order_id']);
				
				// Below Two lines added By Dileswar for Adding Discount coupon on dated 25-07-2013
				$disCouponcode = '';
				$discountAmountCoupon = 0;
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	//$commission_amount = $shipmentpayout_report1_val['commission_percent'];
				$commission_amount = 20;
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
		    	
				// Added By Dileswar On dated 25-07-2013 For get the Value of coupon id & vendorid
				$couponCodeId = Mage::getModel('salesrule/coupon')->load($order->getCouponCode(), 'code');
				$_resultCoupon = Mage::getModel('salesrule/rule')->load($couponCodeId->getRuleId());
				$couponVendorId = $_resultCoupon->getVendorid();
				if($couponVendorId == $vendorId)
				{
					$discountAmountCoupon = $order->getBaseDiscountAmount();
					$disCouponcode = $order->getCouponCode();
				}
				
				//$gen_random_number = "K".$this->gen_rand();
                       $vendor_amount = (($total_amount+$itemised_total_shippingcost+$discountAmountCoupon)*(1-($commission_amount/100)*(1+0.1450)));
						
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						
			    	
					$vendor_amount = $vendor_amount - ($logisticamount*(1+0.1450));
				
		    	$kribha_amount = ((($total_amount1+$base_shipping_amount+$discountAmountCoupon)) - $vendor_amount);
				
				//Below lines for to update the value in shipmentpayout table ...
					
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');
					if($disCouponcode){
					$queryUpdateDiscount = "update shipmentpayout set `discount` ='".$discountAmountCoupon."',`couponcode` = '".$disCouponcode."' WHERE `shipment_id` = '".$shipmentpayout_report1_val['shipment_id']."'";
					$write->query($queryUpdateDiscount);
					}
				
					$utr = $shipmentpayout_report1_val['citibank_utr'];
					$neft = 'EFT';
						if(($vendor_amount+$closingbalance) <= 0)
							{
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){$vendor_amount = 0;}
								
								else{	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									//$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									//$queryUpdate = "update shipmentpayout set `adjustment` ='".$adjustmentAmount."',`type` = '".$neft."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$queryUpdate = "update shipmentpayout set `shipmentpayout_update_time` = NOW(),`payment_amount`= '".$adjustmentAmount."',`adjustment` ='".$adjustmentAmount."',`shipmentpayout_status` = '1',`type` = '".$neft."',`comment` = 'Adjusted Against Refund By System' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	
								
								}
							}	
							
				for($m =0; $m < sizeof($fieldlist); $m++) {
					$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "Debit Account Number")
		    		{
		    			//$output .= '710607028';
		    			$output .= '712097019';
		    		}
		    		
		    		if($fieldvalue == "Value Date")
		    		{
		    			$output .= $dateOpen;
		    		}
		    		
		    		if($fieldvalue == "Customer Reference No")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Beneficiary Name")
		    		{
		    			$output .= $vendors->getCheckPayTo();
		    		}
		    			
		    		if($fieldvalue == "Payment Type")
		    		{
		    			$output .= $neft;
		    		}
		    			
		    		if($fieldvalue == "Bene Account Number")
		    		{
		    			$output .= "'".$vendors->getBankAcNumber();
		    		}
		    			
		    		if($fieldvalue == "Bank Code")
		    		{
		    			$output .= strtoupper($vendors->getBankIfscCode()); 
		    		}
		    			
		    		if($fieldvalue == "Account type")
		    		{
		    			$output .= '2';
		    		}
		    			
		    		if($fieldvalue == "Amount")
		    		{
		    			$output .= str_replace(',','',number_format($vendor_amount,2));
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',substr(strtoupper($vendors->getVendorName()),0,30)));		    		}
		    		
		    		if($fieldvalue == "Payment Details 3")
		    		{
		    			$output .= "";
		    		}
		    			
		    		if($fieldvalue == "Payment Details 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Payable Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Code *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Print Location Name *")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 3")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Beneficiary Address 4")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Delivery Method")
		    		{
		    				$output .= "";
		    		}
					if($fieldvalue == "Cheque Number")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Bene E-mail ID")
		    		{
		    			$output .= $vendors->getEmail();
		    		}
					if($fieldvalue == "Instrument Detail 1")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Instrument Detail 2")
		    		{
		    			$output .= "";
		    		}
					if($fieldvalue == "Craftsvilla Commission")
		    		{
		    			$output .= $kribha_amount;
		    		}
					
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    	
				}
		    	$output .= "\n";
				
    		}
	    }
		
    	// Send the CSV file to the browser for download
	
		//header("Content-type: text/x-csv");
		//header("Content-Disposition: attachment; filename=$filename.csv");
		//echo $output;
		$filePathOfCsv = Mage::getBaseDir('media').DS.'payoutreports'.DS.$filename.'.csv';
		$fp=fopen($filePathOfCsv,'w');
		fputs($fp, $output);
    	fclose($fp);
		
		
    $i++;
Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Cod Download Done: Please Download from here:'));
$this->_redirect('*/*/index');
	}
	
	public function autorefundpayuAction()
		{
			$shipmentpayoutIds = $this->getRequest()->getParam('shipmentpayout');
			if(!is_array($shipmentpayoutIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			} else {
				try {
					foreach ($shipmentpayoutIds as $shipmentpayoutId) 
					{
						if($shipmentpayoutId)
						{
							$shipmentpayoutdetails = Mage::getSingleton('shipmentpayout/shipmentpayout')->load($shipmentpayoutId);
							$shipmentId = $shipmentpayoutdetails->getShipmentId();                    
							$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
					$shipmentpayout_report1->getSelect()
							->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('entity_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
							->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
							->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
							->where('sales_flat_order_payment.method = "payucheckout_shared"')// a.udropship_status IN (1,15,17)
							->where("main_table.shipment_id = '".$shipmentId."'");
					//echo $shipmentpayout_report1->getSelect()->__toString();
				//exit();
			
		  	$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();			
			$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
			
			foreach($shipmentpayout_report1_arr as $row)
			{
				if($row['refundtodo'] != "")
					{
					$id = $row['entity_id'];
					$refundedAmount = $row['refundtodo'];
					$orderid = $row['order_id'];
					$shipmentId = $row['shipment_id'];
					$payoutStatus = $row['shipmentpayout_status'];
					$payoutAdjust = $row['adjustment'];
					$total_amount1 = $row['subtotal'];
					$total_amount = $row['subtotal'];
					$vendorId = $row['udropship_vendor'];
					$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
					$closingbalance = $collectionVendor->getClosingBalance();
					$shipment = Mage::getModel('sales/order_shipment')->load($id);
					$vendors = Mage::helper('udropship')->getVendor($row['udropship_vendor']);
					$commentText = 'Refunded By system of amount '.$refundedAmount.'';
					
					$mihpayuid = $this->getmihpayuid($orderid);
					//$key = "C0Dr8m";
					//$salt = "3sf0jURk";
					$key = "ZwzD3B";
					$salt = "8HLsQdw4";
					$command = "cancel_refund_transaction";
					$hash_str = $key  . '|' . $command . '|' . $mihpayuid . '|' . $salt ;
					$hash = strtolower(hash('sha512', $hash_str));
					$r = array('key' => $key , 'hash' =>$hash , 'var1' => $mihpayuid , 'command' => $command, 'var2' => $orderid, 'var3' => $refundedAmount);
					
					$qs= http_build_query($r);
					$wsUrl = "https://info.payu.in/merchant/postservice.php";
					$c = curl_init();
					curl_setopt($c, CURLOPT_URL, $wsUrl);
					curl_setopt($c, CURLOPT_POST, 1);
					curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
					curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
					$o = curl_exec($c);
						if (curl_errno($c)) {
						$sad = curl_error($c);
						throw new Exception($sad);
						}
					   $valueSerialized = @unserialize($o);
						if($o === 'b:0;' || $valueSerialized !== false) {
						print_r($valueSerialized);
						}
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$refundedAmount."' WHERE `order_id` = '".$orderid."'";
					$write->query($queryRefamt);
					//echo "The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11;
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$orderid."Amount".$refundedAmount));			
					//query for change the status of refund intiated		
					$writeshipment = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$queryShipmentUpdate = "update `sales_flat_shipment` set `udropship_status` = '12' WHERE `increment_id` = '".$shipmentId."'";
					$writeshipment->query($queryShipmentUpdate);
					//echo "The shipmentno.".$shipmentId." status has changes to refund initiated";
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The shipmentno.".$shipmentId." status has changes to refund initiated'));			
					$comment = Mage::getModel('sales/order_shipment_comment')
					->setParentId($id)
					->setComment($commentText)
					->setCreatedAt(NOW())
					->setUdropshipStatus(@$statuses[12])
					->save();
					$shipment->addComment($comment);		
					
					//for adjustment copied from shipment controller.php
					if($payoutStatus == 1)
						{
						$order = Mage::getModel('sales/order')->loadByIncrementId($row['order_id']);
						$_orderCurrencyCode = $order->getOrderCurrencyCode();
						if($_orderCurrencyCode != 'INR') 
						$total_amount = $row['subtotal']/1.5.'<br>';
						$commission_amount = $row['commission_percent'].'<br>';
						$itemised_total_shippingcost = $row['itemised_total_shippingcost'].'<br>';
						$base_shipping_amount = $row['base_shipping_amount'];
						
						if($vendors->getManageShipping() == "imanage")
							{
							
							$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
							$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$row['cod_fee'])*0.97 - $vendor_amount;
							}
						else
							{
							$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
							$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
							}
						
						
						$payoutAdjust = $payoutAdjust-$vendor_amount;
						
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
						
						$closingbalance = $closingbalance - $vendor_amount;
						$queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
						$write->query($queyVendor);
						
						}
					else
					{
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipmentId."'";
					$write->query($queryUpdate);
					
					}
					
					curl_close($c);
					
					}
				else
					{
					echo "The Payment For this Order is not ready to Refunded :-".$orderid;
					
					}
						}
				}
						
					}
					$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully Refunded', count($shipmentpayoutIds))
					);
				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}
		
	public function getmihpayuid($transactionid)
{
	
	               //$transactionid = "100128123";
	               // $key = "C0Dr8m";
					//$salt = "3sf0jURk";
					$key = "ZwzD3B";
					$salt = "8HLsQdw4";
					$command = "verify_payment";
					$hash_str = $key  . '|' . $command . '|' . $transactionid . '|' . $salt ;
					$hash = strtolower(hash('sha512', $hash_str));
				   // $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command, 'var2' => $salt, 'var3' => $amount);
				   $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command);
				   
					$qs= http_build_query($r);
					$wsUrl = "https://info.payu.in/merchant/postservice.php";
				//	$wsUrl = "https://info.payu.in/merchant/postservice.php";
					$c = curl_init();
					curl_setopt($c, CURLOPT_URL, $wsUrl);
					curl_setopt($c, CURLOPT_POST, 1);
					curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
					curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
					curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
					$o = curl_exec($c);
					if (curl_errno($c)) {
				   $sad = curl_error($c);
				   throw new Exception($sad);
				}
			curl_close($c);
		
			$valueSerialized = @unserialize($o);
			if($o === 'b:0;' || $valueSerialized !== false) {
                return $valueSerialized['transaction_details'][$transactionid]['mihpayid'];
			}
			return NULL;
			
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

public function autorefundcodAction()
		{
			$shipmentpayoutIds = $this->getRequest()->getParam('shipmentpayout');
			if(!is_array($shipmentpayoutIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
			} else {
				try {
					foreach ($shipmentpayoutIds as $shipmentpayoutId) 
					{
						if($shipmentpayoutId)
						{
							$shipmentpayoutdetails = Mage::getSingleton('shipmentpayout/shipmentpayout')->load($shipmentpayoutId);
							$shipmentId = $shipmentpayoutdetails->getShipmentId();                    
							$shipmentpayout_report1 = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection();
					$shipmentpayout_report1->getSelect()
							->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('entity_id','udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
							->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
							->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
							->where('sales_flat_order_payment.method = "cashondelivery"')// a.udropship_status IN (1,15,17)
							->where("main_table.shipment_id = '".$shipmentId."'");
					//echo $shipmentpayout_report1->getSelect()->__toString();
				//exit();
			
		  	$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();			
			$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
			
			foreach($shipmentpayout_report1_arr as $row)
			{
				if($row['refundtodo'] != "")
					{
					$id = $row['entity_id'];
					$refundedAmount = $row['refundtodo'];
					$orderid = $row['order_id'];
					$shipmentId = $row['shipment_id'];
					$payoutStatus = $row['shipmentpayout_status'];
					$payoutAdjust = $row['adjustment'];
					$total_amount1 = $row['subtotal'];
					$total_amount = $row['subtotal'];
					$vendorId = $row['udropship_vendor'];
					$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
					$closingbalance = $collectionVendor->getClosingBalance();
					$shipment = Mage::getModel('sales/order_shipment')->load($id);
					$vendors = Mage::helper('udropship')->getVendor($row['udropship_vendor']);
					$commentText = 'Refunded By system of amount of Rs. '.$refundedAmount.'';
					
					
					$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
					$queryRefamt = "update shipmentpayout set `refunded_amount` = '".$refundedAmount."' WHERE `shipment_id` = '".$shipmentId."'";
					$write->query($queryRefamt);
					//echo "The Payment For this Order is Refunded :-".$myvar4."Amount".$myvar11;
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("The Payment For this Order is Refunded :-".$shipmentId."Amount".$refundedAmount));			
					//query for change the status of refund intiated		
					$writeshipment = Mage::getSingleton('core/resource')->getConnection('core_write');	
					$queryShipmentUpdate = "update `sales_flat_shipment` set `udropship_status` = '12' WHERE `increment_id` = '".$shipmentId."'";
					$writeshipment->query($queryShipmentUpdate);
					//echo "The shipmentno.".$shipmentId." status has changes to refund initiated";
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The shipmentno."'.$shipmentId.'" status has changes to refund initiated'));			
					$comment = Mage::getModel('sales/order_shipment_comment')
					->setParentId($id)
					->setComment($commentText)
					->setCreatedAt(NOW())
					->setUdropshipStatus(@$statuses[12])
					->save();
					$shipment->addComment($comment);		
					
					//for adjustment copied from shipment controller.php
					if($payoutStatus == 1)
						{
						$order = Mage::getModel('sales/order')->loadByIncrementId($row['order_id']);
						$_orderCurrencyCode = $order->getOrderCurrencyCode();
						if($_orderCurrencyCode != 'INR') 
						$total_amount = $row['subtotal']/1.5.'<br>';
						$commission_amount = $row['commission_percent'].'<br>';
						$itemised_total_shippingcost = $row['itemised_total_shippingcost'].'<br>';
						$base_shipping_amount = $row['base_shipping_amount'];
						
						if($vendors->getManageShipping() == "imanage")
							{
							
							$vendor_amount = ($total_amount*(1-($commission_amount/100)*(1+0.1236)));
							$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$row['cod_fee'])*0.97 - $vendor_amount;
							}
						else
							{
							$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
							$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
							}
						
						
						$payoutAdjust = $payoutAdjust-$vendor_amount;
						
						//$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set adjustment='".$payoutAdjust."' , `comment` = 'Adjustment Against Refund Paid'  WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
						
						$closingbalance = $closingbalance - $vendor_amount;
						$queyVendor = "update `udropship_vendor` set closing_balance = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
						$write->query($queyVendor);
						
						}
						else
						{
						$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
						$queryUpdate = "update shipmentpayout set shipmentpayout_status= '2' WHERE shipment_id = '".$shipmentId."'";
						$write->query($queryUpdate);
					
						}
					
					}
				else
					{
					echo "The Payment For this Order is not ready to Refunded :-".$shipmentId;
					
					}
						}
				}
						
					}
					$this->_getSession()->addSuccess(
						$this->__('Total of %d record(s) were successfully Refunded', count($shipmentpayoutIds))
					);
				} catch (Exception $e) {
					$this->_getSession()->addError($e->getMessage());
				}
			}
			$this->_redirect('*/*/index');
		}

public function getMerchantIdCv($vendorIdc)
	{
	$readVd = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_read');
	$MerchantIdQuery = "SELECT `merchant_id_city` FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorIdc."'";
	$resultMerchant = $readVd->query($MerchantIdQuery)->fetch();
	$readVd->closeConnection();	
	return $resultMerchant['merchant_id_city']; 
	}

public function checkbanknumber($bannknumber){

			if (!preg_match('/^([A-Za-z0-9]+)$/', $bannknumber)) {
            		return false;
            }
            else{
            	return true; 
           	}    
	}
}
