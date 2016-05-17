<?php
include('session.php');
?>
<!DOCTYPE HTML>
<html>
<head>
<script src="http://canvasjs.com/assets/script/canvasjs.min.js"></script>
<link rel="stylesheet" href="css/style.css"/>
<link rel="stylesheet" href="css/jquery-ui.css" />
<script src="js/jquery.min.js"></script>
<script src="js/jquery-1.11.3.min.js"></script>
<script src="http://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>

<script type="text/javascript">
	function getData(){
		var startdate=document.myForm.startdate.value;
		var enddate=document.myForm.enddate.value;
		//console.log(startdate);
		if (startdate==null || startdate==""){
			alert("Start Date can't be blank");
			return false;
		}else if(enddate==null || enddate==""){
			alert("End date can't be blank");
			return false;
		}
		var date1 = new Date(startdate);
		var date2 = new Date(enddate);
		var timeDiff = (date2.getTime() - date1.getTime());
		var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
		if (diffDays >=31){
			alert("You are exceeding date Range..Please correct");
			return false;
		} else if (diffDays<0 ) {
			alert("You are entering Wrong dates");
			return false;
		}

		$('.loader').css('visibility', 'visible');
		$('.chartContainer').css('visibility', 'collapse');
		var request = $.ajax({
					url: "/financereport/financereport/getGmvMnv?startdate="+startdate + "&enddate="+enddate,
					type: "GET",
					dataType: "html"
				});

				request.done(function(msg) {
					$("#mybox").html(msg);
					$('.loader').css('visibility', 'collapse');
					$('.chartContainer').css('visibility', 'visible');
					var result = JSON.parse(msg);
					console.log(result);
					var chart = new CanvasJS.Chart("chartContainer", {
						title:{
							text: "GMV and NMV"
						},
						data: [
						{
							// Change type to "doughnut", "line", "splineArea", etc.
							type: "column",
							dataPoints: [
								{ label: "GMV",  y: result.gmv  },
								{ label: "NMV", y: result.nmv  },
								{ label: "NMV-COD", y: result.nmvcod },
								{ label: "NMV-Others",  y: result.nmvothers  }
							]
						}
						]
					});
					chart.render();
				});

				request.fail(function(jqXHR, textStatus) {
					$('.loader').css('visibility', 'collapse');
					alert( "Request failed: " + textStatus );
				});
	}

	$(function() {
	    $( "#datepicker1" ).datepicker({
	      dateFormat: 'yy-mm-dd',
	      changeMonth: true,
	      changeYear: true
	    });
	    $( "#datepicker2" ).datepicker({
	      dateFormat: 'yy-mm-dd',
	      changeMonth: true,
	      changeYear: true
	    });
	 });
</script>
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
				<h2>Report</h2>
					<form action='' name="myForm">
						<table  width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl_rptjen">
							<tr>
								<td style="text-align:left;">
									<b>Start Date :</b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="startdate" id="datepicker1">
								</td>

								<td style="text-align:left;">
									<b>End Date : </b><input type="text"  tabindex="15" maxlength="4" size="9" value="" class="field text datpik" name="enddate" id="datepicker2">
								</td>
							</tr>
							<tr>
								<td colspan="2" align="center">
									<button id="filter" class="btn btn-submit" onclick="getData();" type='button' style='margin-right:15px;' > Show Data </button>
								</td>
							</tr>
						</table>
					</form>
				</div>
			</div>
		</div>
	</div>
<!-- 	<div id="mybox">
 -->
 <div id= "loader" style="height: 300px; width: 40%; margin-top: 100px;margin-left: 49%; visibility: collapse;" class="loader"> </div>
 <div id="chartContainer" class="chartContainer" style="height: 300px; width: 40%; margin-top: 100px;margin-left: 30%;"></div>
</body>
</html>
