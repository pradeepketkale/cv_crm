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

$('#downloadcsv').on('click', function () {

	//console.log(jQuery.param( data1 ));
	var data1 = [];
	data1['exportcsv'] = true;
	data1['month'] = $( "#month" ).val();
	data1['year'] = $( "#year" ).val();
	// console.log(data1);
	/*alert("/financereport/financereport/downloadcod/?startdate="+$( "#start" ).val() + "&enddate="+$( "#end" ).val() +"&ustatus="+$( "#ustatus" ).val() +"&paymentstatus="+$( "#paymentstatus" ).val() +"&couriername="+$( "#couriername" ).val());*/
	document.location.href="/financereport/financereport/handlingRdr/?month="+$( "#month" ).val() + "&year="+$( "#year" ).val();
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
		   				<h2>Handling Charges for RDR </h2>
		   				<form action='#' method='POST' onsubmit="return validateform()" name="myForm" id="myForm">
		   					<table  width="50%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">
							</table>
					</form>
					<table border="0" width="50%" cellspacing="0" cellpadding="0" >
						<tr>
							<td><b>Month</b></td>
							<td>
								<select  id="month" form="myForm" style="width: 150px;" >
									<option value="1">Jan</option>
									<option value="2">Feb</option>
									<option value="3">March</option>
									<option value="4">April</option>
									<option value="5">May</option>
									<option value="6">June</option>
									<option value="7">July</option>
									<option value="8">Aug</option>
									<option value="9">Sept</option>
									<option value="10">Oct</option>
									<option value="11">Nov</option>
									<option value="12">Dec</option>
								</select>

							</td>
							<td><b>Year:</b></td>
							<td>
								<select id="year" form="myForm" style="width: 100px;" >
									<option value="2014">2014</option>
									<option value="2015">2015</option>
									<option value="2016">2016</option>
									<option value="2017">2017</option>
									<option value="2018">2018</option>
									<option value="2019">2019</option>
									<option value="2020">2020</option>
									<option value="2021">2021</option>
								</select>

							</td>
						</tr>

						<tr>
							<td colspan="4" align="center">
								<button id="downloadcsv" class="btn btn-submit" type='submit' style='margin-right:15px;margin-top:1em;' > Download CSV </button>
							</td>
						</tr>
					</table>
					<!---pradeep -->



				</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
</body>
</html>
