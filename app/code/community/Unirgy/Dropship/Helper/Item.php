<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Helper_Item extends Mage_Core_Helper_Abstract
{
    public function getVendorIdOption($item)
    {
        return $this->_getItemOption($item, 'udropship_vendor');
    }
    public function setVendorIdOption($item, $value, $visible=false)
    {
        $this->saveItemOption($item, 'udropship_vendor', $value, false);
        if ($visible) {
            $addOptions = $this->getAdditionalOptions($item);
            if (!empty($addOptions) && is_string($addOptions)) {
                $addOptions = unserialize($addOptions);
            }
            if (!is_array($addOptions)) {
                $addOptions = array();
            }
            foreach ($addOptions as $idx => $option) {
                if ($option['udropship_vendor']) {
                    $vendorOptionIdx = $idx;
                    break;
                }
            }
            $vendorOption['label'] = Mage::helper('udropship')->__('Vendor');
            $vendorOption['value'] = Mage::helper('udropship')->getVendor($value)->getVendorName();
            if (isset($vendorOptionIdx)) {
                $addOptions[$vendorOptionIdx] = $vendorOption;
            } else {
                $addOptions[] = $vendorOption;
            }
            $this->saveAdditionalOptions($item, $addOptions);
        }
        return $this;
    }
    public function getAdditionalOptions($item)
    {
        return $this->_getItemOption($item, 'additional_options');
    }
    public function getItemOption($item, $code)
    {
        return $this->_getItemOption($item, $code);
    }
    protected function _getItemOption($item, $code)
    {
        $optValue = null;
        if ($item instanceof Mage_Catalog_Model_Product
            && $item->getCustomOption($code)
        ) {
            $optValue = $item->getCustomOption($code)->getValue();
        } elseif ($item instanceof Mage_Sales_Model_Quote_Item
            && $item->getOptionByCode($code)
        ) {
            $optValue = $item->getOptionByCode($code)->getValue();
        } elseif ($item instanceof Mage_Sales_Model_Quote_Address_Item && $item->getQuoteItem()
            && $item->getQuoteItem()->getOptionByCode($code)
        ) {
            $optValue = $item->getQuoteItem()->getOptionByCode($code)->getValue();
        } elseif ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptions();
            if (isset($options[$code])) {
                $optValue = $options[$code];
            }
        } elseif ($item instanceof Varien_Object && $item->getOrderItem()) {
            $options = $item->getOrderItem()->getProductOptions();
            if (isset($options[$code])) {
                $optValue = $options[$code];
            }
        }
        return $optValue;
    }
    public function saveAdditionalOptions($item, $options)
    {
        return $this->_saveItemOption($item, 'additional_options', $options, true);
    }
    public function saveItemOption($item, $code, $value, $serialize)
    {
        return $this->_saveItemOption($item, $code, $value, $serialize);
    }
    protected function _saveItemOption($item, $code, $value, $serialize)
    {
        if ($item instanceof Mage_Catalog_Model_Product) {
            if ($item->getCustomOption($code)) {
                $item->getCustomOption($code)->setValue($serialize ? serialize($value) : $value);
            } else {
                $item->addCustomOption($code, $serialize ? serialize($value) : $value);
            }
        } elseif ($item instanceof Mage_Sales_Model_Quote_Item) {
            if ($item->getOptionByCode($code)) {
                $item->getOptionByCode($code)->setValue($serialize ? serialize($value) : $value);
            } else {
                $item->addOption(array(
                    'product' => $item->getProduct(),
                    'product_id' => $item->getProduct()->getId(),
                    'code' => $code,
                    'value' => $serialize ? serialize($value) : $value
                ));
            }
        } elseif ($item instanceof Mage_Sales_Model_Quote_Address_Item && $item->getQuoteItem()) {
            if ($item->getQuoteItem()->getOptionByCode($code)) {
                $item->getQuoteItem()->getOptionByCode($code)->setValue($serialize ? serialize($value) : $value);
            } else {
                $item->getQuoteItem()->addOption(array(
                    'product' => $item->getQuoteItem()->getProduct(),
                    'product_id' => $item->getQuoteItem()->getProduct()->getId(),
                    'code' => $code,
                    'value' => $serialize ? serialize($value) : $value
                ));
            }
        } elseif ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptions();
            $options[$code] = $value;
            $item->setProductOptions($options);
        } elseif ($item instanceof Varien_Object && $item->getOrderItem()) {
            $options = $item->getOrderItem()->getProductOptions();
            $options[$code] = $value;
            $item->getOrderItem()->setProductOptions($options);
        }
        return $value;
    }
}