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
 * Currency
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Directory_Currency extends Mage_Directory_Model_Currency 
{
    /**
     * Save currency rates
     *
     * @param array $rates
     * @return object
     */
    public function saveRates($rates)
    {
        $eventPrefix = 'directory_currency_rates';
        Mage::dispatchEvent($eventPrefix.'_save_before', array('rates' => $rates));
        $this->_getResource()->saveRates($rates);
        Mage::dispatchEvent($eventPrefix.'_save_after', array('rates' => $rates));
        return $this;
    }
}