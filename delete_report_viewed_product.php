<?php
error_reporting(E_ALL & ~E_NOTICE);
$link = mysql_connect("newserver10.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
//$link = mysql_connect("localhost","root","");
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
// Select database
mysql_select_db("nzkrqvrxme");
$sql = "DELETE from `report_viewed_product_index` where `added_at` < DATE_SUB(NOW() , INTERVAL 6 MONTH) LIMIT 1,10000 ";
$result = mysql_query($sql);
echo "Rows deleted succesfully";
?>
<?php
mysql_close();
?>
