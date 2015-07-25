<?php
/***
 * This class serves similar logic as Mage_SalesRule_Model_Validator,
 * but it does not extends any class.
 * 
 * This class is only interested in processing [bxgy] salesrule
 */
class Qian_Bxgy_Model_Validator   {
	/**
	 * rule status is a String whose value is one of the following:
	 * 'no_rule' rule does not exist, or not enabled, or not in valid format (it must be a buy_x_get_y rule and name starts with '[bxgy]'
	 * 'no_x'  rule exists, but no ENOUGH x product in cart
	 * 'has_x_no_y' rule exists, has ENOUGH x product in cart, but no y product in cart (This rule has no effects with items in cart, but I'd like self::$_redefinedX worked out to remind people order y product)
	 * 'has_x_has_y' rule exists, has ENOUGH x product and has y product, but free quota of y has not been used up
	 * 'has_x_usedup_y' rule exists, has ENOUGH x product and has y product, and free quota of y used up
	 * 'checking' if initByRule, rule status starts from 'checking' and remains 'checking' if it has not been FULLY checked yet
	 * 'not_checked' if initByScan, rule status starts from 'not_checked' then turn to 'no_x' or 'has_x_no_y' 
	 * null if not initialised either initByRule or initByScan
	 */
	protected static $_ruleStatus;

	//rule of special bxgy
	protected static $_rule;

	//product subselect in the conditions of the rule
	protected static $_productSubselect;

	//cache the product qty met conditions
	protected static $_cartXyQtyArray;
	
	//cache free qty used
	protected static $_freeQtyUsed = 0;
	
	/***
	 * This method constructs rule and productSubselec. If not, set ruleStatus to 'no_rule'
	 * However, even rule pass validation in constructor, it still could be invalid 
	 * (when x value is 0, then ruleStatus would be set to 'no_rule' later in _getCartXyQtyArray())
	 * 
	 * This method is triggered by processBxgy event, which means $rule has been validated before passed in.
	 * The possible $_ruleStatus after this method are one of the following:
	 * 'no_rule'
	 * 'has_x_has_y'
	 * 'has_x_usedup_y'
	 * 'checking'
	 *  
	 */
	public function initByRule($rule) {
		if (null !== self::$_ruleStatus) {
			//initiated before
			return $this;
		}
		self::$_ruleStatus = 'checking';
		return $this->_afterInit($rule);

	}
	
	/***
	 * (If you do not want to show offer reminder message, you do not need this method.)
	 * This method is similar to initByRule
	 * 
	 * This method serves as a backup initialisation if initByRule has not got a chance to initialise.
	 * It goes through rule lists to find the rule and then pick ProductSubselect out.
	 * The possible $_ruleStatus after this method are one of the following:
	 * 'no_rule'
	 * 'no_x'
	 * 'has_x_no_y'
	 * 'not_checked'
	 *  
	 */
	public function initByScan() {
		if (null !== self::$_ruleStatus) {
			//initiated before
			return $this;
		}
		
		$quote = $quote = Mage::getSingleton('checkout/cart')->getQuote();
		$store = Mage::app()->getStore($quote->getStoreId());
		
		$rules = Mage::getModel('salesrule/rule')
				->getCollection()
				//this does magento normal validation
				//but magento does not validate conditions and actions here (I do not need it be validated anyway)
				->setValidationFilter($store->getWebsiteId(), $quote->getCustomerGroupId(), $quote->getCouponCode())
				//additional validation
				->addFieldToFilter('name', array('like'=>'[bxgy]%'))
				->addFieldToFilter('simple_action', 'buy_x_get_y')
				->load()
		;
		foreach ($rules as $rule) {
			$rule->load($rule->getId());
			self::$_ruleStatus = 'not_checked';
			return $this->_afterInit($rule); //I only take first bxgy rule if exists
		}
		
		//bxgy rule not found
		self::$_ruleStatus = 'no_rule';
		return $this;
	}
	
	/***
	 * This method picks out ProductSubselect inside SalesRule.
	 * 
	 * @param $rule: a bxgy rule
	 */
	protected function _afterInit($rule) {
		if ($tmp = $rule->getConditions()) {  //Mage_SalesRule_Model_Rule_Condition_Combine
			if ($tmp = $tmp->getConditions()) { //array of Mage_SalesRule_Model_Rule_Condition_Product_Subselect
				if (is_array($tmp)) {
					//we agreed there should be only one subselect
					$tmp = $tmp[0];
					if ($tmp instanceof Mage_SalesRule_Model_Rule_Condition_Product_Subselect) {
						self::$_productSubselect = $tmp;
						//meaningless to store $rule if subslect is not vaid
						//so I store $rule to self::rule as last step
						self::$_rule = $rule;
						return $this;
					}
				} 		
			}
		}

		//in any other cases
		self::$_ruleStatus = 'no_rule';
		return $this;
	}

	/**
	 * This method is only called by event salesrule_validator_process
	 * @param unknown_type $item
	 * @param unknown_type $result
	 */
	public static function processBxgy($item, $result) {
		//calculate product quantity
		$xy = self::getCartXyQtyArray();
		if (false === $xy) {
			return ; //'no_rule'
		}
		
		$freeQty = $xy[1];

		if ($freeQty == self::$_freeQtyUsed) {
			self::$_ruleStatus = 'has_x_usedup_y';
			//quota already used up
			//do not simply return, do something
			//otherwise magento native buy x get y will take place
			$result
					->setDiscountAmount(0)
					->setBaseDiscountAmount(0);
		}
		elseif ($freeQty > self::$_freeQtyUsed) {
			
			$itemPrice  = $item->getDiscountCalculationPrice();
			if ($itemPrice !== null) {
				$baseItemPrice = $item->getBaseDiscountCalculationPrice();
			} else {
				$itemPrice = $item->getCalculationPrice();
				$baseItemPrice = $item->getBaseCalculationPrice();
			}

			$itemQty = $item->getQty();
			if ($itemQty <= $freeQty - self::$_freeQtyUsed) {
				$discountAmount = $itemQty * $itemPrice;
				$baseDiscountAmount = $itemQty * $baseItemPrice;
				self::$_freeQtyUsed += $itemQty;
				self::$_ruleStatus = 'has_x_has_y';
			}
			else {
				$discountAmount = ($freeQty - self::$_freeQtyUsed) * $itemPrice;
				$baseDiscountAmount = ($freeQty - self::$_freeQtyUsed) * $baseItemPrice;
				self::$_freeQtyUsed = $freeQty;
				self::$_ruleStatus = 'has_x_usedup_y';
			}
			$result
					->setDiscountAmount($discountAmount)
					->setBaseDiscountAmount($baseDiscountAmount);
		}
		else {// ($freeQty < self::$_freeQtyUsed)
			//this should never happen
			Mage::throwException(Mage::helper('bxgy')->__('Free quota more than allowed'));
		}
	}

	/***
	 * This method is inspired by Mage_SalesRule_Model_Rule_Condition_Product_Subselect::validate()
	 * 
	 * @return array of (how many x products in cart, how many y products can be free)
	 */
	protected static function _getCartXyQtyArray() {
		$quote = Mage::getSingleton('checkout/cart')->getQuote();

		$attr = self::$_productSubselect->getAttribute();
		$xQty = 0;
		foreach ($quote->getAllItems() as $item) {
			if (self::_combineValidate($item)) {
				$xQty += $item->getData($attr);
			}
		}
		
		$x = self::$_rule->getDiscountStep();
		$y = self::$_rule->getDiscountAmount();
		if (!$x) {
			self::$_ruleStatus = 'no_rule';
			return false; //eliminate the possibility of divided by 0
		}
		$yQty = intval(intval($xQty / $x) * $y);
		if (self::$_ruleStatus == 'not_checked') { //init(null)
			if ($yQty > 0) {
				self::$_ruleStatus = 'has_x_no_y';
			}
			else {
				self::$_ruleStatus = 'no_x'; //no ENOUGH x product
			}
		}
		return array($xQty, $yQty);
	}
	
	/***
	 * If I offer method to return $xQty or $yQty separately, 
	 * then it opens a possibility to get $yQty before $xQty is calculated.
	 * It is difficult to achieve -
	 * * when people use $yQty, ensure $xQty is calculated first
	 * * $xQty may be calculated before but what if no_rule
	 * * not be silly to calculate $xQty or $yQty multi times
	 * 
	 * For simplicity, always return xy in an array
	 */
	public static function getCartXyQtyArray() {
		if (null === self::$_cartXyQtyArray) {
			self::$_cartXyQtyArray = self::_getCartXyQtyArray();
		}
		return self::$_cartXyQtyArray;
	}
	

		
	/*
	 * This method is inspired by Mage_Rule_Model_Condition_Combine::validate()
	 */
	protected static function _combineValidate($item) {
		if (!self::$_productSubselect->getConditions()) {
			return true;
		}

		$all    = self::$_productSubselect->getAggregator() === 'all';
		$true   = (bool)self::$_productSubselect->getValue();

		foreach (self::$_productSubselect->getConditions() as $cond) {
			$validated = $cond->validate($item);

			if ($all && $validated !== $true) {
				return false;
			} elseif (!$all && $validated === $true) {
				return true;
			}
		}
		return $all ? true : false;
	}
	
	/***
	 * This method is only called before process() is called.
	 * @return true or false
	 */
	public static function isWorthFurtherProcessing() {
		switch (self::$_ruleStatus) {
			case 'no_rule':
				return false;
			default:
				//probably: has_x_has_y, has_x_usedup_y, checking
				//even has_x_usedup_y, it's still worth processing because we need to change original buy_x_get_y logic
				return true;
				//not possible: no_x, has_x_no_y
		}	
	}

	public function getRuleStatus() {
		return self::$_ruleStatus;
	}
	
	public function getFreeQtyUsed() {
		return self::$_freeQtyUsed;
	}
}