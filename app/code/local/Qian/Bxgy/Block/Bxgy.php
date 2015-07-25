<?php
/***
 * This Block class is not a usual one - it does not output itself.
 * 
 * The purpose is to trigger a layout update with a certain url, e.g. /checkout/cart/index
 * then to add a message to messagesblock.
 */
class Qian_Bxgy_Block_Bxgy extends Mage_Core_Block_Template
{
	const XML_PATH_BXGY_ENABLE_REMINDER = 'qian_bxgy/general/enable_reminder';
	const XML_PATH_BXGY_REMINDER_TEMPLATE = 'qian_bxgy/general/reminder_template';
	const XML_PATH_BXGY_YQTY_UNIT = 'qian_bxgy/general/yqty_unit';
	const XML_PATH_BXGY_YQTY_UNIT_MORE = 'qian_bxgy/general/yqty_unit_more';
	
	public function _prepareLayout()
    {
    	if (!Mage::getStoreConfig(self::XML_PATH_BXGY_ENABLE_REMINDER)) {
    		return $this;
    	}
    	if ($message = $this->getReminderMessage()) {
    		$this->getMessagesBlock()->addNotice($message);
    	}
		return $this;
    }
    
    

	/**
	 * This method composes a reminder message ideally shown in cart
	 * 
	 * If processBxgy event is not triggered, bxgy validator singleton has not been initialised before.
	 * So I use initByScan to inilialise validator.
	 * 
	 * It is safe to use initByScan even validator has been initialsed before because initByScan will do a simple return in this case.
	 *
	 * @return String
	 */
	public function getReminderMessage() {
		$validator = Mage::getSingleton('bxgy/validator')->initByScan();
		
		if ($validator->getRuleStatus() ==  'no_rule') {
				return '';
		}
		
		$xy = $validator->getCartXyQtyArray();
		
		if (false === $xy) {
			return ''; //'no_rule'
		}
		
		$freeQty = $xy[1];
		if (0 == $freeQty) {
			return '';
		}
		
		$freeQtyUsed = $validator->getFreeQtyUsed();
		if ($freeQty <= $freeQtyUsed) {
			return '';
		}
		
		$template = Mage::getStoreConfig(self::XML_PATH_BXGY_REMINDER_TEMPLATE);
		
		if (0 == $freeQtyUsed) {
			$howManyPacks = $freeQty . ' ' . Mage::getStoreConfig(self::XML_PATH_BXGY_YQTY_UNIT);
		}
		else {
			$howManyPacks = ($freeQty - $freeQtyUsed) . ' ' . Mage::getStoreConfig(self::XML_PATH_BXGY_YQTY_UNIT_MORE);
		}
		return Mage::helper('bxgy')->__($template, $howManyPacks);		
	}
    
}