<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Phoenix
 * @package    Phoenix_CashOnDelivery
 * @copyright  Copyright (c) 2008-2009 Andrej Sinicyn, Mik3e
 * @copyright  Copyright (c) 2010 Phoenix Medien GmbH & Co. KG (http://www.phoenix-medien.de)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Phoenix_CashOnDelivery_Model_CashOnDelivery extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'cashondelivery';
    protected $_canUseForMultishipping  = false;

    protected $_formBlockType = 'cashondelivery/form';
    protected $_infoBlockType = 'cashondelivery/info';

    public function getCODTitle()
    {
        return $this->getConfigData('title');
    }

    public function getInlandCosts()
    {
        return floatval($this->getConfigData('inlandcosts'));
    }

    public function getForeignCountryCosts()
    {
        return floatval($this->getConfigData('foreigncountrycosts'));
    }

    public function getCustomText()
    {
        return $this->getConfigData('customtext');
    }

    /**
     * Returns COD fee for certain address
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return decimal
     *
     */
    public function getAddressCosts(Mage_Customer_Model_Address_Abstract $address)
    {
        if ($address->getCountry() == Mage::getStoreConfig('shipping/origin/country_id')) {
			//Changed By Amit Pitre On (13-Apr-2012) to show COD Fee for all vendors instead of Flat COD Fee.
			//return Mage::getModel('checkout/cart')->getQuote()->getCodFee();
				return 0;
            //return $this->getInlandCosts();
        } else {
            return $this->getForeignCountryCosts();
        }
    }

    public function getAddressCodFee(Mage_Customer_Model_Address_Abstract $address, $value = null, $alreadyExclTax = false)
    {
        if (is_null($value)) {
            $value = $this->getAddressCosts($address);
        }
        if (Mage::helper('cashondelivery')->codPriceIncludesTax()) {
            if (!$alreadyExclTax) {
                $value = Mage::helper('cashondelivery')->getCodPrice($value, false, $address, $address->getQuote()->getCustomerTaxClassId());
            }
        }
        return $value;
    }

    public function getAddressCodTaxAmount(Mage_Customer_Model_Address_Abstract $address, $value = null, $alreadyExclTax = false)
    {
        if (is_null($value)) {
            $value = $this->getAddressCosts($address);
        }
        if (Mage::helper('cashondelivery')->codPriceIncludesTax()) {
            $includingTax = Mage::helper('cashondelivery')->getCodPrice($value, true, $address, $address->getQuote()->getCustomerTaxClassId());
            if (!$alreadyExclTax) {
                $value = Mage::helper('cashondelivery')->getCodPrice($value, false, $address, $address->getQuote()->getCustomerTaxClassId());
            }
            return $includingTax - $value;
        }
        return 0;
    }

    /**
     * Return true if the method can be used at this time
     *
     * @return bool
     */
    public function isAvailable($quote=null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }
        if (!is_null($quote)) {
			/// Craftsvilla Comment Added By Amit Pitre On 31-07-2012 for minimum order total check for COD.//////
			if($quote->getBaseGrandTotal()<100)
				return false;
			//////////////////////////////////////////////////////////////////////////////////////////////////////
            if($this->getConfigData('shippingallowspecific', $quote->getStoreId()) == 1) {
                $country = $quote->getShippingAddress()->getCountry();
                $availableCountries = explode(',', $this->getConfigData('shippingspecificcountry', $quote->getStoreId()));
                if(!in_array($country, $availableCountries)){
                    return false;
                }

            }
            if ($this->getConfigData('disallowspecificshippingmethods', $quote->getStoreId()) == 1) {
                $shippingMethodCode = explode('_',$quote->getShippingAddress()->getShippingMethod());
                $shippingMethodCode = $shippingMethodCode[0];
                if (in_array($shippingMethodCode, explode(',', $this->getConfigData('disallowedshippingmethods', $quote->getStoreId())))) {
                    return false;
                }
            }
			$pincode = $quote->getShippingAddress()->getPostcode();
			$isCod = $this->postalcodecheck($pincode);
			if(!$isCod){
				return false;
			}
        }
        return true;
    }
	
	public function postalcodecheck($postcode){
			try{
				$resource = Mage::getSingleton('core/resource');
				$read = $resource->getConnection('checkout_pobox');
				//$select_1 = $read->select()->from('checkout_pobox')
				//					   ->where("pincode = $postcode");
				//Mage::log($select_1->__toString());					   
				//$row_1 = $read->fetchRow($select_1);
				//$quoteId = $this->getCheckout()->getQuote()->getId();
				$quotePostCode = Mage::getModel('sales/quote_address')->load('quote_id',$quoteId)->getPostcode();
	//Below line commented and added condn to check cod availabale or not
				//$where = "pincode = {$postcode} AND is_cod = 0";
				$delhivery = Mage::getStoreConfig('courier/general/delhivery'); 
					if($delhivery=='1')
					{
						
						$where = "pincode = {$postcode} AND is_cod = 0 AND `carrier` like '%Delhivery%'";
					
					}
					else
					{
						
						$where = "pincode = {$postcode} AND is_cod = 0 AND `carrier` like '%Aramex%'";
					
					}
				$select_2 = $read->select()->from('checkout_cod_craftsvilla')
									    ->where($where);
				//Mage::log($select_2->__toString());
				//$row_1 = $read->fetchRow($select_1);
				$row_2 = $read->fetchRow($select_2);
				//Mage::log($row_2.'num count');
				/*if($row_1):
					$row_one_value = 1;
				else: 
					$row_one_value = 0;
				endif;*/

				if($row_2){
					$row_two_value = true;
					}
				else{ 
					$row_two_value = false;
				}
				
				//echo $row_one_value."|".$row_two_value;
				$result = array();
				$result['service'] = 1;
				$result['cod'] = $row_two_value;
				
				return $row_two_value;
					
			}catch(Exception $e){
				return false;
			}
			
		}	
	
}
