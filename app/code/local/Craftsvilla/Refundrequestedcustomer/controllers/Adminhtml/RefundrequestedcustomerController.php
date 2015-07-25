<?php

class Craftsvilla_Refundrequestedcustomer_Adminhtml_RefundrequestedcustomerController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('refundrequestedcustomer/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
        $this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_grid')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
//print_r($model); exit;
			Mage::register('refundrequestedcustomer_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('refundrequestedcustomer/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_edit'))
				->_addLeft($this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('refundrequestedcustomer')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
					/* Starting upload */	
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
			}
	  			
	  			
			$model = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer');		
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
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('refundrequestedcustomer')->__('Item was successfully saved'));
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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('refundrequestedcustomer')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer');
				 
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
        $refundrequestedcustomerIds = $this->getRequest()->getParam('refundrequestedcustomer');
        if(!is_array($refundrequestedcustomerIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($refundrequestedcustomerIds as $refundrequestedcustomerId) {
                    $refundrequestedcustomer = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer')->load($refundrequestedcustomerId);
                    $refundrequestedcustomer->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($refundrequestedcustomerIds)
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
      	$refundrequestedcustomerIds = $this->getRequest()->getParam('refundrequestedcustomer');
        $refundreqcustId =  $refundrequestedcustomerIds[0]; 
        if($refundreqcustId == '' ) {
           
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
            
        } else {
        
            try {
                foreach ($refundrequestedcustomerIds as $refundrequestedcustomerId) 
                {
                
               	    $refundStatus = $this->getRequest()->getParam('status'); 
               	    $read = Mage::getSingleton('core/resource')->getConnection('refundrequestedcustomer_read');
        			$write = Mage::getSingleton('core/resource')->getConnection('refundrequestedcustomer_write');
		    		$remarks = mysql_escape_string($this->getRequest()->getParam('remarks'));  
		    		
               		$model = Mage::getModel('refundrequestedcustomer/refundrequestedcustomer')->load($refundrequestedcustomerId);
		         	//print_r($model); exit;
		         	$shipmetId = $model['shipment_id'];
        			$modelstatus = $model['refund_status'];  
        			$shipment = Mage::getModel('sales/order_shipment');
        			$shipmentData = $shipment->loadByIncrementId($shipmetId);
        			//print_r($shipmentData); exit;
        			$shipmentStatus = $shipmentData->getUdropshipStatus();  
        			$entityId = $shipmentData->getEntityId(); 
        			$baseTotalValue = $shipmentData->getBaseTotalValue(); 
        			$itemisedtotalshippingcost = $shipmentData->getItemisedTotalShippingcost();  
        			$summaryPrice = $baseTotalValue + $itemisedShippingValue;
        			$orderId = $shipmentData->getOrderId(); 
        			$customerData = Mage::getModel('sales/order')->load($orderId);
        			//echo '<pre>'; print_r($customerData); exit;
        			$customeremailId = $customerData->getCustomerEmail();
					$firstname = $customerData->getCustomerFirstname();
					$shipmentVendor = $shipmentData->getUdropshipVendor();   
					$vendorData = Mage::getModel('udropship/vendor')->load($shipmentVendor);
					//echo '<pre>'; print_r($vendorData); exit;
					$vendorEmail = $vendorData->getEmail();  
					$vendorName = $vendorData->getVendorName();
					
					
					if($refundStatus == 1) {
        				if($shipmentStatus == 23) {
        					$this->_getSession()->addError("Refund Cannot Be Requested for Shipment: ".$shipmetId." as refund already Approved");
				        $this->_redirect('*/*/');
						return;
        				}
        			}
        			if($refundStatus == 1 && (!empty($remarks))) {
					$this->_getSession()->addError("Remarks should be added only in case of Reject");
				    $this->_redirect('*/*/');
					return;
					
					}
					
					if($refundStatus == 3 && (empty($remarks))) {
					$this->_getSession()->addError("Please enter remarks for rejecting refund for Shipment: ".$shipmetId);
				    $this->_redirect('*/*/');
					return;
					
					}
					
        			if($refundStatus == 3 && (!empty($remarks))) {
        			$_items = $shipment->getAllItems();
					//echo '<pre>'; print_r($_items); exit;
				$customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
			try{				
			$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
			}
			catch(Exception $e){}
			$customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipmetId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
				 $customerShipmentItemHtml .= "</table>";
        			
        			$remarksInsertQuery = "UPDATE `refundrequestedcustomer` SET `remarks`='".$remarks."' WHERE `shipment_id`='".$shipmetId."'";
        			$remarksInsertQueryRes = $write->query($remarksInsertQuery);
        			
        			Mage::helper('udropship')->addShipmentComment($shipmentData, ('Refund rejected for Shipment: '.$shipmetId.' and the remarks are : '.$remarks));
   		     		$shipmentData->save();
        			
        			
        					$templateId ='remarksreject_email_to_customer';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$vars = Array( 'shipmentID'=>$shipmetId,
										   'custtomerName'=>$firstname,
										   'customerShipmentItemHtml'=>$customerShipmentItemHtml,
										   'remarks'=>$remarks,
										 );

							$_email->setReplyTo($sender['email'])
								   ->sendTransactional($templateId, $sender,$customeremailId,'', $vars);
								
							echo "email sent successfully to your email";
							
							$templateIdVendor ='remarksreject_email_to_vendor';
							$senderVendor = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$varsVendor = Array( 'shipmentID'=>$shipmetId,
										   		 'vendorName'=>$vendorName,
										   		 'customerShipmentItemHtml'=>$customerShipmentItemHtml,
										   		 'remarks'=>$remarks,
										   	   );

							$_email->setReplyTo($senderVendor['email'])
								->sendTransactional($templateIdVendor, $senderVendor,$vendorEmail,'', $varsVendor);
								
							echo "email sent successfully to your email";
							
							
					}
					if($refundStatus == 2 && (!empty($remarks))) {
					$this->_getSession()->addError("Remarks should be added only in case of Reject");
				        $this->_redirect('*/*/');
						return;
					
					}
        			if($refundStatus == 2) { 
        			$accountNumber = mysql_escape_string($model['account_number']);
        			$ifsccode = mysql_escape_string($model['ifsccode']);
        			$trackingNumber = mysql_escape_string($model['trackingcode']);
        			$courierName = mysql_escape_string($model['couriername']);
        			$accHolderName = mysql_escape_string($model['name_on_account']);
        			
        			
        			
        			$paymentamount = Mage::helper('udropship')->getrefundCv($entityId);	
        			$orderid = $customerData->getEntityId();
					$baseDiscountAmount = $customerData->getBaseDiscountAmount();
					$lastname = $customerData->getCustomerLastname();
					$fullname = $firstname.$lastname;
					$street = $customerData->getShippingAddress()->getStreet();
					$street1 = $street[0].$street[1];		
		 			$city = $customerData->getShippingAddress()->getCity();
		 			$postcode = $customerData->getShippingAddress()->getPostcode();
					$countrycode = $customerData->getShippingAddress()->getCountryId();

					$customerName = mysql_escape_string($firstname.' '.$lastname); 
					
					$customerShipaddressHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$fullname."<br/>".$street1."<br/>".$city."<br/>".$postcode."<br/>".$countrycode."</td></tr><table>";
					
					$_items = $shipment->getAllItems();
					//echo '<pre>'; print_r($_items); exit;
				$customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
			try{				
			$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
			}
			catch(Exception $e){}
			$customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipmetId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		 $customerShipmentItemHtml .= "</table>"; 
		
		
		$summaryprice = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;text-align: right;background:#F2F2F2;color:#CE3D49;'>Total Value : Rs.".$baseTotalValue."<br/>Shipping & Handling:Rs. ".$itemisedtotalshippingcost."<br/></td></tr><table>";
		
					$returnTrackDetails = '';
   		     		$returnTrackDetails .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentID</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Return Tracking Number</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Courier Name</td></tr>";
   		     		$returnTrackDetails .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>$shipmetId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>$trackingNumber</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>$courierName</td></tr>";
					$returnTrackDetails .= "</table>";
   		     		//print_r($returnTrackDetails); exit;
		
        			$user = Mage::getSingleton('admin/session'); 
					$userFirstname = $user->getUser()->getFirstname();
					
					
					$duplicodRefundShipmentQuery = "SELECT * FROM `codrefundshipmentgrid` WHERE `shipment_id` = '".$shipmetId."'";
        			$duplicodRefundShipmentQueryRes = $read->query($duplicodRefundShipmentQuery)->fetch();
   		     		if($duplicodRefundShipmentQueryRes) {
   		     		$model->setRefundStatus($modelstatus);
   		     		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('refundrequestedcustomer')->__('Already refund Approved for Shipment:'.$shipmetId));
					$this->_redirect('*/*/');
					return;
		    
   		     		} else {	
   		     		$codRefundShipmentQuery = "INSERT INTO `codrefundshipmentgrid`(`shipment_id`, `order_id`, `cust_name`, `accountno`, `ifsccode`, `paymentamount`, `created_time`, `update_time`) VALUES ($shipmetId,$orderId,'$accHolderName',$accountNumber,'$ifsccode',$paymentamount,Now(),Now())"; 
   		     		$write->query($codRefundShipmentQuery);
   		     		
   		     		$customerReturnQuery = "INSERT INTO `customerreturn`(`shipment_id`, `trackingcode`, `couriername`, `status`, `created_at`, `update_at`) VALUES ($shipmetId,'$trackingNumber','$courierName',$shipmentStatus,Now(),Now())"; 
   		     		$write->query($customerReturnQuery);
   		     		
   		     		$shipmentData->setUdropshipStatus(23);
   		     		Mage::helper('udropship')->addShipmentComment($shipmentData, ('Refund Amount of Rs. '.$paymentamount.'  has been Approved to customer requested by agent '.$userFirstname));
   		     		$shipmentData->save();
                    
                        	$templateId ='refundtodo_email_to_customer';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$vars = Array( 'shipmentID'=>$shipmetId,
										   'custtomerName'=>$firstname,
										   'refundedamt'=>$paymentamount,
										   'custAddress'=>$customerShipaddressHtml,
										   'shipmentitem'=>$customerShipmentItemHtml,
										   'summaryPrice'=>$summaryprice,
										   
										   );

							$_email->setReplyTo($sender['email'])
								->sendTransactional($templateId, $sender,$customeremailId,'', $vars);
								echo "email sent successfully to your email";
							
							$templateIdvendor ='disputedcustomer_trackdetails_to_vendor';
							$senderVendor = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$varsVendor = Array( 'shipmentId'=>$shipmetId,
										   		 'vendorName'=>$vendorName,
										   		 'returntrackDetails'=>$returnTrackDetails,
										   	   );

							$_email->setReplyTo($senderVendor['email'])
								->sendTransactional($templateIdvendor, $senderVendor,$vendorEmail,'', $varsVendor);
								echo "email sent successfully";
                        
                        $read->closeConnection();
	   					$write->closeConnection();

   		     		}
		
		
        	}
        	if($refundStatus == 4) {
        	
        	$remarksCheckedQuery = "UPDATE `refundrequestedcustomer` SET `remarks`='".$remarks."' WHERE `shipment_id`='".$shipmetId."'";
        	$remarksCheckedQueryRes = $write->query($remarksCheckedQuery);
        			
        	Mage::helper('udropship')->addShipmentComment($shipmentData, ('Refund for Shipment: '.$shipmetId.' is checked and remarks are : '.$remarks));
   		    $shipmentData->save();
        			
        	
        	}
        	
        	
                    $refundrequestedcustomer = Mage::getSingleton('refundrequestedcustomer/refundrequestedcustomer')
                        ->load($refundrequestedcustomerId)
                        ->setRefundStatus($refundStatus)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated '.''.$shipmetId, count($refundrequestedcustomerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'refundrequestedcustomer.csv';
        $content    = $this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'refundrequestedcustomer.xml';
        $content    = $this->getLayout()->createBlock('refundrequestedcustomer/adminhtml_refundrequestedcustomer_grid')
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
}
