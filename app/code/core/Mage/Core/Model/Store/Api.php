<?php

class Mage_Core_Model_Store_Api extends Mage_Api_Model_Resource_Abstract {

	public function info() {
		$stores = Mage::getModel('core/store')->getCollection();
		$result = array();
		foreach ($stores as $store) {
			$store->getCode();
			$result[] = $store->toArray(array('store_id', 'code'));
		}
		return $result;
	}

	public function itmes() {
		$stores = Mage::getModel('core/store')->getCollection();
		$result = array();
		foreach ($stores as $store) {
			$store->getCode();
			$result[] = $store->toArray(array('store_id', 'code'));
		}
		return $result;
	}

}