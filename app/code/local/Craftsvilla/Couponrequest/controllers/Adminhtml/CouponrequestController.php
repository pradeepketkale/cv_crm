<?php
class Craftsvilla_Couponrequest_Adminhtml_CouponrequestController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('couponrequest/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Coupon Request'), Mage::helper('adminhtml')->__('Coupon Request'));
		
		return $this;
	}   
 	
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}
	

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('couponrequest/couponrequest')->load($id);
		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data)) {
			 $model->setData($data);
				
			}
			
			Mage::register('couponrequest_data', $model);
			$this->loadLayout(); 
			$this->_setActiveMenu('couponrequest/items');
		
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Coupon Request'), Mage::helper('adminhtml')->__('Coupon Request'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			
			$this->_addContent($this->getLayout()->createBlock('couponrequest/adminhtml_couponrequest_edit'))
				->_addLeft($this->getLayout()->createBlock('couponrequest/adminhtml_couponrequest_edit_tabs'));
			
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('couponrequest')->__('Coupon does not exist'));
			$this->_redirect('*/*/');
		       }
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() 
	{
		$user = Mage::getSingleton('admin/session'); 
		$userFirstname = $user->getUser()->getFirstname();
		if ($data = $this->getRequest()->getPost()) {
			$model = Mage::getModel('couponrequest/couponrequest');		
			$model->setData($data)->setId($this->getRequest()->getParam('id'));
			$model->getData($data);
			$_couponrequestCreateDate = now();
			$couponnewdate = strtotime($_couponrequestCreateDate)+90*24*60*60;
			$expecteddate1 = date('Y-m-d h:m:s', $couponnewdate);   	
			//print_r($model);exit;
			try {
			 
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}
				$shipmentid = $model->getShipmentId();
				$read = Mage::getSingleton('core/resource')->getConnection('couponrequest_read');
				$squery = "SELECT * FROM `sales_flat_shipment` WHERE `increment_id` = '".$shipmentid."'";
				$resultquery = $read->query($squery)->fetch();
				if($resultquery)
				{
					//To check Duplicate Shipment ID entered
					$duplicateq = "SELECT * FROM couponrequest WHERE shipment_id = '".$shipmentid."'";
					$duplres = $read->query($duplicateq)->fetch();
					if($duplres) 
					{
		    		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('couponrequest')->__('Duplicate Shipment ID')); 
		    		}
					else 
					{
						//To check Coupon Value is less than Shipment Value 
	$pricequery = "SELECT base_total_value,base_shipping_amount FROM sales_flat_shipment WHERE increment_id = '".$shipmentid."'";
	$qres = $read->query($pricequery)->fetch();
	$basetotalvalue = $qres['base_total_value'];
	$baseshippingamount = $qres['base_shipping_amount'];
	$priceres = $basetotalvalue + $baseshippingamount;
	$shipmentprice = $model->getPrice();
	 	//To check only numbers are entered into price field
	 	if(!preg_match("/^[0-9]*$/",$shipmentprice))
		{
	 	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('couponrequest')->__('Only Numeric values are allowed in Price field'));
	 	} 
		else 
	 	{
			if($shipmentprice <= $priceres)
			{
			$model->setStatusCoupon(1);
	$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentid);
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('couponrequest')->__('Coupon was successfully saved'));
								Mage::getSingleton('adminhtml/session')->setFormData(false);
								//To change the status of shipment when coupon requested
								if($model->getStatusCoupon() == 1) 
								{
								$shipmentData->setUdropshipStatus(23);
								Mage::helper('udropship')->addShipmentComment($shipmentData, ('Coupon requested by agent '.$userFirstname.' And amount is '.$shipmentprice));
								$shipmentData->save();
								
								}
								$model->setExpireDateOfcoupon($expecteddate1);								
								$model->setAgentUser($userFirstname);
								$model->save();
								if ($this->getRequest()->getParam('back')) {
									$this->_redirect('*/*/edit', array('id' => $model->getId()));
									return;
								}
								$this->_redirect('*/*/');
								return;
			} 
			else 
			{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('couponrequest')->__('CouponValue is more than the Shipment Value')); 
			}
					 	}
						 
					}
		    
		    }		
        	else{
				$couponstatus ='';
				Mage::getSingleton('adminhtml/session')->addError($this->__('Shipment Id doesnot exist'));
				}
         
   	}  
            catch (Exception $e) 
            {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
        	}
        $this->_redirect('*/*/');
	}
	
 }
 
 public function updateAction(){
 		$connwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
 		$connread = Mage::getSingleton('core/resource')->getConnection('core_read');
 		$couponrequestIds = $this->getRequest()->getParam('couponrequest');
 		
 		$couponrequestIds = $this->getRequest()->getParam('couponrequest');
 		$idCoupon = $couponrequestIds[0];
 		$setShipmentId = $this->getRequest()->getParam('shipmentId');
 		$setprice = $this->getRequest()->getParam('price');
 		$setExpDate = $this->getRequest()->getParam('expireDateOfCoupon');
 		$couponRequestData = Mage::getModel('couponrequest/couponrequest')->load($idCoupon)->getData();
 		
 		$getshipmentId = $couponRequestData['shipment_id'];
 		$getprice = $couponRequestData['price'];
 		$getExpDate = $couponRequestData['expire_date_ofcoupon'];
 		
 		$duplicatequery = "SELECT * FROM couponrequest WHERE shipment_id='".$setShipmentId."'"; 
 		$duplicatequeryResult = $connread->query($duplicatequery)->fetch();
 		$sfsquery = "SELECT * FROM sales_flat_shipment WHERE increment_id='".$setShipmentId."'";
 		$sfsqueryResult = $connread->query($sfsquery)->fetch();
 		$basetotalshippingvalue = $sfsqueryResult['base_total_value']+$sfsqueryResult['base_shipping_amount'];
		//To check ShipmentID exist
		if($sfsqueryResult){
			if($setShipmentId)
 			{
 				//To check duplicate shipmentID
 				if($duplicatequeryResult){
 				 	if($setShipmentId == $getshipmentId)
 				 	{
 				 	$lastShipmentId = $setShipmentId;
 				 	Mage::getSingleton('adminhtml/session')->addSuccess("Coupon Details are modified successfully");
 				 	} else {
 				Mage::getSingleton('adminhtml/session')->addError("Shipment ID already exist");
 			$lastShipmentId = $getshipmentId;
 							}	 
 				}	else {
 				$lastShipmentId = $setShipmentId;
 				Mage::getSingleton('adminhtml/session')->addSuccess("Coupon Details are modified successfully");
 				}

 			}
 			else{
 			$lastShipmentId = $getshipmentId;
 			}
 			
 		}	
 		else{
 			$lastShipmentId = $getshipmentId;
 			Mage::getSingleton('adminhtml/session')->addError("Shipment ID does not exist");
 			}
 		if($setprice)
 			{
 				//To check only numeric values into price 
 				if(!preg_match("/^[0-9]*$/",$setprice))
 				{
			 		$lastPrice = $getprice;
			 	Mage::getSingleton('adminhtml/session')->addError(Mage::helper('couponrequest')->__('Only Numeric values are allowed in Price Field'));
			 	} 
			 	else 
			 	{ 
 				
 				//To check CouponValue is less than ShipmentValue
 				if($setprice <= $basetotalshippingvalue){
 			$lastPrice = $setprice;
 			} else {
 			$lastPrice = $getprice;
 				Mage::getSingleton('adminhtml/session')->addError("Coupon Value is more than Shipment Value");
 				   }
 				}
 			
 			}
 		else
 		{
 				//To check existing price is valid for new shipment ID
 			if($getprice <= $basetotalshippingvalue){
 			$lastPrice = $getprice;
 			} else {
 			$val = 0;
 			$lastPrice = $val;
 			Mage::getSingleton('adminhtml/session')->addError("Coupon Value is more than Shipment Value");
 				   }
 		}
 		if($setExpDate)
 			{
 			$lastExpdate = $setExpDate;
 			}
 		else{
 			$lastExpdate = $getExpDate;
 			}
 				
 			
	 		echo $updateQuery = 
	 		"UPDATE `couponrequest` SET `shipment_id`='".$lastShipmentId."',`expire_date_ofcoupon`='".$lastExpdate."',`price`='".$lastPrice."' WHERE `couponrequest_id` = '".$idCoupon."'";
	 		$connwrite->query($updateQuery);
	 $this->_redirect('*/*/index');	
 }
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('couponrequest/couponrequest');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Coupon was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() 
    {
        $couponrequestIds = $this->getRequest()->getParam('couponrequest');
       	$cid = $couponrequestIds[0]; 
        if($cid == '')
        { 
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select coupon(s)'));
        }
        else { 
		            try {
		        	foreach ($couponrequestIds as $couponrequestId) {
		            $couponrequest = Mage::getModel('couponrequest/couponrequest')->load($couponrequestId);
		            $couponrequest->delete();
		        				}
		        	Mage::getSingleton('adminhtml/session')->addSuccess(
		            Mage::helper('adminhtml')->__(
		                'Total of %d record(s) were successfully deleted', count($couponrequestIds)
		            )
		        	);
		    			} 
		    		catch (Exception $e) {
                		Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            		}
        
        	} 
       
        $this->_redirect('*/*/index');
    }

    
    public function massStatusAction()
    {
         $couponrequestIds = $this->getRequest()->getParam('couponrequest');
         $cid = $couponrequestIds[0]; 
        
        if($cid == '')
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select coupon(s)'));
        } 
        else {
            try {
                foreach ($couponrequestIds as $couponrequestId) 
                {
                $couponstatus =$this->getRequest()->getParam('status');
                $model = Mage::getModel('couponrequest/couponrequest')->load($couponrequestId);
                
                $shipmentid = $model['shipment_id'];
                $read = Mage::getSingleton('core/resource')->getConnection('couponrequest_read');
        		
        		$user = Mage::getSingleton('admin/session'); 
			    $userFirstname = $user->getUser()->getFirstname(); 
                //To change the status of shipment when coupon requested
                if($couponstatus == 1) 
                {
		            $shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentid);
		             
		            $_shipmentStatus = $shipmentData->getUdropshipStatus();
		            if (($_shipmentStatus == 12) || ($_shipmentStatus == 6))
		            {
		            
				        $this->_getSession()->addError("Coupon Cannot Be Requested as Shipment Already Refunded or Cancelled");
				        $this->_redirect('*/*/');
						return;
				    }
		            else 
		            {
		            
		                $shipmentData->setUdropshipStatus(23);
				        Mage::helper('udropship')->addShipmentComment($shipmentData, ('Coupon requested by agent '.$userFirstname));
				        $shipmentData->save();
		            }
                }
                
        		if($couponstatus == 2)
        		{
		    		$read = Mage::getSingleton('core/resource')->getConnection('couponrequest_read');
		    		$user = Mage::getSingleton('admin/session'); 
					$userFirstname = $user->getUser()->getFirstname();
		
		            $model = Mage::getModel('couponrequest/couponrequest')->load($couponrequestId);
		            $shipmentid = $model['shipment_id'];
					$expiredate = $model['expire_date_ofcoupon'];
					$createddate = $model['created_time'];
					$couponvalue = $model['price'];
						
					//To create Random Coupon Code	
					$random_id_length = 7;
					$rndid = crypt(uniqid(rand(),1)); 
					$rndid = strip_tags(stripslashes($rndid)); 
					$rndid = str_replace(array(".", "$"),"",$rndid); 
					$rndid = strrev(str_replace("/","",$rndid));
					$rndid = strtoupper(substr($rndid,0,$random_id_length)); 
					$couponnum = "$rndid";
					$couponnum = str_replace(".", "", "$couponnum");
					$couponcode = 'CVR'.$couponnum; 
		            $rule = Mage::getModel('salesrule/rule');
					$customer_groups = array(0, 1, 2, 3);
	 	  
	 	  $rule->setName('Coupon Code: '.$couponcode.' for Shipment No. '.$shipmentid)
		  ->setDescription('Coupon Code: '.$couponcode.' for Shipment No. '.$shipmentid )
		  ->setFromDate($createddate)
		  ->setToDate($expiredate)
		  ->setCouponType(2)
		  ->setCouponCode($couponcode)
		  ->setUsesPerCustomer(1)
		  ->setUsesPerCoupon(1)
		  ->setCustomerGroupIds($customer_groups) 
		  ->setIsActive(1)
		  ->setConditionsSerialized('')
		  ->setActionsSerialized('')
		  ->setStopRulesProcessing(0)
		  ->setIsAdvanced(1)
		  ->setProductIds('')
		  ->setSortOrder(0)
		  ->setSimpleAction('cart_fixed')
		  ->setDiscountAmount($couponvalue)
		  ->setDiscountQty(null)
		  ->setDiscountStep(0)
		  ->setSimpleFreeShipping('0')
		  ->setApplyToShipping('1')
		  ->setIsRss(0)
		  ->setVendorid('')
		  ->setWebsiteIds(array(1));
		  $rule->save();
		$shipmentData =
		Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentid);
		$shipmentData->setUdropshipStatus(12);
		$shipOrderId = $shipmentData->getOrderId();
		$queryTogetEmail = "SELECT `customer_email` FROM `sales_flat_order` WHERE `entity_id` = '".$shipOrderId."'";
		$queryTogetEmailresult = $read->query($queryTogetEmail)->fetch();
		$customeremailId = $queryTogetEmailresult['customer_email'];
Mage::helper('udropship')->addShipmentComment($shipmentData, ('Coupon Code '.$couponcode.' of Value Rs.'.$couponvalue.' has been sent to customer requested by agent '.$userFirstname));
						
                        $shipmentData->save();
                      	$itemshipment = $shipmentData->getAllItems();
						$vendorShipmentItemHtml .= "<table border='1'
width='50px'><tr><td style='font-size: 13px;height: 26px;padding:11px;vertical-align:left;background:#F2F2F2;color:#CE3D49;'>Image</td><td
style='font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Shipment
Id</td><td style='font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU/Vendorsku</td><td
style='font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product
Name</td><td style='font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
                        foreach($itemshipment as $_itemshipment)
                        {
 
              			$product =Mage::getModel('catalog/product')->load($_itemshipment->getProductId());
           
						   try 
						   {
				$image="<img src='".Mage::helper('catalog/image')->init($product,'image')->resize(154, 154)."' alt='' width='154' border='0'style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
						   } 
						   catch(Exception $e)
						   {}

                                  $vendorShipmentItemHtml .= "<tr><td
style='font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td
style='font-size: 13px;height: 26px;padding:
11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipmentid."</td><td style='font-size:13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getSku()."/ ".$product->getVendorsku()."</td><td style='font-size: 13px;height:
26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getName()."</td><td
style='font-size: 13px;height: 26px;padding:
11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getPrice()."</td></tr>";
                      }
								$vendorShipmentItemHtml .= "</table>";

$expdateformat = date('Md Y',strtotime($expiredate));
$couponhtml =  "<table border='1' width='auto'><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Coupon Value: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Rs. ".$couponvalue."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Coupon Code: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$couponcode."</td></tr><tr><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Expiry DateOfCoupon: </td><td style='font-size: 13px;height: 26px;padding: 9px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'> ".$expdateformat."</td>";

							$couponhtml .= "</table>";	
							$templateId ='coupon_code_for_approved';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Coupon Code for Shipment No.'.$shipmentid;
							$vars = Array( 'couponcode'=>$couponcode,
										   'couponvalue'=>$couponvalue,
										   'expirydate'=>$expiredate,
										   'couponhtml'=>$couponhtml,
										   'shipmentid'=>$shipmentid,
										   'vendorShipmentItemHtml'=>$vendorShipmentItemHtml
										   );

							$_email->setTemplateSubject($mailSubject)
							 	->setReplyTo($sender['email'])
								->sendTransactional($templateId, $sender,$customeremailId,'', $vars);
								//->sendTransactional($templateId, $sender,'srilatharapolu@gmail.com','', $vars);
							echo "email sent successfully to your email";
			    
 				}
		 			$couponrequest = Mage::getSingleton('couponrequest/couponrequest')
									->load($couponrequestId)
									->setStatusCoupon($couponstatus)
									->setCouponCode($couponcode)
									->setIsMassupdate(true)
									->save();	   
           }
               		$this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated '.''.$shipmentid, count($couponrequestIds))
                );
            } 
            catch (Exception $e) 
            {
               $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');   
    }
    
    
}

