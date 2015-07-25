<?php
/******************************************************
 * @package MT OneStepCheckOut module for Magento 1.4.x.x and Magento 1.5.x.x
 * @version 1.5.2.1
 * @author http://www.magentheme.com
 * @copyright (C) 2011- MagenTheme.Com
 * @license PHP files are GNU/GPL
*******************************************************/
?>
<?php
class MagenThemes_MTOneStepCheckout_IndexController extends Mage_Checkout_Controller_Action
{
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('dob'));
        return $data;
    }

    public function indexAction()
    {
	if(!Mage::helper('mtonestepcheckout')->isActive()) {
	    $this->_redirect('checkout/onepage');
	    return;
	}

	$quote = $this->getOnepage()->getQuote();
	if(!$quote->hasItems() || $quote->getHasError()) {
	    $this->_redirect('checkout/cart');
	    return;
	}

	/*if (!$quote->validateMinimumAmount()) {
	    $error = Mage::getStoreConfig('sales/minimum_order/error_message');
	    Mage::getSingleton('checkout/session')->addError($error);
	    $this->_redirect('checkout/cart');
	    return;
	}*/

	if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
	    $this->getOnepage()->saveCheckoutMethod(Mage_Sales_Model_Quote::CHECKOUT_METHOD_GUEST);
	}

	if(!count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses())){
	    $defaultCountry = Mage::getStoreConfig('general/country/default');
	    if(!$quote->getBillingAddress()->getCountryId()) {
		$quote->getBillingAddress()->setCountryId($defaultCountry)->save();
	    }
	    
	    if(!$quote->getShippingAddress()->getCountryId()) {
		$quote->getShippingAddress()->setCountryId($defaultCountry)->save();
	    }
	}

	Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
	Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
	$this->getOnepage()->initCheckout();   
	
	//Below all lines of functions commented By Dileswar for stop queries of default values on dated 23-01-2014
	
	//save default Zip/Postal Code
	/*if($defaultPostcode = Mage::helper('mtonestepcheckout')->defaultZipCode()) {
	    if(!$quote->getBillingAddress()->getPostcode()) {
		$quote->getBillingAddress()->setPostcode($defaultPostcode)->save();
	    }
	    if(!$quote->getShippingAddress()->getPostcode()) {
		$quote->getShippingAddress()->setPostcode($defaultPostcode)->save();
	    }
	}*/
	//save default shipping method if the shipping method of cart is null
	/*if($defaultShippingMethod = Mage::helper('mtonestepcheckout')->defaultShippingMethod()) {
	    if(!$quote->getShippingAddress()->getShippingMethod()) {
		$quote->getShippingAddress()->setShippingMethod($defaultShippingMethod)->save();
		$this->getOnepage()->saveShippingMethod($defaultShippingMethod);
	    }
	}*/
	//save default payment method if the payment method of cart is null
	/*if($defaultPaymentMethod = Mage::helper('mtonestepcheckout')->defaultPaymentMethod()) {
	    if(!$quote->getPayment()->getMethod()) {
		$quote->getPayment()->setMethod($defaultPaymentMethod)->save();
		$quote->collectTotals()->save();
	    }
	}*/
	$this->loadLayout();
	$this->_initLayoutMessages('customer/session');
	//$this->getLayout()->getBlock('head')->setTitle($this->__('One Step Checkout'));
	$this->getLayout()->getBlock('head')->setTitle($this->__(Mage::helper('mtonestepcheckout')->checkoutTitle()));
	$this->renderLayout();
    }

    public function loginPostAction()
    {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $session = Mage::getSingleton('customer/session');
	$message = '';
	$result = array();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost();
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    $message = $e->getMessage();
                }
            } else {
                $message = $this->__('Login and password are required');
            }
        }
		
	if($message) {
	    $result['error'] = $message;
	} else {
	    $result['redirect'] = 1;
	}
	
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function reloadReviewAction() {
	$cart = Mage::getSingleton('checkout/cart')->getItemsCount();
	if($cart == 0){
		return;
	}
	if($this->_expireAjax()) {
	    return;
	}

	$result = array();
	try{
	    $result['new'] = $this->_getReviewHtml();
	} catch(Exception $e) {
	    $result['error'] = $e->getMessage();
	}
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	
    public function reloadReviewhAction() {
		$cart = Mage::getSingleton('checkout/cart')->getItemsCount();
		if($cart == 0){
			return;
		}
		if($this->_expireAjax()) {
			return;
		}
		$method = $_POST['method'];
		if(strlen($method) > 0){
			$q = $this->getQuote();
			$this->getQuote()->getPayment()->setMethod($method)->save();
			$this->getQuote()->collectTotals()->save();
		
		}
		//$result['new'] = $this->_getReviewHtml();
		
		//echo $this->getQuote()->getPayment()->getName();exit;
		//echo json_encode($result);exit;
		
		
		//print_r();
		//print_r(get_class_methods($q));exit;
		//print_r($result['new']);exit;
	
		
		

		$result = array();
		try{
			$_colspan = Mage::helper('tax')->displayCartBothPrices() ? 5 : 3;
			//$result['new1'] = $this->renderTotals(null, $_colspan).$this->renderTotals('footer', $_colspan); 
			$result['newreview'] = $this->_getReviewHtml();
		} catch(Exception $e) {
			$result['error'] = $e->getMessage();
		}
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	
	/*
	-------------------------------------------------------------------------------------------TOTALS FUNCTIONS
	*/

	protected $_totalRenderers;
    protected $_defaultRenderer = 'checkout/total_default';
    protected $_totals = null;

    public function getTotals()
    {
        //if (is_null($this->_totals)) {
           // return $this->getOnepage()->getQuote()->getTotals();
            return $this->getQuote()->getTotals();
        //}
        //return $this->_totals;
    }

    public function setTotals($value)
    {
        $this->_totals = $value;
        return $this;
    }

    protected function _getTotalRenderer($code)
    {
        $blockName = $code.'_total_renderer';
        $block = $this->getLayout()->getBlock($blockName);
        if (!$block) {
            $block = $this->_defaultRenderer;
            $config = Mage::getConfig()->getNode("global/sales/quote/totals/{$code}/renderer");
            if ($config) {
                $block = (string) $config;
            }

            $block = $this->getLayout()->createBlock($block, $blockName);
        }
        /**
         * Transfer totals to renderer
         */
        $block->setTotals($this->getTotals());
        return $block;
    }

    public function renderTotal($total, $area = null, $colspan = 1)
    {
        $code = $total->getCode();
        if ($total->getAs()) {
            $code = $total->getAs();
        }
        return $this->_getTotalRenderer($code)
            ->setTotal($total)
            ->setColspan($colspan)
            ->setRenderingArea(is_null($area) ? -1 : $area)
            ->toHtml();
    }

    /**
     * Render totals html for specific totals area (footer, body)
     *
     * @param   null|string $area
     * @param   int $colspan
     * @return  string
     */
    public function renderTotals($area = null, $colspan = 1)
    {
        $html = '';
        foreach($this->getTotals() as $total) {
            if ($total->getArea() != $area && $area != -1) {
                continue;
            }
            $html .= $this->renderTotal($total, $area, $colspan);
        }
        return $html;
    }
	
	/*
	------------------------------------------------------------------------------------------------------------------------------------------------------------------
	*/
	
    public function switchMethodAction() {
	if($this->_expireAjax()) {
	    return;
	}

	$method = $this->getRequest()->getPost('method');
	if($this->getRequest()->isPost() && $method)
	    $this->getOnepage()->saveCheckoutMethod($method);
	}

	public function reloadPaymentAction() {
	if($this->_expireAjax()) {
	    return;
	}

	$result = array();
	try{
	    $result['payment'] = $this->_getPaymentMethodsHtml();
	} catch(Exception $e) {
	    $result['error'] = $e->getMessage();
	}
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function getAddressAction() {
	if ($this->_expireAjax()) {
            return;
        }
		
        $addressId = $this->getRequest()->getParam('address', false);
        if ($addressId) {
            $address = $this->getOnepage()->getAddress($addressId);
			
            if (Mage::getSingleton('customer/session')->getCustomer()->getId() == $address->getCustomerId()) {
                $this->getResponse()->setHeader('Content-type', 'application/x-json');
                $this->getResponse()->setBody($address->toJson());
            } else {
                $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
            }
        }
    }

    public function saveBillingAction() {
	if ($this->_expireAjax()) {
            return;
        }
		
	$result = array();
	$data = $this->getRequest()->getPost();
	if($data) {
	    if (!$this->getOnepage()->getQuote()->isVirtual()) {
		if($data['use_for'] == 'billing') {
		    Mage::getSingleton('core/session')->setData('use_for_shipping', false);
		    try{
			$billingAddress = $this->getQuote()->getBillingAddress()
				->setCountryId($data['country_id'])
				->setPostcode($data['postcode']);
			if(isset($data['region']) && $data['region']) {
			    $billingAddress->setRegion($data['region']);
			}
			if(isset($data['region_id']) && $data['region_id']) {
			    $billingAddress->setRegionId($data['region_id']);
			}
			
			$billingAddress->save();
			
			$shippingAddress = $this->getQuote()->getShippingAddress()
				->setCountryId($data['country_id'])
				->setPostcode($data['postcode']);
			if(isset($data['region']) && $data['region']) {
			    $shippingAddress->setRegion($data['region']);
			}
			
			if(isset($data['region_id']) && $data['region_id']) {
			    $shippingAddress->setRegionId($data['region_id']);
			}
			
			$shippingAddress->save();
			$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
			$result['shippingMethod'] = $this->_getShippingMethodsHtml();
			$result['payment'] = $this->_getPaymentMethodsHtml();
		    } catch(Exception $e) {
			    $result['error'] = $e->getMessage();
		    }
		} else {
		    Mage::getSingleton('core/session')->setData('use_for_shipping', true);
		    try {
			$billingAddress = $this->getQuote()->getBillingAddress()
				->setCountryId($data['country_id'])
				->setPostcode($data['postcode']);
			if(isset($data['region']) && $data['region']) {
			    $billingAddress->setRegion($data['region']);
			}
			
			if(isset($data['region_id']) && $data['region_id']) {
			    $billingAddress->setRegionId($data['region_id']);
			}
			
			$billingAddress->save();
			
			$result['payment'] = $this->_getPaymentMethodsHtml();
		    } catch(Exception $e) {
			$result['error'] = $e->getMessage();
		    }
		}
	    } else {
		try{
		    $billingAddress = $this->getQuote()->getBillingAddress()
			    ->setCountryId($data['country_id'])
			    ->setPostcode($data['postcode']);
		    if(isset($data['region']) && $data['region']) {
			$billingAddress->setRegion($data['region']);
		    }
		    if(isset($data['region_id']) && $data['region_id']) {
			$billingAddress->setRegionId($data['region_id']);
		    }
		    
		    $billingAddress->save();
		    $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
		    $result['payment'] = $this->_getPaymentMethodsHtml();
		} catch(Exception $e) {
		    $result['error'] = $e->getMessage();
		}
	    }
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
    }

    public function saveShippingAction() {
	if ($this->_expireAjax()) {
	    return;
	}
	
	Mage::getSingleton('core/session')->setData('use_for_shipping', true);
	$result = array();
	$data = $this->getRequest()->getPost();
	if($data) {
	    try{
		$shippingAddress = $this->getQuote()->getShippingAddress()
				->setCountryId($data['country_id'])
				->setPostcode($data['postcode']);
		if(isset($data['region']) && $data['region']) {
			$shippingAddress->setRegion($data['region']);
		}
		if(isset($data['region_id']) && $data['region_id']) {
			$shippingAddress->setRegionId($data['region_id']);
		}
		
		$shippingAddress->save();
		$this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
		$result['shippingMethod'] = $this->_getShippingMethodsHtml();
		$result['payment'] = $this->_getPaymentMethodsHtml();
	    } catch(Exception $e) {
		$result['error'] = $e->getMessage();
	    }
	}

	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveShippingMethodAction() {
	if($this->_expireAjax()) {
	    return;
	}
	
	$result = array();
	$data = $this->getRequest()->getPost();
	if($data) {
	    try{
		$return = $this->getOnepage()->saveShippingMethod($data['shipping_method']);
		
		//var_dump($return);exit;
		
		if(!$return) {
		    Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request'=>$this->getRequest(), 'quote'=>$this->getOnepage()->getQuote()));
		    $this->getOnepage()->getQuote()->collectTotals();
		}
		$this->getOnepage()->getQuote()->collectTotals()->save();
		$result['payment'] = $this->_getPaymentMethodsHtml();
		$result['review'] = $this->_getReviewHtml();
	    } catch(Exception $e) {
		$result['error'] = $e->getMessage();
	    }
	}
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function savePaymentAction() {
	if($this->_expireAjax()) {
	    return;
	}
	
	$result = array();
	$data = $this->getRequest()->getPost();
	if($data) {
	    try{
		$this->getQuote()->getPayment()->setMethod($data['method'])->save();
		$this->getQuote()->collectTotals()->save();
		$result['review'] = $this->_getReviewHtml();
	    } catch(Exception $e) {
		$result['error'] = $e->getMessage();
	    }
	}
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function couponAction() {
	$result = array();
	if (!$this->getQuote()->getItemsCount()) {
	    $result['redirect'] = Mage::getUrl('checkout/cart');
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    return;
	}

	$couponCode = (string) $this->getRequest()->getParam('coupon_code');
	
$couponcodeSub = strtoupper(substr($couponCode,0,3));
		if($couponcodeSub == 'NCA'){
			$couponCode = 'NCA1111';
		}

	if ($this->getRequest()->getParam('remove') == 1) {
	    $result['coupon'] = 'remove';
	    $couponCode = '';
	} else {
	    $result['coupon'] = 'add';
	}
	
	$oldCouponCode = $this->getQuote()->getCouponCode();

	if (!strlen($couponCode) && !strlen($oldCouponCode)) {
	    return;
	}

	try {
	    $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
	    $this->getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
		    ->collectTotals()
		    ->save();

	    if ($couponCode) {
		if ($couponCode != $this->getQuote()->getCouponCode()) {
		    $result['error'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		    return;
		}
	    }
	    $result['review'] = $this->_getReviewHtml();
	} catch (Mage_Core_Exception $e) {
	    $result['error'] = $e->getMessage();
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	} catch (Exception $e) {
	    $result['error'] = $this->__('Can not apply coupon code.');
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
	
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    //Update 1.4.1.4
    public function updateQtyAction() {
	$result = array();
	try {
	    $this->_getSession()->setCartWasUpdated(true);
	    $cartData = $this->getRequest()->getParam('cart');
	    if (is_array($cartData)) {
		$filter = new Zend_Filter_LocalizedToNormalized(
		    array('locale' => Mage::app()->getLocale()->getLocaleCode())
		);
		foreach ($cartData as $index => $data) {
		    if (isset($data['qty'])) {
			$cartData[$index]['qty'] = $filter->filter($data['qty']);
		    }
		}
		$cart = $this->_getCart();
		if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
		    $cart->getQuote()->setCustomerId(null);
		}
		$cart->updateItems($cartData)
			->save();
	    }
	    $this->_getSession()->setCartWasUpdated(false);
	    if(!$cart->getItemsQty()) {
		$result['redirect'] = Mage::getUrl('checkout/cart');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    }
	    $result['totalQty'] = $cart->getItemsQty();
	} catch (Mage_Core_Exception $e) {
	    $result['error'] = $e->getMessage();
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    return;
	} catch (Exception $e) {
	    $result['error'] = $this->__('Cannot update shopping cart.');
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	    return;
	}
	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function saveOrderAction() {
	if ($this->_expireAjax()) {
            return;
        }
	if ($this->getRequest()->isPost()) {
	    $result = array();
	    
	    //save Coupon Code
	    $couponCode = $this->getRequest()->getPost('coupon_code');
	    if(strlen($couponCode)) {
		try{
		    $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
		    $this->getQuote()->setCouponCode($couponCode)
		    ->collectTotals()
		    ->save();

		    if ($couponCode != $this->getQuote()->getCouponCode()) {
			    $result['error'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
			    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			    return;
		    }
		} catch (Mage_Core_Exception $e) {
		    $result['error'] = $e->getMessage();
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		} catch (Exception $e) {
		    $result['error'] = $this->__('Can not apply coupon code.');
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		}
	    }

	    try{
		//save Payment
		$paymentData = $this->getRequest()->getPost('payment', array());
		//print_r($_POST);exit;
		$resultPayment = $this->getOnepage()->savePayment($paymentData);

		$redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
		if(isset($resultPayment['error'])) {
		    $result['error'] = $resultPayment['error'];
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		    return;
		}

		if ($redirectUrl) {
		    $result['redirect'] = $redirectUrl;
		}

	    } catch (Mage_Payment_Exception $e) {
		if ($e->getFields()) {
		    $result['fields'] = $e->getFields();
		}
		$result['error'] = $e->getMessage();
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    } catch (Mage_Core_Exception $e) {
		$result['error'] = $e->getMessage();
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    } catch(Exception $e) {
		Mage::logException($e);
		$result['error'] = $this->__('Unable to set Payment Method.');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    }
	    //save BillingAddres
	    $billingPostData = $this->getRequest()->getPost('billing', array());
	    $billingData = $this->_filterPostData($billingPostData);
	    $billingAddressId = $this->getRequest()->getPost('billing_address_id', false);
	    if (isset($billingData['email'])) {
		$billingData['email'] = trim($billingData['email']);
	    }

	    $resultBilling = $this->getOnepage()->saveBilling($billingData, $billingAddressId);
	    if(isset($resultBilling['error'])) {
		$result['error'] = $resultBilling['message'];
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    }
	    if (!$this->getOnepage()->getQuote()->isVirtual()) {
		//save Shipping Method
		//$shippingMethodData = $this->getRequest()->getPost('shipping_method', '');
		$shippingMethodData = $this->getRequest()->getPost('shipping_method', '') ? $this->getRequest()->getPost('shipping_method', '') : 'udropship_Per Item Shipping'; //Edited
		
		$resultShippingMethod = $this->getOnepage()->saveShippingMethod($shippingMethodData);
		$this->getQuote()->collectTotals();
		$this->getQuote()->save();
		if(isset($resultShippingMethod['error'])) {
		    $result['error'] = $resultShippingMethod['error'];
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		    return;
		}
	    }
	    


	    if(isset($billingData['use_for_shipping']) && $billingData['use_for_shipping'] == 2) {
		//save ShippingAddress
		$shippingData = $this->getRequest()->getPost('shipping', array());
		$shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
		$resultShipping = $this->getOnepage()->saveShipping($shippingData, $shippingAddressId);
		
		if(isset($resultShipping['error'])) {
		    $result['error'] = $resultShipping['message'];
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		    return;
		}
	    } else {
		$resultShipping = $this->getOnepage()->saveShipping($billingData, $billingAddressId);
		if(isset($resultShipping['error'])) {
		    $result['error'] = $resultShipping['message'];
		    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		    return;
		}
	    }
				
		
	    try {
		if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
		    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
		    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
			$result['error'] = $this->__('Please agree to all Terms and Conditions before placing the order.');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
			return;
		    }
		}
		if ($data = $this->getRequest()->getPost('payment', false)) {
		    $this->getOnepage()->getQuote()->getPayment()->importData($data);
		}
		$this->getOnepage()->saveOrder();
		$redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
		$result['success'] = true;
	    } catch (Mage_Core_Exception $e) {
		Mage::logException($e);
		Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
		$this->getOnepage()->getQuote()->save();
		$result['error'] = $e->getMessage();
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    } catch (Exception $e) {
		Mage::logException($e);
		Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
		$this->getOnepage()->getQuote()->save();
		$result['error'] = $this->__('There was an error processing your order. Please contact us or try again later.');
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		return;
	    }
	    				
		
	    if($redirectUrl) {
		$result['redirect'] = $redirectUrl;
	    }
	    //fix on magento 1.4.1.1
	    $this->getOnepage()->getQuote()->save();

	    Mage::getSingleton('core/session')->unsetData('use_for_shipping');
	    if(isset($billingData['is_subscribed']) && $billingData['is_subscribed'] == 1) {
		Mage::getSingleton('customer/session')->getCustomer()->setIsSubscribed($billingData['is_subscribed'])->save();
	    }
	    
	    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	}
    }

    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    protected function _getReviewHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $update->load('checkout_onestep_review');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }


    private function getOnepage() {
	return Mage::getSingleton('checkout/type_onepage');
    }

    private function getCheckout() {
	return $this->getOnepage()->getCheckout();
    }

    private function getQuote() {
	return $this->getCheckout()->getQuote();
    }
    //update on version 1.4.1.4
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
	
/*
		##################################################################################################################################
		######### New Functions for mcheckout module
	*/
	
	/*
		Ajax method called in mcheckout module for forgot password
	*/
	
	public function forgotPasswordPostAjaxAction(){
		$email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
               // $this->_getSession()->setForgottenEmail($email);
				echo 'Invalid email address.';
                //$this->_getSession()->addError($this->__('Invalid email address.'));
               // $this->_redirect('*/*/forgotpassword');
                exit;
            }

            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($email);

            if ($customer->getId()) {
                try {
					 //$customer->setPassword($customer->generatePassword($passwordLength))->save();
					 
			/////few lines added for forgot password on Dated 06-02-2013#############///////////////////////
					$newPassword = $customer->generatePassword();
                    $customer->changePassword($newPassword, false);
					$customer->sendPasswordReminderEmail();
                    //$newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    //$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    //$customer->sendPasswordResetConfirmationEmail();
                } catch (Exception $exception) {
                    //$this->_getSession()->addError($exception->getMessage());
                    //$this->_redirect('*/*/forgotpassword');
                   // return;
					echo $exception->getMessage();exit;
                }
            }
          //  $this->_getSession()->addSuccess(Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email)));
			echo 'success';exit;	
            //$this->_redirect('*/*/');
            //return;
        } else {
          //  $this->_getSession()->addError($this->__('Please enter your email.'));
            //$this->_redirect('*/*/forgotpassword');
            //return;
			echo 'Please enter your email';exit;
        }
	}
	
	 /**
     * Ajax Login post action for mcheckout
     */
    public function ajaxloginpostAction()
    {
		//$login['username'] = (string) $this->getRequest()->getPost('username');
	//	$login['password'] = (string) $this->getRequest()->getPost('password');
		$session = Mage::getSingleton('customer/session');
        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost();
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
					echo $message;exit;
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
				echo 'success'; exit;
            } else {
                $session->addError($this->__('Login and password are required.'));
				echo 'Login and password are required.';exit;
            }
        }

        //$this->_loginPostRedirect();
    }
	
		public function deletecartAction(){
			$itemId = (int) $this->getRequest()->getParam('id');
			if($itemId > 0){
				Mage::getSingleton('checkout/cart')->getQuote()->removeItem($itemId)->save();
				Mage::getModel('checkout/cart')->save();
				echo 'success';
			} else{
				echo 'false';
			}
			
		}
		
		public function postalcodecheckAction(){
			try{
				$resource = Mage::getSingleton('core/resource');
				$read = $resource->getConnection('checkout_pobox');
				//$select_1 = $read->select()->from('checkout_pobox')->where("pincode = $_POST[postcode]");
				//Mage::log($select_1->__toString());					   
				//$row_1 = $read->fetchRow($select_1);
				$where = "pincode = {$_POST[postcode]} AND is_cod = 0";
				$select_2 = $read->select()->from('checkout_cod')
									 
									   ->where($where);
				//Mage::log($select_2->__toString());exit();					   
				//$row_1 = $read->fetchRow($select_1);
				$row_2 = $read->fetchRow($select_2);
			/* 	if($row_1):
					$row_one_value = 1;
				else: 
					$row_one_value = 0;
				endif; */

				if($row_2):
					$row_two_value = 1;
				else: 
					$row_two_value = 0;
				endif;
				
				//echo $row_one_value."|".$row_two_value;
				$result = array();
				//$result['service'] = $row_one_value;
				$result['service'] = 1; // All the pincodes are serviceable. Replace this with above line in case actual results from database need to be used.
				$result['cod'] = $row_two_value;
				
					
			}catch(Exception $e){
				//echo $e->getMessage();
				$result = array();
				$result['service'] = 2; 
				//$result['service'] = 1;
				
			}
				echo json_encode($result);
			
		}	
		
		public function disableCodAction(){
			if($this->_expireAjax()) {
				return;
			}
			
			 //$methods = $this->getOnepage()->getQuote()->getPayment();
			 $methods = Mage::getModel('cashondelivery/cashondelivery');
			 $methods->setDataUsingMethod('isAvailable', false);
			 var_dump($methods->isAvailable());
			 print_r(get_class_methods($methods->getCode()));exit;
			
			
		}
	
}
