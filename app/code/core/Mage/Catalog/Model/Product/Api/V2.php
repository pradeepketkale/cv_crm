<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog product api V2
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Api_V2 extends Mage_Catalog_Model_Product_Api {

	/**
	 * Retrieve list of products with basic info (id, sku, type, set, name)
	 *
	 * @param array $filters
	 * @param string|int $store
	 * @return array
	 */
	public function items($filters = null, $store = null) {
		$collection = Mage::getModel('catalog/product')->getCollection()
				->addStoreFilter($this->_getStoreId($store))
				->addAttributeToSelect('name');

		$preparedFilters = array();
		if (isset($filters->filter)) {
			foreach ($filters->filter as $_filter) {
				$preparedFilters[$_filter->key] = $_filter->value;
			}
		}
		if (isset($filters->complex_filter)) {
			foreach ($filters->complex_filter as $_filter) {
				$_value = $_filter->value;
				$preparedFilters[$_filter->key] = array(
					$_value->key => $_value->value
				);
			}
		}

		if (!empty($preparedFilters)) {
			try {
				foreach ($preparedFilters as $field => $value) {
					if (isset($this->_filtersMap[$field])) {
						$field = $this->_filtersMap[$field];
					}

					$collection->addFieldToFilter($field, $value);
				}
			} catch (Mage_Core_Exception $e) {
				$this->_fault('filters_invalid', $e->getMessage());
			}
		}

		$result = array();

		foreach ($collection as $product) {
			$result[] = array(
				'product_id' => $product->getId(),
				'sku' => $product->getSku(),
				'name' => $product->getName(),
				'set' => $product->getAttributeSetId(),
				'type' => $product->getTypeId(),
				'category_ids' => $product->getCategoryIds(),
				'website_ids' => $product->getWebsiteIds()
			);
		}

		return $result;
	}

	/**
	 * Retrieve product info
	 *
	 * @param int|string $productId
	 * @param string|int $store
	 * @param stdClass $attributes
	 * @return array
	 */
	public function info($productId, $store = null, $attributes = null, $identifierType = null) {
		$product = $this->_getProduct($productId, $store, $identifierType);

		if (!$product->getId()) {
			$this->_fault('not_exists');
		}

		$result = array(// Basic product data
			'product_id' => $product->getId(),
			'sku' => $product->getSku(),
			'set' => $product->getAttributeSetId(),
			'type' => $product->getTypeId(),
			'categories' => $product->getCategoryIds(),
			'websites' => $product->getWebsiteIds()
		);

		$allAttributes = array();
		if (!empty($attributes->attributes)) {
			$allAttributes = array_merge($allAttributes, $attributes->attributes);
		}
		else {
			foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
				if ($this->_isAllowedAttribute($attribute, $attributes)) {
					$allAttributes[] = $attribute->getAttributeCode();
				}
			}
		}

		$_additionalAttributeCodes = array();
		if (!empty($attributes->additional_attributes)) {
			foreach ($attributes->additional_attributes as $k => $_attributeCode) {
				$allAttributes[] = $_attributeCode;
				$_additionalAttributeCodes[] = $_attributeCode;
			}
		}

		$_additionalAttribute = 0;
		foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
			if ($this->_isAllowedAttribute($attribute, $allAttributes)) {
				if (in_array($attribute->getAttributeCode(), $_additionalAttributeCodes)) {
					$result['additional_attributes'][$_additionalAttribute]['key'] = $attribute->getAttributeCode();
					$result['additional_attributes'][$_additionalAttribute]['value'] = $product->getData($attribute->getAttributeCode());
					$_additionalAttribute++;
				}
				else {
					$result[$attribute->getAttributeCode()] = $product->getData($attribute->getAttributeCode());
				}
			}
		}

		return $result;
	}

	/**
	 * Create new product.
	 *
	 * @param string $type
	 * @param int $set
	 * @param string $sku
	 * @param array $productData
	 * @param string $store
	 * @return int
	 */
	public function create($type, $set, $sku, $productData, $store = null) {
		if (!$type || !$set || !$sku) {
			$this->_fault('data_invalid');
		}

		/** @var $product Mage_Catalog_Model_Product */
		$product = Mage::getModel('catalog/product');
		$product->setStoreId($this->_getStoreId($store))
				->setAttributeSetId($set)
				->setTypeId($type)
				->setSku($sku);

		if (property_exists($productData, 'website_ids') && is_array($productData->website_ids)) {
			$product->setWebsiteIds($productData->website_ids);
		}

		Mage::log("in product data create");
		Mage::log($productData->getData());

		if (property_exists($productData, 'additional_attributes')) {
			foreach ($productData->additional_attributes as $_attribute) {
				$_attrCode = $_attribute->key;
				$productData->$_attrCode = $_attribute->value;
			}
			unset($productData->additional_attributes);
		}

		foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
			$_attrCode = $attribute->getAttributeCode();
			if ($this->_isAllowedAttribute($attribute) && isset($productData->$_attrCode)) {
				if ($attribute->getAttributeCode() == 'price') {
					$websitePriceCombination = array();
					$websiteCurrencyCombination = array();

					$websitesProductsPrices = explode('@', $productData->$_attrCode);
					$countPrice = 0;
					$baseCurrency = Mage::app()->getBaseCurrencyCode();

					foreach ($websitesProductsPrices as $websiteProductPrice) {
						$rowData = explode('^', $websiteProductPrice);

						if ($countPrice == 0) {
							$defaultPrice = $rowData[1];
							$defaultCurrency = strtoupper($rowData[2]);
						}
						$countPrice++;

						$websitePriceCombination[$rowData[0]] = $rowData[1];
						$websiteCurrencyCombination[$rowData[0]] = strtoupper($rowData[2]);
					}

					if ($defaultCurrency != $baseCurrency && $defaultPrice != null) {
						$objDirCurrency = new Mage_Directory_Model_Currency();
						$arrAllowedCurrencies = $objDirCurrency->getConfigAllowCurrencies();

						try {
							$arrCurrencyRates = $objDirCurrency->getCurrencyRates($baseCurrency, array_values($arrAllowedCurrencies));

							if (count($arrCurrencyRates)) {
								$defaultPrice = round(floatval($defaultPrice) / floatval($arrCurrencyRates[$defaultCurrency]), 2);
							}
						} catch (Mage_Core_Exception $e) {
							$this->_fault('data_invalid', $e->getMessage());
						}
					}

					//$product->setData($attribute->getAttributeCode(), current($websitePriceCombination));
					$product->setData($attribute->getAttributeCode(), $defaultPrice);
					continue;
				}

				$product->setData($attribute->getAttributeCode(), $productData->$_attrCode);
			}
		}

		$this->_prepareDataForSave($product, $productData);

		try {
			/**
			 * @todo implement full validation process with errors returning which are ignoring now
			 * @todo see Mage_Catalog_Model_Product::validate()
			 */
			if (is_array($errors = $product->validate())) {
				$strErrors = array();
				foreach ($errors as $code => $error) {
					$strErrors[] = ($error === true) ? Mage::helper('catalog')->__('Attribute "%s" is invalid.', $code) : $error;
				}
				$this->_fault('data_invalid', implode("\n", $strErrors));
			}

			$product->setData('sap_sync', 1);
			$product->save();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}

		if (count($websitePriceCombination) && count($websiteCurrencyCombination)) {
			$this->_setWebsiteProductPrice($product->getId(), $websitePriceCombination, $websiteCurrencyCombination);
		}

		/**
		 * Section for making the "Use Default Values" Checkboxes selected
		 */
		$paramAttributes = array(
			'name',
			'description',
			'short_description',
			'status',
			'visibility',
			'tax_class_id',
			'url_key',
			'image',
			'small_image',
			'thumbnail'
		);
		$objSapConfig = new Insync_Sap_Model_Config();
		$objSapConfig->setDefaultChecked($product->getId(), (array) $productData->website_ids, $paramAttributes);

		return $product->getId();
	}

	/**
	 * Update product data
	 *
	 * @param int|string $productId
	 * @param array $productData
	 * @param string|int $store
	 * @return boolean
	 */
	public function update($productId, $productData, $store = null, $identifierType = null) {
		if (property_exists($productData, 'website_ids') && is_array($productData->website_ids)) {
			$_websiteIds = $productData->website_ids;
		}
		else {
			$this->_fault('data_invalid', "No Website IDs available");
		}

		/**
		 * This variable is used for manipulating the Product with the actual Product ID.
		 * Don't unset this variable anywhere.
		 */
		$_productIdActual = 0;

		$flagWebsiteWiseAttributePrice = 1;

		/**
		 * Product Initialization method changed from the default Helper method to the method defined in this Model "Insync_Sap_Model_Config" Class,
		 * due to a Big Bug in the Magento code.
		 * @author SBOeConnect Team
		 */
		$objSapConfig = new Insync_Sap_Model_Config();
		$product = $objSapConfig->_getProduct($productId, $this->_getStoreId($store), $identifierType);

		if (!$product->getId()) {
			$this->_fault('not_exists');
		}

		if (property_exists($productData, 'website_ids') && is_array($productData->website_ids)) {
			$product->setWebsiteIds($productData->website_ids);
		}

		Mage::log("in product data create");
		Mage::log($productData->getData());

		if (property_exists($productData, 'additional_attributes')) {
			foreach ($productData->additional_attributes as $_attribute) {
				$_attrCode = $_attribute->key;
				$productData->$_attrCode = $_attribute->value;
			}
			unset($productData->additional_attributes);
		}

		foreach ($product->getTypeInstance(true)->getEditableAttributes($product) as $attribute) {
			$_attrCode = $attribute->getAttributeCode();
			if ($this->_isAllowedAttribute($attribute) && isset($productData->$_attrCode)) {
				if ($attribute->getAttributeCode() == 'price' && $flagWebsiteWiseAttributePrice) {
					$websitePriceCombination = array();
					$websiteCurrencyCombination = array();

					$websitesProductsPrices = explode('@', $productData->$_attrCode);
					$countPrice = 0;
					$baseCurrency = Mage::app()->getBaseCurrencyCode();

					foreach ($websitesProductsPrices as $websiteProductPrice) {
						$rowData = explode('^', $websiteProductPrice);

						if ($countPrice == 0) {
							$defaultPrice = $rowData[1];
							$defaultCurrency = strtoupper($rowData[2]);
						}
						$countPrice++;

						$websitePriceCombination[$rowData[0]] = $rowData[1];
						$websiteCurrencyCombination[$rowData[0]] = strtoupper($rowData[2]);
					}

					if ($defaultCurrency != $baseCurrency && $defaultPrice != null) {
						$objDirCurrency = new Mage_Directory_Model_Currency();
						$arrAllowedCurrencies = $objDirCurrency->getConfigAllowCurrencies();

						try {
							$arrCurrencyRates = $objDirCurrency->getCurrencyRates($baseCurrency, array_values($arrAllowedCurrencies));

							if (count($arrCurrencyRates)) {
								$defaultPrice = round(floatval($defaultPrice) / floatval($arrCurrencyRates[$defaultCurrency]), 2);
							}
						} catch (Mage_Core_Exception $e) {
							$this->_fault('data_invalid', $e->getMessage());
						}
					}

					$flagWebsiteWiseAttributePrice = 0;

					$product->setData($attribute->getAttributeCode(), $defaultPrice);
					//$product->setData($attribute->getAttributeCode(), current($websitePriceCombination));
					continue;
				}

				if ($_attrCode == 'description') {
					continue;
				}

				$product->setData($attribute->getAttributeCode(), $productData->$_attrCode);
			}
		}

		$this->_prepareDataForSave($product, $productData);

		try {
			/**
			 * @todo implement full validation process with errors returning which are ignoring now
			 * @todo see Mage_Catalog_Model_Product::validate()
			 */
			if (is_array($errors = $product->validate())) {
				$strErrors = array();
				foreach ($errors as $code => $error) {
					$strErrors[] = ($error === true) ? Mage::helper('catalog')->__('Value for "%s" is invalid.', $code) : Mage::helper('catalog')->__('Value for "%s" is invalid: %s', $code, $error);
				}
				$this->_fault('data_invalid', implode("\n", $strErrors));
			}

			$product->setData('sap_sync', 1);
			$product->save();
			$_productIdActual = $product->getId();
		} catch (Mage_Core_Exception $e) {
			$this->_fault('data_invalid', $e->getMessage());
		}

		unset($product, $strErrors);

		if (count($websitePriceCombination) && count($websiteCurrencyCombination)) {
			$this->_setWebsiteProductPrice($_productIdActual, $websitePriceCombination, $websiteCurrencyCombination);
		}

		/**
		 * Section for making the "Use Default Values" Checkboxes selected
		 */
		$paramAttributes = array(
			'name',
			'description',
			'short_description',
			'status',
			'visibility',
			'tax_class_id',
			'url_key',
			'image',
			'small_image',
			'thumbnail'
		);
		$objSapConfig->setDefaultChecked($_productIdActual, (array) $productData->website_ids, $paramAttributes);

		return true;
	}

	/**
	 *  Set additional data before product saved
	 *
	 *  @param    Mage_Catalog_Model_Product $product
	 *  @param    array $productData
	 *  @return	  object
	 */
	protected function _prepareDataForSave($product, $productData) {
		if (property_exists($productData, 'category_ids') && is_array($productData->category_ids)) {
			$product->setCategoryIds($productData->category_ids);
		}

		if (property_exists($productData, 'websites') && is_array($productData->websites)) {
			foreach ($productData->websites as &$website) {
				if (is_string($website)) {
					try {
						$website = Mage::app()->getWebsite($website)->getId();
					} catch (Exception $e) {

					}
				}
			}
			$product->setWebsiteIds($productData->websites);
		}

		if (Mage::app()->isSingleStoreMode()) {
			$product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
		}

		if (property_exists($productData, 'stock_data')) {
			$_stockData = array();
			foreach ($productData->stock_data as $key => $value) {
				$_stockData[$key] = $value;
			}
		}
		else {
			$_stockData = array('use_config_manage_stock' => 0);
		}
		$product->setStockData($_stockData);

		if (property_exists($productData, 'tier_price')) {
			$tierPrices = Mage::getModel('catalog/product_attribute_tierprice_api_V2')->prepareTierPrices($product, $productData->tier_price);
			$product->setData(Mage_Catalog_Model_Product_Attribute_Tierprice_Api_V2::ATTRIBUTE_CODE, $tierPrices);
		}
	}

	/**
	 * Update Product Special Price
	 * Fully Customized with Consideration of each Website instead of each Store View
	 *
	 * @param int|string $productId
	 * @param float $specialPrice
	 * @param string $fromDate
	 * @param string $toDate
	 * @param string|int $store
	 * @param string $currencyCode
	 * @return boolean
	 * @author SBOeConnect Team
	 */
	public function setSpecialPrice($productId, $specialPrice = null, $fromDate = null, $toDate = null, $store = null, $currencyCode = null, $identifierType = null) {
		$objSapConfig = new Insync_Sap_Model_Config();
		$objProduct = $objSapConfig->_getProduct($productId, null, $identifierType);

		if (!$objProduct->getId()) {
			$this->_fault('not_exists');
		}

		if (is_null($store) || empty($store)) {
			$websiteIds = $objProduct->getWebsiteIds();
		}
		else {
			$websiteIds = array($store);
		}

		if (!count($websiteIds)) {
			$this->_fault('data_invalid', "No Website IDs available for this Product, when Converting Special Price");
		}

		if (is_null($currencyCode)) {
			$currencyCode = Mage::app()->getBaseCurrencyCode();
		}
		$currencyCode = strtoupper($currencyCode);

		$objDirCurrency = new Mage_Directory_Model_Currency();
		$arrAllowedCurrencies = $objDirCurrency->getConfigAllowCurrencies();

		$flag = false;
		foreach ($websiteIds as $_eachWebsiteId) {
			$objWebsite = new Mage_Core_Model_Website();
			$objWebsite->load($_eachWebsiteId);
			$storeIds = $objWebsite->getStoreIds();

			$websiteBaseCurrencyCode = $objWebsite->getBaseCurrency()->getCode();
			if (empty($websiteBaseCurrencyCode)) {
				$websiteBaseCurrencyCode = Mage::app()->getBaseCurrencyCode();
			}

			$arrCurrencyRates = $objDirCurrency->getCurrencyRates($websiteBaseCurrencyCode, array_values($arrAllowedCurrencies));

			$currencyRate = '1';
			if (count($arrCurrencyRates)) {
				$currencyRate = $arrCurrencyRates[$currencyCode];
			}

			$newSpecialPrice = $specialPrice;

			try {
				$newSpecialPrice = round(floatval($specialPrice) / floatval($currencyRate), 2);
			} catch (Mage_Core_Exception $e) {
				$this->_fault('data_invalid', $e->getMessage());
			}

			$product = Mage::getModel('catalog/product')
					->setStoreId(end($storeIds))
					->load($objProduct->getId());
			$product->setData('special_price', $newSpecialPrice);
			$product->setData('special_from_date', $fromDate);
			$product->setData('special_to_date', $toDate);
			$product->save();

			unset($objWebsite, $storeIds, $websiteBaseCurrencyCode, $arrCurrencyRates, $currencyRate, $newSpecialPrice, $product);

			$flag = true;
		}

		return $flag;
	}

	/**
	 * Retrieve product special price
	 *
	 * @param int|string $productId
	 * @param string|int $store
	 * @return array
	 */
	public function getSpecialPrice($productId, $store = null) {
		return $this->info($productId, $store, array(
			'attributes' => array(
				'special_price',
				'special_from_date',
				'special_to_date'
			)
				)
		);
	}

	private function _setWebsiteProductPrice($productId, $websitePriceArray, $websiteCurrencyCombination) {
		Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));

		$objDirCurrency = new Mage_Directory_Model_Currency();
		$arrAllowedCurrencies = $objDirCurrency->getConfigAllowCurrencies();

		foreach ($websitePriceArray as $websiteId => $price) {
			$websiteObj = new Mage_Core_Model_Website();
			$websiteObj->load($websiteId);

			$websiteBaseCurrencyCode = $websiteObj->getBaseCurrency()->getCode();
			if (empty($websiteBaseCurrencyCode)) {
				$websiteBaseCurrencyCode = Mage::app()->getBaseCurrencyCode();
			}

			if ($websiteCurrencyCombination[$websiteId] != $websiteBaseCurrencyCode && $websiteCurrencyCombination[$websiteId] != NULL) {
				$arrCurrencyRates = $objDirCurrency->getCurrencyRates($websiteBaseCurrencyCode, array_values($arrAllowedCurrencies));

				$currencyRate = '1';
				if (count($arrCurrencyRates)) {
					$currencyRate = $arrCurrencyRates[$websiteCurrencyCombination[$websiteId]];
				}

				try {
					$price = round(floatval($price) / floatval($currencyRate), 2);
				} catch (Mage_Core_Exception $e) {
					$this->_fault('data_invalid', $e->getMessage());
				}

				unset($arrCurrencyRates, $currencyRate);
			}

			$storeIds = $websiteObj->getStoreIds();
			$product = Mage::getModel('catalog/product')
					->setStoreId(end($storeIds))
					->load($productId);
			$product->setData('price', $price);
			$product->setData('sap_sync', 1);
			$product->save();

			unset($websiteObj, $websiteBaseCurrencyCode, $storeIds, $product);
		}
	}

}