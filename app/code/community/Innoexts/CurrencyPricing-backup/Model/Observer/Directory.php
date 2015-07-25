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
 * Directory observer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Model_Observer_Directory
{
    /**
     * Get currency pricing helper
     * 
     * @return Innoexts_CurrencyPricing_Helper_Data
     */
    protected function getCurrencyPricingHelper()
    {
        return Mage::helper('currencypricing');
    }
    /**
     * After currency rates save
     * 
     * @param 	Varien_Event_Observer $observer
     * @return 	Innoexts_CurrencyPricing_Model_Observer_Directory
     */
    public function afterCurrencyRatesSave(Varien_Event_Observer $observer)
    {
        $helper = $this->getCurrencyPricingHelper();
        $rates = $observer->getEvent()->getRates();
        if (count($rates)) {
            $productPriceProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
            if ($productPriceProcess) {
                $productPriceProcess->reindexAll();
            }
        }
        return $this;
    }
}