<?php
class Craftsvilla_Courier_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getClientInfo()
      {
		$account=Mage::getStoreConfig('courier/general/account_number');
		$username=Mage::getStoreConfig('courier/general/user_name');
		$password=Mage::getStoreConfig('courier/general/password');
		$pin=Mage::getStoreConfig('courier/general/account_pin');
		$entity=Mage::getStoreConfig('courier/general/account_entity');
		$country_code=Mage::getStoreConfig('courier/general/account_country_code');
		return array(
			'AccountCountryCode'	=> $country_code,
			'AccountEntity'		 	=> $entity,
			'AccountNumber'		 	=> $account,
			'AccountPin'		 	=> $pin,
			'UserName'			 	=> $username,
			'Password'			 	=> $password,
			'Version'			 	=> 'v1.0',
			'Source'				=> 31
		);
      }
	  public function getWsdlPath(){
		$wsdlBasePath = Mage::getModuleDir('etc', 'Craftsvilla_Courier')  . DS . 'wsdl' . DS . 'Aramex2' . DS;
		return $wsdlBasePath;
	  }

}
