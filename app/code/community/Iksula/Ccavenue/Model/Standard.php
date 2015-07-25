<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Iksula_Ccavenue
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Ccavenue Standard Model
 *
 * @category   Mage
 * @package    Iksula_Ccavenue
 * @name       Iksula_Ccavenue_Model_Standard
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Iksula_Ccavenue_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'ccavenue_standard';
    protected $_formBlockType = 'ccavenue/standard_form';

    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    protected $_order = null;


    /**
     * Get Config model
     *
     * @return object Iksula_Ccavenue_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('ccavenue/config');
    }

    /**
     * Payment validation
     *
     * @param   none
     * @return  Iksula_Ccavenue_Model_Standard
     */
    public function validate()
    {
        parent::validate();
        $paymentInfo = $this->getInfoInstance();
        if ($paymentInfo instanceof Mage_Sales_Model_Order_Payment) {
            $currency_code = $paymentInfo->getOrder()->getBaseCurrencyCode();
        } else {
            $currency_code = $paymentInfo->getQuote()->getBaseCurrencyCode();
        }
       // if ($currency_code != $this->getConfig()->getCurrency()) {
         //   Mage::throwException(Mage::helper('ccavenue')->__('Selected currency //code ('.$currency_code.') is not compatabile with SecureEbs'));
       // }
        return $this;
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture (Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *  Returns Target URL
     *
     *  @return	  string Target URL
     */
    public function getCcavenueUrl ()
    {
        return 'https://www.ccavenue.com/shopzone/cc_details.jsp';
    }

    /**
     *  Return URL for Ccavenue success response
     *
     *  @return	  string URL
     */
    protected function getSuccessURL ()
    {
        return Mage::getUrl('ccavenue/standard/success', array('_secure' => true));
    }

    /**
     *  Return URL for Ccavenue notification
     *
     *  @return	  string Notification URL
     */
    protected function getNotificationURL ()
    {
        return Mage::getUrl('ccavenue/standard/notify', array('_secure' => true));
    }

    /**
     *  Return URL for Ccavenue failure response
     *
     *  @return	  string URL
     */
    protected function getFailureURL ()
    {
        return Mage::getUrl('ccavenue/standard/failure', array('_secure' => true));
    }

    /**
     *  Form block description
     *
     *  @return	 object
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('ccavenue/form_standard', $name);
        $block->setMethod($this->_code);
        $block->setPayment($this->getPayment());

        return $block;
    }

    /**
     *  Return Order Place Redirect URL
     *
     *  @return	  string Order Redirect URL
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('ccavenue/standard/redirect');
    }

    /**
     *  Return Standard Checkout Form Fields for request to Ccavenue
     *
     *  @return	  array Array of hidden form fields
     */
    public function getStandardCheckoutFormFields ()
    {
        $order = $this->getOrder();
        if (!($order instanceof Mage_Sales_Model_Order)) {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }

        $billingAddress = $order->getBillingAddress();
		$shippingAddress = $order->getShippingAddress();
		
        $streets = $billingAddress->getStreet();
        $street = isset($streets[0]) && $streets[0] != ''
                  ? $streets[0]
                  : (isset($streets[1]) && $streets[1] != '' ? $streets[1] : '');

        if ($this->getConfig()->getDescription()) {
            $transDescription = $this->getConfig()->getDescription();
        } else {
            $transDescription = Mage::helper('ccavenue')->__('Order #%s', $order->getRealOrderId());
        }

        if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } elseif ($billingAddress->getEmail()) {
            $email = $billingAddress->getEmail();
        } else {
            $email = '';
        }

		/*
			USD currency conversion code to INR
			Harpreet Singh
		*/
		$currencyCode = Mage::app()->getStore()-> getCurrentCurrencyCode();
		//$productPrice =  $order->getBaseGrandTotal();
		/*echo "prod pr".$productPrice;exit;
		//echo "Currency code".$currencyCode;exit;
		if($currencyCode == 'USD'){
			$url = "http://www.google.co.in/ig/calculator?hl=en&q=1USD=?INR";
			 $ch = curl_init();
			 $timeout = 0;
			 curl_setopt ($ch, CURLOPT_URL, $url);
			 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			 curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT  6.1)");
			 curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			 $rawdata = curl_exec($ch);
			 curl_close($ch);
			 $data = explode('"', $rawdata);
			 $data = explode(' ', $data['3']);
			 $var = $data['0'];
			 $INRval = round($var,3);
			 echo "INR Value"."<br>".$INRval;
			 
			 $productPrice = $productPrice*$INRval;
			 
			 echo "product price after"."</br>".$productPrice;exit;
		}*/
		
		/*
			USD currency conversion code to INR
			code ends here
		*/
     $fields = array(
						'account_id'       => $this->getConfig()->getAccountId(),
                       	'return'           => Mage::getUrl('ccavenue/standard/success',array('_secure' => true)),
                        'product_name'     => $transDescription,
                        'product_price'    => $order->getBaseGrandTotal(),
                       // 'product_price'    => $productPrice, // USD currency conversion 
                        'language'         => $this->getConfig()->getLanguage(),
                        'f_name'           => $billingAddress->getFirstname(),
                        's_name'           => $billingAddress->getLastname(),
                        'street'           => $street,
                        'city'             => $billingAddress->getCity(),
                        'state'            => $billingAddress->getRegionModel()->getCode(),
                        'zip'              => $billingAddress->getPostcode(),
                        'country'          => $billingAddress->getCountryModel()->getIso3Code(),
                        'phone'            => $billingAddress->getTelephone(),
                        'email'            => $email,
                        'cb_url'           => $this->getNotificationURL(),
                        'cb_type'          => 'P', // POST method used (G - GET method)
                        'decline_url'      => $this->getFailureURL(),
                      	'cs1'              => $order->getRealOrderId()               );
						
		
		//echo '<pre>';print_r($fields);exit;			
					
					
		$streets = $shippingAddress->getStreet();
        $street = isset($streets[0]) && $streets[0] != ''
                  ? $streets[0]
                  : (isset($streets[1]) && $streets[1] != '' ? $streets[1] : '');

        if ($this->getConfig()->getDescription()) {
            $transDescription = $this->getConfig()->getDescription();
        } else {
            $transDescription = Mage::helper('ccavenue')->__('Order #%s', $order->getRealOrderId());
        }

        if ($order->getCustomerEmail()) {
            $email = $order->getCustomerEmail();
        } elseif ($shippingAddress->getEmail()) {
            $email = $shippingAddress->getEmail();
        } else {
            $email = '';
        }			
		//print_r($billingAddress->getRegionModel());
		//print_r(get_class_methods($billingAddress->getRegionModel()));exit;
		$addrArr = $shippingAddress->getStreet();
		$shippingAdd = $addrArr[0].' '.$addrArr[1];
		$fields = array(
						'Merchant_Id'       => $this->getConfig()->getAccountId(),
						'Redirect_Url' => Mage::getUrl('ccavenue/standard/success',array('_secure' => true)),
                        'billing_cust_address'           => $street,
                        'billing_cust_city'             => $billingAddress->getCity(),
                        'billing_cust_state'            => $billingAddress->getRegionModel()->getName(),
                        'billing_zip_code'              => $billingAddress->getPostcode(),
                        'billing_cust_country'          => $billingAddress->getCountryModel()->getName(),
                        'billing_cust_tel'            =>    $billingAddress->getTelephone(),
                        'billing_cust_email'            => $email,
						
						'billing_cust_name'           => $order->getcustomer_firstname().' '.$order->getcustomer_middlename().' '.$order->getcustomer_lastname().' ',
						'delivery_cust_name'           => $order->getcustomer_firstname().' '.$order->getcustomer_middlename().' '.$order->getcustomer_lastname().' ',
                        'delivery_cust_address'             => $shippingAdd,
                        'delivery_cust_country'            => $shippingAddress->getCountryModel()->getName(),
                        'delivery_cust_state'              => $shippingAddress->getRegionModel()->getName(),
                        'delivery_cust_tel'          => $shippingAddress->getTelephone(),
                        //'delivery_cust_notes'            => $shippingAddress->getTelephone(),
                        'delivery_cust_city'            => $shippingAddress->getCity(),
						'delivery_zip_code'          => $shippingAddress->getPostcode(),
						
                        /*'cb_url'           => $this->getNotificationURL(),
                        'cb_type'          => 'P', // POST method used (G - GET method)
                        'decline_url'      => $this->getFailureURL(),
						'product_name'     => $transDescription,
                        'product_price'    => $order->getBaseGrandTotal(),
                        'language'         => $this->getConfig()->getLanguage(),
                        'f_name'           => $billingAddress->getFirstname(),
                        's_name'           => $billingAddress->getLastname(),*/
                      	'Order_Id'              => $order->getRealOrderId()               );		
		//echo '<pre>';
		//print_r($fields);exit;
		
        if ($this->getConfig()->getDebug()) {
            $debug = Mage::getModel('ccavenue/api_debug')
                ->setRequestBody($this->getCcavenueUrl()."\n".print_r($fields,1))
                ->save();
            $fields['cs2'] = $debug->getId();
        }

        return $fields;
    }

}