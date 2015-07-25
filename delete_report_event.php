<?php
error_reporting(E_ALL & ~E_NOTICE);

$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$sql = "DELETE FROM `report_event` WHERE `logged_at` < DATE_SUB(NOW() , INTERVAL 1 DAY)";
$result =  $db->query($sql)->fetch();
echo "Rows deleted succesfully";
$sql1 = "TRUNCATE TABLE `index_process_event`";
$result =  $db->query($sql1)->fetch();
$sql2 = "TRUNCATE TABLE `index_event`";
$result =  $db->query($sql2)->fetch();
$sql3 = "DELETE FROM `sales_flat_quote_address` WHERE `updated_at` < DATE_SUB(NOW() , INTERVAL 60 DAY)";
$result =  $db->query($sql3)->fetch();
$sql4 = "DELETE FROM `sales_flat_quote_item` WHERE `updated_at` < DATE_SUB(NOW() , INTERVAL 60 DAY)";
$result =  $db->query($sql4)->fetch();
?>
<?php
mysql_close();
?>
