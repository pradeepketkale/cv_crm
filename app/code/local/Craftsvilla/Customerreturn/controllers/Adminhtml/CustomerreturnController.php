<?php

class Craftsvilla_Customerreturn_Adminhtml_CustomerreturnController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('customerreturn/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Customer Return Data'), Mage::helper('adminhtml')->__('Customer Return Data'));
		
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
        $this->getLayout()->createBlock('customerreturn/adminhtml_customerreturn_grid')->toHtml()
        );
    }
	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('customerreturn/customerreturn')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('customerreturn_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('customerreturn/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('customer return data'), Mage::helper('adminhtml')->__('Customer Return data'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('customerreturn/adminhtml_customerreturn_edit'))
				->_addLeft($this->getLayout()->createBlock('customerreturn/adminhtml_customerreturn_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreturn')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
			$user = Mage::getSingleton('admin/session'); 
		$userFirstname = $user->getUser()->getFirstname();
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'customer_return_to_seller_email_template';
			$sender = Array('name'  => 'Craftsvilla',
							'email' => 'customercare@craftsvilla.com');
			
			$_email = Mage::getModel('core/email_template');

		if ($data = $this->getRequest()->getPost()) {
			$shipid4512 = $data['shipment_id'];
			$courierName = $data['couriername'];
			$trackingcode = $data['trackingcode'];
			
			$shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipid4512);
			//echo '<pre>';print_r($shipment);			
			if($shipment->getData()){
			$venId4521 = $shipment->getUdropshipVendor();
			$vdata = Mage::getModel('udropship/vendor')->load($venId4521);
			$vemail = $vdata->getEmail();
			$vName = $vdata->getVendorName();
		
			$model = Mage::getModel('customerreturn/customerreturn');
			$model->setData($data)
				  ->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedAt == NULL || $model->getUpdateAt() == NULL) {
					$model->setCreatedAt(now())
						->setUpdateAt(now());
				} else {
					$model->setUpdateAt(now());
				}	
				//$commentText = "Status has changes to 'Returned By Customer' and Tracking Number is '".$trackingcode."' Added By '".$userFirstname."' !";
				$commentText = "Status has changes to 'Returned By Customer' , Tracking Number is '".$trackingcode."' and Courier name is '".$courierName."' Added By '".$userFirstname."' !";
				$model->save();
				$shipment->setUdropshipStatus(26);
				Mage::helper('udropship')->addShipmentComment($shipment,$commentText);
		        $shipment->save();
				$vars = Array('shipmentid' => $shipid4512,'vendorName' =>$vName,'courierName' => $courierName , 'trackingcode' => $trackingcode);
				//print_r($vars);exit;
				$_email->sendTransactional($templateId, $sender, $vemail, '', $vars, $storeId);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('customerreturn')->__('Shipment was successfully saved'));
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
		else{
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreturn')->__('Please Add Correct Shipment'));
			}
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('customerreturn')->__('Unable to find shipment to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('customerreturn/customerreturn');
				 
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
        $customerreturnIds = $this->getRequest()->getParam('customerreturn');
        if(!is_array($customerreturnIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerreturnIds as $customerreturnId) {
                    $customerreturn = Mage::getModel('customerreturn/customerreturn')->load($customerreturnId);
                    $customerreturn->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($customerreturnIds)
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
        $customerreturnIds = $this->getRequest()->getParam('customerreturn');
        if(!is_array($customerreturnIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($customerreturnIds as $customerreturnId) {
                    $customerreturn = Mage::getSingleton('customerreturn/customerreturn')
                        ->load($customerreturnId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($customerreturnIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	public function checkcustomeremailAction()
	{
		$customerreturnIds = $this->getRequest()->getParam('customerreturn');
        if(!is_array($customerreturnIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } 
		else{
			try{
				foreach ($customerreturnIds as $customerreturnId) 
				{
					$customerreturn = Mage::getSingleton('customerreturn/customerreturn')->load($customerreturnId);
					$shipmentId = $customerreturn->getShipmentId();
					$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
					$orderid = $shipmentData->getOrderId();
					$orderData = Mage::getModel('sales/order')->load($orderid)->getCustomerEmail();
				}
				$this->_getSession()->addSuccess($this->__('customer email is :'.$orderData.' for shipment Id :'. $shipmentId));
			}
			catch (Exception $e){
			}

		}	
$this->_redirect('*/*/index');	
	}
	public function sellercityAction()
	{
		$customerreturnIds = $this->getRequest()->getParam('customerreturn');
        if(!is_array($customerreturnIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } 
		else{
		
			try{
				foreach ($customerreturnIds as $customerreturnId) 
				{
					$customerreturn = Mage::getSingleton('customerreturn/customerreturn')->load($customerreturnId);
					$shipmentId = $customerreturn->getShipmentId();
					$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
					$vendorid = $shipmentData->getUdropshipVendor();
					$vendorData = Mage::getModel('udropship/vendor')->load($vendorid)->getCity();
				}
				$this->_getSession()->addSuccess($this->__('Vendor City is :'.$vendorData.' for shipment Id :'. $shipmentId));
			}
			catch (Exception $e){
			}
		}	
$this->_redirect('*/*/index');	
	}
public function getpaymentmethodAction()
	{
		$customerreturnIds = $this->getRequest()->getParam('customerreturn');
        if(!is_array($customerreturnIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } 
		else{
		
			try{
				foreach ($customerreturnIds as $customerreturnId) 
				{
					$customerreturn = Mage::getSingleton('customerreturn/customerreturn')->load($customerreturnId);
					$shipmentId = $customerreturn->getShipmentId();
					$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
					$orderid = $shipmentData->getOrderId();
					$orderpayment = Mage::getModel('sales/order')->load($orderid)->getPayment()->getMethod();
					
				}
				$this->_getSession()->addSuccess($this->__('Payment Method : '.$orderpayment.' for shipment Id :'. $shipmentId));
			}
			catch (Exception $e){
			}
		}	
$this->_redirect('*/*/index');	
	}
	public function getRefundamountAction()
		{
			$customerreturnIds = $this->getRequest()->getParam('customerreturn');
		    if(!is_array($customerreturnIds)) {
				Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
		    } 
			else{
		
				try{
					foreach ($customerreturnIds as $customerreturnId) 
					{
						$customerreturn = Mage::getSingleton('customerreturn/customerreturn')->load($customerreturnId);
						$shipmentId = $customerreturn->getShipmentId();
						$shipmentData = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentId);
						$shipmentid = $shipmentData->getEntityId();
						$autoRefundedamount = Mage::helper('udropship')->getrefundCv($shipmentid);
					}
					$this->_getSession()->addSuccess($this->__('Refund to do amount is Rs.: '.$autoRefundedamount.' for shipment Id :'. $shipmentId));
				}
				catch (Exception $e){
				}
			}	
	$this->_redirect('*/*/index');	
		}


//$shipment_id_value,$shipentId)
public function refundtodoamtaction()  
		{ 
        $customerreturnIds = $this->getRequest()->getParam('customerreturn');
		$refundPrice = $this->getRequest()->getParam('refund_price');
        if(!is_array($customerreturnIds)) {
			Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } 
	else{

		try{
		foreach ($customerreturnIds as $customerreturnId) 
			{

			$customerreturn = Mage::getSingleton('customerreturn/customerreturn')->load($customerreturnId);
			$shipentId = $customerreturn->getShipmentId();

			$refundedamount = $refundPrice;
			$storeId = Mage::app()->getStore()->getId();	  
			$templateId = 'refundtodo_email_to_customer';
			$templateId2 = 'refundtodo_email_to_vendor';
			$shipment1 = Mage::getModel('sales/order_shipment');		
			$shipment = $shipment1->loadByIncrementId($shipentId);
			$dropship = Mage::getModel('udropship/vendor')->load($shipment->getUdropshipVendor());
			$vendorName = $dropship->getVendorName();
			$venEmail = $dropship->getEmail();
			//	echo '<pre>';print_r($shipment);exit;		
			$_order = $shipment->getOrder();
			//echo '<pre>';print_r($_order);exit;		
			$entityid = $shipment->getEntityId();
			$baseTotalValue = floor($shipment->getBaseTotalValue());
			$itemisedtotalshippingcost = floor($shipment->getItemisedTotalShippingcost());
			$totalcost = floor($itemisedcost);
			$write = Mage::getSingleton('core/resource')->getConnection('shipmentpayout_write');	
			$sender = Array('name'  => 'Craftsvilla',
			'email' => 'customercare@craftsvilla.com');
			$emailrefunded = Mage::getModel('core/email_template');
			$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
			$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			->where('main_table.parent_id='.$entityid)
			->columns('SUM(a.base_discount_amount) AS amount');

			//echo $orderitem->getSelect()->__toString();exit;
			$orderitemdata = $orderitem->getData();
			//print_r($orderitemdata);exit;
			foreach($orderitemdata as $_orderitemdata)
			{
			$discountamount = floor(($_orderitemdata['amount']));
			}
			$totalAmounttoRefund = Mage::helper('udropship')->getrefundCv($entityid);
			//to get summary of all price

			$summaryprice = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;text-align: right;background:#F2F2F2;color:#CE3D49;'>Total Value : Rs.".$baseTotalValue."<br/>Shipping & Handling:Rs. ".$itemisedtotalshippingcost."<br/> Discount:Rs.".$discountamount."</td></tr><table>";
			//echo $summaryprice;exit; 
			$_address = $_order->getShippingAddress();
			//echo '<pre>';print_r($_order->getShippingAddress());exit;
			//to get Shipping address		
			$getName = $_order->getCustomerFirstname();
			$customerTelephone = $_order->getBillingAddress()->getTelephone();
			$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
			$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
			$street = $_order->getShippingAddress()->getStreet();
			$street1 = $street[0].$street[1];		
			$city = $_order->getShippingAddress()->getCity();
			$fname = $_order->getShippingAddress()->getFirstname();
			$lname = $_order->getShippingAddress()->getLastname();
			$fullname = $fname.$lname;
			$postcode = $_order->getShippingAddress()->getPostcode();
			$countrycode = $_order->getShippingAddress()->getCountryId();


			$customerShipaddressHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$fullname."<br/>".$street1."<br/>".$city."<br/>".$postcode."<br/>".$countrycode."</td></tr><table>";


			$currencysym = Mage::app()->getLocale()->currency($_order->getBaseCurrencyCode())->getSymbol();
			$_items = $shipment1->getAllItems();
			$customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
			foreach ($_items as $_item)
			{
			//echo $_item['product_id'];exit;
			$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
			try{				
			$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";
			}
			catch(Exception $e){}
			$customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$shipentId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
			}
			$customerShipmentItemHtml .= "</table>";

			if($refundedamount)		
				{
				$editRefundamount = $refundedamount;
				$vars = array('shipmentid'=>$_shipmentId,
				'summaryPrice'=>$summaryprice,
				'custAddress' => $customerShipaddressHtml,
				'shipmentitem' => $customerShipmentItemHtml,
				'custtomerName'=> $getName,
				'shipmentID' =>	$shipentId,
				'refundedamt' => $editRefundamount,
				'sellerName' => $vendorName,
				);
				//print_r($vars);exit;
				$emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->setReplyTo('customercare@craftsvilla.com')
					->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);	
				$emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					->sendTransactional($templateId2, $sender,$venEmail, '', $vars, $storeId);	
				$queryUpdate = "update shipmentpayout set `refundtodo`='".$editRefundamount."' WHERE shipment_id = '".$shipentId."'";
					$write->query($queryUpdate);
				$commentText = "Status has changes to Refund Todo & Amount is :Rs. ".$editRefundamount." Through Customer return grid";
				$shipment->setUdropshipStatus(23);
				Mage::helper('udropship')->addShipmentComment($shipment,$commentText);
		        $shipment->save();
				$this->_getSession()->addSuccess($this->__(' Refundtodo Amount: '.$editRefundamount.' For shipment Id :'. $shipentId));
				}
			//else{
				//$systRefundamount = $totalAmounttoRefund;
				//$vars = array('shipmentid'=>$_shipmentId,
				//'summaryPrice'=>$summaryprice,
				//'custAddress' => $customerShipaddressHtml,
				//'shipmentitem' => $customerShipmentItemHtml,
				//'custtomerName'=> $getName,
				//'shipmentID' =>	$shipentId,
				//'refundedamt' => $systRefundamount,
				//);
				//$emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					//->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
				//$emailrefunded->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
					//->sendTransactional($templateId2, $sender,$venEmail, '', $vars, $storeId);
				//$queryUpdate = "update shipmentpayout set `refundtodo`='".$systRefundamount."' WHERE shipment_id = '".$shipentId."'";
					//$write->query($queryUpdate);
				//}
			//$this->_getSession()->addSuccess($this->__(' Refundtodo Amount: '.$systRefundamount.' For shipment Id :'. $shipentId));
			}
		 
		}
		catch (Exception $e){
		}
	}	
$this->_redirect('*/*/index');		
	 }

}
