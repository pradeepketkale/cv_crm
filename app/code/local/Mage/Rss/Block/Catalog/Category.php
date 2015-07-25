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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Catalog_Category extends Mage_Rss_Block_Catalog_Abstract
{
	protected function _construct()
	{
		/*
		 * setting cache to save the rss for 10 minutes
		 */
		$this->setCacheKey('rss_catalog_category_'
		. $this->getRequest()->getParam('cid') . '_'
		. $this->getRequest()->getParam('store_id') . '_'
		. Mage::getModel('customer/session')->getId()
		);
		$this->setCacheLifetime(600);
	}

	protected function _toHtml()
	{
		$categoryId = $this->getRequest()->getParam('cid');
		$storeId = $this->_getStoreId();
		$rssObj = Mage::getModel('rss/rss');
		if ($categoryId) {
			$category = Mage::getModel('catalog/category')->load($categoryId);
			if ($category && $category->getId()) {
				$layer = Mage::getSingleton('catalog/layer')->setStore($storeId);
				//want to load all products no matter anchor or not
				$category->setIsAnchor(true);
				$newurl = $category->getUrl();
				$title = $category->getName();
				$data = array('title' => $title,
               	'description' => $title,
                'link'        => $newurl,
                'charset'     => 'UTF-8',
				);

				$rssObj->_addHeader($data);

				/*$_collection = $category->getCollection();
				$_collection->addAttributeToSelect('url_key')
				->addAttributeToSelect('name')
				->addAttributeToSelect('is_anchor')
				->addAttributeToFilter('is_active',1)
				->addIdFilter($category->getChildren())
				->load()
				;
				$productCollection = Mage::getModel('catalog/product')->getCollection();

				$currentyCateogry = $layer->setCurrentCategory($category);
				$layer->prepareProductCollection($productCollection);
				$productCollection->addCountToCategories($_collection);*/

				if($this->getRequest()->getParam('xml'))
				{				
					$_productCollection = Mage::getModel('catalog/product')
								->getCollection()
								->addAttributeToSelect('*')
								->addAttributeToSort('updated_at','desc')
								->addCategoryFilter($category)
								->addAttributeToFilter('status', 1)
								->setCurPage(1)
                						->setPageSize(100)
								;
				}
				else {
					$_productCollection = Mage::getModel('catalog/product')
								->getCollection()
								->addAttributeToSelect('*')
								->addAttributeToSort('updated_at','desc')
								->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds())
								->addCategoryFilter($category)
								->addAttributeToFilter('status', 1)
								->setCurPage(1)
                				->setPageSize(50)
								;
				}

				//echo "<pre>";
				//print_r($_productCollection->getData());
				//exit();
				//echo "SKU VALUE:".sku

					
				if($this->getRequest()->getParam('xml'))
				{
					$categories_valuesto_disp = Mage::getModel('catalog/category')
                    							->getCollection()
					                			->addAttributeToSelect('*')
                    							->addIsActiveFilter();	
					$categories_valuesto_disp_arr = array();
					foreach($categories_valuesto_disp as $categories_value_disp)
					{
						$categories_valuesto_disp_arr[$categories_value_disp->getId()] = $categories_value_disp->getName(); 
					}
					
					$xml_value_display = '<?xml version="1.0" encoding="UTF-8"?>
						<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" version="2.0">						
						<CV_Product_Catalog>';				
					/*$_subcategories = $category->getChildrenCategories();
					$children_categories_name = '';
					$children_categories_id = '';
					$i = 0;
					if (count($_subcategories) > 0){
						foreach($_subcategories as $_subcategory){
							if($i == (count($_subcategories)-1))
							{
								$children_categories_name .= $_subcategory->getName();
								$children_categories_id .= $_subcategory->getId();
							}
							else {
								$children_categories_name .= $_subcategory->getName().", ";
								$children_categories_id .= $_subcategory->getId().", ";
							}
							$i++;
						}
					}*/
					
					/*$categories_valueto_disp = Mage::getModel('catalog/category')
                    							->getCollection()
                    							->addAttributeToSelect('*')
                    							->addIsActiveFilter();*/
					
					if ($_productCollection->getSize()>0) {
						foreach ($_productCollection as $_product) {
							unset($product_val);
							unset($categoryCollection);
							unset($children_categories_id);
							unset($children_categories_name);
							//$product_val = Mage::getModel(’catalog/product’)->load($_product->getId());
							 							
							//load the categories of this product 
							$categoryCollection = $_product->getCategoryCollection();
							$kk = 0;
							$total_collection_categories = count($categoryCollection);
							if($categoryCollection && $total_collection_categories)
							{
								foreach($categoryCollection as $category_values)
								{
									if($kk == ($total_collection_categories-1))
									{
										$children_categories_id .=  $category_values->getId();
										/*unset($category_name_value);										
										$category_name_value = Mage::getModel('catalog/category')->load($category_values->getId());
										$children_categories_name .=  $category_name_value->getName();*/
										$children_categories_name .=  $categories_valuesto_disp_arr[$category_values->getId()];
									}
									else {
										$children_categories_id .=  $category_values->getId().", ";
										/*unset($category_name_value);										
										$category_name_value = Mage::getModel('catalog/category')->load($category_values->getId());
										$children_categories_name .=  $category_name_value->getName().", ";*/
										$children_categories_name .=  $categories_valuesto_disp_arr[$category_values->getId()].", ";
									}
									$kk++;	
								}
							}
							
							$_gallery = Mage::getModel('catalog/product')->load($_product->getId())->getMediaGalleryImages();
							$imgcount = Mage::getModel('catalog/product')->load($_product->getId())->getMediaGalleryImages()->count();
							
							unset($images_values);
							$jj = 1;
							if($imgcount)
							{
								foreach($_gallery as $_image)
								{
									$images_values .= '<imageurl'.$jj.'><![CDATA['.$this->helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile()).']]></imageurl'.$jj.'>';
									$jj++;
								}
							}
							else {
								$images_values .= '<imageurl><![CDATA[]]></imageurl>';
							}
							
							$xml_value_display .= '<Product>';

							$xml_value_display .= '<productid><![CDATA['.$_product->getId().']]></productid>
'.$images_values.
							'
							<PageURL><![CDATA['.$_product->getProductUrl().']]></PageURL>
    							<ProductName><![CDATA['.$_product->getName().']]></ProductName>
    							<ProductDesc><![CDATA['.$_product->getShortDescription().']]></ProductDesc>
    							<ProductFullDesc><![CDATA['.$_product->getDescription().']]></ProductFullDesc>
								<BrandName><![CDATA[]]></BrandName>    									
    							<SubCategoryName><![CDATA['.$children_categories_name.']]></SubCategoryName>
    							<SubCategoryId><![CDATA['.$children_categories_id.']]></SubCategoryId>
    							<CategoryName><![CDATA['.$category->getName().']]></CategoryName>
    							<CategoryID><![CDATA['.$category->getId().']]></CategoryID>
    							<Keywords><![CDATA['.$_product->getMetaKeyword().']]></Keywords>
    							<Size><![CDATA['.$_product->getAttributeText('size').']]></Size>
    							<Unit><![CDATA[]]></Unit>
    							<Color><![CDATA['.$_product->getAttributeText('color').']]></Color>
    							<MRP><![CDATA['.$_product->getPrice().']]></MRP>';

							if($_product->getSpecialPrice())
							{
								unset($discount_price_disp);
								$discount_price_disp = ($_product->getPrice() - $_product->getSpecialPrice());
								$xml_value_display .= '<Discount><![CDATA['.$_product->getSpecialPrice().']]></Discount>
											<OnSale><![CDATA[True]]></OnSale>';								
							}
							else {
								$xml_value_display .= '<Discount><![CDATA[0.00]]></Discount>
											<OnSale><![CDATA[False]]></OnSale>';									
							}
							unset($qtyStock);
							$qtyStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
							$xml_value_display .= '<CurrentStock><![CDATA['.$qtyStock.']]></CurrentStock>
    										<Gender><![CDATA[]]></Gender>
    										<AgeFrom><![CDATA[]]></AgeFrom>
    										<AgeTo><![CDATA[]]></AgeTo>
    										<IsActive><![CDATA['.$_product->getStatus().']]></IsActive>';
							unset($product_type);
							$product_type = $_product->getTypeId();//exit();
							if ($product_type == 'simple') {
								$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
								->getParentIdsByChild($_product->getId());
								$product = Mage::getModel('catalog/product')->load($parentIds[0])->getData();
								$xml_value_display .= '<Product_Group_ID><![CDATA['.$product['sku'].']]></Product_Group_ID>
											<Product_Sequence><![CDATA['.$_product->getSku().']]></Product_Sequence>';	
							}
							else{
								$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
								->getParentIdsByChild($_product->getId());
								$product = Mage::getModel('catalog/product')->load($parentIds[0])->getData();
								$xml_value_display .= '<Product_Group_ID><![CDATA[]]></Product_Group_ID>
											<Product_Sequence><![CDATA['.$_product->getSku().']]></Product_Sequence>';
							}

							$xml_value_display .= '</Product>';

						}
						$xml_value_display .= '</CV_Product_Catalog>
			               		</rss>';
						return $xml_value_display;
					}
				}
				else if(!$this->getRequest()->getParam('xml')) {
					if ($_productCollection->getSize()>0) {
						$args = array('rssObj' => $rssObj);
						foreach ($_productCollection as $_product) {
							$args['product'] = $_product;
							$this->addNewItemXmlCallback($args);
						}
					}
					return $rssObj->createRssXml();
				}
			}
		}
	}


	/**
	 * Preparing data and adding to rss object
	 *
	 * @param array $args
	 */
	public function addNewItemXmlCallback($args)
	{
		$product = $args['product'];
		$product->setAllowedInRss(true);
		$product->setAllowedPriceInRss(true);

		Mage::dispatchEvent('rss_catalog_category_xml_callback', $args);

		if (!$product->getAllowedInRss()) {
			return;
		}

		$description = '<table><tr><td  style="text-decoration:none;">'.$product->getSku().'</td></tr><tr>'
		. '<td><a href="'.$product->getProductUrl().'"><img src="'
		. $this->helper('catalog/image')->init($product, 'thumbnail')->resize(75, 75)
		. '" border="0" align="left" height="75" width="75"></a></td>'
		. '<td  style="text-decoration:none;">' . $product->getDescription();

		if ($product->getAllowedPriceInRss()) {
			$description.= $this->getPriceHtml($product,true);
		}

		$description .= '</td></tr></table>';
		$rssObj = $args['rssObj'];
		$data = array(
                'title'         => $product->getName(),
                'link'          => $product->getProductUrl(),
                'description'   => $description,
		);

		$rssObj->_addEntry($data);
	}
}
