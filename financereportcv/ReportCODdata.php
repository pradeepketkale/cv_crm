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
	  dateFormat: 'yy-mm-dd'
    });   
            
            
            
       
       
            
    $('#example').DataTable( {
         
           dom: 'Bfrtip',
          
          "ajax": '/financereport/financereport/reportcod/',
        		
		//'aaData': dummyData,	
          
                

         'aoColumns': [
         { "data": "order_id" },
           { "data": "order_date" },
           { "data": "shipment_id" },

           { "data": "shipment_datec" },
           { "data": "awb_number" },
           { "data": "shipment_update" },
           { "data": "payment_created_date" },
           { "data": "payment_updated_date" },

           { "data": "vendor_name" },
           { "data": "SubTotal" },
           { "data": "payment_amount" },
           { "data": "comission_amount" },
           { "data": "ustatus" },
           { "data": "payoutstatus" },
         
       ],
      
        
     
        buttons: [
      
        {
            extend: 'csv',
            text: 'CSV',
            extension: '.csv',
            exportOptions: {
                modifier: {
                   
                }
            },
            title: 'ReportCOD'
        }, 
       
        
        
    ],  
      
       
        initComplete: function () {
         
            this.api().columns().every( function () {
                
                var column = this;
                
               
              


                
                
                var select = $('<select><option value="">--Select--</option></select>')
                 
                      .appendTo( $(column.footer()) )
            
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                           
                        );
 
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
    
    
 
    
     var $tableSel = $('#example');
     $('#filter').on('click', function (e) {
        //console.log('#filter');
	 e.preventDefault();
        var startDate = $('#start').val(), endDate = $('#end').val()
         //console.log(startDate);
        // console.log(endDate);
        filterByDate(3, startDate, endDate);
       
        //filterselected(13,paystatus);
        
         $tableSel.dataTable().fnDraw();
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
				<div class="container-wrapper1">
					<div style="width:100%; height:auto;padding:10px;border:1px solid #e1e1e1;border-radius:5px;">
					<form action='#codreport' method='POST'>						
					<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">
                                            
								  <tr>
									<td> <b>Start Date :</b></td>
									<td style="text-align:left;">
									 <input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="Field12" id="start">
									</td>
									<td> <b>End Date : </b></td>
									<td style="text-align:left;">
									 <input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="Field12" id="end">
									</td>
								  </tr>
								  
								<!--  <tr>
                                                                      <td ><b>PO Status: </b></td>
									<td class="postatus">
								<select name="select" class="select2 left" id ="selectstatus">
								<?php foreach ($uvstatus as $key => $value){  ?>
										<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
									<?php	}?>
									</select>
									</td>
									<td><b>Payment Method : </b></td>
									<td>
									<select name="select" class="select2 left">
										<option value="">-Payment Method-</option>
										<option value="opt1">2&nbsp;</option>
									</select>
									</td>-->
								  </tr>
								  
								  
								  <!--<tr>
									<td><b>Payment Method : </b></td>
									<td>
									<select name="select" class="select2 left">
										<option value="">-Payment Method-</option>
										<option value="opt1">2&nbsp;</option>
									</select>
									</td>
									<td><b>Payment Status: </b></td>
									<td>
									<select name="select" class="select2 left">
										<option value="">-Payment Status-</option>
										<option value="opt1">2&nbsp;</option>
									</select>
									</td>
								  </tr>
								  
								  <tr>
									<td><b>Payment Status: </b></td>
									<td class="test">
									<select name="select" class="select2 left  " id="paystatus">
										<option value=""></option>
										<option value="unpaid">Unpaid</option>
										<option value="paid">Paid</option>
										<option value="refunded">Refunded</option>
									</select>
									</td>
									<td><b>Courier</b></td>
									<td class="Cour">
									<select name="select" class="select2 left" id="courier">
										<option value="">Courier</option>
										<option value="aramex">Aramex</option>
										<option value="fedex">Fedex</option>
										<option value="dtdc">DTDC</option>
									</select>
									</td>
								  </tr>-->
								  
								 <!-- <tr>
									<td><b>PO Status: </b></td>
									<td>
									<select name="select" class="select2 left">
										<option value="">-PO Status-</option>
										<option value="opt1">2&nbsp;</option>
									</select>
									</td>
									<td><b>Parameters - : </b></td>
									<td>
									<select name="select" class="select2 left">
										<option value="">-Parameters-</option>
										<option value="opt1">2&nbsp;</option>
									</select>
									</td>
								  </tr>-->
								   <tr>
									<td colspan="4" class="" style="text-align:center;">
										
									</td>
                                                                  
                                                                  <tr>
                                                                      <td></td>
                                                                      <td></td>
                                                                      <td><button id="filter" class="btn btn-submit" type='submit' style='margin-right:15px;' > Submit </button><button id="clearFilter" class="btn btn-submit" type='submit' > Clear  </button></td>
                                                                      <td></td>
                                                                      
                                                                  </tr>
								<!--- <tr>
									<td colspan="4" class="bdr" style="text-align:center;">

											
	</div><br><div id='codrepo' style='display:none'></div></div><div id="display"></div>	
        
	</td>
      
								  </tr> -->
						</table>
					</form>
					</div>
				</div>
				<div class="clear"></div> 
			</div>
	   </div>
	   <div class="grid grid-pad">
			<div class="col-1-1">
				<div class="container-wrapper">
					<!--<div style="width:100%;height: 42px;clear: both;text-align: right;"><input type="submit" value="Export to Excel" class="paginate_button current"></div>-->
					<div style="width:100%; height:auto;padding:10px;border:1px solid #e1e1e1;border-radius:5px;">
						<!----<div id="oTable_wrapper" class="dataTables_wrapper" role="grid"></div>
						 <table id="oTable" class="dataTable"> -->
						<table id="example" class="display" cellspacing="0" width="100%">  
        <thead >
            <tr>
               <th>Order Id</th>
                <th>Order Date</th>
                <th>Shipment Id</th>
                <th>Shipment Datec</th>
                <th>Awb Number</th>
                <th>Shipment Update</th>
                <th>Payment Created Date</th>
                <th>Payment Updated Date</th>
                <th>Vendor Name</th>
                <th>SubTotal</th>
                <th>Payment Amount</th>
                <th>Comission Amount</th>
		<th>Udropship Status</th>
                <th>Payout Status</th>
		</tr>
		
        </thead>
        <tfoot >
            <tr>
                <th>Order Id</th>
                <th>Order Date</th>
                <th>Shipment Id</th>
                <th>Shipment Datec</th>
                <th>Awb Number</th>
                <th>Shipment Update</th>
                <th>Payment Created Date</th>
                <th>Payment Updated Date</th>
                <th>Vendor Name</th>
                <th>SubTotal</th>
                <th>Payment Amount</th>
                <th>Comission Amount</th>
		<th>Udropship Status</th>
                <th>Payout Status</th>
		</tr>
		
        </tfoot>
        
         
        </tbody>
    </table>
					</div>
				</div>
				<div class="clear"></div> 
			</div>
	   </div>
</body>
</html>
<!--<script>
function codsubmitform() {
		jQuery('#codrepo').show();
		var datepicker1 = document.getElementById("datepicker1").value;
		var datepicker2 = document.getElementById("datepicker2").value;


	var param = 'from_date='+datepicker1+'&to_date='+datepicker2;
	var daterangeurl = '/financereport/financereport/reportcod/';
	jQuery.ajax({
		url : daterangeurl,
		type: "post",

		data : param,
		success : function(data) {
			jQuery('#codrepo').html(data);
			return false;
					
		},
		error:function(){
            alert("failure");
            jQuery("#codrepo").html('There is error while submit');
        }
	})
      }
</script>-->
