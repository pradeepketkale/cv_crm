<?php
error_reporting(E_ALL & ~E_NOTICE);

$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$sql = "TRUNCATE TABLE `core_cache_tag`";
$result = $db->query($sql);
echo "Rows Truncated succesfully";
?>
<?php
mysql_close();
?>
