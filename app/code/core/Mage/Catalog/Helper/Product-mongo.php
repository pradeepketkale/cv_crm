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
 * Catalog category helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Product extends Mage_Core_Helper_Url
{
    const XML_PATH_PRODUCT_URL_SUFFIX           = 'catalog/seo/product_url_suffix';
    const XML_PATH_PRODUCT_URL_USE_CATEGORY     = 'catalog/seo/product_use_categories';
    const XML_PATH_USE_PRODUCT_CANONICAL_TAG    = 'catalog/seo/product_canonical_tag';

    /**
     * Cache for product rewrite suffix
     *
     * @var array
     */
    protected $_productUrlSuffix = array();

    protected $_statuses;

    protected $_priceBlock;

    /**
     * Retrieve product view page url
     *
     * @param   mixed $product
     * @return  string
     */
    public function getProductUrl($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            return $product->getProductUrl();
        }
        elseif (is_numeric($product)) {
            return Mage::getModel('catalog/product')->load($product)->getProductUrl();
        }
        return false;
    }

    /**
     * Retrieve product price
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  float
     */
    public function getPrice($product)
    {
        return $product->getPrice();
    }

    /**
     * Retrieve product final price
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  float
     */
    public function getFinalPrice($product)
    {
        return $product->getFinalPrice();
    }

    /**
     * Retrieve base image url
     *
     * @return string
     */
    public function getImageUrl($product)
    {
        $url = false;
        if (!$product->getImage()) {
            $url = Mage::getDesign()->getSkinUrl('images/no_image.jpg');
        }
        elseif ($attribute = $product->getResource()->getAttribute('image')) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve small image url
     *
     * @return unknown
     */
    public function getSmallImageUrl($product)
    {
        $url = false;
        if (!$product->getSmallImage()) {
            $url = Mage::getDesign()->getSkinUrl('images/no_image.jpg');
        }
        elseif ($attribute = $product->getResource()->getAttribute('small_image')) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve thumbnail image url
     *
     * @return unknown
     */
    public function getThumbnailUrl($product)
    {
        return '';
    }

    public function getEmailToFriendUrl($product)
    {
        $categoryId = null;
        if ($category = Mage::registry('current_category')) {
            $categoryId = $category->getId();
        }
        return $this->_getUrl('sendfriend/product/send', array(
            'id' => $product->getId(),
            'cat_id' => $categoryId
        ));
    }

    public function getStatuses()
    {
        if(is_null($this->_statuses)) {
            $this->_statuses = array();//Mage::getModel('catalog/product_status')->getResourceCollection()->load();
        }

        return $this->_statuses;
    }

    /**
     * Check if a product can be shown
     *
     * @param  Mage_Catalog_Model_Product|int $product
     * @return boolean
     */
    public function canShow($product, $where = 'catalog')
    {
        if (is_int($product)) {
            $product = Mage::getModel('catalog/product')->load($product);
        }

        /* @var $product Mage_Catalog_Model_Product */

        if (!$product->getId()) {
            return false;
        }

        return $product->isVisibleInCatalog() && $product->isVisibleInSiteVisibility();
    }

    /**
     * Retrieve product rewrite sufix for store
     *
     * @param int $storeId
     * @return string
     */
    public function getProductUrlSuffix($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }

        if (!isset($this->_productUrlSuffix[$storeId])) {
            $this->_productUrlSuffix[$storeId] = Mage::getStoreConfig(self::XML_PATH_PRODUCT_URL_SUFFIX, $storeId);
        }
        return $this->_productUrlSuffix[$storeId];
    }

    /**
     * Check if <link rel="canonical"> can be used for product
     *
     * @param $store
     * @return bool
     */
    public function canUseCanonicalTag($store = null)
    {
        return Mage::getStoreConfig(self::XML_PATH_USE_PRODUCT_CANONICAL_TAG, $store);
    }

    /**
     * Return information array of product attribute input types
     * Only a small number of settings returned, so we won't break anything in current dataflow
     * As soon as development process goes on we need to add there all possible settings
     *
     * @param string $inputType
     * @return array
     */
    public function getAttributeInputTypes($inputType = null)
    {
        /**
        * @todo specify there all relations for properties depending on input type
        */
        $inputTypes = array(
            'multiselect'   => array(
                'backend_model'     => 'eav/entity_attribute_backend_array'
            ),
            'boolean'       => array(
                'source_model'      => 'eav/entity_attribute_source_boolean'
            )
        );

        if (is_null($inputType)) {
            return $inputTypes;
        } else if (isset($inputTypes[$inputType])) {
            return $inputTypes[$inputType];
        }
        return array();
    }

    /**
     * Return default attribute backend model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeBackendModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    /**
     * Return default attribute source model by input type
     *
     * @param string $inputType
     * @return string|null
     */
    public function getAttributeSourceModelByInputType($inputType)
    {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    /**
     * Inits product to be used for product controller actions and layouts
     * $params can have following data:
     *   'category_id' - id of category to check and append to product as current.
     *     If empty (except FALSE) - will be guessed (e.g. from last visited) to load as current.
     *
     * @param int $productId
     * @param Mage_Core_Controller_Front_Action $controller
     * @param Varien_Object $params
     *
     * @return false|Mage_Catalog_Model_Product
     */
    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new Varien_Object();
        }

        // Init and load product
        Mage::dispatchEvent('catalog_controller_product_init_before', array('controller_action' => $controller));

        if (!$productId) {
            return false;
        }
		$product = Mage::getModel('catalog/product');
       $product->setStoreId(Mage::app()->getStore()->getId())
	    	    ->setEntityId($productId);
		   
			
//below line commented and set the product id on uabove for set entity id on dated 21-03-2014.......................		
			//->load($productId);

        /*if (!$this->canShow($product)) {
            return false;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }*/

        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && ($categoryId !== false)) {
            $lastId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
            if ($product->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        }

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            Mage::register('current_category', $category);
        }

        // Register current data and dispatch final events
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product' => $product));
            Mage::dispatchEvent('catalog_controller_product_init_after',
                            array('product' => $product,
                                'controller_action' => $controller
                            )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }
//New function added for call meta title in header page on dated 21-03-2014-------by dileswar	
	public function productheaderLayout($productId)
    {
		$this->getLayout()->createBlock('catalog/breadcrumbs');
		
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {

			if($this->getProductTypeId($productId) == 'configurable'){
				$product = $this->getProduct($productId);
				$productgetName = $product->getName();			
				}
			else{
            	$product = $this->loadnew($productId);
				$productgetName = $product->getProductName();				
				}
		//$product = $this->getProduct1($productId);
			
			Mage::unregister('product');
			Mage::unregister('current_product');
			Mage::register('current_product', $product);
			Mage::register('product', $product);
			
			//$productgetName = $this->fetchProductAttr($productId,'productname');
			//$productgetName = $product->getProductName();
            //$category_arr = $product->getCategoryIds();
			
			$categorynvendor_arr = $this->fetchProductCategory($productId);
			$product_vendor_name = $categorynvendor_arr[1];
			$arryCat = array('6'=>'Jewelry','74' =>'Sarees','5'=>'Home Decor','1070'=>'Home Furnishing','4'=>'Clothing','9'=>'Bags','8'=>'Accessories','284'=>'Bath & Beauty','69'=>'Food & Health','1114'=>'Rakhi Gifts');
			if(!$category_arr[0] == 0)
			{			
				$product_category_name = $arryCat[$categorynvendor_arr[0]];
			}
			else
			{
				$product_category_name = 'Online Shopping';
			}

			//unset($product_category_name);
    		//unset($product_vendor_name);

     		//if($category_arr[0] == 2)
     		//{
     			//$category = Mage::getModel('catalog/category')->load($category_arr[1]);
     		//}
     		//else {
     			//$category = Mage::getModel('catalog/category')->load($category_arr[0]);
     		//}
    
     		//$product_category_name = $category->getName();
     
     		//$product_vendor_name = Mage::helper('udropship')->getVendorName($product->getUdropshipVendor());
     		
            //$title = $product->getMetaTitle()."-".$product_category_name."-".$product_vendor_name;
            //$title = $product->getName()."-".$product_category_name."-".$product_vendor_name;
			$title = $productgetName."-".$product_category_name."-".$product_vendor_name;
            if ($title) {
                $headBlock->setTitle($title);
            }
			$keyword = $product->getMetaKeyword();
            //$keyword = $this->fetchProductAttr($productId,'metakeyword');
			$currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } elseif($currentCategory) {
                $headBlock->setKeywords($productgetName);
				//$headBlock->setKeywords($productgetName);
            }
            $description = $product->getDescription();
			//$description = $this->fetchProductAttr($productId,'metadesc');
			//$prdDescription = $this->fetchProductAttr($productId,'fulldesc');

           // if ($description) {
                $headBlock->setDescription(substr($description,0,255));
            //} else {
               // $headBlock->setDescription(Mage::helper('core/string')->substr($prdDescription, 0, 255));
            //}
            if ($this->canUseCanonicalTag()) {
                $params = array('_ignore_category'=>true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
            }

        }

        }
public function getProductTypeId($entiyPrdId)
		{
			$readId1 = Mage::getSingleton('core/resource')->getConnection('core_read');
			$typeIdquery1 = "SELECT `type_id` FROM `catalog_product_entity` WHERE `entity_id` ='".$entiyPrdId."'";
			$resultoftype = $readId1->query($typeIdquery1)->fetch();
			$typeid = $resultoftype['type_id'];
			return $typeid;
		}
public function loadnew($entiyPrdId){
		$product = Mage::getModel('catalog/product');

		$product->setStoreId(Mage::app()->getStore()->getId())
	    	    ->setEntityId($entiyPrdId);
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
		//for product images
		$prdImageQuery = "SELECT `value_id`,`value` as `file` FROM `catalog_product_entity_media_gallery` WHERE `entity_id` = '".$entiyPrdId."'";
		$resultImage['images'] = 	$readId->query($prdImageQuery)->fetchAll();
		$resultImage['values'] = array();
		$i = 0;
		foreach($resultImage['images'] as $_resultimg)
		{
			$resultImage['images'][$i]['label'] = '';
			$resultImage['images'][$i]['position'] = $i+1;
			$resultImage['images'][$i]['disabled'] = 0;
			$resultImage['images'][$i]['label_default'] = '';
			$resultImage['images'][$i]['position_default'] = $i+1;
			$resultImage['images'][$i]['disabled_default'] = 0;
			$i++;
		}
//check inventory `is_in_stock`
	$catStockItem = "SELECT `is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$entiyPrdId."'";
	$resultcatStockItem = $readId->query($catStockItem)->fetch();
	$checkstocks = $resultcatStockItem['is_in_stock'];
	
//check international ship allowed or not
	$attrquery = "SELECT `value` FROM `catalog_product_entity_int` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` = 627";
	$resultAttr = $readId->query($attrquery)->fetch();
	$checkAvailable = $resultAttr['value'];	// 1- mean available 0- not availabel
//type_id & sku
	$typeIdnskuquery = "SELECT `type_id`,`sku` FROM `catalog_product_entity` WHERE `entity_id` ='".$entiyPrdId."'";
	$resultofskunidtype = $readId->query($typeIdnskuquery)->fetch();
	$typeid = $resultofskunidtype['type_id'];
	$skuu = $resultofskunidtype['sku'];
//meta keyword,  Full Desc, Short Desc			
$catentityTextQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_text` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('57','58','68') ";
	$resultcattext	= $readId->query($catentityTextQuery)->fetchAll();
	$cattext = array();

foreach($resultcattext as $_resultcattext){$cattext[$_resultcattext['attribute_id']] = $_resultcattext['value'];}
	
	$fulldesc = $cattext['57'];
	$shortDesc = $cattext['58'];
	$metaKeyword = $cattext['68'];
//---------------------------------------
//price,special price,shipping cost, internationalshipping price

$catentityDecimalQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('60','61','535','655') ";
$resultcatdecimal	= $readId->query($catentityDecimalQuery)->fetchAll();

foreach($resultcatdecimal as $_resultcatdecimal){$cattext[$_resultcatdecimal['attribute_id']] = $_resultcatdecimal['value'];}
	
	if($currentCurrencyCode !== 'INR'){
		$priceorginal = ($cattext['60']*(1.5));
		$specialPrice = ($cattext['61']*(1.5));
		}
	else{	
		$priceorginal = $cattext['60'];
		$specialPrice = $cattext['61'];
		}
	$shippingcost = $cattext['535'];
	//$intershippingcost = $cattext['643'];//local	
	$intershippingcost = $cattext['655'];//live id
//-----------------------------------			

//special_price_from _date,special_price_to_date

$catentityDatetimeQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_datetime` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('62','63') ";
$resultcatdatetime	= $readId->query($catentityDatetimeQuery)->fetchAll();

foreach($resultcatdatetime as $_resultcatdatetime){$cattext[$_resultcatdatetime['attribute_id']] = $_resultcatdatetime['value'];}
	
	
	$specialPricedateFrom = $cattext['62'];
	$specialPricedateTo = $cattext['63'];

//-----------------------------------			



//Meta Desc, product name
$catentityvarcharQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('56','69') ";
$resultcatvarchar	= $readId->query($catentityvarcharQuery)->fetchAll();
foreach($resultcatvarchar as $_resultcatvarchar){$cattext[$_resultcatvarchar['attribute_id']] = $_resultcatvarchar['value'];}
	
	$productName = $cattext['56'];
	$metaDesc = $cattext['69'];
//-----------------------------------			

$categorynvendor_arr = $this->fetchProductCategory($entiyPrdId);
			$product_vendor_name = $categorynvendor_arr[1];
			$arryCat = array('6'=>'Jewelry','74' =>'Sarees','5'=>'Home Decor','1070'=>'Home Furnishing','4'=>'Clothing','9'=>'Bags','8'=>'Accessories','284'=>'Bath & Beauty','69'=>'Food & Health','1114'=>'Rakhi Gifts');
			if(!$category_arr[0] == 0)
			{			
				$product_category_name = $arryCat[$categorynvendor_arr[0]];
			}
			else
			{
				$product_category_name = 'Online Shopping';
			}

	$product->setTypeId($typeid);
	$product->setSku($skuu);
	$product->setProductName($productName);
	$product->setMetaKeyword($metaKeyword);
	$product->setMetaDescription($metaDesc);
	$product->setDescription($fulldesc);
	$product->setShortDescription($shortDesc);
	$product->setMediaGallery($resultImage);
	$product->setImage($resultImage['images'][0]['file']);
	$product->setSmallImage($resultImage['images'][0]['file']);
    $product->setThumbnail($resultImage['images'][0]['file']);
	$product->setPrice($priceorginal);
	$product->setSpecialPrice($specialPrice);
	$product->setSpecialFromDate($specialPricedateFrom);
	$product->setSpecialToDate($specialPricedateTo);
	$product->setInternationalShipping($checkAvailable);
	$product->setShippingcost($shippingcost);
	$product->setIntershippingcost($intershippingcost);
	$product->setUdropshipVendor($product_vendor_name);
	$product->setIsInStock($checkstocks);
	return $product;

}

/*public function fetchProductAttr($entiyPrdId,$attr){

		$readId = Mage::getSingleton('core/resource')->getConnection('core_read');

		switch($attr){
			case "productname" :
		//For meta title
				$attrquery = "SELECT * FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` = 56";
				break;
		case  "metakeyword" :
			//For meta keword
				$attrquery = "SELECT * FROM `catalog_product_entity_text` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` = 68";
				break;
		case  "metadesc" :	//For meta Desc
				$attrquery = "SELECT * FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` = 69";
				break;
		case  "fulldesc" :
			//For fulldesc
				$attrquery = "SELECT * FROM `catalog_product_entity_text` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` = 57";
				break;
		}

		$resultAttr = $readId->query($attrquery)->fetch();
		return $resultAttr['value'];
		//return $attr;

}*/

public function fetchProductCategory($entiyPrdId){
	$readcId = Mage::getSingleton('core/resource')->getConnection('core_read');
	$attrCatquery = "SELECT * FROM `catalog_product_craftsvilla3` WHERE `entity_id` = '".$entiyPrdId."'";
	$resultcat = $readcId->query($attrCatquery)->fetch();
	//$resultcat['category_id2'];
	//$resultcat['udropship_vendor'];
	$vendorQ = "SELECT * FROM `udropship_vendor` WHERE `vendor_id` = '".$resultcat['udropship_vendor']."'";
	$resultData = $readcId->query($vendorQ)->fetch();
	$vendorName = $resultData['vendor_name'];
	//$vendorDesc = $resultData['vendor_name'];
	return array($resultcat['category_id2'],$vendorName);	
	
	}
	

    /**
     * Prepares product options by buyRequest: retrieves values and assigns them as default.
     * Also parses and adds product management related values - e.g. qty
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return Mage_Catalog_Helper_Product
     */
    public function prepareProductOptions($product, $buyRequest)
    {
        $optionValues = $product->processBuyRequest($buyRequest);
        $optionValues->setQty($buyRequest->getQty());
        $product->setPreconfiguredValues($optionValues);

        return $this;
    }

    /**
     * Process $buyRequest and sets its options before saving configuration to some product item.
     * This method is used to attach additional parameters to processed buyRequest.
     *
     * $params holds parameters of what operation must be performed:
     * - 'current_config', Varien_Object or array - current buyRequest that configures product in this item,
     *   used to restore currently attached files
     * - 'files_prefix': string[a-z0-9_] - prefix that was added at frontend to names of file inputs,
     *   so they won't intersect with other submitted options
     *
     * @param Varien_Object|array $buyRequest
     * @param Varien_Object|array $params
     * @return Varien_Object
     */
    public function addParamsToBuyRequest($buyRequest, $params)
    {
        if (is_array($buyRequest)) {
            $buyRequest = new Varien_Object($buyRequest);
        }
        if (is_array($params)) {
            $params = new Varien_Object($params);
        }


        // Ensure that currentConfig goes as Varien_Object - for easier work with it later
        $currentConfig = $params->getCurrentConfig();
        if ($currentConfig) {
            if (is_array($currentConfig)) {
                $params->setCurrentConfig(new Varien_Object($currentConfig));
            } else if (!($currentConfig instanceof Varien_Object)) {
                $params->unsCurrentConfig();
            }
        }

        /*
         * Notice that '_processing_params' must always be object to protect processing forged requests
         * where '_processing_params' comes in $buyRequest as array from user input
         */
        $processingParams = $buyRequest->getData('_processing_params');
        if (!$processingParams || !($processingParams instanceof Varien_Object)) {
            $processingParams = new Varien_Object();
            $buyRequest->setData('_processing_params', $processingParams);
        }
        $processingParams->addData($params->getData());

        return $buyRequest;
    }

    /**
     * Return loaded product instance
     *
     * @param  int|string $productId (SKU or ID)
     * @param  int $store
     * @param  string $identifierType
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct($productId, $store, $identifierType = null) {
        $loadByIdOnFalse = false;
        if ($identifierType == null) {
            if (is_string($productId) && !preg_match("/^[+-]?[1-9][0-9]*$|^0$/", $productId)) {
                $identifierType = 'sku';
                $loadByIdOnFalse = true;
            } else {
                $identifierType = 'id';
            }
        }

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product');
        if ($store !== null) {
            $product->setStoreId($store);
        }
        if ($identifierType == 'sku') {
            $idBySku = $product->getIdBySku($productId);
            if ($idBySku) {
                $productId = $idBySku;
            }
            if ($loadByIdOnFalse) {
                $identifierType = 'id';
            }
        }

        if ($identifierType == 'id' && is_numeric($productId)) {
            $productId = !is_float($productId) ? (int) $productId : 0;
            $product->load($productId);
        }

        return $product;
    }

}
