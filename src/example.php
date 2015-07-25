<?php
	require_once( 'SolrPhpClient/Apache/Solr/Service.php' );
	require_once '../app/Mage.php';
	Mage::app();

	$document = new Apache_Solr_Document();
	$solr = new Apache_Solr_Service( 'craftssolrman:crafts6123@localhost', '8983', '/solr' );
	
$solr->setAuthenticationCredentials("craftssolrman", "crafts@6123");	
	if ( ! $solr->ping() ) {
		echo 'Solr service not responding.';
		exit;
	}
	
	//
	//
	// Create two documents to represent two auto parts.
	// In practive, documents would likely be assembled from a 
	//   database query. 
	//


$entiyPrdId1 = array(2211947);
foreach($entiyPrdId1 as $entiyPrdId){
$document->entity_id = $entiyPrdId;

$product = Mage::getModel('catalog/product');

		$product->setStoreId(Mage::app()->getStore()->getId())
	    	    ->setEntityId($entiyPrdId);
		$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		
		$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
		//for product images
		$prdImageQuery = "SELECT `value_id`,`value` as `file` FROM `catalog_product_entity_media_gallery` WHERE `entity_id` = '".$entiyPrdId."'";
		$resultImage['images'] = 	$readId->query($prdImageQuery)->fetchAll();
//print_r($resultImage['images']);exit;



$document->image = $resultImage['images'][0]['file'];
$document->gallery_images = implode("," , array_map(function ($ar) {return $ar['file'];}, $resultImage['images']));
//check inventory `is_in_stock`
	$catStockItem = "SELECT `is_in_stock` FROM `cataloginventory_stock_item` WHERE `product_id` = '".$entiyPrdId."'";
	$resultcatStockItem = $readId->query($catStockItem)->fetch();
	$checkstocks = $resultcatStockItem['is_in_stock'];
	$prodQty = $resultcatStockItem['qty']>0?$resultcatStockItem['qty']:0;

$document->qty = $prodQty;

//check international ship allowed or not
	$attrquery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_int` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN ('531', '627')";
	$resultAttrInt = $readId->query($attrquery)->fetchAll();
	$checkAvailableInt = array();	// 1- mean available 0- not availabel
	foreach($resultAttrInt as $_resultAttrInt){$checkAvailableInt[$_resultAttrInt['attribute_id']] = $_resultAttrInt['value'];}
	$vendorIdCv = $checkAvailableInt['531'];
	$checkAvailable = $checkAvailableInt['627'];

$document->vendor_id = $vendorIdCv;

$dropshipQuery = mysql_query("select * from `udropship_vendor` where `vendor_id` = '".$vendorIdCv."'");
$vendorTotaldata = mysql_fetch_array($dropshipQuery);
$customDataValue = $vendorTotaldata['custom_vars_combined'];
$customData = Zend_Json::decode($customDataValue);
$document->vendor = $vendorTotaldata['vendor_name'];
$document->vendor_city = $vendorTotaldata['city'];
$document->vendor_logo = $customData['shop_logo'];
$document->vendor_url = $vendorTotaldata['url_key'];
$document->vendor_owner = $customData['check_pay_to'];

//type_id & sku
	$typeIdnskuquery = "SELECT `type_id`,`sku` FROM `catalog_product_entity` WHERE `entity_id` ='".$entiyPrdId."'";
	$resultofskunidtype = $readId->query($typeIdnskuquery)->fetch();
	$typeid = $resultofskunidtype['type_id'];
	$skuu = $resultofskunidtype['sku'];

$document->sku = $skuu;

//meta keyword,  Full Desc, Short Desc			
$catentityTextQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_text` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('57','58','68') ";
	$resultcattext	= $readId->query($catentityTextQuery)->fetchAll();
	$cattext = array();

foreach($resultcattext as $_resultcattext){$cattext[$_resultcattext['attribute_id']] = $_resultcattext['value'];}
	
	$fulldesc = $cattext['57'];
	$shortDesc = $cattext['58'];
	$metaKeyword = $cattext['68'];

$document->long_description = $fulldesc;
$document->short_description = $shortDesc;
//---------------------------------------
//price,special price,shipping cost, internationalshipping price

$catentityDecimalQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_decimal` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('60','61','535','643') ";
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
	$intershippingcost = $cattext['643'];

$document->price = $priceorginal>0?$priceorginal:0;
$document->discount = $specialPrice>0?$specialPrice:0;

//local	
	//$intershippingcost = $cattext['655'];//live id
//-----------------------------------			

//special_price_from _date,special_price_to_date

$catentityDatetimeQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_datetime` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('62','63') ";
$resultcatdatetime	= $readId->query($catentityDatetimeQuery)->fetchAll();

foreach($resultcatdatetime as $_resultcatdatetime){$cattext[$_resultcatdatetime['attribute_id']] = $_resultcatdatetime['value'];}
	
	
	$specialPricedateFrom = $cattext['62'];
	$specialPricedateTo = $cattext['63'];

//-----------------------------------			



//Meta Desc, product name, url_key
$catentityvarcharQuery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_varchar` WHERE `entity_id` = '".$entiyPrdId."' AND `attribute_id` IN('56','69','82') ";
$resultcatvarchar	= $readId->query($catentityvarcharQuery)->fetchAll();
foreach($resultcatvarchar as $_resultcatvarchar){$cattext[$_resultcatvarchar['attribute_id']] = $_resultcatvarchar['value'];}
	
	$productName = $cattext['56'];
	$metaDesc = $cattext['69'];
	$urlKey = $cattext['82'];

$document->name = $productName;
$document->url_path = $urlKey;
//-----------------------------------			

$categoryIdcv = Mage::helper('catalog/product')->fetchProductCategory($entiyPrdId);
			//$product_vendor_name = $categorynvendor_arr[1];
			$arryCat = array('6'=>'Jewelry','74' =>'Sarees','5'=>'Home Decor','1070'=>'Home Furnishing','4'=>'Clothing','9'=>'Bags','8'=>'Accessories','284'=>'Bath & Beauty','69'=>'Food & Health','1114'=>'Rakhi Gifts');

			if($categoryIdcv)
			{			
				$product_category_name = $arryCat[$categoryIdcv];
			}else{
				$product_category_name = 'Online Shopping';
				$categoryIdcv = 50000;
			}
$document->category_name = $product_category_name;
$document->category_id = $categoryIdcv;


                $tags = "";
                $sql = "SELECT tag_id FROM tag_relation WHERE product_id='".$entiyPrdId."'";
                
		$tagIdRes = $readId->query($sql)->fetchAll();
                foreach ($tagIdRes as $tagIdRow) {

			if($tagIdRow[0] > 0){
                    $get_tag_value_Qr = "select name from tag where tag_id = '".$tagIdRow[0]."'";
                    
                    $tags[] = $readId->query($get_tag_value_Qr)->fetchRow();
			}
                }
               // $tags = substr($tags, 0, -2);

$document->tags = $tags;		
               
 //print_r($document);exit;

	try {
		$solr->addDocument( $document );
		$solr->commit();
		$solr->optimize();
	}
	catch ( Exception $e ) {
		echo $e->getMessage();
	}
	
}
	
?>
