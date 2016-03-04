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
	document.location.href="/financereport/financereport/recoForPayment/?"+ jQuery.param( data );
} );

$('.btnhdsh').hide();

		$('#filter').on('click', function (e) {
			
			//validation pradeep
			console.log('working')  
				var startdate=document.myForm.startdate.value;  
				var enddates=document.myForm.enddates.value;  
  
				if (startdate==null || startdate==""){  
	 				 alert("Start Date can't be blank");  
	  				 return false;  
					 }else if(enddates==null || enddates==""){  
	  				 alert("End date can't be blank");  
	  				 return false;  
					}
		//validation end

		//pradeep
		 $('#example tfoot tr th').find('select').css('background-color','red').hide();
		  $('#example thead	 tr th').find('.ui-icon ').css('background-color','red').hide();
			
		$('.btnhdsh').show();		
		 table = $('#example').DataTable( {
			
				"processing": true,
				"serverSide": true,
					"bDestroy": true,	
				"ajax": {
					"url": "/financereport/financereport/recoForPayment/",
					"data": function ( d ) {
						d.startdate = $( "#start" ).val();// "2014-01-01";
						d.enddate = $( "#end" ).val();//"2016-01-01";
						d.exportcsv = false;
            		}
        		},
        		"lengthMenu": [ 25, 50, 75, 100 ],
 
		        initComplete: function () {
		        	this.api().columns().every( function () {
		        		var column = this;
		        		var select = $('<select><option value="">--Select--</option></select>')
		        		.appendTo( $(column.footer()) )
		        		.on( 'change', function () {
							
		        			var val = $.fn.dataTable.util.escapeRegex(
		        				$(this).val()        				
		        				
		        				);
		        				//console.log(this);
		        				
		        			column
		        			.search( val ? '^'+val+'$' : '', true, false )
		        			.draw();
		        		} );

		        		column.data().unique().sort().each( function ( d, j ) {
		        			select.append( '<option value="'+d+'">'+d+'</option>' )
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
				<h2>Record For Payment</h2>
					<form action='#codreport' method='POST' onsubmit="return validateform()" name="myForm">						
						<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">

							<tr>
								
								<td style="text-align:left;">
									<b>Start Date :</b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="start" required>
								</td>
								
								<td style="text-align:left;">
									<b>End Date : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="enddates" id="end" required>
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
<div class="clear"></div> 
</div>
</div>
<div class="grid grid-pad">
		
							<table id="example" class="display btnhdsh" cellspacing="0" width="100%">    
								<button id="csv" class="btn btn-submit btnhdsh" type='submit' style="margin-bottom:10px;"> Convert To CSV  </button>
								<thead >
									<tr>
										<th>Shipment Id</th>
										<th>AWB Number</th>										
										<th>Merchant Name</th>
										<th>Merchant Id</th>
										<th>Amount</th>
										<th>Pay In Date</th>
										<th>UTR Number</th>
										<th>Shipment Payout Update Time</th>
									</tr>

								</thead>
								<tfoot >
									<tr>
										<th>Shipment Id</th>
										<th>AWB Number</th>
										<th>Merchant Name</th>
										<th>Merchant Id</th>
										<th>Amount</th>
										<th>Pay In Date</th>
										<th>UTR Number</th>
										<th>Shipment Payout Update Time</th>
									</tr>
								</tfoot>

		</div>
	</body>
	</html>