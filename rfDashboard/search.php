<?php
session_start();
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
/*Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);*/

//umask(0);
Mage::app();

if(!$_SESSION['sess_username'])
{
	session_destroy();
	session_unset();
	header("location: index.php");
	exit();
}

$no_of_referal="";
$no_of_registered_referals="";
$no_of_purchase_referral="";
$limit="";
$from_date="";
$to_date="";

if($_POST['search_value'])
{
	if(isset($_POST['no_of_referal'])){$no_of_referal=$_POST['no_of_referal'];}
	if(isset($_POST['no_of_registered_referals'])){$no_of_registered_referals=$_POST['no_of_registered_referals'];}
	if(isset($_POST['no_of_purchase_referral'])){$no_of_purchase_referral=$_POST['no_of_purchase_referral'];}
	if(isset($_POST['limit'])){$limit=$_POST['limit'];}
	if(isset($_REQUEST["from_date"]) && !empty($_REQUEST["from_date"])){$from_dateArr=explode("/",$_POST["from_date"]);$from_date=$from_dateArr[2]."-".$from_dateArr[1]."-".$from_dateArr[0];}
	if(isset($_REQUEST["to_date"])&& !empty($_REQUEST["to_date"])){$to_dateArr=explode("/",$_POST["to_date"]);$to_date=$to_dateArr[2]."-".$to_dateArr[1]."-".$to_dateArr[0];}
	
	$strCond="";
	if(!empty($from_date)){$strCond.=" WHERE t1.created_ts >= '".$from_date."'";}
	if(!empty($to_date)){$strCond.=" AND t1.created_ts <= '".$to_date."'";}
	
	$no_of_referal_sel = "";
	$no_of_registered_referals_sel = "";
	$no_of_purchase_referral_sel = "";
	
	if($_POST['no_of_referal'] == 'no_of_referal' )
	{
		$no_of_referal_sel = "checked";
		$cond = "ORDER BY no_of_referal";
	}
	else if($_POST['no_of_referal'] == 'no_of_registered_referals')
	{
		$no_of_registered_referals_sel = "checked";
		$cond = "ORDER BY no_of_registered_referals";
	}
	else if($_POST['no_of_referal'] == 'no_of_purchase_referral')
	{
		$no_of_purchase_referral_sel = "checked";
		$cond = "ORDER BY no_of_purchase_referral";
	}
	else
	{
		$cond = "";
	}
	
	
	if(!$limit)
	{
		$strCond_1 =" limit 5";
	}
}
	
	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
	
	$sql="SELECT t1.referral_parent_id, COUNT(*)  AS no_of_referal,SUM(IF(`referral_register_status` = 1, 1, 0)) AS no_of_registered_referals,SUM(IF(`referral_purchase_status` = 1, 1, 0)) AS no_of_purchase_referral ,t2.entity_id as customer_id,t2.email,t1.created_ts 
	FROM `referfriend_referral` AS t1 
	LEFT JOIN customer_entity AS t2 ON t1.referral_parent_id=t2.entity_id ".$strCond." GROUP BY t1.referral_parent_id ".$cond." DESC ".$strCond_1."";
	
	$_SESSION['excelSql']="";
	$_SESSION['excelSql']=base64_encode($sql);
	
	$readresult=$read->fetchAll($sql);
	$count = count($readresult);
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
.fr{ float:right;}
.gride tr th{ background:#E9E8E8; padding:3px 5px; font-weight:normal; color:#333;}
input,select{ color:#444; font-family:Arial, Helvetica, sans-serif; border:1px solid #ccc; width:214px; padding:3px;}
input[type="radio"],input[type="checkbox"]{ width:auto;}
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
<link href="calendar/CalendarControl.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="calendar/calendar.js"></script>
<script language="javascript" type="text/javascript" src='calendar/CalendarControl.js'></script>
</head>
<body>
<div class="content">
<!--<a class="logo" href="#"></a>-->


<p class="roller">
    
    Welcome <?php echo ucfirst($_SESSION["sess_username"]);?> |<?php $url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);?>
    <a href="<?php echo $url;?>rfDashboard/product_details.php" style=" text-decoration: none;">
        Product details of completed orders
    </a> | <a href="logout.php">Logout</a>

</p>
<form action="search.php" method="POST">
<table border="0" cellspacing="0" cellpadding="5" class="rightaligne">
  <tr>
    <td class="left"><input type="radio" name="no_of_referal" class="Highese_Referal" value="no_of_referal" id="no_of_referal" <?php echo $no_of_referal_sel;?> /></td>  	
    <td>Top 5 Highest_Referal:</td>
    
    <td class="left"><input type="radio" name="no_of_referal" class="Highese_Referal" value="no_of_registered_referals" id="no_of_registered_referals" <?php echo $no_of_registered_referals_sel;?> /> </td>  	
    <td>Top 5 Highest_Registered:</td>		
    
    <td class="left"><input type="radio" name="no_of_referal" class="Highese_Purchase" value="no_of_purchase_referral" id="no_of_purchase_referral" <?php echo $no_of_purchase_referral_sel;?> /> </td>  	
    <td>Top 5 Highest_Purchased:</td>		
    
    <td>From Date:</td>
    <td><input class="date" type="text" name="from_date" id="from_date" value="<?php if(isset($_POST['from_date']) && $_POST['from_date']!=""){echo $_POST['from_date'];}?>" onclick="javascript:popupCalender('from_date');" />&nbsp;<a href="javascript:popupCalender('from_date')"><IMG SRC="calendar/calendar.gif"  BORDER="0" ALT="Show Calendar"></a></td> 
    <td>To Date:</td>
    <td><input type="text" name="to_date" id="to_date" class="date" value="<?php if(isset($_POST['to_date']) && $_POST['to_date']!=""){echo $_POST['to_date'];}?>" onclick="javascript:popupCalender('to_date');" />&nbsp;<a href="javascript:popupCalender('to_date')"><IMG SRC="calendar/calendar.gif"  BORDER="0" ALT="Show Calendar"></a></td>    <td class="left"><input type="submit" name="search_value" value="Search" title="Search" /></td>
  </tr>
</table>
</form>
<table border="0" cellspacing="5" cellpadding="0"  width="100%" class="paddbo">
<tr>
	<td><?php echo ("No. Of Records Found-").$count;?></td>
    <td align="right"><input type="button" style="cursor:pointer;" value="Export into Excel" onclick="window.location='export_excel.php'" title="Download" /> </td>
</tr>
</table>
<table border="0" cellpadding="0" cellspacing="1" class="gride">
    <tr>
        <th>User ID</th>
        <th>Email</th>
        <th>no_of_referal</th> 
        <th>No_of_Registered_Referals</th>
        <th>no_of_purchase_referral</th> 
        <!--<th>No_of_orders</th>
        <th>Subtotal</th>-->
        <th>Date</th>
    </tr>
<?php 
if($readresult)
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
<p align="center">&copy; <a href="http://www.craftsvilla.com">craftsvilla.com.</a> All rights reserved.</p>
</div><br />
</body>
</html>
