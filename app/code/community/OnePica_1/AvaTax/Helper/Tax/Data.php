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
 * Catalog data helper
 */
class OnePica_AvaTax_Helper_Tax_Data extends Mage_Tax_Helper_Data
{
    
	/**
	 * Always show the price without tax
	 *
	 * @return bool
	 */
    public function priceIncludesTax($store = null)
    {
    	return false;
    }
    
	/**
	 * Always show the shipping price without tax (however, may be included in total tax)
	 *
	 * @return bool
	 */
    public function shippingPriceIncludesTax($store = null)
    {
    	return false;
    }
    
	/**
	 * Returns AvaTax's hard-coded shipping tax class
	 *
	 * @return string
	 */
    public function getShippingTaxClass($store)
    {
        return 'FR020100';
    }
    
	/**
	 * AvaTax always computes tax based on ship from and ship to addresses
	 *
	 * @return string
	 */
    public function getTaxBasedOn($store = null)
    {
        return 'shipping';
    }

	/**
	 * Always apply tax on custom price
	 *
	 * @return bool
	 */
    public function applyTaxOnCustomPrice($store = null) {
        return true;
    }

	/**
	 * Always apply tax on custom price (not original)
	 *
	 * @return bool
	 */
    public function applyTaxOnOriginalPrice($store = null) {
        return false;
    }

	/**
	 * Always apply discount first since AvaTax doesn't support line-level item discount amounts
	 *
	 * @return bool
	 */
    public function applyTaxAfterDiscount($store = null) {
        return true;
    }

	/**
	 * Always apply discount first since AvaTax doesn't support line-level item discount amounts
	 *
	 * @return bool
	 */
    public function discountTax($store = null) {
        return true;
    }
    
    /**
     * Jurisdiction data is not kept in Magento at this time
     *
     * @param   mixed $store
     * @return  bool
     */
    public function displayFullSummary($store = null)
    {
        return false;
    }
    
}