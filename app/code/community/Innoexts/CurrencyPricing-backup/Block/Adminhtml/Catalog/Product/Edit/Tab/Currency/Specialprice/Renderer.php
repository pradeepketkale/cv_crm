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
 * Product currency special price tab renderer
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Currency_Specialprice_Renderer 
    extends Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Renderer_Abstract 
    implements Varien_Data_Form_Element_Renderer_Interface 
{
    /**
     * Constructor
     */
    public function __construct() {
        $this->setTemplate('currencypricing/catalog/product/edit/tab/currency/specialprice/renderer.phtml');
    }
    /**
     * Get default price
     * 
     * @return float
     */
    public function getDefaultSpecialPrice()
    {
        $price = $this->getProduct()->getSpecialPrice();
        if ($price) {
            return $price;
        } else {
            return null;
        }
    }
    /**
     * Get default currency special price
     * 
     * @param string 
     * @return float
     */
    public function getDefaultCurrencySpecialPrice($currency)
    {
        $price = $this->getDefaultSpecialPrice();
        if (!is_null($price)) {
            return $this->getStore()->getBaseCurrency()->convert($price, $currency);
        } else {
            return null;
        }
    }
    /**
     * Get values
     * 
     * @return array
     */
    public function getValues()
    {
        $helper = $this->getCurrencyPricingHelper();
        $values = array();
        $currencies = $this->getCurrencyCodes();
        if (count($currencies)) {
            $element = $this->getElement();
            $readonly = $element->getReadonly();
            $data = $element->getValue();
            $baseCurrency = $this->getStore()->getBaseCurrency();
            foreach ($currencies as $currency) {
                if (!$baseCurrency->getRate($currency)) {
                    continue;
                }
                $value = array('currency' => $currency);
                $defaultPrice = $helper->getEscapedPrice($this->getDefaultCurrencySpecialPrice($currency));
                if (isset($data[$currency])) {
                    $value['price'] = $helper->getEscapedPrice($data[$currency]);
                    $value['default_price'] = $defaultPrice;
                    $value['use_default'] = 0;
                } else {
                    if (!is_null($defaultPrice)) {
                        $value['price'] = $helper->getEscapedPrice($defaultPrice);
                    } else {
                        $value['price'] = null;
                    }
                    $value['default_price'] = $defaultPrice;
                    $value['use_default'] = 1;
                }
                $value['readonly'] = $readonly;
                $value['rate'] = array(); 
                foreach ($this->getCurrencyCodes() as $_currency) {
                    $value['rate'][$_currency] = $helper->getCurrencyRate($_currency, $currency);
                }
                array_push($values, $value);
            }
        }
        usort($values, array($this, 'sortValues'));
        return $values;
    }
}