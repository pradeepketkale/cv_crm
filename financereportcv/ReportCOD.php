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



		var table;
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



// table.on( 'xhr', function () {
// 				    var data = table.ajax.url();
// 				    alert( 'Search term was: '+ data.search.value );
// 				} );

var $tableSel = $('#example');
$('#csv').on('click', function () {
	var data = table.ajax.params();
	data['exportcsv'] = true;
	//alert (JSON.stringify(data));
	document.location.href="/financereport/financereport/reportcod/?"+ jQuery.param( data );
} );

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
	//console.log(jQuery.param( data1 ));
	var data1 = [];
	data1['startdate'] = $( "#start" ).val();// "2014-01-01";
	data1['enddate'] = $( "#end" ).val();//"2016-01-01";
	data1['exportcsv'] = true;
	data1['ustatus'] = $( "#ustatus" ).val();
	data1['paymentstatus'] = $( "#paymentstatus" ).val();
	data1['couriername'] = $( "#couriername" ).val();
	// console.log(data1);
	/*alert("/financereport/financereport/downloadcod/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val());*/
	document.location.href="/financereport/financereport/downloadcod/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val();
} );

$('.btnhdsh').hide();

$('#filter').on('click', function (e) {

		//validation pradeep
			//console.log('working')
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
			var timeDiff = Math.abs(date2.getTime() - date1.getTime());
			var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
			if (diffDays >=31){
				alert("You are exceeding 31 days date Range..Please correct");
				return false;
			}
		//validation end

		//
		$('#example tfoot tr th').find('select').css('background-color','red').hide();
		$('#example thead	 tr th').find('.ui-icon ').css('background-color','red').hide();

		// pradeep
		$('.btnhdsh').show();
		table = $('#example').DataTable( {
		 	//"bJQueryUI": true,
				// "bRetrieve": true,
				"processing": true,
				"serverSide": true,
				"bDestroy": true,
				"ajax": {
					"url": "/financereport/financereport/reportcod/",
					"data": function ( d ) {
						d.startdate = $( "#start" ).val();// "2014-01-01";
						d.enddate = $( "#end" ).val();//"2016-01-01";
						d.ustatus = $( "#ustatus" ).val();
						d.paymentstatus = $( "#paymentstatus" ).val();
						d.couriername = $( "#couriername" ).val();
						d.exportcsv = false;
					}

				},


				"lengthMenu": [ 25, 50, 75, 100 ],

				initComplete: function () {

					this.api().columns().every( function () {

						var column = this;

		        		//console.log(column[0]);
		        		var select = $('<select><option value="">--Select--</option></select>')
		        		.appendTo( $(column.footer()) )

		        		.on( 'change', function () {


		        			var val = $.fn.dataTable.util.escapeRegex(
		        				$(this).val()


		        				);
		        				//console.log(this);

		        				column

		        				.search( val ? '^'+val+'$' : '', true, false )
		        				.draw()

		        			} );


		        		column.data().unique().sort().each( function ( d, j ) {

		        			select.append( '<option value="'+d+'">'+d+'</option>' )
		        		//console.log(data);

		        	} );
		        	} );
				},




			} );




			//console.log('#filter');
			e.preventDefault();
			var startDate = $('#start').val(), endDate = $('#end').val()
			 //console.log(startDate);
			// console.log(endDate);
			filterByDate(3, startDate, endDate);

			//filterselected(13,paystatus);


			$.fn.dataTableExt.afnFiltering.length = 0;

			return false;

			//validation



		} );





$('#clearFilter').on('click', function (e) {
	e.preventDefault();
	$.fn.dataTableExt.afnFiltering.length = 0;
	$tableSel.dataTable().fnDraw();
});
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
		   				<h2>COD Report </h2>
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

					<!----pradeep -->

					<table border="0" width="100%" cellspacing="0" cellpadding="0" style="width:90%;padding:0px; margin:0 auto;">
						<tr>
							<td><b>Udropship Status:</b></td>
							<td>
								<select id="ustatus" form="myForm" style="margin-right:3em; width:15em">
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
								</select>

							</td>
							<td><b>Payment Status:</b></td>
							<td>
								<select id="paymentstatus" form="myForm" style="margin-right:3em; width:6em">
									<option value="all">ALL</option>
									<option value="0">Unpaid</option>
									<option value="1">Paid</option>
									<option value="2">Refunded</option>
								</select>

							</td>
							<td><b>Courier name:</b></td>
							<td>
								<select id="couriername" form="myForm" style="margin-right:3em; width:10em">
									<option value="all">ALL</option>
									<option value="Aramex">Aramex</option>
									<option value="Fedex">Fedex</option>
									<option value="Dtdc">Dtdc</option>
									<option value="India Post">India Post</option>
									<option value="EcomExpress">EcomExpress</option>
									<option value="Dhl_int">DHL International</option>
								</select>

							</td>
						</tr>

						<tr>

							<td colspan="2" align="right">

								<button id="filter" class="btn btn-submit" type='submit' form="myForm"  style='margin-right:15px;margin-left:30%;margin-top:1em;' > Show Data </button>

							</td>
							<td colspan="4" align="left">
								<button id="downloadcsv" class="btn btn-submit" type='submit' style='margin-right:15px;margin-left:20%;margin-top:1em;' > Download CSV </button>
							</td>



						</tr>
					</table>
					<!---pradeep -->



				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="grid grid-pad">

		<table id="example" class="display btnhdsh" cellspacing="0" width="100%">
			<button id="csv" class="btn btn-submit btnhdsh" type='submit' style="margin-bottom:10px;"> Convert To CSV  </button>
			<thead >
				<tr>
					<th>Order Id</th>
					<th>Order Date</th>
					<th>Shipment Id</th>
					<th>Udropship Status</th>
					<th>Payout Status</th>
					<th>Shipment Date</th>
					<th>Awb Number</th>
					<th>Shipment Update</th>
					<th>UTR Number</th>
					<th>Payment Updated Date</th>
					<th>Vendor Name</th>
					<th>SubTotal</th>
					<th>Payment Amount</th>
					<th>Comission Amount</th>
					<th>Courier Name</th>
				</tr>

			</thead>
			<tfoot >
				<tr class="aaa">
					<th>Order Id</th>
					<th>Order Date</th>
					<th>Shipment Id</th>
					<th>Udropship Status</th>
					<th>Payout Status</th>
					<th>Shipment Date</th>
					<th>Awb Number</th>
					<th>Shipment Update</th>
					<th>UTR Number</th>
					<th>Payment Updated Date</th>
					<th>Vendor Name</th>
					<th>SubTotal</th>
					<th>Payment Amount</th>
					<th>Comission Amount</th>
					<th>Courier Name</th>
				</tr>

			</tfoot>
		</table>

	</div>
</body>
</html>
