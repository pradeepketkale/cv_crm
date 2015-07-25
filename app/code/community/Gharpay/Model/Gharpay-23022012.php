<?php 
class Gharpay_Model_Gharpay extends Mage_Core_Model_Abstract 
{
	protected function _construct()
	{
		$this->_init('gharpay/gharpay');
	}

	public static function viewOrderStatus($gharpayOrderID){
	
		$url = 'http://webservices.gharpay.in/rest/GharpayService/viewOrderStatus?orderID='.$gharpayOrderID;
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:pgf!5@%z'));
		
		$result 	= curl_exec($ch);
		$result 	= str_replace("\r\n\r\nHTTP/", "\r\nHTTP/", $result);
		$parts 		= explode("\r\n\r\n",$result);
		$headers 	= array_shift($parts);
		$result 	= implode("\r\n\r\n", $parts);
		$xml		= simplexml_load_string($result);
		$orderStatus= $xml->orderStatus;
		$orderId 	= $xml->orderID;
		
		$gharpay = Mage::getModel('gharpay/gharpay')->getCollection()->addFieldToFilter('gharpay_order_id',$orderId);
		$data = $gharpay->getData();
		if (!$data[0]['id']) {
			return false;
			// No order found
		}
		$dateTime = date("Y-m-d H:i:s");
		$model = Mage::getModel('gharpay/gharpay')->load($data[0]['id']);
		$model->setOrderStatus($orderStatus);
		$model->setUpdateTime($dateTime);
		$model->save();
		if($orderStatus == 'Delivered')
		{
			if ($data[0]['order_id'] != null) {
				$salesModel = Mage::getModel('sales/order');
				$salesModel->loadByIncrementId($data[0]['order_id']);
			}
			if($salesModel->getStatus() == 'canceled')
			{
				$message = "Secont attempt to order canceled to Processing. For Order id==".$data[0]['order_id']." and Gharpay Order Id is==".$data[0]['gharpay_order_id'];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				mail('bhavik@craftsvilla.com', 'Second attempt to order canceled to Processing', $message, $headers);
			}
			else{
				$salesModel->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true,'',false);
				$salesModel->save();
				$message = "Order processing from gharpay. For Order id==".$data[0]['order_id']." and Gharpay Order Id is==".$data[0]['gharpay_order_id'];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				$to  = 'bhumi@craftsvilla.com' . ', ';
				$to .= 'sivakumar@craftsvilla.com' . ', ';
				$to .= 'sonali@craftsvilla.com' . ', ';
				$to .= 'kunal@craftsvilla.com';
				mail($to, 'Order processing from gharpay', $message, $headers);
			}
		}
		if($orderStatus == 'Failed' || $orderStatus == 'Cancelled by Customer' || $orderStatus == 'Invalid')
		{
			if ($data[0]['order_id'] != null) {
				$salesModel = Mage::getModel('sales/order');
				$salesModel->loadByIncrementId($data[0]['order_id']);
			}
			if($salesModel->getStatus() == 'processing')
			{
				$message = "Secont attempt to order Processing to canceled. For Order id==".$data[0]['order_id']." and Gharpay Order Id is==".$data[0]['gharpay_order_id'];
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: raf@craftsvilla.com' . "\r\n";
				mail('bhavik@craftsvilla.com', 'Second attempt to order Processing to canceled', $message, $headers);
			}else{
				$salesModel->cancel();
				$salesModel->save();
			}
		}
		return true;	
	}
	
	public static function viewOrderDetails($gharpayOrderID){
	
		$url = 'http://services.gharpay.in/rest/GharpayService/viewOrderDetails?orderID='.$gharpayOrderID;
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:@#*qqya3'));
		print_r($ch) ;exit;
		$response = curl_exec($ch);
	
		return $response;
	}
	
	public static function getAllPincodes(){
		$url = 'http://webservices.gharpay.in/rest/GharpayService/getAllPincodes';
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:pgf!5@%z'));
		//print_r($ch) ;
		$result = curl_exec($ch);
		$result 	= str_replace("\r\n\r\nHTTP/", "\r\nHTTP/", $result);
		$parts 		= explode("\r\n\r\n",$result);
		$headers 	= array_shift($parts);
		$result 	= implode("\r\n\r\n", $parts);
		$xml		= simplexml_load_string($result);
		return $xml;
		//print_r($xml);exit;
		//echo "\nResponse:\n".$response;
	}
	
	public static function isCityPresent($cityName){
		$url = 'http://services.gharpay.in/rest/GharpayService/isCityPresent?cityName='.$cityName;
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:@#*qqya3'));
		print_r($ch) ;
		$response = curl_exec($ch);
	
		echo "\nResponse:\n".$response;
	}
	
	public static function getCityList(){
		$url = 'http://webservices.gharpay.in/rest/GharpayService/getCityList';
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:pgf!5@%z'));
		print_r($ch) ;
		$response = curl_exec($ch);
		
		echo "\nResponse:\n".$response;
	}
	
	public static function isPincodePresent($pincode){
		$url = 'http://services.gharpay.in/rest/GharpayService/isPincodePresent?pincode='.$pincode;
		$ch = curl_init($url) ;
		curl_setopt($ch,CURLOPT_HEADER,true);
		curl_setopt($ch,CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($ch,CURLOPT_HTTPHEADER, array('username:craftsvilla_api','password:@#*qqya3'));
		print_r($ch) ;
		$response = curl_exec($ch);
	
		echo "\nResponse:\n".$response;
	}
	
}
?>