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

			$( "#end" ).datepicker({
				showOn: "button",
				changeMonth: true,
				changeYear: true,
				buttonImage: "img/cal.png",
				buttonImageOnly: true,
				dateFormat: 'yy-mm-dd',
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
			//console.log(jQuery.param( "yo" ));
			var data1 = [];
			data1['startdate'] = $( "#start" ).val();// "2014-01-01";
			data1['enddate'] = $( "#end" ).val();//"2016-01-01";
			data1['exportcsv'] = true;
			data1['ustatus'] = $( "#ustatus" ).val();
			data1['paymentstatus'] = $( "#paymentstatus" ).val();
			data1['couriername'] = $( "#couriername" ).val();
			// console.log(data1);
			/*alert("/financereport/financereport/downloadcod/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val());*/
			document.location.href="/financereport/financereport/downloadprepaid/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val();
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
					<h2>Prepaid Report</h2>
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


							<!-- <tr>
										
								<td colspan="2" align="center">
									<button id="filter" class="btn btn-submit" type='submit' style='margin-right:15px;' > Submit </button>
								</td>
							</tr> -->
						</table>
					</form>
					<div>
						<table border="0" width="100%" cellspacing="0" cellpadding="0" style="width:90%;padding:0px; margin:0 auto;">
<tr>

					<td><b>Udropship Status:</b></td>
					<td><select id="ustatus" form="myForm" style="margin-right:3em; width:10em">
						<option value="all">ALL</option>
						<option value="pending">pending</option>
						<option value="shipped to customer">shipped to customer</option>
						<option value="partial">partial</option>
						<option value="pendingpickup">pendingpickup</option>
						<option value="ack">ack</option>
						<option value="exported">exported</option>
						<option value="ready">ready</option>
						<option value="onhold">onhold</option>
						<option value="backorder">backorder</option>
						<option value="cancelled">cancelled</option>
						<option value="delivered">delivered</option>
						<option value="processing">processing</option>
						<option value="refundintiated">refundintiated</option>
						<option value="not delivered">not delivered</option>
						<option value="charge_back">charge_back</option>
						<option value="shipped craftsvilla">shipped craftsvilla</option>
						<option value="qc_rejected">qc_rejected</option>
						<option value="received">received</option>
						<option value="out of stock">out of stock</option>
						<option value="partial refund initiated">partial refund initiated</option>
						<option value="dispute raised">dispute raised</option>
						<option value="shipment delayed">shipment delayed</option>
						<option value="partially shipped">partially shipped</option>
						<option value="refund to do">refund to do</option>
						<option value="Accepted">Accepted</option>
						<option value="Returned By Customer">Returned By Customer</option>
						<option value="Returned To Seller">Returned To Seller</option>
						<option value="Mainfest Shared">Mainfest Shared</option>
						<option value="COD SHIPMENT PICKED UP">COD SHIPMENT PICKED UP</option>
						<option value="Packing slip printed">Packing slip printed</option>
						<option value="Handed to courier">Handed to courier</option>
						<option value="Returned Recieved from customer">Returned Recieved from customer</option>
						<option value="partially recieved">partially recieved</option>
						<option value="Damage/Lost in Transit">Damage/Lost in Transit</option>
					</select></td>
					
					<td><b>Payment Status:</b></td>
					<td><select id="paymentstatus" form="myForm" style="margin-right:3em; width:6em">
						<option value="all">ALL</option>
						<option value="0">Unpaid</option>
						<option value="1">Paid</option>
						<option value="2">Refunded</option>
					</select></td>

					<td><b>Courier name:</b></td>
					<td><select id="couriername" form="myForm" style="margin-right:3em; width:10em">
						<option value="all">ALL</option>
						<option value="Aramex">Aramex</option>
						<option value="Fedex">Fedex</option>
						<option value="Dtdc">Dtdc</option>
					</select></td>
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