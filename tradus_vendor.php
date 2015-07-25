<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once 'app/Mage.php';
	Mage::app();
//	$catPost_id = $_POST['category_id'];
//	$fromDate = $_POST['category_id_from'];
//	$toDate = $_POST['category_id_to'];
	$catPost_id = '6';
    $fromDate = '2013-05-01';
    $toDate = '2013-06-30';
	
	$categoryIdarray = array('54','214','33','34','55','74','5');
	$categoryNamearray = array('Anklets','Rings','Necklaces','Earrings','Bracelets','Saris','HomeAndGarden');
	$categoryBrowsenode = array('802216031','821286031','821276031','802265031','802227031','824657031','836523031');	
	
    $cat_index = $catPost_id;

    if($cat_index == '1') {
      $ringsize = '16';
    } else
    {
	$ringsize = '';
    }
	
//	$category_id = $categoryIdarray[$cat_index];
	$category_id = $categoryIdarray[$cat_index];
	
  //  $catagory_model = Mage::getModel('catalog/category')->load($category_id); //where $category_id is the id of the category
 
    $collection = Mage::getResourceModel('catalog/product_collection');
 
 //	$collection->addCategoryFilter($catagory_model); //category filter
 	
	
    $collection->addAttributeToFilter('status',1); //only enabled product
    $collection->addAttributeToFilter('udropship_vendor',854); //only enabled product
	
	$collection->addAttributeToSelect(array('entity_id','sku','name','price','special_price','url_path','small_image','short_description','shippingcost')); //add product attribute to be fetched
    
   // $collection->addAttributeToFilter('created_at',array('gteq' =>$fromDate));
	
   // $collection->addAttributeToFilter('created_at',array('lteq' =>$toDate));
	
	$collection->joinField('inventory_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id','is_in_stock',array('qty'=>'0','eq' => "0")); 
	$collection->joinField('inventory_qty', 'cataloginventory_stock_item', 'qty', 'product_id=entity_id'); 
	
//	$collection->getSelect()->limit(10);
	
	$collection->addStoreFilter();          
	
//	echo $collection->getSelect();exit;
	
    if($cat_index == '5') {
	$resultQuery .='SKU,Inventory,Title,Link,Price,Discount Amount,Sale Start Date,Sale End Date,Delivery Time,Gold Carat,Metal Type,Stone Type,Recommended Browse Node,Category,Description,Shipping Cost,Standard Product ID,Product ID Type,Image,List Price,Availability,Brand,Manufacturer,Mfr part number,Model Number,Colour Name,Colour Map,Item package quantity,Gender,Ring Size,Total Diamond Weight,Occasion Lifestyle,Material,Age,Shipping Weight,Weight,Length,Height,Width,Keywords1,Keywords2,Keywords3,Keywords4,Keywords5,Bullet point1,Bullet point2,Bullet point3,Bullet point4,Bullet point5,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Offer note,Is Gift Wrap Available,Registered Parameter,Update Delete';
	$material = 'Chiffon';
	$occasion = 'Formal';
	$categoryName = 'Saris';
} else {
	$resultQuery .='SKU,Inventory,Title,Link,Price,Discount Amount,Sale Start Date,Sale End Date,Delivery Time,Gold Carat,Metal Type,Stone Type,Recommended Browse Node,Category,Description,Shipping Cost,Standard Product ID,Product ID Type,Image,List Price,Availability,Brand,Manufacturer,Mfr part number,Model Number,Colour Name,Colour Map,Item package quantity,Gender,Ring Size,Total Diamond Weight,Size per Pearl,Band Material,Age,Shipping Weight,Weight,Length,Height,Width,Keywords1,Keywords2,Keywords3,Keywords4,Keywords5,Bullet point1,Bullet point2,Bullet point3,Bullet point4,Bullet point5,Other image-url1,Other image-url2,Other image-url3,Other image-url4,Other image-url5,Offer note,Is Gift Wrap Available,Registered Parameter,Update Delete';
	$material = '';
	$occasion = '';
	 if($cat_index == '6'){
		$categoryName = 'HomeAndGarden';
	} else {
		$categoryName = 'Jewelry';
	}
}
	$resultQuery .="\n";

	if(!empty($collection))
 
    {
		 
 
            foreach ($collection as $_product):
 			
			$inventory = number_format($_product->getInventoryQty(),0);
			$_images = Mage::getModel('catalog/product')->load($_product->getEntityId())->getMediaGalleryImages();
			
			$my_product = Mage::getModel('catalog/product')->load($_product->getId());

			//$longdesc = strip_tags(preg_replace('[^a-zA-Z0-9<>/]/s',' ',$my_product->getDescription()));
			//$longdesc = strip_tags(str_replace('\n,\r',' ',$my_product->getDescription()));
			$productName = substr(rip_tags($_product->getName()),0,400);
			//preg_replace('/[^a-zA-Z0-9]/s','',$longdesc);
			$longdesc = substr(rip_tags($my_product->getDescription()),0,1900);
			$priceProduct = $_product->getPrice();
			$splPriceProduct = $_product->getSpecialPrice();
			if($splPriceProduct > 0)
			{
				$discount = $priceProduct - $splPriceProduct;
				$startDate = '07/24/2013';
				$endDate = '07/24/2016';
			} else {
				$discount = 0;
				$startDate = '';
				$endDate = '';
			}

			$i = 0;
			foreach($_images as $_imagefile){
				$imageArray[$i] = 'http://d1g63s1o9fthro.cloudfront.net/catalog/product/cache/1/image/9df78eab33525d08d6e5fb8d27136e95'.$_imagefile->getFile();
				$i++;
				}	
			//echo preg_replace('/[^a-zA-Z0-9]/s','',str_replace(' ','',$_product->getShortDescription()));exit;
			$resultQuery .= ''.$_product->getSku().',';
			$resultQuery .= ''.$inventory.',';
			$resultQuery .= ''.$productName.','; 
			$resultQuery .= ''.'http://www.craftsvilla.com/index.php/'.$_product->getUrlPath().',';
			$resultQuery .= ''.$priceProduct.',';
			$resultQuery .= ''.$discount.',';
			$resultQuery .= ''.$startDate.',';
			$resultQuery .= ''.$endDate.',';
			$resultQuery .= ''.'2'.',';
			$resultQuery .= ''.'9k'.',';
			$resultQuery .= ''.'Base Metal'.',';
			$resultQuery .= ''.'Turquoise'.',';
			$resultQuery .= ''.$categoryBrowsenode[$cat_index].',';
			$resultQuery .= ''.$categoryName.',';
			//$resultQuery .= ''.preg_replace('/[^a-zA-Z0-9]/s\n','',str_replace(' ','',$_product->getShortDescription())).',';
			//preg_replace('/[\r\n]/', '', $subject);
			$resultQuery .= ''.$longdesc.',';
			$resultQuery .= ''.$_product->getShippingcost().',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
        	$resultQuery .= ''.$imageArray[0].',';
			$resultQuery .= ''.$splPriceProduct.',';
			$resultQuery .= ''.'TRUE'.',';
			$resultQuery .= ''.'Craftsvilla.com'.',';
			$resultQuery .= ''.'Craftsvilla.com'.',';
			$resultQuery .= ''.$_product->getSku().',';
			$resultQuery .= ''.$_product->getSku().',';
			$resultQuery .= ''.'Multicolour'.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.'WOMENS'.',';
			$resultQuery .= ''.$ringsize.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.$occasion.',';
			$resultQuery .= ''.$material.',';
			$resultQuery .= ''.'18-45 Years'.',';
			$resultQuery .= ''.'500'.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.'Buy Online'.',';
			$resultQuery .= ''.$categoryNamearray[$cat_index].',';
			$resultQuery .= ''.'Craftsvilla.com'.',';
			$resultQuery .= ''.'Handmade'.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.'Intricate Handwork'.',';
			$resultQuery .= ''.'Designer Unique Collection'.',';
			$resultQuery .= ''.'Handmade by Artisans in India'.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.$imageArray[0].',';
			$resultQuery .= ''.$imageArray[1].',';
			$resultQuery .= ''.$imageArray[2].',';
			$resultQuery .= ''.$imageArray[3].',';
			$resultQuery .= ''.$imageArray[4].',';
			$resultQuery .= ''.'Global shipping available and customer friendly return policy'.',';
			$resultQuery .= ''.'TRUE'.',';
			$resultQuery .= ''.''.',';
			$resultQuery .= ''.''.',';
			$resultQuery .="\n";
			unset($imageArray);
		endforeach;
		
	//export to csv
	header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=jungleexport.csv");
	header("Pragma: no-cache");
	header("Expires: 0");
	echo $resultQuery;	 
  	  }else
 
        {
 
            echo 'No products exists';
 
    }              
 
    function rip_tags($string) {
   
    // ----- remove HTML TAGs -----
    $string = preg_replace ('/<[^>]*>/', ' ', $string);
   
    // ----- remove control characters -----
    $string = str_replace("\r", '', $string);    // --- replace with empty space
    $string = str_replace("\n", ' ', $string);   // --- replace with space
    $string = str_replace("\t", ' ', $string);   // --- replace with space
	$string = str_replace("*", ' ', $string);   // --- replace with space
	$string = str_replace("&", ' ', $string);   // --- replace with space
	$string = str_replace("#", ' ', $string);   // --- replace with space
	$string = str_replace("%", ' ', $string);   // --- replace with space
	$string = str_replace("$", ' ', $string);   // --- replace with space
	$string = str_replace("@", ' ', $string);   // --- replace with space
	$string = str_replace(".com", ' ', $string);   // --- replace with space
	$string = str_replace("?", ' ', $string);   // --- replace with space
	$string = str_replace("=", ' ', $string);   // --- replace with space
	$string = str_replace("-", ' ', $string);   // --- replace with space
	$string = str_replace("+", ' ', $string);   // --- replace with space
	$string = str_replace(",", ' ', $string);   // --- replace with space
	$string = str_replace("!", ' ', $string);   // --- replace with space
	$string = str_replace("\"", ' ', $string);   // --- replace with space
	$string = str_replace("%", ' ', $string);   // --- replace with space
	$string = str_replace("^", ' ', $string);   // --- replace with space
	$string = str_replace("'", ' ', $string);   // --- replace with space
	$string = str_replace("/", ' ', $string);   // --- replace with space
	$string = str_replace("_", ' ', $string);   // --- replace with space
	$string = str_replace("~", ' ', $string);   // --- replace with space
	//$string = str_replace("\", ' ', $string);   // --- replace with space
	//$string = str_replace("\{", ' ', $string);   // --- replace with space
	//$string = str_replace("\}", ' ', $string);   // --- replace with space
	//$string = str_replace("\]", ' ', $string);   // --- replace with space
	//$string = str_replace("\[", ' ', $string);   // --- replace with space
	//$string = str_replace("|", ' ', $string);   // --- replace with space
	$string = str_replace("nbsp", ' ', $string);   // --- replace with space
   
    // ----- remove multiple spaces -----
    $string = trim(preg_replace('/ {2,}/', ' ', $string));
   
    return $string;

} 
 
?>
