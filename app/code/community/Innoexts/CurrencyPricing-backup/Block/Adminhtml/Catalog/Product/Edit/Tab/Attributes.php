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
 * Product attributes tab
 * 
 * @category   Innoexts
 * @package    Innoexts_CurrencyPricing
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes 
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Attributes 
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
     * Retrieve registered product model
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function getProduct()
    {
        return Mage::registry('product');
    }
    /**
     * Prepare form before rendering HTML
     * 
     * @return Innoexts_CurrencyPricing_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $helper = $this->getCurrencyPricingHelper();
        $form = $this->getForm();
        $product = $this->getProduct();
        if (($price = $form->getElement('price')) && ($group = $this->getGroup())) {
            $fieldset = $form->getElement('group_fields'.$group->getId());
            if ($fieldset) {
                $fieldset->addField('currency_prices', 'text', array(
                    'name'      => 'currency_prices', 
                    'label'     => $helper->__('Price per Currency'), 
                    'title'     => $helper->__('Price per Currency'), 
                    'required'  => false, 
                    'value'     => $product->getWebsiteCurrencyPrices(), 
                ), 'price');
                $form->getElement('currency_prices')->setRenderer(
                    $this->getLayout()->createBlock('currencypricing/adminhtml_catalog_product_edit_tab_currency_price_renderer')
                );
            }
        }
        if (($price = $form->getElement('special_price')) && ($group = $this->getGroup())) {
            $fieldset = $form->getElement('group_fields'.$group->getId());
            if ($fieldset) {
                $fieldset->addField('currency_special_prices', 'text', array(
                    'name'      => 'currency_special_prices', 
                    'label'     => $helper->__('Special Price per Currency'), 
                    'title'     => $helper->__('Special Price per Currency'), 
                    'required'  => false, 
                    'value'     => $product->getWebsiteCurrencySpecialPrices(), 
                ), 'special_price');
                $form->getElement('currency_special_prices')->setRenderer(
                    $this->getLayout()->createBlock('currencypricing/adminhtml_catalog_product_edit_tab_currency_specialprice_renderer')
                );
            }
        }
        return $this;
    }
}