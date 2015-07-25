<?php
session_start();
require_once '../app/Mage.php';

Mage::app();

$fileName = 'referfriend-data.xls';
header("Content-type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=$fileName");
error_reporting(E_ALL & ~E_NOTICE);

$readexcel = Mage::getSingleton('core/resource')->getConnection('core_read');

/*$sql="SELECT t1.referral_parent_id, COUNT(*)  AS no_of_referal,SUM(IF(`referral_register_status` = 1, 1, 0)) AS no_of_registered_referals,SUM(IF(`referral_purchase_status` = 1, 1, 0)) AS no_of_purchase_referral ,t2.entity_id as customer_id,t2.email,t1.created_ts 
	FROM `referfriend_referral` AS t1 
	LEFT JOIN customer_entity AS t2 ON t1.referral_parent_id=t2.entity_id GROUP BY t1.referral_parent_id ".$cond." DESC ".$strCond.";";*/
$sql=base64_decode($_SESSION['excelSql']);
$readexresult=$readexcel->fetchAll($sql);
$countex = count($readexresult);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Craftsvilla</title>
<style type="text/css">
body{font-family:calibri,tahoma,verdana,arial; font-size:12px;}
.contnt{ width:980px; font-family:Arial, Helvetica, sans-serif; font-size:11px; color:#444; margin:0 auto;}
.gride{ background:#ccc; width:100%; border:0;}
.gride tr td{ background:#fff; padding:3px 5px;}
.gride tr.altnet td{ background:#f8f8f8;}
.gride tr th{ background:#E9E8E8; padding:3px 5px;}
input,select{ color:#444; font-family:Arial, Helvetica, sans-serif; border:1px solid #ccc; width:214px; padding:3px;}
input.date{ width:70px; text-align:center;}
input[type="submit"]{ width:auto; background:#999; color:#fff; font-weight:bold; outline:1px solid #666; border-color:#fff; cursor:pointer;}
select{ width:118px;}
.rightaligne tr td{ text-align:right;}
.rightaligne tr td.left{ text-align:left;}
.logo {background: url(images/home-sprite.png) no-repeat scroll -20px -215px transparent; height: 134px;width: 223px; display:block; margin:0 auto 20px;}
.roller{ border-bottom:1px solid #ccc; margin-bottom:20px; text-align:right;}
.contnt a{ text-decoration:none; color:#444;}
.rightaligne{ margin:0 auto 20px;}
.pagination{ margin:0;}
.paddbot{ padding-bottom:10px;}
</style>
</head>
<body>
<table border cellpadding=0 cellspacing=1 class=gride>
<th>User_ID</th>
<th>Email</th>
<th>No_Of_Referal</th> 
<th>No_Of_Registered Referals</th>
<th>No_Of_Purchase_Referral</th> 
<!--<th>No. of orders</th>-->
<th>Date</th>
<?php 
if($readresult && $countex)
{
	foreach($readresult as $row)
	{
	?>
    <tr>
        <td><?php echo $row['referral_parent_id']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['no_of_referal']; ?></td> 
        <td><?php echo $row['no_of_registered_referals']; ?></td>
        <td><?php echo $row['no_of_purchase_referral']; ?></td> 
        <!--<td><?php //echo $row[$fetchOrder[0]['count']]; ?></td>
        <td><?php //echo $row[$subTotal]; ?></td>-->
        <td><?php echo date("d-m-Y", strtotime($row['created_ts']));?></td>
    </tr>
<?php	
	} 
}
?>
</table>
</body>
</html>