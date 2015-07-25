<?php
class Craftsvilla_Sms_Model_Observer
{	
	public function hookToShippmentSaveEvent($observer)
	{
		$_order = $observer->getOrder();
		$_orderShippingCountry = $_order->getShippingAddress()->getCountryId();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		$_customerTelephone = $_order->getBillingAddress()->getTelephone();
		$orderId = $_order->getId();
		$shipment = Mage::getModel('sales/order_shipment');
		$_helpv = Mage::helper('udropship');
		$shippmentCollection = $shipment->getCollection()
									->addAttributeToFilter('order_id', $orderId);
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');

		
		foreach($shippmentCollection as $_shipment){
			$vendorIfo = '';
			$message = '';
			$vendorIfo = $_helpv->getVendor($_shipment->getUdropshipVendor());
			$_vendorTelephone = $vendorIfo->getTelephone();
			if($_orderShippingCountry!='IN')
				$message = 'Received new shipment: International, #'.$_shipment->getIncrementId().', Rs. '.number_format($_shipment->getBaseTotalValue(), 2, '.', '').'. Please ship immediately to Craftsvilla Warehouse in Mumbai.';
			else
				$message = 'Received new shipment: Domestic, #'.$_shipment->getIncrementId().', Rs. '.number_format($_shipment->getBaseTotalValue(), 2, '.', '').'. Please ship immediately to Customer directly.';
			$_smsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_vendorTelephone."&source=".$_smsSource."&message=".urlencode($message);
			$parse_url = file($_smsUrl);

			// Send SMS to customer
				if($_orderBillingCountry == 'IN'){
					$customerMessage = 'Thank you for your order. Shipment#: '.$_shipment->getIncrementId().' , Vendor Name: '.$vendorIfo->getVendorName().' - Craftsvilla.com (Customercare Email: customercare@craftsvilla.com)
';
					$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$_customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
					$parse_url = file($_customerSmsUrl);
				}
			///
		}
  	}
}
