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

			$('#filter').click(function(){


		//validation pradeep
			console.log('working')  
				var startdate=document.myForm.startdate.value;  
				var enddate=document.myForm.enddate.value;  
  
				if (startdate==null || startdate==""){  
	 				 alert("Start Date can't be blank");  
	  				 return false;  
					 }else if(enddate==null || enddate==""){  
	  				 alert("End date can't be blank");  
	  				 return false;  
					}
		//validation end


				$('.dataloaddiv').show();
			})

			$( "#start" ).datepicker({
				showOn: "button",
				changeMonth: true,
				changeYear: true,
				buttonImage: "img/cal.png",
				buttonImageOnly: true,
				dateFormat: 'yy-mm-dd',
			});
			
			$( "#end" ).datepicker({
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
								<h1>Finance Report Dashboard</h1>
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
				<h2>Payment Invoice</h2>
					<form action='/financereport/financereport/paymentInvoice/' method='get' name="myForm">						
						<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">

							<tr>
								
								<td style="text-align:left;">
									<b>Start Date :</b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="start">
								</td>
								
								<td style="text-align:left;">
									<b>End Date : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="enddate" id="end">
								</td>
							</tr>
							<tr>

								<td colspan="2" align="center">
									<button id="filter" class="btn btn-submit" type='submit' style='margin-right:15px;' > Submit </button>


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