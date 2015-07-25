<?php 
class Gharpay_Model_Observer
{
	public function createorder($observer)
	{ 
		if(Mage::registry('sales_order_save_commit_observer_gharpay_executed')){
			return $this; //this method has already been executed once in this request (see comment below)
		}
		$order = $observer->getOrder();
		$paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();
		if($paymentMethod == 'Cash In Advance' && $order->getStatus() == 'pending' && $order->getState() == 'new')
		{
			$billing_address = $order->getBillingAddress();
			$date = $billing_address->getCreatedAt();
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
				            <orderAmount>".$order->getGrandTotal()."</orderAmount>
							<productDetails>";
								foreach ($order->getAllItems() as $item) {
									$request_xml .="<productID>".$item->getSku()."</productID>
									<productQuantity>".$item->getQtyOrdered()."</productQuantity>
									<unitCost>".$item->getPrice()."</unitCost>";
									 
								}
			$request_xml .="</productDetails>
				          </orderDetails>
				       </transaction>";
			//echo "xmlll ".$request_xml;
			$url 	= "http://webservices.gharpay.in/rest/GharpayService/createOrder";
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
				$message = "Transaction did not went successfully cause order denied by gharpay services.";
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				mail($billing_address->getEmail(), 'Order denied by gharpay services', $message, $headers);
			}
		}
		
		if($order->getStatus() == 'canceled')
		{
			$gharpay = Mage::getModel('gharpay/gharpay')->getCollection()->addFieldToFilter('order_id',$order->getIncrementId());
			$gharpayData = $gharpay->getData();
			if($gharpayData[0]['gharpay_order_id'] != '')
			{
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