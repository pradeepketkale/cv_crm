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
 * @package    Unirgy_DropshipMicrosite
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_DropshipMicrosite_VendorController extends Mage_Core_Controller_Front_Action
{
    protected $_loginFormChecked = false;
	
    protected function _setTheme()
    {
       	$theme = explode('/', Mage::getStoreConfig('udropship/vendor/interface_theme'));
        if (empty($theme[0]) || empty($theme[1])) {
            $theme = 'default/default';
        }
        Mage::getDesign()->setPackageName($theme[0])->setTheme($theme[1]);
    }

    protected function _renderPage($handles=null, $active=null)
    {
        $this->_setTheme();
        $this->loadLayout($handles);
        if (($root = $this->getLayout()->getBlock('root'))) {
            $root->addBodyClass('udropship-vendor');
        }
        if ($active && ($head = $this->getLayout()->getBlock('header'))) {
            $head->setActivePage($active);
        }
        $this->_initLayoutMessages('udropship/session');
        $this->renderLayout();
    }

    public function registerAction()
    {
        $this->_renderPage(null, 'register');
    }
    
    public function registerPostAction()
    {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            try {
                $data = $r->getParams();
                $data['landing_page_title'] = "Buy ".ucfirst($data['shop_name'])." Products on Craftsvilla";
                $session = Mage::getSingleton('udropship/session');
//-----------------------------Add by chetan---------------------------------------------------

		if (strstr($data['bank_ac_number'], " " ))
                {
                    $session->addError($this->__('Spaces are not allowed in Bank Account Number.'));
                    $this->_redirect('*/*/register');
                    return;
                }

		//$panLength = strlen($data['pan_number']);
		//if($panLength != 10)
		//{

		//    $session->addError($this->__('Enter correct  PAN Number.'));
                 //   $this->_redirect('*/*/register');
                 //   return;
		
		//}
		//$data['bank_ifsc_code'] = strtoupper($data['bank_ifsc_code']);
		//$ifscLength = strlen($data['bank_ifsc_code']);
		//if($ifscLength != 11)
		//{

		 //   $session->addError($this->__('Enter correct IFSC code.'));
                //    $this->_redirect('*/*/register');
                //    return;
		//}

  //------------------------------------------------------------------------------------------------------              
		//echo $data['bank_ifsc_code'];exit;

                if (strstr($data['url_key'], " " ))
                {
                    $session->addError($this->__('Spaces are not allowed in shop url.'));
                    $this->_redirect('*/*/register');
                    return;
                }
                
                if(preg_match('/[^a-z0-9 _]+/i', $data['url_key'])) {
                    $session->addError($this->__('Special characters are not allowed in shop url.'));
                    $this->_redirect('*/*/register');
                    return;
                }
               //added by dileswar	 
                $urlKey = strtolower($data['url_key']);
				if($urlKey != 'craftsvilla' && $urlKey != 'microsite' && $urlKey != 'umicrosite' && $urlKey != 'customer' && $urlKey != 'sales' && $urlKey != 'craftsvillacustomer' && $urlKey != 'sell' && $urlKey != 'marketplace' && $urlKey != 'kribhasanvi' && $urlKey != 'catalog' && $urlKey != 'checkout' && $urlKey != 'hcheckout' && $urlKey != 'shopcoupon' && $urlKey != 'searchresults' )
		{
                $modelCheck = Mage::getModel('udropship/vendor')->getCollection();
                $modelCheck->addFieldToFilter('url_key', array('like' => $urlKey.'%'));
                if($modelCheck->count() > 0): 
                    $session->addError($this->__('This shop url is not available.'));
                    $this->_redirect('*/*/register');
                    return; 
                endif;
         }
		else{
			
			$session->addError($this->__('This shop url is not available for registraion.'));
			$this->_redirect('*/*/register');
           return; 
		}  

     
                $model = Mage::getModel('udropship/vendor');
                $model->addData($data)
                      ->validate()
                      ->save();
                
                $shippingModel = Mage::getModel('udropship/vendor_shipping');
                $shippingModel->setVendorId($model->getId())
                                ->setShippingId(1)
                                ->setCarrierCode('tablerate')
                                ->setEstCarrierCode('tablerate');
                $shippingModel->save();
                
                $shippingModel2 = Mage::getModel('udropship/vendor_shipping');
                $shippingModel2->setVendorId($model->getId())
                                ->setShippingId(2)
                                ->setCarrierCode('pis')
                                ->setEstCarrierCode('pis');
                $shippingModel2->save();
                
                $hlp    = Mage::helper('umicrosite');
                $hlp->sendVendorWelcomeEmail($model);
                $hlp->sendVendorRegistration($model);
                
                $session = Mage::getSingleton('udropship/session');
                if (!empty($data['email']) && !empty($data['password'])) {
                    if (!$session->login($data['email'], $data['password'])) {
                        $session->addError($this->__('Invalid username or password.'));
                    }
                    $this->_redirect('marketplace/vendor/addproduct/');
                    return;
                } else {
                    $session->addError($this->__('Login and password are required'));
                }
                return;
            } catch (Exception $e) {
                Mage::getSingleton('udropship/session')->addError($e->getMessage());
                $this->_redirect('*/*/register');
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    public function checkShopUrlAction(){ Mage::log('inside');
        $data = $this->getRequest()->getParams();
        $urlKey = strtolower($data['shop_url']);
		if($urlKey != 'craftsvilla' && $urlKey != 'microsite' && $urlKey != 'umicrosite' && $urlKey != 'customer' && $urlKey != 'sales' && $urlKey != 'craftsvillacustomer' && $urlKey != 'sell' && $urlKey != 'marketplace' && $urlKey != 'kribhasanvi' && $urlKey != 'catalog' && $urlKey != 'checkout' && $urlKey != 'hcheckout' && $urlKey != 'shopcoupon' && $urlKey != 'searchresults' && $urlKey != 'addtocart' && $urlKey != 'product' && $urlKey != 'shipmentpayout' && $urlKey != 'shipment' && $urlKey != 'udropship' && $urlKey != 'search' && $urlKey != 'order' && $urlKey != 'orders'  )
		{
        $model = Mage::getModel('udropship/vendor')->getCollection();
        $model->addFieldToFilter('url_key', array('like' => $urlKey.'%'));
	    if($model->count() > 0):
            echo 0;
        else:
            echo 1;
        endif;
		}
		else
		{
			 echo 0;
		}
    }
    /*public function registerPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('umicrosite');
        try {
            $data = $this->getRequest()->getParams();
            $session->setRegistrationFormData($data);
            $reg = Mage::getModel('umicrosite/registration')
                ->setData($data)
                ->validate()
                ->save();
            $hlp->sendVendorSignupEmail($reg);
            $hlp->sendVendorRegistration($reg);
            $session->unsRegistrationFormData();
            $session->addSuccess($hlp->__('Thank you for application. As soon as your registration has been verified, you will receive an email confirmation'));
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            *///$this->_redirect('*/*/register');
            /*
            return;
        }
        $this->_redirect('udropship/vendor');
    }*/

    public function registerSuccessAction()
    {
        $this->_renderPage(null, 'register');
    }
		public function replyshipmentdateAction()
		{
			
			$ordershipmentId = $this->getRequest()->getParam('q');
			$date = $this->getRequest()->getParam('date');
			$vendorId = $this->getRequest()->getParam('vendorid');
			$shipmeIdp =  $this->getRequest()->getParam('shipmentid');
			$year = '20'.substr($date,4,2);
			$month = substr($date,2,2);
			$day = substr($date,0,2);
			//To get Current date did the below line
			$date1 = new DateTime();
			$date1->setDate($year,$month,$day);
			$expectedDate = $date1->format('jS F');
			$printdate = $date1->format('Y-m-d h:m:s');
			
			$dataofCustomer = Mage::getModel('sales/order')->load($ordershipmentId);
			$customerEmail = $dataofCustomer->getCustomerEmail();
			$dataofVendor = Mage::getModel('udropship/vendor')->load($vendorId);
			$vendorEmail = $dataofVendor->getEmail();
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'dayofshipping_email_template';
			$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
			//$translate  = Mage::getSingleton('core/translate');
			//$translate->setTranslateInline(false);
			$_email = Mage::getModel('core/email_template');
			
			
							
			//check for shipment already exist or NOT
			$shippmentExpCollection = Mage::getModel('activeshipment/activeshipment')->getCollection()
									->addFieldToFilter('shipment_id', $shipmeIdp);
			foreach($shippmentExpCollection as $_shippmentExpCollection){
				$id = $_shippmentExpCollection->getActiveshipmentId();
				}
			if($shippmentExpCollection->count() == 0)
				{
					$shippmentExp = Mage::getModel('activeshipment/activeshipment')->setActiveshipmentId($id)
																				   ->setShipmentId($shipmeIdp)
																				   ->setExpectedShipingdate($printdate)
																				   ->save();
					
					//echo 'Inset me@if';exit;
				}
			else
				{
				$shippmentExp = Mage::getModel('activeshipment/activeshipment')->load($id)
											->setExpectedShipingdate($printdate)->save();
					   
									   //echo 'Update me@else';exit;
				}
				//exit;
			$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmeIdp);
			Mage::helper('udropship')->addShipmentComment($shipmentData,
							  ('The Expected Shipping Date '.$expectedDate.' For This Shipment: '.$shipmeIdp.' Has Been Communicated By The Seller.'));
			$shipmentData->save();				  
			$itemshipment = $shipmentData->getAllItems();
			$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU/Vendorsku</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
			foreach($itemshipment as $_itemshipment)
					{
					//$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_itemshipment->getSku());
					$product = Mage::getModel('catalog/product')->load($_itemshipment->getProductId());
					$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
					$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$shipmeIdp."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getSku()." / ".$product->getVendorsku()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_itemshipment->getPrice()."</td></tr>";
					}
				$vendorShipmentItemHtml .= "</table>";

			$vars = Array('expdate' => $expectedDate,
						  'vendorItemHTML' =>$vendorShipmentItemHtml,
						  'custname' => $dataofCustomer->getCustomerFirstname(),
						  'sellerName' => $dataofVendor->getVendorName(),
						  'shipmentId' => $shipmeIdp,
						  'vendorTelephone' => $dataofVendor->getTelephone(),
						);
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId));
				   //->sendTransactional($templateId, $sender,$vendorEmail, $dataofVendor->getVendorName(), $vars, $storeId);
			$_email->sendTransactional($templateId, $sender,$customerEmail, $dataofCustomer->getCustomerFirstname(), $vars, $storeId);	
			//$_email->sendTransactional($templateId, $sender,'places@craftsvilla.com', '', $vars, $storeId);	
			$translate->setTranslateInline(true);
			
			echo 'The Expected Shipping Date '.$expectedDate.' For This Shipment: '.$shipmeIdp.' Has Been Communicated To Customer.';
			//echo 'hello India';
		}
		
	public function paymentresnAction()
		{
			
			$ordershipmentId = $this->getRequest()->getParam('q');
			$payment = $this->getRequest()->getParam('payment');
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$salesQuery = "SELECT * FROM `sales_flat_order` where `increment_id` = '".$ordershipmentId."'";
			$rquery = $read->query($salesQuery)->fetch();
			$custemail = $rquery['customer_email'];
			$custfname = $rquery['customer_firstname'];
			$custlname = $rquery['customer_lastname'];
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'payment_reason_email_template';
			$translate  = Mage::getSingleton('core/translate');
			$_email = Mage::getModel('core/email_template');
			$mailSubject = 'Customer Feeback:'.$payment;
			$sender = Array('name' => 'Craftsvilla Places',
			'email' => 'places@craftsvilla.com');
			
		$vars = Array('custmail' => $custemail, 'Fname' => $custfname, 'Lname'=> $custlname,'order' => $ordershipmentId,'payment' => $payment);
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->setTemplateSubject($mailSubject)
							->sendTransactional($templateId,$sender,'manoj@craftsvilla.com',$recname, $vars,$storeId);
						$translate->setTranslateInline(true);
		
		echo "Thanks for your input. Your feedback has been received successfully. We will review this and update you as soon as possible if required.";	
		}
		
	public function custvoucheracceptAction()
		{
			$ordershipmentId = $this->getRequest()->getParam('q');
		    if(is_numeric($ordershipmentId))
			 {
			$vouchercode = $this->getRequest()->getParam('voucher');
			$action = $this->getRequest()->getParam('action');
			$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$shipmentQuery = "SELECT * FROM `sales_flat_shipment` where `increment_id` = '".$ordershipmentId."'";
			$shipmentQueryres = $read->query($shipmentQuery)->fetch();
			$entityid = $shipmentQueryres['entity_id'];
			if($entityid)
			 {
			$orderid = $shipmentQueryres['order_id'];
			$salesorder = "SELECT * FROM `sales_flat_order` where `entity_id` = '".$orderid."'";
			$salesorderres = $read->query($salesorder)->fetch();
			$custname = $salesorderres['customer_firstname'];
			$custemail = $salesorderres['customer_email'];
			$shipmentstatus = $shipmentQueryres['udropship_status'];
			$sabtotalvalue = $shipmentQueryres['base_total_value'];
			$itemisedcost = $shipmentQueryres['itemised_total_shippingcost'];
			$totalcost = floor($itemisedcost);
			$baseshippingamount = $shipmentQueryres['base_shipping_amount'];
			$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
			
			$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$entityid)
								  ->columns('SUM(a.base_discount_amount) AS amount');
			$orderitemdata = $orderitem->getData();
			foreach($orderitemdata as $_orderitemdata)
			{
			  $discountamount = floor(($_orderitemdata['amount']));
			
			}
			if($action=='Accept')
			{
				if($shipmentstatus==13)
				{
					$read = Mage::getSingleton('core/resource')->getConnection('core_read');
					$couponQuery = "SELECT * FROM `salesrule_coupon` where `code` = '".$vouchercode."'";
					$couponQueryres = $read->query($couponQuery)->fetch();
					$ruleid = $couponQueryres['rule_id'];
					$rule = Mage::getModel('salesrule/rule')->load($ruleid);
					$rule->setIsActive(1)
						 ->save();
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$productquery = "update `sales_flat_shipment` set `udropship_status` = 12 WHERE `increment_id`= ".$ordershipmentId;
					$writequery = $write->query($productquery);
					$shipment=Mage::getModel('sales/order_shipment')->loadByIncrementId($ordershipmentId);
					Mage::helper('udropship')->addShipmentComment($shipment,
									   $this->__('Customer accepted the voucher '.$vouchercode)
									  );	
					
                
					$shipment->save();
				}
			echo 'Thanks for accepting voucher refund from craftsvilla.com. You can now go ahead and use the voucher code '.$vouchercode.' given to you for shopping on craftsvilla.com';
//commented By dileswar on dated 10-10-2014 to block cc emails to customercare			
			//$mail = Mage::getModel('core/email');
			//$mail->setToName('Customer Care');
			//$mail->setToEmail('customercare@craftsvilla.com');
			//$mail->setBody('Customer accepted the voucher: '.$vouchercode.' for shipment '.$ordershipmentId);
			//$mail->setSubject('Customer accepted the voucher: '.$vouchercode.' for shipment '.$ordershipmentId);
			//$mail->setFromEmail('places@craftsvilla.com');
			//$mail->setFromName("Craftsvilla Places");
			//$mail->setType('html');
			//$mail->send();
			}
			else
			{
				if($shipmentstatus==13)
				{
					$refundamount = $sabtotalvalue + $totalcost - $discountamount;
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$productquery = "update `sales_flat_shipment` set `udropship_status` = 23 WHERE `increment_id`= ".$ordershipmentId;
					$writequery = $write->query($productquery);
					$queryRefamt = "update shipmentpayout set `refundtodo` = '".$refundamount."' WHERE `shipment_id` = '".$ordershipmentId."'";
					$write->query($queryRefamt);
					$shipment=Mage::getModel('sales/order_shipment')->loadByIncrementId($ordershipmentId);
					Mage::helper('udropship')->addShipmentComment($shipment,
									   $this->__('Customer rejected the voucher '.$vouchercode.' and refunded amount Rs.'.floor($refundamount))
									  );	
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_REFUND_TODO);
				$shipment->save();
				}
			echo $msgrefund = 'We are sorry to hear that you have not accepted the voucher refund. If this was done in error, you can always go back and click the Accept button again. Your refund process has been initiated for amount Rs. '.floor($refundamount).'. You will receive this refund amount back in your credit card/bank account within 15 Days. Please contact customercare team at customercare@craftsvilla.com if you wish to be refunded through alternate means.';
//commented By dileswar on dated 10-10-2014 to block cc emails to customercare
			//$mail = Mage::getModel('core/email');
			//$mail->setToName('Customer Care');
			//$mail->setToEmail('customercare@craftsvilla.com');
			//$mail->setBody('Customer rejected the voucher: '.$vouchercode.' for shipment '.$ordershipmentId);
			//$mail->setSubject('Customer rejected the voucher: '.$vouchercode.' for shipment '.$ordershipmentId);
			//$mail->setFromEmail('places@craftsvilla.com');
			//$mail->setFromName("Craftsvilla Places");
			//$mail->setType('html');
			//$mail->send();
			
			$mail = Mage::getModel('core/email');
			$mail->setToName($custname);
			$mail->setToEmail($custemail);
			$mail->setBody($msgrefund);
			$mail->setSubject('Your Refund On Craftsvilla.com Has Been Initiated For Shipment '.$ordershipmentId);
			$mail->setFromEmail('places@craftsvilla.com');
			$mail->setFromName("Craftsvilla Places");
			$mail->setType('html');
			$mail->send();
			}
			}
			else
			{
			 echo 'Order Id is not valid. Please contact customer care at customercare@craftsvilla.com';
			}
			}
			else
			{
			 echo 'Order Id is not valid. Please contact customer care team at customercare@craftsvilla.com';
			}
		}
		
//Added By dileswar to get the cart of abandoned product....
	public function addProductTocartAction(){
		$qid = $this->getRequest()->getParam('q');
		$parent_quote = Mage::getModel("sales/quote")->load($qid);
					//$itemQuote = Mage::getModel('sales/quote_item')->load($qid,'quote_id');
					//echo $itemQuote->getSku().'<br>';
					foreach($parent_quote->getAllItems() as $itemsQuote){
						$productid = $itemsQuote->getProductId(); //product id
						$prdSku = $itemsQuote->getSku();
						$qty_value = $itemsQuote->getQty(); //ordered qty of item
						$prdName = $itemsQuote->getName();
						$price = $itemsQuote->getBasePrice();
						$product_model = Mage::getModel('catalog/product');
						$my_product = $product_model->load($productid);
						//echo '<pre>';print_r($my_product->getData());exit;
						//Add to cart code
						$cart = Mage::getModel('checkout/cart');
						$cart->init();
						$cart->addProduct($my_product, array('qty' => $qty_value));
						$cart->save();
						Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
						
			
			}
		$this->_redirect('checkout/cart');
		}
		
		
		
		
public function closedisputeAction()
		{
		  $shipid = $this->getRequest()->getParam('q');
		  $authid = $this->getRequest()->getParam('authid');
		  $logid = $this->getRequest()->getParam('log_id');
		 $vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment')->getCollection()->addFieldToFilter('increment_id',$shipid);
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$readlog = "select * from `craftsvilla_auth` where `reference_number`=".$shipid;
		$readlogresult = $read->query($readlog)->fetch(); 
		$log = $readlogresult['log_id'];
		$auth = $readlogresult['authid'];
		$referenceid = $readlogresult['reference_number'];
		if($referenceid==$shipid && $auth==$authid && $log==$logid)
		{
		foreach($shipment as $_shipment)
		{
		//$shipmentid = $shipment->getIncrementId();
		$storeId = Mage::app()->getStore()->getId();
		//$session = $this->_getSession();
		
		//$shipmentData = $shipment->load($shipid);
		$vendorDataCust = $vendor->load($_shipment->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		//echo $_FILES["file"]["type"];
		
		$customerData = Mage::getModel('sales/order')->load($_shipment->getOrderId());
		$orderEmail = $customerData->getCustomerEmail();
		$orderid = $customerData->getEntityId();
		
		$customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('email',$orderEmail);
		foreach($customer as $_customer)
		{
		  $custid = $_customer['entity_id'];
		}
		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
			$disputeQuery = "update `disputeraised` set `status` = 2 where `increment_id` = ".$shipid;
			$disputeQueryresult = $write->query($disputeQuery);
		
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'vendorclosedispute_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$read = Mage::getSingleton('core/resource')->getConnection('udropship_read');	
		$getshipmentQuery = "SELECT `entity_id` FROM `sales_flat_shipment` where `increment_id` = '".$shipid."'";
	    $getshipmentQueryResult = $read->query($getshipmentQuery)->fetch();
	    $shipentityid = $getshipmentQueryResult['entity_id'];
	    $orderpayment = "select `method` from `sales_flat_order_payment` where `parent_id` = '".$orderid."'";
	    $orderpaymentResult = $read->query($orderpayment)->fetch();
	    $ordermethod = $orderpaymentResult['method'];
	    $model1  = Mage::getModel('disputeraised/disputeraised')->getCollection()->addFieldToFilter('increment_id',$shipid)->setOrder('id', 'DESC');;
		
		$html = '';
		$html .= '<div><table style="border-collapse: collapse;border: 1px solid black;"><tr><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Image</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Content</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Added By</font></th></tr>';
		foreach ($model1 as $_model)
		{
            $incrementid = $_model->getIncrementId();
           
            $html .= '<tr><td style="border-collapse: collapse;border: 1px solid black;">'.$_model['image'].'</td><td width="600px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['content'].'</td><td width="250px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['addedby'].'</td></tr>';
		}
		 $html .= '</table></div>';
       
		$vars = Array('shipmentId' => $shipid,
					'shipmentDate' => date('jS F Y',strtotime($_shipment->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_custnote1,
					'imagecust' => $pathhtml,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
		            'html' => $html,
				);
		//print_r($vars);exit;	
		//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))->setReplyTo($orderEmail);
				$_email->sendTransactional($templateId, $sender, $orderEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		
		$_email->sendTransactional($templateId, $sender, $vendorEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		$_email->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
			if ($_shipment->getUdropshipStatus()== Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DISPUTE_RAISED) {
               // $getshipmenttrack = "SELECT `number` FROM `sales_flat_shipment_track` where `parent_id` = ".$shipentityid;
               $getshipmenttrack = "SELECT `number` FROM `sales_flat_shipment_track` where `parent_id` = ".$shipentityid;//.$shipentityid;
	   			$getshipmenttrackResult = $read->query($getshipmenttrack)->fetch();
	   			$tracknumber = $getshipmenttrackResult['number'];
	   			if($tracknumber=='')
	   			{
	   			$_shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PROCESSING);
	   			}
	   			if($ordermethod=='cashondelivery')
				{
	   				$_shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
	   			}
	   			else {
				$_shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
	   			}
					Mage::helper('udropship')->addShipmentComment($_shipment,
							  ('Customer has closed the dispute for this shipment'));
						
							}
					$_shipment->save();
		
			echo 'Your dispute has been closed successfully';
		}
		
		}
		else
		{
			echo 'You are not registered user.';
		}
		}
		
}
