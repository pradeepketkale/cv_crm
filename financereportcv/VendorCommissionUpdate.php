<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title></title>
	<link rel="stylesheet" href="css/style.css"/>
	<link rel="stylesheet" href="css/grid.css"/>
	<link rel="stylesheet" href="css/jquery-ui.css" />
	<link rel="stylesheet" href="css/jquery.dataTables.min.css" />
	<script src="js/jquery.min.js"></script>
	<script src="js/jquery-1.11.3.min.js"></script>
	<script src="js/jquery-ui.min.js"></script>
	<script src="js/jquery.dataTables.js"></script>
	<script src="js/dataTables.jqueryui.min.js"></script>
	<script src="js/dataTables.buttons.min.js"></script>
	<script src="js/buttons.flash.min.js"></script>
	<script src="js/jszip.min.js"></script>
	<script src="js/pdfmake.min.js"></script>
	<script src="js/vfs_fonts.js"></script>
	<script src="js/buttons.html5.min.js"></script>
	<script src="js/buttons.print.min.js"></script>

<script type="text/javascript">
	
	$(document).ready(function(){

			$( "#start" ).datepicker({
				showOn: "button",
				changeMonth: true,
				changeYear: true,
				buttonImage: "img/cal.png",
				buttonImageOnly: true,
				dateFormat: 'yy-mm-dd',

			});

	});

</script>
<script type="text/javascript">
	$(document).ready(function() {
		var matched, browser;
		jQuery.uaMatch = function( ua ) {
			ua = ua.toLowerCase();
			var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
			/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
			/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
			/(msie) ([\w.]+)/.exec( ua ) ||
			ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
			[];
			return {
				browser: match[ 1 ] || "",
				version: match[ 2 ] || "0"
			};
		};
		matched = jQuery.uaMatch( navigator.userAgent );
		browser = {};
		if ( matched.browser ) {
			browser[ matched.browser ] = true;
			browser.version = matched.version;
		}
			// Chrome is Webkit, but Webkit is also Safari.
			if ( browser.chrome ) {
				browser.webkit = true;
			} else if ( browser.webkit ) {
				browser.safari = true;
			}
			jQuery.browser = browser;
		} );

</script>

<style>
	#ui-datepicker-div{width:18% !important;}
	#example_wrapper{overflow-x: scroll;}

	#example_paginate .ui-state-default{width: 50px;}
	.dt-button{padding: :10px;}
</style>
</head>
<body>
	<?php
	error_reporting(E_ALL ^ E_NOTICE);
	require_once '../app/Mage.php';
	Mage::app();
	$vendor_id = $_GET['vendorid'];
	if($vendor_id == ''){
		echo "Vendor ID not Entered";
		exit;
	}
	$readQuery = Mage::getSingleton('core/resource')->getConnection('custom_db');
	$sqlVendorComm = "SELECT `vendor_id`,`commission_percent` FROM `finance_vendor_commission` WHERE `vendor_id` = 100";
	$rVendor = $readQuery->query($sqlVendorComm)->fetchAll();
	print_r($rVendor);

	$uvstatus = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();	
	?>
	
	<div class="grid Page-container">
		<div class="col-1-1">

			<div class="grid">
				<div class="col-2-12">
					<div class="container" style="width: 247px;height: 58px;background-color: #e1e1e1;">
						<div class="logo_h"></div>
					</div>
				</div>

				<div class="col-10-12">
					<div class="container">
						<div class="page-breadcrumb">

							<div class="page-heading">            
								<h1>Finance Dashboard</h1>

							</div>

							<div class="clear"></div> 
						</div>


					</div>
				</div>

			</div>
		</div>
	</div>	


	<div class="grid grid-pad">
		<div class="col-1-1">
			<div class="container-wrapper2">
				<div style="width:100%; height:auto;padding:10px;border:1px solid #e1e1e1;border-radius:5px;">
				<h2>Vendor Commission Update</h2>
					<form action='/financereport/financereport/setCommissionPercent/' method='get' name="myForm">						
						<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">

							<tr>						
								<td style="text-align:left;width: 40%;" >
									<b>Current Commission (%) : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="<?php echo $rVendor[0]['commission_percent'] ?>" class="field text datpik" name="vendorcommission" id="vendorcommission">
								</td>
								<td style="text-align:left;">
									<b>Start Date :</b>
									<input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="start" required>
									<input type="text" value="<?php echo $rVendor[0]['vendor_id'] ?>" name="vendorid" id="vendorid" style = "visibility: collapse;">
								</td>	
							</tr>
							<tr>
								<td>
									<button id="save" class="btn btn-submit" type='submit' style='margin:7%;' > Save </button>
								</td>
							</tr>
						</table>
					</form>

				</div>
			</div>

		</div>
	</div>

			<div class="dataloaddiv">

				<h3>Please wait while your CSV gets Prepared. <br> Please Reload the page After file gets Download. </h3>	
				<div class="loader"></div>
			</div>


</body>
</html>