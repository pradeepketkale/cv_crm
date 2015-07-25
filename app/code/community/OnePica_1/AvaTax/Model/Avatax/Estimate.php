<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2009 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */


class OnePica_AvaTax_Model_Avatax_Estimate extends OnePica_AvaTax_Model_Avatax_Abstract
{
	/**
	 * Length of time in minutes for cached rates
	 *
	 * @var int
	 */
	const CACHE_TTL = 120;
	
	/**
	 * An array of rates that acts as a cache
	 * Example: $_rates[$cachekey] = array('rate'=>int, 'tax'=>int, 'timestamp'=>int)
	 *
	 * @var array
	 */
	protected $_rates = array();
	
	/**
	 * An array of line items
	 *
	 * @var array
	 */
	protected $_lines = array();
	
	/**
	 * An array of line numbers to quote item ids
	 *
	 * @var array
	 */
	protected $_lineToLineId = array();
	
	/**
	 * Loads any saved rates in session
	 *
	 */
	protected function _construct() {
		$rates = Mage::getSingleton('avatax/session')->getRates();
		if(is_array($rates)) {
			foreach($rates as $key=>$rate) {
				if($rate['timestamp'] < strtotime('-' . self::CACHE_TTL . ' minutes')) {
					unset($rates[$key]);
				}
			}
			$this->_rates = $rates;
		}
		return parent::_construct();
	}

	/**
	 * Estimates tax rate for one item. 
	 *
	 * @param Varien_Object $item
	 * @return int
	 */
	public function getItemRate($item) {
		if($this->isProductCalculated($item)) {
			return 0;
		} else {
	        $cacheKey = $this->_getRates($item);
	        return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['rate'] : 0;
		}
	}
	

	/**
	 * Estimates tax amount for one item. 
	 *
	 * @param Varien_Object $data
	 * @return int
	 */
	public function getItemTax($item) {
		if($this->isProductCalculated($item)) {
			$tax = 0;
			foreach($item->getChildren() as $child) {
				$tax += $this->getItemTax($child);
			}
			return $tax;
		} else {
	        $cacheKey = $this->_getRates($item);
	        return array_key_exists($cacheKey, $this->_rates) ? $this->_rates[$cacheKey]['tax'] : 0;
		}
	}	

	/**
	 * Get rates from Avalara
	 *
	 * @param Varien_Object $data
	 * @return string
	 */
	protected function _getRates($item) {
        if(self::$_hasError) {
        	return 'error';
        }
        
		$quote = $item->getQuote();
        $this->_lines = array();
        
        //set up request
		$this->_request = new GetTaxRequest();
        $this->_request->setDocType(DocumentType::$SalesOrder);
        $this->_request->setDocCode('quote-' . $quote->getId());
        $this->_addGeneralInfo($quote);
		$this->_setOriginAddress($quote->getStoreId());
		$shippingAddress = ($quote->getShippingAddress()->getPostcode()) ? $quote->getShippingAddress() : $quote->getBillingAddress();
		$this->_setDestinationAddress($shippingAddress);
        $this->_request->setDetailLevel(DetailLevel::$Line);
        $this->_addCustomer($quote);
        $this->_addItemsInCart($quote);
        $this->_addShipping($quote);
        
        //check to see if we can/need to make the request to Avalara
        $requestKey = $this->_genRequestKey();
        $cacheKey = $this->_genCacheKey($item->getId(), $requestKey);
        $makeRequest  = !isset($this->_rates[$cacheKey]);
        $makeRequest &= count($this->_lineToLineId) ? true : false;
        $makeRequest &= $this->_request->getDestinationAddress()=='' ? false : true;
        $makeRequest &= $quote->getId() ? true : false;
        
        //make request if needed and save results in cache
        if($makeRequest) {
        	$result = $this->_send($quote->getStoreId());
        	
        	//success
        	if($result->getResultCode() == SeverityLevel::$Success) {
				foreach($result->getTaxLines() as $ctl) {
	        		$key = $this->_genCacheKey($this->_lineToLineId[$ctl->getNo()], $requestKey);
	        		$this->_rates[$key] = array(
	        			'rate' => ($ctl->getTax() ? $ctl->getRate() : 0) * 100,
	        			'tax' => $ctl->getTax(),
	        			'timestamp' => time()
	        		);
				}
			
			//failure
			} else {
				if(Mage::helper('avatax')->fullStopOnError($quote->getStoreId())) {
					$quote->setHasError(true);
				}
			}
			
			Mage::getSingleton('avatax/session')->setRates($this->_rates);
        }
        
        //return $cacheKey so it doesn't have to be calculated again
        return $cacheKey;
	}
	
	/**
	 * Generates a hash key for the exact request
	 *
	 * @return string
	 */
	protected function _genRequestKey() {
		return hash('md4', serialize($this->_request));
	}
	
	/**
	 * Generates a hash key for the exact request and quote item id
	 *
	 * @param string $itemId
	 * @param string $requestKey
	 * @return string
	 */
	protected function _genCacheKey($itemId, $requestKey) {
		return hash('md4', $itemId . ':' . $requestKey);
	}
	
	/**
	 * Adds shipping cost to request as item
	 *
	 * @param Mage_Sales_Model_Quote
	 * @return int
	 */
	protected function _addShipping($quote) {
		$lineNumber = count($this->_lines);
    	$storeId = Mage::app()->getStore()->getId();
    	$taxClass = Mage::helper('tax')->getShippingTaxClass($storeId);
		$shippingAmount = (float)$quote->getShippingAddress()->getShippingAmount();
		
		$line = new Line();
		$line->setNo($lineNumber);
		$shippingSku = Mage::helper('avatax')->getShippingSku($storeId);
        $line->setItemCode($shippingSku ? $shippingSku : 'Shipping');
        $line->setDescription('Shipping costs');
        $line->setTaxCode($taxClass);
        $line->setQty(1);
        $line->setAmount($shippingAmount);
        $line->setDiscounted(false);
        
        $this->_lines[$lineNumber] = $line;
    	$this->_request->setLines($this->_lines);
    	$this->_lineToLineId[$lineNumber] = Mage::helper('avatax')->getShippingSku($storeId);
    	return $lineNumber;
	}
	
	/**
	 * Adds all items in the cart to the request
	 *
	 * @param Mage_Sales_Model_Quote
	 * @return int
	 */
	protected function _addItemsInCart($quote) {
		$items = $quote->getAllItems();
		
		if(count($items) > 0) {
			foreach($items as $item) {
		        $lineNum = $this->_newLine($item);
			}
        	$this->_request->setLines($this->_lines);
		}
		return count($this->_lines);
	}
	
	/**
	 * Makes a Line object from a product item object
	 *
	 * @param Varien_Object
	 * @return int
	 */
	protected function _newLine($item) {
		$product = $item->getProduct();
		$lineNumber = count($this->_lines);
		$line = new Line();
		
		if($this->isProductCalculated($item)) {
			$price = 0;
		} else {
			$price = $item->getRowTotal() - $item->getDiscountAmount();
		}
		
		$line->setNo($lineNumber);
        $line->setItemCode(substr($product->getSku(), 0, 50));
        $line->setDescription($product->getName());
        $taxClass = Mage::getModel('tax/class')->load($product->getTaxClassId())->getOpAvataxCode();
        $line->setTaxCode($taxClass);
        $line->setQty($item->getQty());
        $line->setAmount($price);
        $line->setDiscounted($item->getDiscountAmount() ? true : false);
        
		$ref1Code = Mage::helper('avatax')->getRef1AttributeCode($product->getStoreId());
		if($ref1Code && $product->getResource()->getAttribute($ref1Code)) {
			$ref1 = $product->getResource()->getAttribute($ref1Code)->getFrontend()->getValue($product);
			try { $line->setRef1((string)$ref1); } catch(Exception $e) { }
		}
		$ref2Code = Mage::helper('avatax')->getRef2AttributeCode($product->getStoreId());
		if($ref2Code && $product->getResource()->getAttribute($ref2Code)) {
			$ref2 = $product->getResource()->getAttribute($ref2Code)->getFrontend()->getValue($product);
			try { $line->setRef2((string)$ref2); } catch(Exception $e) { }
		}
        
        $this->_lines[$lineNumber] = $line;
        $this->_lineToLineId[$lineNumber] = $item->getId();
        return $lineNumber;
	}
	
}