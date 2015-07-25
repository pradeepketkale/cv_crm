<?php
class Craftsvilla_Uagent_CreateorderController extends Craftsvilla_Uagent_Controller_AgentAbstract
{
	 public function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }
	public function createorderAction()
	{
	$this->_renderPage(null, 'createorder');
	}
	public function addProductTocartAction()
	{
	
	$products = explode(',', $this->getRequest()->getParam('sku'));
    $quantities = explode(',', $this->getRequest()->getParam('qty'));
	
		 $id = Mage::getModel('catalog/product')->getIdBySku($products);
		if ($id){
		$numProducts = count($products);
			$cart = $this->_getCart();
			for($i=0;$i<$numProducts;$i++) {
				
				$product_id_sku = $products[$i];
				$product_id = $id;
				
				//commented by dileswar
				//$product_id = Mage::getModel('catalog/product')->loadByAttribute('sku',$product_id_sku)->getId();
				
				$quantity = $quantities[$i];
		
				if ($product_id == '') continue;
				if(!is_numeric($quantity) || $quantity <= 0) continue;
				$pModel = Mage::getModel('catalog/product')->load($product_id);
				//echo '<pre>'.$product_id;print_r($pModel->getData());exit;
				if($pModel->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
					//echo 'Hello I m entered here';exit;
					try {
						$eventArgs = array(
							'product' => $pModel,
							'qty' => $quantity,
							'additional_ids' => array(),
							'request' => $this->getRequest(),
							'response' => $this->getResponse(),
						);
						Mage::dispatchEvent('checkout_cart_before_add', $eventArgs);
						$cartObject = $cart->addProduct($pModel, array('product'=>$product_id,'qty' => $quantity));
						
						
						Mage::dispatchEvent('checkout_cart_after_add', $eventArgs);
						Mage::dispatchEvent('checkout_cart_add_product', array('product'=>$pModel));
						
						$message = $this->__('%s was successfully added to your shopping cart.', $pModel->getName());
						Mage::getSingleton('uagent/session')->addSuccess($message);
					} catch (Mage_Core_Exception $e) {
						if (Mage::getSingleton('uagent/session')->getUseNotice(true)) {
							Mage::getSingleton('uagent/session')->addNotice($pModel->getName() . ': ' . $e->getMessage());
						}
						else {
							Mage::getSingleton('uagent/session')->addError($pModel->getName() . ': ' . $e->getMessage());
						}
					} catch (Exception $e) {
						Mage::getSingleton('uagent/session')->addException($e, $this->__('Can not add item to shopping cart'));
					}
				}
			}
			$cart->save();
			$this->_getSession()->setCartWasUpdated(true);
	//$cartObject->getQuote()->getEntityId();
		}
		
		else{
		Mage::getSingleton('uagent/session')->addError("SKU does not exist"); 
		}
	
	$this->_redirect('uagent/createorder/createorder');
	}

	public function removeproductcartAction()
		{

			$removeId = $this->getRequest()->getParam('remove');
			$skuid = Mage::getModel('sales/quote_item')->load($removeId);	
//			echo $skuid->getSku();exit;	
				$session = Mage::getSingleton('checkout/session');
				$quote = $session->getQuote();
				
				$cart = Mage::getModel('checkout/cart');
				$cartItems = $cart->getItems();
				
				foreach($cartItems as $item)
				{
					if ($item->getSku() == $skuid->getSku())
					{
					$quote->removeItem($item->getId())->save();
					}
				}
				
				Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
				$cart->init();
				$cart->save();
			
			$message = $this->__('%s was successfully removed from your shopping cart.', $skuid->getSku());
            Mage::getSingleton('uagent/session')->addSuccess($message);
			$this->_redirect('uagent/createorder/createorder'); 
			
		}
	public function couponpostAction()
    {
		$couponCode = (string) $this->getRequest()->getParam('coupon_code');
        	
			if ($this->getRequest()->getParam('remove') == 1) {
				$couponCode = '';
			}
			$_quote = Mage::getSingleton('checkout/session')->getQuote();
			$oldCouponCode = $_quote->getCouponCode();
	
			if (!strlen($couponCode) && !strlen($oldCouponCode)) {
				$this->_goBack();
				return;
			}
	
			try {
				//$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
				$_quote->setCouponCode(strlen($couponCode) ? $couponCode : '')
					->collectTotals()
					->save();
				if ($couponCode) {
					if ($couponCode == $_quote->getCouponCode()) {
						$this->_getSession()->addSuccess(
							$this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
						);
					}
					else {
						$this->_getSession()->addError(
							$this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
						);
					}
				} else {
					$this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
				}
	
			} catch (Mage_Core_Exception $e) {
				$this->_getSession()->addError($e->getMessage());
			} catch (Exception $e) {
				$this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
				Mage::logException($e);
			}
		
$this->_redirect('uagent/createorder/createorder'); 
        
    }
	public function addToaddressAction()
	{
		$session = Mage::getSingleton('checkout/session');
		$quote = $session->getQuote();
		$emailS = $this->getRequest()->getParam('email');
		$firstname = $this->getRequest()->getParam('firstname');
		$lastname = $this->getRequest()->getParam('lastname');
		$street1 =$this->getRequest()->getParam('street1');
		$street2 = $this->getRequest()->getParam('street2');
		$street = $street1.$street2; 
		$city = $this->getRequest()->getParam('city');
		$country = $this->getRequest()->getParam('country');
		$region_name = $this->getRequest()->getParam('region_id');
		$regionModel = Mage::getModel('directory/region')->loadByName($region_name, $country);
		$regionId = $regionModel->getId();
		$zip = $this->getRequest()->getParam('zip');
		$telephone = $this->getRequest()->getParam('telephone');
		$customer = Mage::getModel('customer/customer');
		//$customer  = new Mage_Customer_Model_Customer();
		$password = '123456';
		$email = $emailS;
		$customer->setWebsiteId(Mage::app()->getWebsite()->getId());
		$customer->loadByEmail($emailS);
		//Zend_Debug::dump($customer->debug()); exit;
		if(!$customer->getId()) {
		$customer->setEmail($emailS);
		$customer->setFirstname($firstname);
		$customer->setLastname($lastname);
		$customer->setPassword($password);
		}
		try {
		$customer->save();
		$customer->setConfirmation(null);
		$customer->save();
		//Make a "login" of new customer
		Mage::getSingleton('customer/session')->loginById($customer->getId());
		}
		catch (Exception $ex) {
		//Zend_Debug::dump($ex->getMessage());
		}

		$_custom_address = array (
		'firstname' => $firstname,
		'lastname' => $lastname,
		'street' => array (
		'0' => $street1,
		'1' => $street2,
		),
		'city' => $city,
		'region_id' => $regionId,
		'region' => $region_name,
		'postcode' => $zip,
		'country_id' => $country, /* Croatia */
		'telephone' => $telephone,
		);
		$customAddress = Mage::getModel('customer/address');
		//$customAddress = new Mage_Customer_Model_Address();
		$customAddress->setData($_custom_address)
			->setCustomerId($customer->getId())
			->setIsDefaultBilling('1')
			->setIsDefaultShipping('1')
			->setSaveInAddressBook('1');
		try {
			$customAddress->save();
			$message = $this->__('Shiiping Address was successfully Added to you order.');
            Mage::getSingleton('uagent/session')->addSuccess($message);
			$this->_redirect('uagent/createorder/createorder'); 
			}
		catch (Exception $ex) {
		//Zend_Debug::dump($ex->getMessage());
		}
		$quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
		
		
		$this->_redirect('uagent/createorder/createorder'); 
		//Mage::getSingleton('customer/session')->logout();
	}

	public function createorderpostAction()
	{
		$agentSession = Mage::getSingleton('uagent/session');
		$agentId = $agentSession->getId();
		$session = Mage::getSingleton('checkout/session');
		//$quote = $session->getQuote();
		$quote_id = $session->getQuote()->getId();
		$quote = Mage::getModel('sales/quote')->load($quote_id);
		$quoteCustomer = $quote->getCustomerEmail();
		
		if($quoteCustomer)
		{
		$customer = Mage::getModel('customer/customer')
    		->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
		    ->loadByEmail($quote->getCustomerEmail());
		$quote->assignCustomer($customer);
		$quote->setIsMultiShipping(false);
		$quote->save();
		$address = $quote->getShippingAddress();
		
		$address->setCollectShippingRates(true);
		$quote->collectTotals()->save();
		
		// set shipping method
		
		$quote->getShippingAddress()->setShippingMethod('Drop Shipping - Best Shipping Way');
		$quote->collectTotals()->save();
		// set payment
		$payment = $quote->getPayment();
		$payment->importData(array(
		'method' => 'purchaseorder', 
		));
		$quote->getShippingAddress()->setPaymentMethod($payment->getMethod());
		$quote->collectTotals()->save();
		
		// save quote
		$quote->reserveOrderId();
		
		$convertQuote = Mage::getModel('sales/convert_quote');
		$order = $convertQuote->addressToOrder($quote->getShippingAddress());
		$order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
		$order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
		$order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
		$order->setAgentId($agentId);
		
		foreach ($quote->getAllItems() as $item) {
			$orderItem = $convertQuote->itemToOrderItem($item);
			if ($item->getParentItem()) {
				$orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
			}
			$order->addItem($orderItem);
		}
		$message = 'Order Created By Agent: '.$agentId;
		$order->addStatusToHistory($order->getStatus(), $message);
		$order->place();
		$order->sendNewOrderEmail();
		$order->setAgentStatus(0);
		$order->save();
		
		$quote->setIsActive(false);
		$message = $this->__('Order has been Succesfully Created');
            Mage::getSingleton('uagent/session')->addSuccess($message);
			$this->_redirect('uagent/createorder/createorder'); 
		
		Mage::getSingleton('customer/session')->logout();
		}
		else
		{
		   Mage::getSingleton('uagent/session')->addError('Please Enter The Shipping Address And Click Save In Address Book Button Below Before Clicking Create Order');
		  $this->_redirect('uagent/createorder/createorder'); 
		}
	}
}
