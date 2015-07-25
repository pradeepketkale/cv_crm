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
 * Catalog data helper
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Helper_Data extends Mage_Core_Helper_Abstract
{
    const PRICE_SCOPE_GLOBAL               = 0;
    const PRICE_SCOPE_WEBSITE              = 1;
    const XML_PATH_PRICE_SCOPE             = 'catalog/price/scope';
    const XML_PATH_SEO_SAVE_HISTORY        = 'catalog/seo/save_rewrites_history';
    const CONFIG_USE_STATIC_URLS           = 'cms/wysiwyg/use_static_urls_in_catalog';
    const CONFIG_PARSE_URL_DIRECTIVES      = 'catalog/frontend/parse_url_directives';
    const XML_PATH_CONTENT_TEMPLATE_FILTER = 'global/catalog/content/tempate_filter';

    /**
     * Breadcrumb Path cache
     *
     * @var string
     */
    protected $_categoryPath;

    /**
     * Currenty selected store ID if applicable
     *
     * @var int
     */
    protected $_storeId = null;

    /**
     * Set a specified store ID value
     *
     * @param int $store
     */
    public function setStoreId($store)
    {
        $this->_storeId = $store;
        return $this;
    }

    /**
     * Return current category path or get it from current category
     * and creating array of categories|product paths for breadcrumbs
     *
     * @return string
     */
    public function getBreadcrumbPath()
    {
        if (!$this->_categoryPath) {

            $path = array();
            if ($category = $this->getCategory()) {
                $pathInStore = $category->getPathInStore();
                $pathIds = array_reverse(explode(',', $pathInStore));

                $categories = $category->getParentCategories();

                // add category path breadcrumb
                foreach ($pathIds as $categoryId) {
                    if (isset($categories[$categoryId]) && $categories[$categoryId]->getName()) {
                        $path['category'.$categoryId] = array(
                            'label' => $categories[$categoryId]->getName(),
                            'link' => $this->_isCategoryLink($categoryId) ? $categories[$categoryId]->getUrl() : ''
                        );
                    }
                }
            }

            if ($this->getProduct()) {
                $path['product'] = array('label'=>$this->getProduct()->getName());
            }

            $this->_categoryPath = $path;
        }
        return $this->_categoryPath;
    }

    /**
     * Check is category link
     *
     * @param int $categoryId
     * @return bool
     */
    protected function _isCategoryLink($categoryId)
    {
        if ($this->getProduct()) {
            return true;
        }
        if ($categoryId != $this->getCategory()->getId()) {
            return true;
        }
        return false;
    }

    /**
     * Return current category object
     *
     * @return Mage_Catalog_Model_Category|null
     */
    public function getCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * Retrieve current Product object
     *
     * @return Mage_Catalog_Model_Product|null
     */
    public function getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Retrieve Visitor/Customer Last Viewed URL
     *
     * @return string
     */
    public function getLastViewedUrl()
    {
        if ($productId = Mage::getSingleton('catalog/session')->getLastViewedProductId()) {
            $product = Mage::getModel('catalog/product')->load($productId);
            /* @var $product Mage_Catalog_Model_Product */
            if (Mage::helper('catalog/product')->canShow($product, 'catalog')) {
                return $product->getProductUrl();
            }
        }
        if ($categoryId = Mage::getSingleton('catalog/session')->getLastViewedCategoryId()) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            /* @var $category Mage_Catalog_Model_Category */
            if (!Mage::helper('catalog/category')->canShow($category)) {
                return '';
            }
            return $category->getCategoryUrl();
        }
        return '';
    }

    /**
     * Split SKU of an item by dashes and spaces
     * Words will not be broken, unless thir length is greater than $length
     *
     * @param string $sku
     * @param int $length
     * @return array
     */
    public function splitSku($sku, $length = 30)
    {
        return Mage::helper('core/string')->str_split($sku, $length, true, false, '[\-\s]');
    }

    /**
     * Retrieve attribute hidden fields
     *
     * @return array
     */
    public function getAttributeHiddenFields()
    {
        if (Mage::registry('attribute_type_hidden_fields')) {
            return Mage::registry('attribute_type_hidden_fields');
        } else {
            return array();
        }
    }

    /**
     * Retrieve attribute disabled types
     *
     * @return array
     */
    public function getAttributeDisabledTypes()
    {
        if (Mage::registry('attribute_type_disabled_types')) {
            return Mage::registry('attribute_type_disabled_types');
        } else {
            return array();
        }
    }

    /**
     * Retrieve Catalog Price Scope
     *
     * @return int
     */
    public function getPriceScope()
    {
        return Mage::getStoreConfig(self::XML_PATH_PRICE_SCOPE);
    }

    /**
     * Is Global Price
     *
     * @return bool
     */
    public function isPriceGlobal()
    {
        return $this->getPriceScope() == self::PRICE_SCOPE_GLOBAL;
    }

    /**
     * Indicate whether to save URL Rewrite History or not (create redirects to old URLs)
     *
     * @param int $storeId Store View
     * @return bool
     */
    public function shouldSaveUrlRewritesHistory($storeId = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_SEO_SAVE_HISTORY, $storeId);
    }

    /**
     * Check if the store is configured to use static URLs for media
     *
     * @return bool
     */
    public function isUsingStaticUrlsAllowed()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_USE_STATIC_URLS, $this->_storeId);
    }

    /**
     * Check if the parsing of URL directives is allowed for the catalog
     *
     * @return bool
     */
    public function isUrlDirectivesParsingAllowed()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PARSE_URL_DIRECTIVES, $this->_storeId);
    }

    /**
     * Retrieve template processor for catalog content
     *
     * @return Varien_Filter_Template
     */
    public function getPageTemplateProcessor()
    {
        $model = (string)Mage::getConfig()->getNode(self::XML_PATH_CONTENT_TEMPLATE_FILTER);
        return Mage::getModel($model);
    }


public function recomendedCachedProducts(){

$CacheIdProductDesktop = "recomdsktp-";
		$lifetime = 864000;		

		if ($cacheContentKey = Mage::app()->loadCache($CacheIdProductDesktop)){ 
		//echo "in cache";	
		return $cacheContentKey;
			}
		else{ 

$hlp = Mage::helper('generalcheck');
$mainconn = $hlp->getMaindbconnection();
$product_id = array();

	$mrktngProduct_query = mysql_query("SELECT `product_sku` FROM `mktngproducts`",$mainconn);

		while($mrktngProduct_result = mysql_fetch_array($mrktngProduct_query)) {

		$mrktngProduct_sku = $mrktngProduct_result['product_sku'];

		$mrktngproduct_SubQuery = mysql_query("SELECT `entity_id` FROM `catalog_product_entity` WHERE `sku` = '".$mrktngProduct_sku."'",$mainconn);

		$mrktngproduct_SubResult = mysql_fetch_array($mrktngproduct_SubQuery);

		array_push($product_id, $mrktngproduct_SubResult[0]);
		
		}

mysql_close($mainconn);

$statconn = $hlp->getStatsdbconnection();

	$trendingProduct_query = mysql_query("SELECT `product_id` FROM `craftsvilla_trending`",$statconn);
	
mysql_close($statconn);

	while($trendingProduct_result = mysql_fetch_array($trendingProduct_query)){

		$trending_productId = $trendingProduct_result['product_id'];
		array_push($product_id, $trending_productId);

	}

$json_product = json_encode($product_id);

$cacheContentKey = $json_product;
Mage::app()->saveCache($cacheContentKey, $CacheIdProductDesktop, $tags, $lifetime);

return $cacheContentKey;

}
}

public function recomendedDesktopProducts($viewProduct_id){

$product_id = $this->recomendedCachedProducts($viewProduct_id); 

$product_id = json_decode($product_id);

//$product_id = array(26055,26058,26056,25540,55420,49556,49355,26555,355263,13552);
$allproduct_count = count($product_id);

$view_id = array();

$viewProduct_idValue = $viewProduct_id;
$view_product_id = substr($viewProduct_id,-2);

	for($k=0;$k<$allproduct_count;$k++)
	{
					
			if((strpos($product_id[$k], $view_product_id) !== false) && ($product_id[$k] != $viewProduct_idValue)) 
			{

				array_push($view_id, $product_id[$k]);

			}


	}

$prod_count = min(count($view_id),4);
	$_columnCount = 4;
	$i=0; 

$j=0;
if(!empty($view_id)){
	$bodyhtml = "<br><br><div><h2>Recomended Products</h2><br></div></div>

	</div><div style='margin-left:auto;margin-right: auto;'>";
	}

foreach($view_id as $_resultProduct)
	{
if($j < $prod_count)
{
			$product = Mage::helper('catalog/product')->loadnew($_resultProduct);
   if($product->getPrice() != null)
{

	if ($i++%$_columnCount==0)
		{ 
			$bodyhtml .= "<ul class='products-grid first odd you_like'>";
		} 
			$prodImage = Mage::helper('catalog/image')->init($product, 'image')->resize(500,500);
			$firstlast = "";
		if(($i-1)%$_columnCount==0)
	{
			$firstlast = "first";
	}

	 elseif($i%$_columnCount==0){
		$firstlast = "last";
	}
		$bodyhtml .= "<li class='item ".$firstlast."'>";
		$bodyhtml .= "<div >";
		$bodyhtml .= "<a class='' title='".$prodImage."' href='".$product->getProductUrl()."'><img width='100%' height='auto' src='$prodImage' alt=''></a>";
		$bodyhtml .= "<p class='shopbrief' ><a title='' href='".$product->getProductUrl()."'>".substr($product->getName(),0,25)."..</a></p>";

				$storeurl = '';
				$vendorinfo= Mage::helper('udropship')->getVendor($product);
				$storeurl = Mage::helper('umicrosite')->getVendorUrl($vendorinfo->getData('vendor_id'));
				$storeurl = substr($storeurl, 0, -1);
				if($storeurl != '') {
		 
		$bodyhtml .= "<p class='vendorname'>by <a target='_blank' href='".$storeurl."'>".$vendorinfo->getVendorName()."</a></p>";
	 } 

	if(!$product->getSpecialPrice()):
		$bodyhtml .= "<div class='products price-box' > <span id='product-price-80805' class='regular-price'> <span class='price 123' > Rs. ".number_format($product->getPrice(),0)."</span> </span> </div>";
	else:
		$bodyhtml .= "<div class='products price-box'>";
		$bodyhtml .= "<p class='old-price' > <span class='price-label'></span> <span id='old-price-77268' class='price' >Rs. ".number_format($product->getPrice(),0)."</span> </p>";
		$bodyhtml .= "<p class='special-price' > <span class='price-label'></span> <span id='product-price-77268' class='price' >Rs. ".number_format($product->getSpecialPrice(),0)."</span> </p>";
		$bodyhtml .= "</div>";
	endif;
		$bodyhtml .= "<div class='clear'></div>";
		$bodyhtml .= "</div>";
		$bodyhtml .= "</li>";
	
	 if ($i%$_columnCount==0){
		$bodyhtml .= "</ul>";
	}
}
}$j++;
 }
	$bodyhtml .= "</div>";

//echo $bodyhtml;
return $bodyhtml;


}
	         	         

public function recomendedMobileProducts($viewProduct_id){

$product_id = $this->recomendedCachedProducts($viewProduct_id);

$product_id = json_decode($product_id);

$allproduct_count = count($product_id);

$view_id = array();

$viewProduct_idValue = $viewProduct_id;
$view_product_id = substr($viewProduct_id,-2);

	for($k=0;$k<$allproduct_count;$k++)
	{
					
			if((strpos($product_id[$k], $view_product_id) !== false) && $product_id[$k] != $viewProduct_idValue) 
			{

				array_push($view_id, $product_id[$k]);

			}

	}

$prod_count = min(count($view_id),4);
	$_columnCount = 2;
	$i=0; 

$j=0;
if(!empty($view_id)){
	$bodyhtml = "<br><br><div><h2 style='font-size:40px;'>Recomended Products</h2><br></div>

	</div><div style='margin-left:auto;margin-right: auto;'>";
	}

foreach($view_id as $_resultProduct)
	{
if($j < $prod_count)
{
			$product = Mage::helper('catalog/product')->loadnew($_resultProduct);
   if($product->getPrice() != null)
{

		if ($i++%$_columnCount==0)
			{ 
				$bodyhtml .= "<ul class='products-grid first odd you_like' style='z-index: 0;'>";
			} 
				
				$prodImage = Mage::helper('catalog/image')->init($product, 'image')->resize(500,500);
				$firstlast = "";
			if(($i-1)%$_columnCount==0)
		{
				$firstlast = "first";
		}

		 elseif($i%$_columnCount==0){
			$firstlast = "last";
		}
			$bodyhtml .= "<li class='item ".$firstlast."' style='width:43%;'>";
			$bodyhtml .= "<a class='' title='".$prodImage."' href='".$product->getProductUrl()."'><img width='100%' height='auto' src='$prodImage' alt=''></a>";
			$bodyhtml .= "<p class='shopbrief' style='padding-top: 25px;padding-bottom:15px'><a title='' style='font-size:30px;line-height: 26px;' href='".$product->getProductUrl()."'>".substr($product->getName(),0,25)."..</a></p>";

					$storeurl = '';
					$vendorinfo = Mage::helper('udropship')->getVendor($product);
					$storeurl = Mage::helper('umicrosite')->getVendorUrl($vendorinfo->getData('vendor_id'));
					$storeurl = substr($storeurl, 0, -1);
					if($storeurl != '') {
			 
			$bodyhtml .= "<p class='vendorname' style='padding-bottom:30px;'><font size='+3'>by </font><a target='_blank' href='".$storeurl."'><font size='+3'>".$vendorinfo->getVendorName()."</font></a></p>";
		 } 

		if(!$product->getSpecialPrice()):
			$bodyhtml .= "<div class='products price-box' style='padding-bottom:15px'> <span id='product-price-80805' class='regular-price'> <span class='price 123' style='font-size: 35px;padding-bottom:35px'> Rs. ".number_format($product->getPrice(),0)."</span> </span> </div>";
		else:
			$bodyhtml .= "<div class='products price-box'>";
			$bodyhtml .= "<p class='old-price' style='margin-right: 15%;float:left;padding-bottom:35px'> <span class='price-label'></span> <span id='old-price-77268' class='price' style='font-size: 35px;'>Rs. ".number_format($product->getPrice(),0)."</span> </p>";
			$bodyhtml .= "<p class='special-price' style='padding-bottom:15px'> <span class='price-label'></span> <span id='product-price-77268' class='price' style='font-size: 35px;'>Rs. ".number_format($product->getSpecialPrice(),0)."</span> </p>";
			$bodyhtml .= "</div>";
		endif;
			$bodyhtml .= "<div class='clear'></div>";
			$bodyhtml .= "</li>";
	
		 if ($i%$_columnCount==0){
			$bodyhtml .= "</ul>";
}	
}
}$j++;  
 }
	$bodyhtml .= "</div>";

return $bodyhtml;


	}



}
