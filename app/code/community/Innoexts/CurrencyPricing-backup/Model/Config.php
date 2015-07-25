<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the InnoExts Commercial License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://innoexts.com/commercial-license-agreement
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_CurrencyPricing
 * @copyright   Copyright (c) 2012 Innoexts (http://www.innoexts.com)
 * @license     http://innoexts.com/commercial-license-agreement  InnoExts Commercial License
 */

/**
 * Currency pricing config
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Config extends Varien_Object 
{
    /**
     * Config path constants
     */
    const XML_PATH_CATALOG_PRICE_CURRENCY_PRICE_ENABLED = 'catalog/price/currency_price_enabled';
    /**
     * Check if shipping methods filter is enabled
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
    /**
     * Get enabled option value
     * 
     * @return bool
     */
    public function getEnabled()
    {
        return '1';
    }
}
?>