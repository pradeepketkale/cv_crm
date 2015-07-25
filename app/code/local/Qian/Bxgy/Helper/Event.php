<?php
class Qian_Bxgy_Helper_Event extends Mage_Core_Helper_Abstract {
	
	
	/**
	 * process the modified version of buy x get y sale rule
	 * @param $observer
	 */
	public function processBxgy($observer) {
		$rule = $observer->getRule();
		//the special rule name must start with '[bxgy]'
		if (strtolower(substr($rule->getName(), 0, 4)) != 'bxgy') {
			return;
		}
		//the rule in itself must be a buy x get y offer
		if ($rule->getSimpleAction() != 'buy_x_get_y') {
			return;
		}

		$validator = Mage::getSingleton('bxgy/validator')->initByRule($rule);
		if (!$validator->isWorthFurtherProcessing()) {
			return;
		}

		$validator->processBxgy($observer->getItem(), $observer->getResult());
	}
	
}