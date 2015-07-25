<?php
error_reporting(E_ALL & ~E_NOTICE);
session_start();
require_once '../app/Mage.php';

Varien_Profiler::enable();
Mage::setIsDeveloperMode(true);
ini_set('display_errors', 1);

umask(0);
Mage::app();

if($_FILES)
{
	//$storeId = Mage::app()->getStore()->getId();
	if($_FILES['uploadfile']['error'] == 0)
	{
		$categoryId = 4;
		 
		$media_path_upload = Mage::getBaseDir('media');
		$filename = $_FILES['uploadfile']['name'];

		$fileName1 = 'values.xls';
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=$fileName1");
		error_reporting(E_ALL & ~E_NOTICE);

		echo '<table border cellpadding=0 cellspacing=1>
			<th>SKU</th>
			<th>Price</th>
			<th>Qty</th>'; 

		if(move_uploaded_file($_FILES['uploadfile']['tmp_name'],$media_path_upload . '/' . $filename))
		{
			if (($handle = fopen($media_path_upload . '/' . $filename, "r")) !== FALSE) {
				while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					$num = count($data);
					for ($c=0; $c < $num; $c++) {
						$_productCollection = Mage::getModel('catalog/product')->loadByAttribute('sku', $data[$c]);
						$final_price = '';
						if($_productCollection)
						{
							$final_price = $_productCollection->getFinalPrice();
						}
						$Quantity_val = '';
						if($_productCollection)
						{
							//$Quantity_val = $_productCollection->getStockData();
							$Quantity_val = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_productCollection)->getQty();
						}
						echo '<tr><td>'.$data[$c].'</td><td>'.$final_price.'</td><td>'.$Quantity_val.'</td></tr>';
					}
					$row++;
				}
				fclose($handle);
				unlink($media_path_upload . '/' . $filename);
			}
		}
		else
		{
			echo "Meaw Meaw";
		}
	}
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Craftsvilla</title>

<style type="text/css">
.contnt {
	width: 980px;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 13px;
	color: #444;
	margin: 0 auto;
}

.gride {
	background: #ccc;
	width: 100%;
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

.login {
	background: #f8f8f8;
	padding: 25px;
	-moz-border-radius: 15px;
	border-radius: 15px;
	width: 50%;
	margin: 70px auto 100px;
}

.login h2 {
	border-bottom: 1px dotted #CCCCCC;
	font-weight: normal;
	margin: 0 0 12px;
	padding: 0 0 6px;
}
</style>



</head>

<body>
	<div class="contnt">

		<a class="logo" href="#"></a>
		<p class="roller">&nbsp;</p>

		<div class="login">
			<h2>Upload CSV File</h2>
			<form method="post" enctype="multipart/form-data" name="fm"
				action="index.php">
				<table border="0" cellspacing="0" cellpadding="5">
					<tr>
						<td>Upload</td>
						<td><input type="file" name="uploadfile" id="uploadfile" /></td>
					</tr>
					<tr>
						<td colspan=2><input type="submit" name="submit_file"
							id="submit_file" value="Submit" />
						</td>
					</tr>
				</table>
			</form>


		</div>
		<p align="center">
			&copy; <a href="http://www.craftsvilla.com">craftsvilla.com.</a> All
			rights reserved.
		</p>

	</div>
</body>
</html>
