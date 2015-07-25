<?php

class Mage_Core_Model_Website_Api extends Mage_Api_Model_Resource_Abstract {

	public function all() {
		$collection = Mage::getModel('core/website')->getCollection();
		$result = array();
		foreach ($collection as $website) {
			$result[] = array('website_id' => $website->getWebsiteId(), 'code' => $website->getCode(), 'name' => $website->getName());
		}
		return $result;
	}

}