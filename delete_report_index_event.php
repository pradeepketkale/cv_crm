<?php
error_reporting(E_ALL & ~E_NOTICE);

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$sql = "DELETE FROM `report_viewed_product_index` WHERE `added_at` < DATE_SUB(NOW() , INTERVAL 1 DAY)";
//$result = mysql_query($sql);
$result = $db->query($sql)->fetch();
echo "Rows deleted succesfully";
?>
<?php
mysql_close();
?>
