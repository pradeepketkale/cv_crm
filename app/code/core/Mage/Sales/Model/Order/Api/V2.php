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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Order API V2
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Api_V2 extends Mage_Sales_Model_Order_Api {

	/**
	 * Retrieve list of orders by filters
	 *
	 * @param array $filters
	 * @return array
	 */
	public function items($filters = null) {
		//TODO: add full name logic
		$billingAliasName = 'billing_o_a';
		$shippingAliasName = 'shipping_o_a';

		/**
		 * Canceled & Complete Orders will not get downloaded.
		 *
		 * @author Anutosh Ghosh
		 */
		$collection = Mage::getModel("sales/order")->getCollection()
				->addAttributeToSelect('*')
				->addAddressFields()
				->addExpressionFieldToSelect(
						'billing_firstname', "{{billing_firstname}}", array('billing_firstname' => "$billingAliasName.firstname")
				)
				->addExpressionFieldToSelect(
						'billing_lastname', "{{billing_lastname}}", array('billing_lastname' => "$billingAliasName.lastname")
				)
				->addExpressionFieldToSelect(
						'shipping_firstname', "{{shipping_firstname}}", array('shipping_firstname' => "$shippingAliasName.firstname")
				)
				->addExpressionFieldToSelect(
						'shipping_lastname', "{{shipping_lastname}}", array('shipping_lastname' => "$shippingAliasName.lastname")
				)
				->addExpressionFieldToSelect(
						'billing_name', "CONCAT({{billing_firstname}}, ' ', {{billing_lastname}})", array('billing_firstname' => "$billingAliasName.firstname", 'billing_lastname' => "$billingAliasName.lastname")
				)
				->addExpressionFieldToSelect(
						'shipping_name', 'CONCAT({{shipping_firstname}}, " ", {{shipping_lastname}})', array('shipping_firstname' => "$shippingAliasName.firstname", 'shipping_lastname' => "$shippingAliasName.lastname")
				)
				->addFieldToFilter('status', array(
					'eq' => 'processing'
				));
		$collection->getSelect()
				->where("SapSync = '0'");

		$preparedFilters = array();
		if (isset($filters->filter)) {
			foreach ($filters->filter as $_filter) {
				$preparedFilters[][$_filter->key] = $_filter->value;
			}
		}

		if (isset($filters->complex_filter)) {
			foreach ($filters->complex_filter as $_filter) {
				$_value = $_filter->value;
				if (is_object($_value)) {
					$preparedFilters[][$_filter->key] = array(
						$_value->key => $_value->value
					);
				}
				elseif (is_array($_value)) {
					$preparedFilters[][$_filter->key] = array(
						$_value['key'] => $_value['value']
					);
				}
				else {
					$preparedFilters[][$_filter->key] = $_value;
				}
			}
		}

		if (!empty($preparedFilters)) {
			try {
				foreach ($preparedFilters as $preparedFilter) {
					foreach ($preparedFilter as $field => $value) {
						if (isset($this->_attributesMap['order'][$field])) {
							$field = $this->_attributesMap['order'][$field];
						}

						$collection->addFieldToFilter($field, $value);
					}
				}
			} catch (Mage_Core_Exception $e) {
				$this->_fault('filters_invalid', $e->getMessage());
			}
		}

		$result = array();

		foreach ($collection as $order) {
			$rOrder = $this->_getAttributes($order, 'order');

			$rOrder['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
			$rOrder['billing_address'] = $this->_getAttributes($order->getBillingAddress(), 'order_address');

			if ($rOrder['shipping_address']['region_id'] != NULL && array_key_exists('region_id', $rOrder['shipping_address'])) {
				$regionObj = new Mage_Directory_Model_Region();
				$regionObj->load($rOrder['shipping_address']['region_id']);
				if ($regionObj->getData('code') != NULL) {
					$rOrder['shipping_address']['region'] = $rOrder['shipping_address']['region'] . '@' . $regionObj->getData('code');
				}
				unset($regionObj);
			}
			if ($rOrder['billing_address']['region_id'] != NULL && array_key_exists('region_id', $rOrder['billing_address'])) {
				$regionObj = new Mage_Directory_Model_Region();
				$regionObj->load($rOrder['billing_address']['region_id']);
				if ($regionObj->getData('code') != NULL) {
					$rOrder['billing_address']['region'] = $rOrder['billing_address']['region'] . '@' . $regionObj->getData('code');
				}
				unset($regionObj);
			}

			$rOrder['items'] = array();
			foreach ($order->getAllItems() as $item) {
				$item['tax_code'] = $this->_setTaxCode($order, $item->getTaxPercent());
				$rOrder['items'][] = $this->_getAttributes($item, 'order_item');
			}

			$rOrder['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');

			$rOrder['status_history'] = array();
			foreach ($order->getAllStatusHistory() as $history) {
				$rOrder['status_history'][] = $this->_getAttributes($history, 'order_status_history');
			}

			//$rOrder['TaxCode']=_setTaxCode();
			$result[] = $rOrder;
			//$result[] = $this->_getAttributes($order, 'order');
		}

		return $result;
	}

	protected function _setTaxCode($ordObj, $taxPercent) {
		try {
			$this->_taxCode = '';
			$read = Mage::getSingleton("core/resource")->getConnection("core_read");

			$billingAddressCountryId = $ordObj->getBillingAddress()->getData('country_id');
			$billingAddressRegionId = $ordObj->getBillingAddress()->getData('region_id');
			$billingAddressPostcode = $ordObj->getBillingAddress()->getData('postcode');

			$sql = sprintf("SELECT * FROM %s
							WHERE rate = '%f'
								AND tax_country_id = '%s'", Insync_Sap_Model_Config::getTableSapContext('tax_calculation_rate'), $taxPercent, $billingAddressCountryId);
			$dataSetTaxRate = $read->fetchAll($sql);

			if (!empty($dataSetTaxRate)) {
				foreach ($dataSetTaxRate as $_data) {
					if (empty($_data['tax_region_id']) || ($billingAddressRegionId == $_data['tax_region_id'])) {
						if (!isset($_data['zip_is_range']) || empty($_data['zip_is_range'])) {
							if ($_data['tax_postcode'] == '*' || ($billingAddressPostcode == $_data['tax_postcode'])) {

								return $this->_taxCode = $_data['code'];
							}
							else {
								return $this->_taxCode = Insync_Sap_Model_Config::SALES_ORDER_NO_TAX_CODE_AVAILABLE;
								// No Tax Rate Found, for the exact Post Code
								// Nothing will be set
							}
						}
						else {
							if ($_data['zip_from'] <= $billingAddressPostcode && $_data['zip_to'] >= $billingAddressPostcode) {
								return $this->_taxCode = $_data['code'];
							}
							else {
								return $this->_taxCode = Insync_Sap_Model_Config::SALES_ORDER_NO_TAX_CODE_AVAILABLE;
								// No Tax Rate Found, for the range Post Code
								// Nothing will be set
							}
						}
					}
					else {
						return $this->_taxCode = Insync_Sap_Model_Config::SALES_ORDER_NO_TAX_CODE_AVAILABLE;
						// No Tax Rate Found, for the Region ID
						// Nothing will be set
					}
				}
			}
		} catch (Exception $e) {
			//echo $e;
		}
	}

	public function syncOrderSBO($orderIncrementId, $flag) {
		try {
			$conn = Mage::getSingleton('core/resource')->getConnection('sales_write');
			$data = array('SapSync' => $flag);
			$where = "increment_id = '" . $orderIncrementId . "'";
			$conn->update(Insync_Sap_Model_Config::getTableSapContext('sales_order'), $data, $where);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}

}
