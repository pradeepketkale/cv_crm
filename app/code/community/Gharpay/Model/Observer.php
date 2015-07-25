<?php 
class Gharpay_Model_Observer
{
	public function createorder($observer)
	{ 
		if(Mage::registry('sales_order_save_commit_observer_gharpay_executed')){
			Mage::unregister('sales_order_save_commit_observer_gharpay_executed');
			return $this; //this method has already been executed once in this request (see comment below)
		}
		$order = $observer->getOrder();
		$paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
		$gharpay = Mage::getModel('gharpay/gharpay')->getCollection()->addFieldToFilter('order_id',$order->getIncrementId());
		$gharpayData = $gharpay->getData();
		if($gharpayData[0]['gharpay_order_id'] == '')
		{
			if($paymentMethod == 'Cash In Advance' && $order->getStatus() == 'pending' && $order->getState() == 'new')
			{
					$billing_address = $order->getBillingAddress();
					$date = $billing_address->getCreatedAt();
					if(!$date):
						$date = date("Y-m-d H:i:s");
					endif;
					$date = strtotime($date);
					$date = strtotime("+1 day", $date);
					$deliveryDate =  date('d-m-Y', $date);
					//echo $billing_address->getCity(); exit;
					$request_xml="<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>
						        <transaction>
						          <customerDetails>
						            <address>".$billing_address->getData('street').$billing_address->getCity().$billing_address->getRegion()."</address>
						            <contactNo>".$billing_address->getTelephone()."</contactNo>
						            <firstName>".$billing_address->getFirstname()."</firstName>
						            <lastName>".$billing_address->getLastname()."</lastName>
							    	<email>".$billing_address->getEmail()."</email>
						          </customerDetails>
						          <orderDetails>
						            <pincode>".$billing_address->getPostcode()."</pincode>
						            <clientOrderID>".$order->getIncrementId()."</clientOrderID>
						            <deliveryDate>".$deliveryDate."</deliveryDate>
						            <orderAmount>".$order->getGrandTotal()."</orderAmount>";
										foreach ($order->getAllItems() as $item) {
											$request_xml .="<productDetails><productID>".$item->getSku()."</productID>
											<productQuantity>".$item->getQtyOrdered()."</productQuantity>
											<unitCost>".$item->getOriginalPrice()."</unitCost></productDetails>";
											 
										}
					$request_xml .="</orderDetails>
						       </transaction>";
					//echo "xmlll ".$request_xml;exit;
					$url 	= "http://webservices.gharpay.in/rest/GharpayService/createOrder";
					//$url 	= "http://services.gharpay.in/rest/GharpayService/createOrder";
					$ch 	= curl_init($url) ;
					curl_setopt($ch, CURLOPT_HEADER,true);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
					curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:pgf!5@%z','Content-Type:application/xml'));
					$result = curl_exec($ch);
					
					//$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
					
					// Separate content and headers
					$result 	= str_replace("\r\n\r\nHTTP/", "\r\nHTTP/", $result);
					$parts 		= explode("\r\n\r\n",$result);
					$headers 	= array_shift($parts);
					$result 	= implode("\r\n\r\n", $parts);
					$xml		= simplexml_load_string($result);
					
					if($order->getIncrementId() == $xml->clientOrderID && $xml->orderID != '')
					{
						$dateTime = date("Y-m-d H:i:s");
						$model = Mage::getModel('gharpay/gharpay');
						$model->setOrderId($order->getIncrementId());
						$model->setGharpayOrderId($xml->orderID);
						$model->setCreatedTime($dateTime);
						$model->setUpdateTime($dateTime);
						$model->save();
					}else{
						if ($order->getId()) {
							$order->addStatusToHistory($order->getStatus(), 'Order denied by gharpay services');
							$order->cancel();
							$order->save();
						}
						
						$storeId = Mage::app()->getStore()->getId();
						$sender = Array('name'  => 'Craftsvilla',
								'email' => 'raf@craftsvilla.com');
						
						$email = $billing_address->getEmail();
						$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
						Mage::getModel('core/email_template')
						->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
						->sendTransactional(4, $sender, $email);
						$translate->setTranslateInline(true);
					}
				
			}
		}
		
		if($order->getStatus() == 'canceled')
		{
			$gharpay = Mage::getModel('gharpay/gharpay')->getCollection()->addFieldToFilter('order_id',$order->getIncrementId());
			$gharpayData = $gharpay->getData();
			if($gharpayData[0]['gharpay_order_id'] != '')
			{
				$dateTime = date("Y-m-d H:i:s");
				$model = Mage::getModel('gharpay/gharpay')->load($gharpayData[0]['id']);
				$model->setOrderStatus('Cancelled by us');
				$model->setUpdateTime($dateTime);
				$model->save();
				
				$request_xml="<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>
										<cancelOrder>
										<orderID>".$gharpayData[0]['gharpay_order_id']."</orderID>
										</cancelOrder>";
					
				$url = "http://webservices.gharpay.in/rest/GharpayService/cancelOrder";
				$ch = curl_init($url) ;
				curl_setopt($ch, CURLOPT_HEADER,true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request_xml);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:pgf!5@%z','Content-Type:application/xml'));
				$response = curl_exec($ch);
				
				//Mage::log($response);
			}
		}
		Mage::register('sales_order_save_commit_observer_gharpay_executed',true);
	}	
}
?>