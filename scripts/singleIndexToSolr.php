<?php
	require_once __DIR__ . '/../lib/solr-php-client/vendor/autoload.php';
	require_once '../app/Mage.php';
	require_once __DIR__ . '/solrConfig.php';
	Mage::app();

	$document = new Apache_Solr_Document();

		$solr = new Apache_Solr_Service( SOLR_HOST, SOLR_PORT, SOLR_PATH."/".SOLR_CORE);
		$solr->setAuthenticationCredentials(SOLR_USERNAME, SOLR_PASSWORD);
	
	if ( ! $solr->ping() ) {
		echo 'Solr service not responding.';
		exit;
	}


$entiyPrdId1 = array(1966205,1959313,1936056);
foreach($entiyPrdId1 as $entiyPrdId){


$document->entity_id = $entiyPrdId;

		$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
//for product images
		$prdImageQuery = "SELECT `value_id`,`value` as `file` FROM `catalog_product_entity_media_gallery` WHERE `entity_id` = '".$entiyPrdId."'";
		$resultImage['images'] = 	$readId->query($prdImageQuery)->fetchAll();
//print_r($resultImage['images']);exit;

$document->image = $resultImage['images'][0]['file'];
$document->gallery_images = implode("," , array_map(function ($ar) {return $ar['file'];}, $resultImage['images']));

//check inventory `is_in_stock`
	$catStockItem = "SELECT `qty`, `is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$entiyPrdId."'";
	$resultcatStockItem = $readId->query($catStockItem)->fetch();

$document->qty = intval($resultcatStockItem['qty'])>0?intval($resultcatStockItem['qty']):0;
$document->is_in_stock = intval($resultcatStockItem['is_in_stock'])>0?intval($resultcatStockItem['is_in_stock']):0;

//check international ship allowed or not

	$attrquery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_int` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN ('531', '627')";
	$resultAttrInt = $readId->query($attrquery)->fetchAll();
	foreach($resultAttrInt as $_resultAttrInt){
			$checkAvailableInt[$_resultAttrInt['attribute_id']] = $_resultAttrInt['value'];
		}
	$vendorIdCv = $checkAvailableInt['531']>0?$checkAvailableInt['531']:0;

$document->vendor_id = $vendorIdCv;

$dropshipQuery = "select `vendor_name`, `country_id`, `custom_vars_combined`,`city`,`url_key` from `udropship_vendor` where `vendor_id` = '".$vendorIdCv."'";

$vendorTotaldata = $readId->query($dropshipQuery)->fetch();

$customDataValue = $vendorTotaldata['custom_vars_combined'];
$customData = Zend_Json::decode($customDataValue);
$document->vendor = $vendorTotaldata['vendor_name'];
$document->vendor_city = $vendorTotaldata['city'];
$document->vendor_logo = $customData['shop_logo'];
$document->vendor_url = $vendorTotaldata['url_key'];
$document->vendor_owner = $customData['check_pay_to'];
$document->vendor_country = Mage::app()->getLocale()->getCountryTranslation($vendorTotaldata['country_id']);

// seller quality ratings  `seller_quality_craftsvilla` 

$statsConn = Mage::getSingleton('core/resource')->getConnection('statsdb_connection'); 
$sellerQuality = "SELECT `craftsvilla_seller_rating` FROM `seller_quality_craftsvilla` WHERE `vendor_id` = '".$vendorIdCv."'";
$resultsellerQuality = $statsConn->query($sellerQuality)->fetch();

$document->seller_ratings = $resultsellerQuality['craftsvilla_seller_rating'] != null?$resultsellerQuality['craftsvilla_seller_rating']:0;
$statsConn->closeConnection();
//echo $document->seller_ratings;exit;
//$statsConn->closeConnection();
//type_id & sku
	$typeIdnskuquery = "SELECT `sku` FROM `catalog_product_entity` WHERE `entity_id` ='".$entiyPrdId."'";
	$resultofskunidtype = $readId->query($typeIdnskuquery)->fetch();

$document->sku = $resultofskunidtype['sku'];

//meta keyword,  Full Desc, Short Desc			
$catentityTextQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_text` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('57','58','68') ";
	$resultcattext	= $readId->query($catentityTextQuery)->fetchAll();
	$cattext = array();

foreach($resultcattext as $_resultcattext){
		$cattext[$_resultcattext['attribute_id']] = $_resultcattext['value'];
	}

$document->long_description = $cattext['57'];
$document->short_description = $cattext['58'];

$colorchoices = array('red', 'green','white','black','yellow','magenta','purple','grey','blue','brown','silver','beige','gold');
	$color = array();
		foreach($colorchoices as $_colorchoices) {
				$res = strpos(strtolower($cattext['57']), strtolower($_colorchoices));
				if ($res !== false){
					array_push($color,ucfirst($_colorchoices));}
			}

$document->color = $color!=null?$color:'Multicolor';

//---------------------------------------
//price,special price,shipping cost, internationalshipping price

$catentityDecimalQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('60','61','535','643') ";
$resultcatdecimal	= $readId->query($catentityDecimalQuery)->fetchAll();

foreach($resultcatdecimal as $_resultcatdecimal){$cattext[$_resultcatdecimal['attribute_id']] = $_resultcatdecimal['value'];}

$document->price = $cattext['60']>0?$cattext['60']:0;
$document->special_price = $cattext['61']>0?$cattext['61']:$cattext['60'];

//------------------ FABRIC-----------------------

$fabricQuery = "SELECT `fabric_value` FROM `craftsvilla_catalog_attribute` WHERE `product_id` = '".$entiyPrdId."'";
$fabricResult = $readId->query($fabricQuery)->fetch();

$document->fabric = $fabricResult['fabric_value'];

//Meta Desc, product name, url_key
$catentityvarcharQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('56','69','82') ";
$resultcatvarchar	= $readId->query($catentityvarcharQuery)->fetchAll();
foreach($resultcatvarchar as $_resultcatvarchar){
		$cattext[$_resultcatvarchar['attribute_id']] = $_resultcatvarchar['value'];
	}

$document->name = mysql_escape_string($cattext['56']);
$document->url_path = $cattext['82'];
//-----------------------------------			
$product = Mage::getModel('catalog/product');

		$product->setStoreId(Mage::app()->getStore()->getId())
	    	    ->setEntityId($entiyPrdId);
$catIds = $product->getCategoryIds();
$document->category_id = $catIds;
$catid2 = array();
	foreach ($catIds as $category_id){
	
	    $_cat = Mage::getModel('catalog/category')->load($category_id) ;
	    $catid2[] = $_cat->getName();
		$document->category_name = $catid2;
	}

                $tags = "";
                $sql = "SELECT tag_id FROM tag_relation WHERE product_id='".$entiyPrdId."'";
                
		$tagIdRes = $readId->query($sql)->fetchAll();
		
                foreach ($tagIdRes as $tagIdRow) {
					
					if($tagIdRow[0] > 0){
                    $get_tag_value_Qr = "select name from tag where tag_id = '".$tagIdRow[0]."'";
                    $tags[] = $readId->query($sql)->fetchRow();
					}
                }

$readId->closeConnection();
$document->tags = $tags;				
//print_r($document);exit;

	try {
		$solr->addDocument( $document );
		$solr->commit();
		//$solr->optimize();
	}
	catch ( Exception $e ) {
		echo $e->getMessage();
	}
	
}
	
?>
