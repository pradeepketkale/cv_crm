<?php
class Craftsvilla_Fedexcourier_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getClientInfo()
      {
		//$key = Mage::getStoreConfig('courier/fedexcrd/fedex_key');
		$key = 'XK68y5alYioKJtVp';
		//$account_number = Mage::getStoreConfig('courier/fedexcrd/account_number');
		$account_number = '510087240';
		//$meter_number = Mage::getStoreConfig('courier/fedexcrd/meter_number');
		$meter_number = '118667471';
		//$password = Mage::getStoreConfig('courier/fedexcrd/password');
		$password = '3AcEDkewdYWc7O3IiWB0yzEUd';
		
		return array(
				'Key' 					=> $key, 
				'Password' 				=> $password,
				'AccountNumber'		 	=> $account_number,
				'MeterNumber'		 	=> $meter_number,
			
		);
      }
	  public function getWsdlPath(){
		$wsdlBasePath = Mage::getModuleDir('etc', 'Craftsvilla_Fedexcourier')  . DS . 'wsdl' . DS . 'fedex' . DS;
		return $wsdlBasePath;
	  }

}
