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
                        <h1>Finance Dashboard</h1>
                       	 <!-- <div class="clear" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>||
                       		 <a href="logout.php" ><b>Logout</b> </a>
                       	 </div> -->
                       	 <div class="FRnavigation" style="align="right";"><a href="dashboard.php" ><b>Dashboard</b></a>
                       		 <a href="logout.php" ><b>Logout</b> </a>
                       	 </div>
                     </div>

                    </div>
			</div>
            </div>

       </div>
	</div>
</div>

 <div class="grid grid-pad">
			<div class="col-1-1">
				<div class="container-wrapper1">
					<div class="col-1-3" >
						<a href="ReportCOD.php">
							<div class="container_inner1">
								<h3>Report For COD</h3>
							</div>
						</a>
					<div class="clear"></div>
				</div>

					<div class="col-1-3" >
						<a href="RecordForPayment.php">
						<div class="container_inner1 bg">
						<h3>Record For Payment</h3>

						</div>
						</a>

						<div class="clear"></div>
					</div>

					<div class="col-1-3" >
						<a href="PaymentInvoice.php">
						<div class="container_inner1 bg1">
						<h3>Payment Invoice</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="ReportforDamage_LostTransit.php">
						<div class="container_inner1 bg3">
						<h3>Report For Damage/Lost in Transit</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="VendorCommissionGet.php">
						<div class="container_inner1 bg2">
						<h3>Update Vendor Commission</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="ReportPrepaid.php">
						<div class="container_inner1 bg4 ">
						<h3>Report for Prepaid</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="StatewiseReport.php">
						<div class="container_inner1 bg6 ">
						<h3>State wise Sales Report</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="HandlingRdr.php">
						<div class="container_inner1 bg5 ">
						<h3>Handing charges invoice - RDR</h3>

						</div>
						</a>


						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="HandlingOrdr.php">
						<div class="container_inner1 bg3 ">
						<h3>Handing charges invoice - ORDR</h3>

						</div>
						</a>

						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="Gmv-Nmv.php">
						<div class="container_inner1 bg ">
						<h3>GMV-NMV Report</h3>

						</div>
						</a>

						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="AwbToCsv.php">
						<div class="container_inner1 bg2 ">
						<h3>AWB vs Shipment ID</h3>

						</div>
						</a>

						<div class="clear"></div>
					</div>

					<div class="col-1-3 padding_top" >
						<a href="shipmentsToDelivered.php">
						<div class="container_inner1 bg7 ">
						<h3>Shipment's to Delivered Status</h3>

						</div>
						</a>

						<div class="clear"></div>
					</div>

				</div>
				<div class="clear"></div>
			</div>
	  </div>






</body>
</html>
