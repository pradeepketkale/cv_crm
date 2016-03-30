<?php
include('session.php');
?>
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
	require_once __DIR__.'/../dbConnectionRead.php';
	$vendor_id = $_GET['vendorid'];
	$vendor_commission = '';
	//var_dump($vendor_id);exit;
	$error = '';
	//echo $vendor_id;exit;
	if($vendor_id == ''){
		echo "<center>Error: Vendor ID not Entered</center>";
		exit;
	}else if (!is_numeric($vendor_id)){
		echo "<center>Error: Please enter numeric Vendor Id</center>";
		exit;
	}
	$sqlCheckVendor = "SELECT `vendor_id` FROM `udropship_vendor` WHERE `vendor_id` = '".$vendor_id."'";
	try{
		$rCheckVendor = mysql_query($sqlCheckVendor,$mainConnection);
		$rows = mysql_num_rows($rCheckVendor);
		if ($rows == 0 ){
			echo "<center>Error: Vendor Id not present in database</center>";
			exit;
		}
	} catch (Exception $e){
		echo 'Caught exception: ',  $e->getMessage(), "\n";
		exit;
	}

	$sqlVendorComm = "SELECT `vendor_id`,`commission_percent`, `date_created` FROM `finance_vendor_commission` WHERE `vendor_id` = '".$vendor_id."' order by `date_created` desc ";
	try{
		$rVendor = mysql_query($sqlVendorComm,$mainConnection);
		$rowCount = mysql_num_rows($rVendor);
		$row = mysql_fetch_assoc($rVendor);
		if($rowCount == 0){
			$vendor_commission = 20;
		} else {
			$vendor_commission = $row['commission_percent'];
		}
	} catch (Exception $e){
		echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	mysql_close($mainConnection); 
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
								<!--<div class="clear" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>||
		                       		 <a href="logout.php" ><b>Logout</b> </a>
		                       	</div> -->
		                       	<div class="FRnavigation" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>
                       		 <a href="logout.php" ><b>Logout</b> </a>
                       	 </div> 
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
								<td style="text-align:left;width: 45%;" >
									<b>Current Commission (%) : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="<?php echo $vendor_commission ?>" class="field text datpik" name="vendorcommission" id="vendorcommission">
								</td>
								<td style="text-align:left;">
									
									<b>Start Date :</b>
									<input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="start" required>
									<input type="text" value="<?php echo $vendor_id ?>" name="vendorid" id="vendorid" style = "visibility: collapse;">
									
								</td>	
							</tr>
							<tr>
								<td>
									<button id="save" class="btn btn-submit" type='submit' style='margin:7%;' > Save </button>
									<input type="text" value="<?php echo $_SESSION['login_user'] ?>" name="login_id" id="login_id" style = "visibility: collapse;">
								</td>
							</tr>
						</table>
					</form>

				</div>
			</div>

		</div>
	</div>

</body>
</html>