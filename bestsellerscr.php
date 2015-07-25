<?php
require_once 'app/Mage.php';
Mage::app();

$fp = fopen("craftsvillatop-header.html","r");
$headerhtml = fread($fp,filesize("craftsvillatop-header.html"));
$fp1 = fopen("craftsvillatop-footer.html","r");
$footerhtml = fread($fp1,filesize("craftsvillatop-footer.html"));
fclose($fp);
fclose($fp1);
/**
if($fp){
$headerhtml = fread($fp,filesize("craftsvillatop-header.html"));

echo "entered";
 echo $headerhtml;
}
else{
echo "error"; 
}
exit;
**/
$_columnCount = 5;
$i=0; 
$j=25; 

$bodyhtml = "<div>";


$read = Mage::getSingleton('core/resource')->getConnection('core_read');

$queryofgetSkus = "SELECT `sku`,`product_id` FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 1 DAY) GROUP BY `sku` DESC LIMIT 120";

$getSkusresult = $read->query($queryofgetSkus)->fetchAll();

foreach($getSkusresult as $_getSkusresult)
	{

if ($i++%$_columnCount==0)
	{ 
		$bodyhtml .= "<ul class='products-grid first odd you_like' style ='float: right;'>";
	} 
	$product = Mage::getModel('catalog/product')->load($_getSkusresult['product_id']);
	$prodImage = Mage::helper('catalog/image')->init($product, 'image')->resize(166, 166);
	$first = "if(($i-1)%$_columnCount==0):"; $last = "elseif($i%$_columnCount==0):"; $end = "endif;";

         $bodyhtml .= "<li class='item".$firstlast." first ".$last." last ".$end."'>";

  $bodyhtml .= "<div class='prCnr'>";
 $bodyhtml .= "<a class='product-image spriteimg' title='$prodImage' href='$product->getProductUrl()'><img width='166' height='166' src='$prodImage' alt=''></a>";
	 $bodyhtml .= "<p class='shopbrief'><a title='' href='$product->getProductUrl()'>".$product->getName()."</a></p>";
     
			//$qtyStock=$product->getInventoryInStock();
			$storeurl = '';
			$vendorinfo= Mage::helper('udropship')->getVendor($product);
			$storeurl = Mage::helper('umicrosite')->getVendorUrl($vendorinfo->getData('vendor_id'));
			$storeurl = substr($storeurl, 0, -1);
			if($storeurl != '') {
         
      $bodyhtml .= "<p class='vendorname'>by <a target='_blank' href='$storeurl'>".$vendorinfo->getVendorName()."</a></p>";
 } 



if(!$product->getSpecialPrice()):
        $bodyhtml .= "<div class='products price-box'> <span id='product-price-80805' class='regular-price'> <span class='price 123'> 'Rs. '".number_format($product->getPrice(),0)."</span> </span> </div>";
else:
        $bodyhtml .= "<div class='products price-box'>";
        $bodyhtml .= "<p class='old-price'> <span class='price-label'></span> <span id='old-price-77268' class='price'>'Rs. '".number_format($product->getPrice(),0)."</span> </p>";
        $bodyhtml .= "<p class='special-price'> <span class='price-label'></span> <span id='product-price-77268' class='price'>'Rs. '".number_format($product->getSpecialPrice(),0)."</span> </p>";
    $bodyhtml .= "</div>";
endif;
      $bodyhtml .= "<div class='clear'></div>";
    $bodyhtml .= "</div>";
  $bodyhtml .= "</li>";
	
 if ($i%$_columnCount==0){
$bodyhtml .= "</ul>";
 } 
} 

$bodyhtml .= "</div>";
echo $bestsellerhtml = $headerhtml.$bodyhtml.$footerhtml;
$bestsellerfp = fopen("craftsvilla-deals.html","w");
$sellerwrite = fwrite($bestsellerfp,$bestsellerhtml);
fclose($bestsellerfp);
?>


