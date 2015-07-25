<?php

require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$productEntity1 = "SELECT COUNT( 'entity_id' ) AS count1 FROM catalog_product_entity WHERE created_at > ( DATE_SUB( NOW( ) ,INTERVAL 24 HOUR ) ) ";

$resultproductEntity1 = $db->query($productEntity1);

$row1 = $resultproductEntity1->fetch();
$entity24 = $row1["count1"];

//echo $entity24;

$productEntity2 = "SELECT COUNT( 'entity_id' ) AS count2 FROM catalog_product_entity WHERE created_at > ( DATE_SUB( NOW( ) ,INTERVAL 7 DAY ) ) ";

$resultproductEntity2 = $db->query($productEntity2);

$row2 = $resultproductEntity2->fetch();
$entity7 = $row2["count2"];

//echo $entity7;

$productEntity3 = "SELECT COUNT( 'entity_id' ) AS count3 FROM catalog_product_entity WHERE created_at > ( DATE_SUB( NOW( ) ,INTERVAL 1 MONTH ) ) ";

$resultproductEntity3 = $db->query($productEntity3);

$row3 = $resultproductEntity3->fetch();
$entity30 = $row3["count3"];

//echo $entity30;

$productEntity4 = "SELECT COUNT( 'entity_id' ) AS count4 FROM catalog_product_entity WHERE created_at > ( DATE_SUB( NOW( ) ,INTERVAL 12 MONTH ) ) ";

$resultproductEntity4 = $db->query($productEntity4);

$row4 = $resultproductEntity4->fetch();
$entity12 = $row4["count4"];

//echo $entity12;


$productEntity11 = "SELECT COUNT( 'entity_id' ) AS count11 FROM catalog_product_entity WHERE updated_at > ( DATE_SUB( NOW( ) ,INTERVAL 24 HOUR ) ) ";

$resultproductEntity11 = $db->query($productEntity11);

$row11 = $resultproductEntity11->fetch();
$entity124 = $row11["count11"];

//echo $entity124;

$productEntity22 = "SELECT COUNT( 'entity_id' ) AS count22 FROM catalog_product_entity WHERE updated_at > ( DATE_SUB( NOW( ) ,INTERVAL 7 DAY ) ) ";

$resultproductEntity22 = $db->query($productEntity22);

$row22 = $resultproductEntity22->fetch();
$entity27 = $row22["count22"];

//echo $entity27;

$productEntity33 = "SELECT COUNT( 'entity_id' ) AS count33 FROM catalog_product_entity WHERE updated_at > ( DATE_SUB( NOW( ) ,INTERVAL 1 MONTH ) ) ";

$resultproductEntity33 = $db->query($productEntity33);

$row33 = $resultproductEntity33->fetch();
$entity330 = $row33["count33"];

//echo $entity330;

$productEntity44 = "SELECT COUNT( 'entity_id' ) AS count44 FROM catalog_product_entity WHERE updated_at > ( DATE_SUB( NOW( ) ,INTERVAL 12 MONTH ) ) ";

$resultproductEntity44 = $db->query($productEntity44);

$row44 = $resultproductEntity44->fetch();
$entity412 = $row44["count44"];

//echo $entity412;

$message1 =   
'<html>
<head>
<title>Product Entity</title>
</head>
<body>
<table border=1>
<tr bgcolor=#ccc>
<th width=250>&nbsp;</th>
<th width=200> < 24 HOUR </th>
<th width=200> < 7 DAYS </th>
<th width=200> < 1 MONTH </th>
<th width=200> < 12 MONTH </th>
</tr>
</table>
</body>
</html>';

$message2 = "
<html>
<head>
<title>Product Entity</title>
</head>
<body>
<table border=1>
<tr>
<td align='center'width=200> Created </td>
<td align='center'width=200> ".$row1["count1"]."</td>
<td align='center'width=200> ".$row2["count2"]."</td>
<td align='center'width=200> ".$row3["count3"]."</td>
<td align='center'width=200> ".$row4["count4"]."</td>
</tr>
<tr>
<td align='center'width=200> Updated </td>
<td align='center'width=200> ".$row11["count11"]."</td>
<td align='center'width=200> ".$row22["count22"]."</td>
<td align='center'width=200> ".$row33["count33"]."</td>
<td align='center'width=200> ".$row44["count44"]."</td>
</tr>
</table>
</body>
</html>";

$message = $message1.$message2;
//echo $message;
$mail = Mage::getModel('core/email');

					$mail->setToName('craftsvilla');
					$mail->setToEmail('manoj@craftsvilla.com');
					$mail->setBody($message);
					$mail->setSubject('Stats: Product Created and Updated');
					$mail->setFromEmail('places@craftsvilla.com');
					$mail->setFromName("Craftsvilla Stats");
					$mail->setType('html');

					if($mail->send())
					{
						echo 'Email has been send successfully';
					$mail->setToEmail('monica@craftsvilla.com');
					$mail->send();
					}
?>
