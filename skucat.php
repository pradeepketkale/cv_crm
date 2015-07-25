<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
//$catid = 4,5,8,9,19,69,74;
$fromdate = '2012-08-01';
$todate = '2012-08-15';
$read = Mage::getSingleton('core/resource')->getConnection('core_read');
$query = "SELECT ccfs1.`name` as CategoryName,sfsi.`sku` as SKU,sfsi.`name` as ProductName,sfoi.base_price,sfs.`created_at` as Date FROM `sales_flat_shipment` as sfs left join `sales_flat_shipment_item` as sfsi ON sfs.entity_id = sfsi.parent_id left join `catalog_category_product` as ccp ON sfsi.product_id = ccp.product_id left join `catalog_category_flat_store_1` as ccfs1 ON ccp.category_id = ccfs1.entity_id left join sales_flat_order_item as sfoi ON sfs.order_id = sfoi.order_id where ccp.`category_id` IN('4','5','6','8','9','19','69','74','284','1070') and ccfs1.level = 2 and sfs.`created_at` between  '".$fromdate."' and '".$todate."'";
$sql = $read->query($query)->fetchAll();

//and t4.`entity_id` IN('4','5','6','8','9','19','69','74','284','1070')
//echo "Here";
/*echo "<pre>";
print_r($sql);exit;*/
//echo "<table border='1' cellpadding='3'>";
//echo "<tr>";
//echo "<td><b>Category Name</b></td>
//	  <td><b>Sku</b></td> 
//	  <td><b>ProdutName</b></td>
//	  <td><b>BasePrice</b></td>
//	  <td><b>Date</b></td>";
//echo "</tr>";

$out = '';
// fiels to export
$out .='CategoryName,SKU,Price,Date,ProductName';
$out .="\n";
foreach($sql as $_catsku):
//print_r($_catsku);continue;

$out .= ''.$_catsku['CategoryName'].',';
$out .= ''.$_catsku['SKU'].',';
$out .= ''.$_catsku['base_price'].',';
$out .= ''.$_catsku['Date'].',';
$out .= ''.$_catsku['ProductName'].',';
$out .="\n";
endforeach;
//$header .= "</table>";
//export to excel

header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=category.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo $out;

?>
