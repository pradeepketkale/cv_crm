<?php

require_once 'app/Mage.php';
Mage::app();

$readId = Mage::getSingleton('core/resource')->getConnection('core_read');

$skuquery = "SELECT sku,product_id,count(*) as count1 FROM `sales_flat_order_item` WHERE `created_at` > DATE_SUB(Now(),INTERVAL 24 HOUR) GROUP BY `product_id` ORDER BY count1 DESC LIMIT 100";
//$resultskuquery = mysql_query($skuquery,$readId);
$resultskuquery = $readId->query($skuquery)->fetchAll();

$message1 =   
'
<table border=1>
<tr bgcolor=#ccc>
<th width=200>SKU</th>
<th width=200>Vendor Name</th>
<th width=100>Product ID</th>
<th width=100>Current Inventory</th>


<th width=166>Image</th>
<th width=80>Price (Rs.)</th>
<th width=200>Product Name</th>
</tr>
';
//while($row = mysql_fetch_array($resultskuquery))
foreach($resultskuquery as $row)
{
$productId = $row["product_id"];
//$sku = $row["sku"];
//echo $sku;
//echo "<br>";//THANKYOUCARD

//$_product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);

$_product = Mage::getModel('catalog/product')->load($productId);

$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);

$getPrice = round($_product->getPrice());
$prdLink = $_product->getProductUrl();

$getName = $_product->getName();
$prodImage=Mage::helper('catalog/image')->init($_product, 'image')->resize(166, 166);
$vendorID = $_product->getUdropshipVendor();
$stock1 = $stock->getQty();
//echo "$stock1";
//echo "<br>";

if($stock1<3)
{
$emailquery = "select * from udropship_vendor where `vendor_id` = '".$vendorID."' ";
//$resultemail = mysql_query($emailquery,$readId);
$resultemail = $readId->query($emailquery);
$row1 = $resultemail->fetch();
$vurl = Mage::getBaseUrl().$resultemail['url_key'];
$email = $row1["email"];
$message2 .= "
<tr>
<td width=200 align='center'>".$row["sku"]."</td>
<td width=200 align='center'><a href=".$vurl." target=_blank>".$row1["vendor_name"]."</a></td>
<td width=100 align='center'>".$row["product_id"]."</td>
<td width=100 align='center' style='background:#F2F2F2;color:#CE3D49;'> $stock1 </td>


<td><a href=".$prdLink." target=_blank><img src=".$prodImage." /></a></td>
<td align='center' width=80 style='background:#F2F2F2;color:#CE3D49;'>
 $getPrice </td>
<td width=200 align='center'> $getName </td>
";
 $messageinfo = "Dear Seller,"."<br><br>"."This alert is issued for below product which is selling well on Craftsvilla.com. The inventory is very low."."<br>"."Please update the inventory as soon as possible.";
$message = $messageinfo."<br><br>".$message1.$message2."</table>";
//echo $message;
					
					$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail($email);
					$mail->setBody($message);
					$mail->setSubject('Alert : Low Inventory Of Best Selling SKU :'.$getName);
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Alerts");
					$mail->setType('html');
					//$mailerror = $mail->send();
					//print_r($mailerror); exit;

					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->send();

					}
					


}
}



?>

