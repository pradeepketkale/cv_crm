<?php
 class Gharpay_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
 
	protected $_code  = 'gharpay_standard';
	
    protected $_formBlockType = 'gharpay/standard_form';

    protected $_isGateway               = false;
    protected $_isInitializeNeeded      = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_order = null;
    
    /**http://in.movies.yahoo.com/blogs/movie-reviews/agneepath-review-112926556.html
    *  Form block description
    *
    *  @return	 object
    */
   
    public function isAvailable($quote=null)
    {
    	if (is_null($quote)) {
           return true;
        }
        if(Mage::getStoreConfig('payment/gharpay_standard/active') == 0){
        	return false;
        }
        if(strlen($quote->getBillingAddress()->gettelephone()) > 10){
        	return false;
        }
        if(ctype_digit($quote->getBillingAddress()->gettelephone()) != 1){
        	return false;
        }
        
    	$pincode	= $quote->getShippingAddress()->getpostcode();
    	$zip		= Mage::getStoreConfig('payment/gharpay_standard/gharpay_is_allowed');
    	$val 		= explode(",",$zip);
    	if(!in_array($pincode, $val))
    	{
    		return false;
    	}
    	return true;
    }
/*
public function getOrderPlaceRedirectUrl()
{
//when you click on place order you will be redirected on this url, if you don't want this action remove this method
return Mage::getUrl('gharpay/index/redirect', array('_secure' => true));
}
 */
}