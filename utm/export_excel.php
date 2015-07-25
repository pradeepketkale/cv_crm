<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
/*Varien_Profiler::enable();
 Mage::setIsDeveloperMode(true);
 ini_set('display_errors', 1);

 umask(0);*/
Mage::app();

$fileName = 'utm-data.xls';
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$fileName");

$readexcel = Mage::getSingleton('core/resource')->getConnection('core_read');

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
body {
	font-family: calibri, tahoma, verdana, arial;
	font-size: 12px;
}

.contnt {
	width: 980px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 11px;
	color: #444;
	margin: 0 auto;
}

.gride {
	background: #ccc;
	width: 100%;
	border: 0;
}

.gride tr td {
	background: #fff;
	padding: 3px 5px;
}

.gride tr.altnet td {
	background: #f8f8f8;
}

.gride tr th {
	background: #E9E8E8;
	padding: 3px 5px;
}

input,select {
	color: #444;
	font-family: Arial, Helvetica, sans-serif;
	border: 1px solid #ccc;
	width: 214px;
	padding: 3px;
}

input.date {
	width: 70px;
	text-align: center;
}

input[type="submit"] {
	width: auto;
	background: #999;
	color: #fff;
	font-weight: bold;
	outline: 1px solid #666;
	border-color: #fff;
	cursor: pointer;
}

select {
	width: 118px;
}

.rightaligne tr td {
	text-align: right;
}

.rightaligne tr td.left {
	text-align: left;
}

.logo {
	background: url(images/home-sprite.png) no-repeat scroll -20px -215px
		transparent;
	height: 134px;
	width: 223px;
	display: block;
	margin: 0 auto 20px;
}

.roller {
	border-bottom: 1px solid #ccc;
	margin-bottom: 20px;
	text-align: right;
}

.contnt a {
	text-decoration: none;
	color: #444;
}

.rightaligne {
	margin: 0 auto 20px;
}

.pagination {
	margin: 0;
}

.paddbot {
	padding-bottom: 10px;
}
</style>
</head>
<body>
	<table border cellpadding=0 cellspacing=1 class=gride>
		<th>ID:</th>
		<th>Email:</th>
		<th>Utm Source:</th>
		<th>Utm Campaign:</th>
		<th>Utm Medium:</th>
		<th>No. of orders:</th>
		<th>Date:</th>
		</tr>
		<?php
		if($readexresult && $countex) {
			foreach($readexresult as $row)
			{
				$fetchOrder=$readexcel->fetchAll("SELECT count(`entity_id`) FROM `sales_flat_order` WHERE `entity_id`=".$row['entity_id']);

				echo "<tr><td>".$row['entity_id']."</td><td>".$row['email']."</td><td>".$row['utm_source']."</td> <td>".$row['utm_campaign']."</td> <td>".$row['utm_medium']."</td> <td>".$fetchOrder[0]."</td><td>".date("d/m/Y",strtotime($row['date_added']))."</td></tr>";
	} 
}
 
?>
	</table>
</body>
</html>
