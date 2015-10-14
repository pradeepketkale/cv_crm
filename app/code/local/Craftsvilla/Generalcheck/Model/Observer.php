<?php
class Craftsvilla_Generalcheck_Model_Observer
{


public function crmTogetProductEventafter($observer) 
	{
	$productId = $observer->getEvent()->getProduct()->getId();
	$model= Mage::getStoreConfig('craftsvilla_config/service_api');
	$url=$model['host'].':'.$model['port'].'/productUpdateNotification';
    	$data = array("productId"=>$productId); 
	$data_string = json_encode($data);//print_r($data_string);exit;
	$handle = curl_init($url); 
	curl_setopt($handle, CURLOPT_POST, true);
	curl_setopt($handle, CURLOPT_POSTFIELDS, $data_string); 
	curl_setopt($handle, CURLOPT_HTTPHEADER, array							(                                                                          
	    'Content-Type: application/json',                                                                                
	    'Content-Length: ' . strlen($data_string))
	    );
	$response=curl_exec($handle);
	$msg=$url.'|'.$productId.'|'.$response;
	error_log($msg);
	$http_status_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

	if($http_status_code == 200){

			return true;

	}else{
		
			return false;
			
	}

	
	curl_close($handle);
		
	}         

}
?>
