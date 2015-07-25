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
      			->join(array('a'=>'sales_flat_shipment'), 'a.increment_id=main_table.shipment_id', array('udropship_vendor', 'subtotal'=>'base_total_value', 'commission_percent'=>'commission_percent', 'itemised_total_shippingcost'=>'itemised_total_shippingcost','cod_fee'=>'cod_fee','base_shipping_amount'=>'base_shipping_amount'))
      			->join(array('b'=>'sales_flat_shipment_grid'), 'b.increment_id=main_table.shipment_id', array('order_created_at'))
      			->joinLeft('sales_flat_order_payment', 'b.order_id = sales_flat_order_payment.parent_id','method')
				->where('main_table.type = "Adjusted Against Refund"');
				//->where('main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.citibank_utr != "" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND sales_flat_order_payment.method IN ("secureebs_standard","purchaseorder","ccavenue_standard")');// a.udropship_status IN (1,15,17)
      	
      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();*/
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "Craftsvilla"."_".$selected_date_val;
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
			$vendors = Mage::helper('udropship')->getVendor($shipmentpayout_report1_val['udropship_vendor']);
	    	if(($shipmentpayout_report1_val['udropship_vendor'] != '') && ($vendors->getMerchantIdCity() != ''))
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
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	$commission_amount = $shipmentpayout_report1_val['commission_percent'];
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
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
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'])*0.97 - $vendor_amount;
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
			    	}
					
		    	}
				
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
					
					$utr = $shipmentpayout_report1_val['citibank_utr'];
					$neft = 'NEFT';
						/*if(($vendor_amount+$closingbalance) <= 0)
							{
								if($shipmentpayout_report1_val['type'] == 'Adjusted Against Refund'){$vendor_amount = 0;}
								
								else{	
									$adjustmentAmount = $adjustmentAmount + $vendor_amount; 
									$closingbalance = $closingbalance + $vendor_amount;
									$vendor_amount = 0;
									$neft = 'Adjusted Against Refund';
									$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									$queryUpdate = "update shipmentpayout set `adjustment` ='".$adjustmentAmount."',`type` = '".$neft."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
									$write->query($queryUpdate);
									$queyVendor = "update `udropship_vendor` set `closing_balance` = '".$closingbalance."' WHERE `vendor_id` = '".$vendorId."'";
									$write->query($queyVendor);	
								
								}
							}	*/
							
							
						
		    	for($m =0; $m < sizeof($fieldlist); $m++) {
		    		
		    		$fieldvalue = $fieldlist[$m];
		    		if($fieldvalue == "ACCOUNT_NO")
		    		{
		    			$output .= '710607788';
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
		    			$output .= $vendors->getMerchantIdCity();
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
		    			$output .= $vendor_amount;
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
			    		$output .= '710607788';
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['shipment_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_created_at'];
			    		$output .= ",";
			    		$output .= 'NEFTKRIBHA001';
			    		$output .= ",";
			    		$output .= 'Kribha Handicrafts Pvt Ltd';
			    		$output .= ",";
			    		$output .= $neft;
			    		$output .= ",";
			    		$output .= '0710607028';
			    		$output .= ",";
			    		$output .= 'CITI0100000';
			    		$output .= ",";
			    		$output .= $gen_random_number;
			    		$output .= ",";
			    		$output .= $kribha_amount;
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
    	
		$filename = "Craftsvilla"."_".date("Y-m-d");
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
				->where('main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND `sales_flat_order_payment`.method IN ("cashondelivery","gharpay_standard","paypal_standard")');// a.udropship_status IN (1,15,17)
				//->where('main_table.shipmentpayout_update_time <= "'.$selected_date_val.' 23:59:59" AND main_table.shipmentpayout_status=0 AND a.udropship_status = 1 AND `sales_flat_order_payment`.method IN ("cashondelivery","gharpay_standard","paypal_standard")');// a.udropship_status IN (1,15,17)
      	
      	/*echo "Query:".$shipmentpayout_report1->getSelect()->__toString();
		exit();*/
      			
      	$shipmentpayout_report1_arr = $shipmentpayout_report1->getData();
    	$filename = "NONNodalReport"."_".$selected_date_val;
		$output = "";
	
		$fieldlist = array("Debit Account Number","Value Date","Customer Reference No","Beneficiary Name","Payment Type","Bene Account Number","Bank Code","Account type","Amount","Payment Details 1","Payment Details 2","Payment Details 3","Payment Details 4","Payable Location Code *","Payable Location Name *","Print Location Code *","Print Location Name *","Beneficiary Address 1","Beneficiary Address 2","Beneficiary Address 3","Beneficiary Address 4","Delivery Method","Cheque Number","Bene E-mail ID","Instrument Detail 1","Instrument Detail 2");
    	
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
				$_orderCurrencyCode = $order->getOrderCurrencyCode();
				if(($_orderCurrencyCode != 'INR') && (strtotime($shipmentpayout_report1_val['order_created_at']) >= strtotime($_liveDate)))
					$total_amount = $shipmentpayout_report1_val['subtotal']/1.5;

		    	$commission_amount = $shipmentpayout_report1_val['commission_percent'];
		    	$itemised_total_shippingcost = $shipmentpayout_report1_val['itemised_total_shippingcost'];
		    	$base_shipping_amount = $shipmentpayout_report1_val['base_shipping_amount'];
				$vendorId = $shipmentpayout_report1_val['udropship_vendor'];
				$adjustmentAmount = $shipmentpayout_report1_val['adjustment'];
				$shipmentpayoutStatus = $shipmentpayout_report1_val['shipmentpayout_status'];
				//Below line is for get closingBalance
				$collectionVendor = Mage::getModel('udropship/vendor')->load($vendorId);
				$closingbalance = $collectionVendor->getClosingBalance();
		    	
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
						$kribha_amount = ($total_amount1+$itemised_total_shippingcost+$shipmentpayout_report1_val['cod_fee'])*0.97 - $vendor_amount;
			    	}
			    	else {
			    		$vendor_amount = (($total_amount+$itemised_total_shippingcost)*(1-($commission_amount/100)*(1+0.1236)));
						//$kribha_amount = (($total_amount1+$itemised_total_shippingcost) - $vendor_amount);
						//change to accomodate 3% Payment gateway charges on dated 20-12-12
						
					// Below line commented by dileswar on dated 18-02-2013 from $itemised_total_shippingcost To $base_shipping_amount***/////////////	
						//$kribha_amount = ((($total_amount1+$itemised_total_shippingcost)*0.97) - $vendor_amount);
						$kribha_amount = ((($total_amount1+$base_shipping_amount)*0.97) - $vendor_amount);
			    	}
					
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
									$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
									$queryUpdate = "update shipmentpayout set `adjustment` ='".$adjustmentAmount."',`type` = '".$neft."' WHERE shipment_id = '".$shipmentpayout_report1_val['shipment_id']."'";
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
		    			$output .= $i;
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
		    			$output .= "'".strval($vendors->getBankAcNumber());
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
		    			$output .= $vendor_amount;
		    		}
		    			
		    		if($fieldvalue == "Payment Details 1")
		    		{
		    			$output .= $shipmentpayout_report1_val['shipment_id'];
		    		}
		    		
		    		if($fieldvalue == "Payment Details 2")
		    		{
		    			$output .= $vendors->getVendorName();		    		}
		    		
		    		if($fieldvalue == "Payment Details 3")
		    		{
		    			$output .= "Pay Det 3";;
		    		}
		    			
		    		if($fieldvalue == "Payment Details 4")
		    		{
		    			$output .= "Pay Det 4";;
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
		    			
		    		if ($m < ($numfields-1))
		    		{
		    			$output .= ",";
		    		}
		    		/*else {
		    			$output .= "\n";
			    		$output .= '710607788';
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['shipment_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_id'];
			    		$output .= ",";
			    		$output .= $shipmentpayout_report1_val['order_created_at'];
			    		$output .= ",";
			    		$output .= 'NEFTKRIBHA001';
			    		$output .= ",";
			    		$output .= 'Kribha Handicrafts Pvt Ltd';
			    		$output .= ",";
			    		$output .= 'Neft';
			    		$output .= ",";
			    		$output .= '0710607028';
			    		$output .= ",";
			    		$output .= 'CITI0100000';
			    		$output .= ",";
			    		$output .= $gen_random_number;
			    		$output .= ",";
			    		$output .= $kribha_amount;
			    		$output .= ",";
			    		$output .= "CORRESPONDING COMMISSION";
			    		$output .= ",";
			    		$output .= $utr;
			    		$output .= ",";
		    		}*/
		    	
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
}
