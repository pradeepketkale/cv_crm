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

// define variable
$utmSource="";
$utmCampaign="";
$utmMedium="";
$from_date="";
$to_date="";
if(isset($_REQUEST["utm_source"])){$utmSource=$_POST["utm_source"];}
if(isset($_REQUEST["utm_campaign"])){$utmCampaign=$_POST["utm_campaign"];}
if(isset($_REQUEST["utm_medium"])){$utmMedium=$_REQUEST["utm_medium"];}
if(isset($_REQUEST["from_date"]) && !empty($_REQUEST["from_date"])){$from_dateArr=explode("/",$_POST["from_date"]);$from_date=$from_dateArr[2]."-".$from_dateArr[1]."-".$from_dateArr[0];}
if(isset($_REQUEST["to_date"])&& !empty($_REQUEST["to_date"])){$to_dateArr=explode("/",$_POST["to_date"]);$to_date=$to_dateArr[2]."-".$to_dateArr[1]."-".$to_dateArr[0];}
$strCond="";
if(!empty($utmSource)){$strCond.=" AND t1.utm_source like '".$utmSource."'";}
if(!empty($utmCampaign)){$strCond.=" AND t1.utm_campaign like '".$utmCampaign."'";}
if(!empty($utmMedium)){$strCond.=" AND t1.utm_medium like '".$utmMedium."'";}
if(!empty($from_date)){$strCond.=" AND t1.date_added >= '".$from_date."'";}
if(!empty($to_date)){$strCond.=" AND t1.date_added <= '".$to_date."'";}

$sql="SELECT t1.entity_id, t1.utm_source, t1.utm_campaign, t1.utm_medium, t2.email,t2.entity_id as customer_id,t1.date_added
FROM customer_utm_master AS t1
LEFT JOIN customer_entity AS t2 ON t1.entity_id = t2.entity_id WHERE 1".$strCond;

$read = Mage::getSingleton('core/resource')->getConnection('core_read');

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
<link href="calendar/CalendarControl.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="calendar/calendar.js"></script>
<script language="javascript" type="text/javascript" src='calendar/CalendarControl.js'></script>
</head>
<body>
<div class="content">
<!--<a class="logo" href="#"></a>-->
<p class="roller">
Welcome <?php echo ucfirst($_SESSION["sess_username"]);?> | <a href="logout.php">Logout</a>
</p>
<form action="search.php" method="POST">
 <table border="0" cellspacing="0" cellpadding="5" class="rightaligne">
  <tr>
    <td>UTM Source:</td>
    <td class="left">
    	<select name="utm_source">
        	<option value="">--Select--</option>
        	<?php
				$readUTMsource=$read->fetchAll("SELECT DISTINCT utm_source FROM customer_utm_master");
				foreach($readUTMsource as $utmObjectsource)
				{
			?>
					<option value='<?php echo $utmObjectsource['utm_source'];?>' <?php if(isset($_POST['utm_source']) && $_POST['utm_source']==$utmObjectsource['utm_source']){echo "selected";}?>><?php echo $utmObjectsource['utm_source'];?></option>
			<?php
                }
			?>
        </select>
    </td>
    <td>UTM Campaign:</td>
    <td class="left">
    	<select name="utm_campaign">
            <option value="">--Select--</option>
        <?php
				$readUTMcompaign=$read->fetchAll("SELECT DISTINCT utm_campaign FROM customer_utm_master");
				foreach($readUTMcompaign as $utmObjectcompaign)
				{
					?>
					<option value='<?php echo $utmObjectcompaign['utm_campaign'];?>' <?php if(isset($_POST['utm_campaign']) && $_POST['utm_campaign']==$utmObjectcompaign['utm_campaign']){echo "selected";}?>><?php echo $utmObjectcompaign['utm_campaign'];?></option>
					<?php
				}
			?>
        </select>
    </td>
  	<td>UTM Medium:</td>
    <td class="left">
    	<select name="utm_medium">
        	<option value="">--Select--</option>
        <?php
				$readUTMmedium=$read->fetchAll("SELECT DISTINCT utm_medium FROM customer_utm_master");
				foreach($readUTMmedium as $utmObjectmedium)
				{
					?>
					<option value='<?php echo $utmObjectmedium->utm_medium;?>' <?php if(isset($_POST['utm_medium']) && $_POST['utm_medium']==$utmObjectmedium['utm_medium']){echo "selected";}?>><?php echo $utmObjectmedium['utm_medium'];?></option>
					<?php
				}
			?>
        </select>
    </td>
    <td>From Date:</td>
    <td><input class="date" type="text" name="from_date" id="from_date" value="<?php if(isset($_POST['from_date']) && $_POST['from_date']!=""){echo $_POST['from_date'];}?>" onclick="javascript:popupCalender('from_date');" />&nbsp;<a href="javascript:popupCalender('from_date')"><IMG SRC="calendar/calendar.gif"  BORDER="0" ALT="Show Calendar"></a></td> 
    <td>To Date:</td>
    <td><input type="text" name="to_date" id="to_date" class="date" value="<?php if(isset($_POST['to_date']) && $_POST['to_date']!=""){echo $_POST['to_date'];}?>" onclick="javascript:popupCalender('to_date');" />&nbsp;<a href="javascript:popupCalender('to_date')"><IMG SRC="calendar/calendar.gif"  BORDER="0" ALT="Show Calendar"></a></td>
    <td class="left"><input type="submit" name="" value="Search" title="Search" /></td>
 
  </tr>
</table>
</form>
<table border="0" cellspacing="5" cellpadding="0"  width="100%"class="paddbo">
  <tr>
    <td><?php echo ("No. of rows selected-").$count;?></td>
     <td align="right"><input type="button" style="cursor:pointer;" value="Export into Excel" onclick="window.location='export_excel.php'" title="Download" /> </td>
  </tr>
</table>
<table border cellpadding=0 cellspacing=1 class=gride>
<th>ID</th>
<th>Email</th>
<th>Utm Source</th> 
<th>Utm Campaign</th> 
<th>Utm Medium</th> 
<th>No. of orders</th>
<th>Subtotal</th>
<th>Avg OrderTotal</th>
<th>Date</th>
</tr>
<?php
if($readresult){
	foreach($readresult as $row)
	{
 		$orderTotal=0;
		$subTotal = 0;
		$avgOrderTotal = 0;
		$readUTMorder=$read->fetchAll("SELECT count(`entity_id`) as count,sum(subtotal) as subtotal FROM `sales_flat_order` WHERE `customer_id`=".$row['customer_id']);

		if($row['subtotal'] != '')
			$subTotal= $row['subtotal'];
		if($subTotal > 0 and $readUTMorder[0]['count'] > 0)
			$avgOrderTotal = $subTotal/$readUTMorder[0]['count'];
		echo "<tr><td>".$row['entity_id']."</td><td>".$row['email']."</td><td>".$row['utm_source']."</td> <td>".$row['utm_campaign']."</td> <td>".$row['utm_medium']."</td> <td>".$readUTMorder[0]['count']."</td><td>".$subTotal."</td><td>".$avgOrderTotal."</td><td>".date("d/m/Y",strtotime($row['date_added']))."</td></tr>"; 
	} 	
}
 
?>
</table>
<p align="center">&copy; <a href="http://www.craftsvilla.com">craftsvilla.com.</a> All rights reserved.</p>
</div>
</body>
</html>