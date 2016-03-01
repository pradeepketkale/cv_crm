<?php


	class Craftsvilla_Retailpay_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract {
		
		
	  
		/**
		  * XML Pathes for configuration constants
		  */
		 const XML_PATH_PAYMENT_RETAILPAY_ACTIVE = 'payment/retailpay_standard/active';
		 const XML_PATH_PAYMENT_RETAILPAY_ORDER_STATUS = 'payment/retailpay_standard/order_status';
		 //const XML_PATH_PAYMENT_FREE_PAYMENT_ACTION = 'payment/free/payment_action';
	  
		/**
		 * Payment Method features
		 * @var bool
		 */
		protected $_canAuthorize                = true;
		protected $_canCapture                  = true;

	
	    protected $_code  = 'retailpay';
	
	  
		
		/**
		* Get config peyment action
		*
		* @return string
		*/
		
	   public function getConfigPaymentAction()
	   {
		   if ('processing' == $this->getConfigData('order_status')) {
			   return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
		   }
		   return parent::getConfigPaymentAction();
	   }
	
	}