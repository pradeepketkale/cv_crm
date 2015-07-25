<?php
class Craftsvilla_Currencypricing_Model_Observer
{	
	public function loadProductCollectionCurrencyPrice(Varien_Event_Observer $observer)
    {
		$mageStore = Mage::app()->getStore();
		if($mageStore->getCurrentCurrencyCode() != 'INR'){
			$collection = $observer->getEvent()->getCollection();
			if ($collection) {
		        foreach ($collection as $product) {
					if($product->getTypeId() == 'configurable'){
						$_productIndexedPrice = $product->getIndexedPrice() * 1.5;
						$product->setIndexedPrice($_productIndexedPrice);
					}
					$_productPrice = $product->getPrice();
					$_productFinalPrice = $product->getFinalPrice();
					$_productMinimalPrice = $product->getMinimalPrice();
					$_newProductPrice = $_productPrice * 1.5;
					$_newfinalPrice = $_productFinalPrice * 1.5;
					$_newminimalPrice = $_productMinimalPrice * 1.5;

					$product->setPrice($_newProductPrice);
					$product->setFinalPrice($_newfinalPrice);
					$product->setMinimalPrice($_newminimalPrice);
					if($_productSpecialPrice = $product->getSpecialPrice()){
						$_newProductSpecialPrice = $_productSpecialPrice * 1.5;
						$product->setSpecialPrice($_newProductSpecialPrice);
					}
		        }
			}
		}
	}
	public function loadProductCurrencyPrice(Varien_Event_Observer $observer)
    {
		$mageStore = Mage::app()->getStore();
		if($mageStore->getCurrentCurrencyCode() != 'INR'){
			$product = $observer->getEvent()->getProduct();
			if($product->getTypeId() == 'configurable')
				return true;
			$_productPrice = $product->getPrice();
			//$_productFinalPrice = $product->getFinalPrice();
			$_productMinimalPrice = $product->getMinimalPrice();

			$_newProductPrice = $_productPrice * 1.5;
			//$_newfinalPrice = $_productFinalPrice * 1.5;
			$_newminimalPrice = $_productMinimalPrice * 1.5;


			$product->setPrice($_newProductPrice);
			//$product->setFinalPrice($_newfinalPrice);
			$product->setMinimalPrice($_newminimalPrice);
			$_productIndexedPrice = $product->getIndexedPrice() * 1.5;
			$product->setIndexedPrice($_productIndexedPrice);
			if($_productSpecialPrice = $product->getSpecialPrice()){
				$_newProductSpecialPrice = $_productSpecialPrice * 1.5;
				$product->setSpecialPrice($_newProductSpecialPrice);
			}
		}
	}
}
