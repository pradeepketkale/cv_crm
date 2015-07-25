<?php
error_reporting(E_ALL & ~E_NOTICE);
$link = mysql_connect("newserver12.cpw1gtbr1jnd.ap-southeast-1.rds.amazonaws.com","poqfcwgwub","2gvrwogof9vnw");
//$link = mysql_connect("localhost","doejofinal1","123456");
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
// Select database
mysql_select_db("nzkrqvrxme");
//mysql_select_db("doejofinal");
$query = "SELECT case sfs.`udropship_status` 
when 11 then 'processing'
end as Shipmentstatus,count(uv.`vendor_name`) as Vendorcount,uv.`vendor_name` as VendorName
FROM `sales_flat_shipment` as sfs
LEFT JOIN `udropship_vendor` as uv 
ON sfs.`udropship_vendor` = uv.`vendor_id`
WHERE sfs.`udropship_status` = '11'
and sfs.`updated_at` < ( CURDATE() - INTERVAL 5 DAY ) group by uv.`vendor_name`";


$result = mysql_query($query);
$fetchQuery=mysql_fetch_array($result);
$resultQuery .='Shipmentstatus,Vendorcount,VendorName';
$resultQuery .="\n";
while($fetchQuery=mysql_fetch_array($result)){

$resultQuery .= ''.$fetchQuery['Shipmentstatus'].',';
$resultQuery .= ''.$fetchQuery['Vendorcount'].',';
$resultQuery .= ''.$fetchQuery['VendorName'].',';
//$resultQuery .= ''.$fetchQuery['Date'].',';
$resultQuery .="\n";
}


//export to csv

header("Content-type: text/x-csv");
header("Content-Disposition: attachment; filename=vendorprocessing.csv");
header("Pragma: no-cache");
header("Expires: 0");
echo $resultQuery;

?>
<?php
mysql_close();
?>
