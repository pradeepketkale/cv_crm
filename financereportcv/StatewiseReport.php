<?php
include('session.php');
require_once __DIR__.'/../dbConnectionRead.php';
$sql_statecode = "SELECT `region_id`, `default_name` FROM `directory_country_region` WHERE `country_id` = 'IN'";
$query = mysql_query($sql_statecode,$mainConnection);
$customer = mysql_query($sql_statecode,$mainConnection);
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
				dateFormat: 'yy-mm-dd 00:00:00',

			});

			$( "#end" ).datepicker({
				showOn: "button",
				changeMonth: true,
				changeYear: true,
				buttonImage: "img/cal.png",
				buttonImageOnly: true,
				dateFormat: 'yy-mm-dd 23:59:59',
			});

			$('#downloadcsv').on('click', function () {

				var startdate=document.myForm.startdate.value;
				var enddates=document.myForm.enddates.value;

				if (startdate==null || startdate==""){
					alert("Start Date can't be blank");
					return false;
				}else if(enddates==null || enddates==""){
					alert("End date can't be blank");
					return false;
				}
				var date1 = new Date(startdate);
				var date2 = new Date(enddates);
				var timeDiff = (date2.getTime() - date1.getTime());
				var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
				if (diffDays >=31){
					alert("You are exceeding date Range..Please correct");
					return false;
				} else if (diffDays<0 ) {
					alert("You are entering Wrong dates");
					return false;
				}
			// console.log(data1);
			/*alert("/financereport/financereport/downloadcod/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val());*/
			document.location.href="/financereport/financereport/statewise/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&vstate="+$( "#vstate" ).val() +"&cstate="+$( "#cstate" ).val();
		} );

			$('.btnhdsh').hide();

		} );


		var filterByDate = function (column, startDate, endDate) {

			$.fn.dataTableExt.afnFiltering.push(function (oSettings, aData, iDataIndex) {
				var rowDate = normalizeDate(aData[column]), start = normalizeDate(startDate), end = normalizeDate(endDate);
				if (start <= rowDate && rowDate <= end) {
					return true;
				} else if (rowDate >= start && end === '' && start !== '') {
					return true;
				} else if (rowDate <= end && start === '' && end !== '') {
					return true;
				} else {
					return false;
				}

			});
		};


		var normalizeDate = function (dateString) {
			var date = new Date(dateString);
			var normalized = date.getFullYear() + '' + ('0' + (date.getMonth() + 1)).slice(-2) + '' + ('0' + date.getDate()).slice(-2);
			return normalized;
		};

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
		   				<h2>State Wise Report</h2>
		   				<form action='#' method='POST' onsubmit="return validateform()" name="myForm" id="myForm">
		   					<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">

		   						<tr>

		   							<td style="text-align:left;">
		   								<b>Start Date :</b>
		   								<input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="start" required>
		   							</td>

		   							<td style="text-align:left;">
		   								<b>End Date : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="enddates" id="end" required>
		   							</td>
		   						</tr>
						</table>
					</form>
					<div>
						<table border="0" width="100%" cellspacing="0" cellpadding="0" style="width:90%;padding:0px; margin:0 auto;">
							<tr>
								<td><b>Vendor State:</b></td>
								<td><select id="vstate" form="myForm" style="margin-right:3em; width:10em">
									<option value="all">ALL</option>
									<?php while($row = mysql_fetch_assoc($query) ) { ?>
									<option value=<?php echo($row['region_id'])?> ><?php echo($row['default_name'])?></option>
									<?php } ?>
								</select></td>

								<td><b>Customer State:</b></td>
								<td><select id="cstate" form="myForm" style="margin-right:3em; width:10em">
									<option value="all">ALL</option>
									<?php while($row = mysql_fetch_assoc($customer) ) { ?>
									<option value=<?php echo($row['region_id'])?> ><?php echo($row['default_name'])?></option>
									<?php } ?>
								</select></td>
							</tr>
								<tr>
									<td colspan="6" align="center">
										<div>
											<button id="downloadcsv" class="btn btn-submit" type='submit'  style='margin-right:15px;margin-top:1em;' > Download CSV </button>
										</div>
									</td>
								</tr>
							</div>
						</div>
					</div>
				</div>
			</body>
			</html>
