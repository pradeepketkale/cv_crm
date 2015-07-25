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
 * Layer price filter
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Layer_Filter_Price extends Mage_Catalog_Model_Layer_Filter_Abstract
{
    const XML_PATH_RANGE_CALCULATION    = 'catalog/layered_navigation/price_range_calculation';
    const XML_PATH_RANGE_STEP           = 'catalog/layered_navigation/price_range_step';

    const RANGE_CALCULATION_AUTO    = 'auto';
    const RANGE_CALCULATION_MANUAL  = 'manual';
    const MIN_RANGE_POWER = 10;

    /**
     * Resource instance
     *
     * @var Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
     */
    protected $_resource;

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_requestVar = 'price';
    }

    /**
     * Retrieve resource instance
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Layer_Filter_Price
     */
    protected function _getResource()
    {
        if (is_null($this->_resource)) {
            $this->_resource = Mage::getResourceModel('catalog/layer_filter_price');
        }
        return $this->_resource;
    }

    /**
     * Get price range for building filter steps
     *
     * @return int
     */
    public function getPriceRange()
    {
        $range = $this->getData('price_range');
        if (!$range) {
            $currentCategory = Mage::registry('current_category_filter');
            if ($currentCategory) {
                $range = $currentCategory->getFilterPriceRange();
            } else {
                $range = $this->getLayer()->getCurrentCategory()->getFilterPriceRange();
            }

            $maxPrice = $this->getMaxPriceInt();
            if (!$range) {
                $calculation = Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_CALCULATION);
                if ($calculation == self::RANGE_CALCULATION_AUTO) {
                    $index = 1;
                    do {
                        $range = pow(10, (strlen(floor($maxPrice)) - $index));
                        $items = $this->getRangeItemCounts($range);
                        $index++;
                    }
                    while($range > self::MIN_RANGE_POWER && count($items) < 2);
                } else {
                    $range = Mage::app()->getStore()->getConfig(self::XML_PATH_RANGE_STEP);
                }
            }

            while (ceil($maxPrice / $range) > 25) {
                $range *= 10;
            }

            $this->setData('price_range', $range);
        }

        return $range;
    }

    /**
     * Get maximum price from layer products set
     *
     * @return float
     */
    public function getMaxPriceInt()
    {
        $maxPrice = $this->getData('max_price_int');
        if (is_null($maxPrice)) {
            $maxPrice = $this->_getResource()->getMaxPrice($this);
            $maxPrice = floor($maxPrice);
            $this->setData('max_price_int', $maxPrice);
        }

        return $maxPrice;
    }

    /**
     * Get information about products count in range
     *
     * @param   int $range
     * @return  int
     */
    public function getRangeItemCounts($range)
    {
        $rangeKey = 'range_item_counts_' . $range;
        $items = $this->getData($rangeKey);
        if (is_null($items)) {
            $items = $this->_getResource()->getCount($this, $range);
            $this->setData($rangeKey, $items);
        }

        return $items;
    }

    /**
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $store      = Mage::app()->getStore();
        $fromPrice  = $store->formatPrice(($value-1)*$range);
        $toPrice    = $store->formatPrice($value*$range);

        return Mage::helper('catalog')->__('%s - %s', $fromPrice, $toPrice);
    }

    /**
     * Get price aggreagation data cache key
     * @deprecated after 1.4
     * @return string
     */
    protected function _getCacheKey()
    {
        $key = $this->getLayer()->getStateKey()
            . '_PRICES_GRP_' . Mage::getSingleton('customer/session')->getCustomerGroupId()
            . '_CURR_' . Mage::app()->getStore()->getCurrentCurrencyCode()
            . '_ATTR_' . $this->getAttributeModel()->getAttributeCode()
            . '_LOC_'
            ;
        $taxReq = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $key.= implode('_', $taxReq->getData());

        return $key;
    }

	// below function added on date 02-07-2014
	 protected function _getItemsData()
	    {
		return array();	
	}

    /**
     * Get data for build price filter items
     *
     * @return array
     */
    protected function _getItemsData02072014()
    {
        $priceCalculation=Mage::getStoreConfig('catalog/layered_navigation/price_range_calculation');
        if($priceCalculation !='auto'){
            $range = Mage::getStoreConfig('catalog/layered_navigation/price_range_step');
        }
        else{ 
            $range =$this->getPriceRange();
        }
        
        $dbRanges   = $this->getRangeItemCounts($range);
        $data       = array();
        
        $currency_code = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$mm = 1;
		
		/*********** Original Start **************************/
        /*foreach ($dbRanges as $index=>$count) {
            $data[] = array(
                'label' => $this->_renderItemLabel($range, $index),
                'value' => $index . ',' . $range,
                'count' => $count,
            );*/
		/*********** Original End **************************/
		
        /******************************* Start Added By suresh 16-05-2012 for custom price range *****************/
		$maxPrice = $this->getMaxPriceInt();
        
		if($currency_code == 'USD')
		{
			///////// 0-5 ///////////////////////
            $data[] = array(
            	'label' => '$0 - $5',
                'value' => 1 . ',' . 5,
                'count' => 1,
            );
            
            if($maxPrice>=5)
            {
	            ///////// 5-10 ///////////////////////
	            $data[] = array(
	            	'label' => '$5 - $10',
	                'value' => 2 . ',' . 5,
	                'count' => 1,
	            );
            }    
            
            if($maxPrice>=10)
            {
	            ///////// 10-15 ///////////////////////
	            $data[] = array(
	            	'label' => '$10 - $15',
	                'value' => 3 . ',' . 5,
	                'count' => 1,
	            );
            }    
            
            if($maxPrice>=15)
            {
				///////// 15-20 ///////////////////////
	            $data[] = array(
	            	'label' => '$15 - $20',
	                'value' => 4 . ',' . 5,
	                'count' => 1,
	            );            	
            }
            
            if($maxPrice>=20)
            {
            	///////// 20-25 ///////////////////////
	            $data[] = array(
	            	'label' => '$20 - $25',
	                'value' => 5 . ',' . 5,
	                'count' => 1,
	            );	
            }
            
            if($maxPrice>=25)
            {
            	///////// 25-50 ///////////////////////            
	            $data[] = array(
	            	'label' => '$25 - $50',
	                'value' => 2 . ',' . 25,
	                'count' => 1,
	            );	
            }
            
            if($maxPrice>=50)
            {
            	///////// 50-100 ///////////////////////            
	            $data[] = array(
	            	'label' => '$50 - $100',
	                'value' => 2 . ',' . 50,
	                'count' => 1,
	            );	
            }
            
			if($maxPrice>=100)
            {
            	///////// 100-1000 ///////////////////////            
	            $data[] = array(
	            	'label' => '$100 - $1K',
	            	//'label' => $this->_renderItemLabel(400, 1.5),
	                'value' => 1.11111 . ',' . 900,
	                'count' => 1,
	            );	
            }
            
			if($maxPrice>=1000)
            {
            	///////// 1000-10000 ///////////////////////            
	            $data[] = array(
	            	'label' => '$1K - $10K',
	                'value' => 1.111111 . ',' . 9000,
	                'count' => 1,
	            );	
            }
            
			if($maxPrice>=10000)
            {
            	///////// 10000-100000 ///////////////////////            
	            $data[] = array(
	            	'label' => '$10K - $100K',
	                'value' => 1.1111111 . ',' . 90000,
	                'count' => 1,
	            );	
            }
                               
		}
		else {
			///////// 0-200 ///////////////////////
	        $data[] = array(
		    	'label' => $this->_renderItemLabel(200, 1),
		        'value' => 1 . ',' . 200,
		        'count' => 1,
		    );
		    
		    if($maxPrice>=200)
            {
		    	///////// 200-400 ///////////////////////        
	            $data[] = array(
	               'label' => $this->_renderItemLabel(200, 2),
	               'value' => 2 . ',' . 200,
	               'count' => 1,
	            );
            }

            if($maxPrice>=400)
            {
            	///////// 400-600 ///////////////////////        
	            $data[] = array(
	                'label' => $this->_renderItemLabel(200, 3),
	                'value' => 3 . ',' . 200,
	                'count' => 1,
                );
            }
		     
            if($maxPrice>=600)
            {
            	///////// 600-800 ///////////////////////        
		        $data[] = array(
			    	'label' => $this->_renderItemLabel(200, 4),
			        'value' => 4 . ',' . 200,
			        'count' => 1,
			    );
            }        
		    
            if($maxPrice>=800)
            {
            	///////// 800-1000 ///////////////////////        
	            $data[] = array(
	                'label' => 'Rs. 800 - Rs. 1K',
	                'value' => 5 . ',' . 200,
	                'count' => 1,
	            );
            }

			if($maxPrice>=1000)
            {
            	///////// 1000-10000 ///////////////////////        
	            $data[] = array(
	                //'label' => 'Rs. 1K - Rs. 10K',1.111111 . ',' . 9000
	                'label' => 'Rs. 1K - Rs. 10K',
	                'value' => 1.111111 . ',' . 9000,
	                'count' => 1,
	            );
            }
            
			if($maxPrice>=10000)
            {
            	///////// 10000-100000 ///////////////////////        
	            $data[] = array(
	                'label' => 'Rs. 10K - Rs. 100K',
	                //'label' => $this->_renderItemLabel(90000, 1.1111111),
	                'value' => 1.1111111 . ',' . 90000,
	                'count' => 1,
	            );
            }
            
			if($maxPrice>=100000)
            {
            	///////// 100000-1000000 ///////////////////////        
	            $data[] = array(
	                'label' => 'Rs. 100K - Rs. 1000K',
	                //'label' => $this->_renderItemLabel(900000, 1.11111111),
	                'value' => 1.11111111 . ',' . 900000,
	                'count' => 1,
	            );
            }
		               
		}

		/******************************* End Added By suresh 16-05-2012 for custom price range *****************/
		
        return $data;
    }

    /**
     * Apply price range filter to collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param $filterBlock
     *
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function apply(Zend_Controller_Request_Abstract $request, $filterBlock)
    {
        /**
         * Filter must be string: $index,$range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }

        list($index, $range) = $filter;

        if ((int)$index && (int)$range) {
            $this->setPriceRange((int)$range);

            $this->_applyToCollection($range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );

            $this->_items = array();
        }
        return $this;
    }

    /**
     * Apply filter value to product collection based on filter range and selected value
     *
     * @param int $range
     * @param int $index
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    protected function _applyToCollection($range, $index)
    {
        $this->_getResource()->applyFilterToCollection($this, $range, $index);
        return $this;
    }

    /**
     * Retrieve active customer group id
     *
     * @return int
     */
    public function getCustomerGroupId()
    {
        $customerGroupId = $this->_getData('customer_group_id');
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        return $customerGroupId;
    }

    /**
     * Set active customer group id for filter
     *
     * @param int $customerGroupId
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function setCustomerGroupId($customerGroupId)
    {
        return $this->setData('customer_group_id', $customerGroupId);
    }

    /**
     * Retrieve active currency rate for filter
     *
     * @return float
     */
    public function getCurrencyRate()
    {
        $rate = $this->_getData('currency_rate');
        if (is_null($rate)) {
            $rate = Mage::app()->getStore($this->getStoreId())->getCurrentCurrencyRate();
        }
        if (!$rate) {
            $rate = 1;
        }
        return $rate;
    }

    /**
     * Set active currency rate for filter
     *
     * @param float $rate
     * @return Mage_Catalog_Model_Layer_Filter_Price
     */
    public function setCurrencyRate($rate)
    {
        return $this->setData('currency_rate', $rate);
    }
}
