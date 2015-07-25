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

/**
 * Tax totals calculation model
 */
class OnePica_AvaTax_Model_Sales_Quote_Address_Total_Tax extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /**
     * Class constructor
     */
    public function __construct() {
        $this->setCode('tax');
    }

    /**
     * Collect tax totals for quote address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function collect(Mage_Sales_Model_Quote_Address $address) {
        $this->_setAddress($address);
        parent::collect($address);
        
        $store = $address->getQuote()->getStore();
        $customer = $address->getQuote()->getCustomer();
        $calculator = Mage::getModel('avatax/avatax_estimate');
        
        $address->setTotalAmount($this->getCode(), 0);
        $address->setBaseTotalAmount($this->getCode(), 0);
        
        $address->setTaxAmount(0);
        $address->setBaseTaxAmount(0);
        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);
        
    	foreach($address->getAllItems() as $item) {
    		$amount = $calculator->getItemTax($item);
    		$percent = $calculator->getItemRate($item);
    		
    		$item->setTaxAmount($amount);
    		$item->setBaseTaxAmount($amount);
    		$item->setTaxPercent($percent);
    		
    		if(!$calculator->isProductCalculated($item)) {
		        $this->_addAmount($amount);
		        $this->_addBaseAmount($amount);
    		}
    	}
    	
    	if($address->getAddressType() == Mage_Sales_Model_Quote_Address::TYPE_SHIPPING || $address->getUseForShipping()) {
			$shippingItem = new Varien_Object();
			$shippingItem->setId(Mage::helper('avatax')->getShippingSku($store->getId()));
			$shippingItem->setProductId(Mage::helper('avatax')->getShippingSku($store->getId()));
			$shippingItem->setQuote($address->getQuote());
			$shippingTax = $calculator->getItemTax($shippingItem);
	    	
	        $address->setShippingTaxAmount($shippingTax);
	        $address->setBaseShippingTaxAmount($shippingTax);
	        $this->_addAmount($shippingTax);
	        $this->_addBaseAmount($shippingTax);
    	}
        
        return $this;
    }

    /**
     * Add tax totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address) {
        $store  = $address->getQuote()->getStore();
        $amount = $address->getTaxAmount();
        
        if (($amount!=0) || (Mage::helper('tax')->displayZeroTax($store))) {
	        $address->addTotal(array(
	            'code'  => $this->getCode(),
	            'title' => Mage::helper('tax')->__('Tax'),
	            'value' => $amount,
	            'area'  => null
	        ));
        }
        
        return $this;
    }
    
    
    /* BELOW ARE MAGE CORE PROPERTIES AND METHODS ADDED FOR OLDER VERSION COMPATABILITY */
    
    
    /**
     * Total Code name
     *
     * @var string
     */
    protected $_code;
    protected $_address = null;

    /**
     * Add total model amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _addAmount($amount)
    {
        $this->_getAddress()->addTotalAmount($this->getCode(),$amount);
        return $this;
    }

    /**
     * Add total model base amount value to address
     *
     * @param   float $amount
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _addBaseAmount($baseAmount)
    {
        $this->_getAddress()->addBaseTotalAmount($this->getCode(), $baseAmount);
        return $this;
    }

    /**
     * Set address shich can be used inside totals calculation
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Sales_Model_Quote_Address_Total_Abstract
     */
    protected function _setAddress(Mage_Sales_Model_Quote_Address $address)
    {
        $this->_address = $address;
        return $this;
    }

    /**
     * Get quote address object
     *
     * @throw   Mage_Core_Exception if address not declared
     * @return  Mage_Sales_Model_Quote_Address
     */
    protected function _getAddress()
    {
        if ($this->_address === null) {
            Mage::throwException(
                Mage::helper('sales')->__('Address model is not defined')
            );
        }
        return $this->_address;
    }
}
