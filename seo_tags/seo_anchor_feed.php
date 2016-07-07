<?php

$valid_passwords = array ("seo" => "s3o");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
  header('WWW-Authenticate: Basic realm="craftsvilla"');
  header('HTTP/1.0 401 Unauthorized');
  die ("Not authorized");
}

?>
<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once '../app/Mage.php';
Mage::app();
?>

<hr>
<div class="label">
Select Page Type
</div>
<div class="detail">
	<select id="pageType">
		<option>Please select</option>
		<option>Category</option>
		<!--option>FeedPage</option-->
		<!--option>VendorPage</option-->
		<option>HomePage</option>
		<!--option>Search page</option-->
		<option>ProductPage</option>
		<!--option>Common</option-->
		<!--option>Others</option-->
	</select>
</div>
<div class="label">
<span class="">URL</span>
</div>	
<div class="detail">
<input type="text" id="pageDetail" placeholder="Insert the URL" onblur="getAnchor(this.value)">
</div>

<p>Suggestions: <span id="txtHint"></span></p>
<div class="subcategorydescbot col-xs-12 col-sm-12 well">
	<ul class="SubCategoryListView">
	<div id="populateAnchor">
		<li>
			<a href="/womens-clothing/sarees/banarasi-sarees/" title="Banarasi Sarees">
				Banarasi Sarees			</a>
		</li>
			<li>
			<a href="/womens-clothing/sarees/bandhani-sarees/" title="Bandhani Sarees">
				Bandhani Sarees			</a>
		</li>
			<li>
			<a href="/womens-clothing/sarees/cotton-sarees/" title="Cotton Sarees">
				Cotton Sarees			</a>
		</li>
	

	</div>
		<li> <div id="AddNew"><div class="icon icon-plus">
				<span class="name"></span>
			</div>
		</li>
	</ul>
		

		<div style="clear:both;"></div>
		<div id="AddNewContet" style="display:none;">
			<div class="label">Anchor text</div>	
			<div class="detail">
				<input type="text" id="anchorTitle" placeholder="Insert the Anchor text">
			</div>

			<div class="label">Anchor Link</div>	
			<div class="detail">
				<input type="text" id="anchorLink" placeholder="Insert the Anchor Link">
			</div>	

			<div class="label">Sequence</div>	
			<div class="detail">
				<input type="text" id="anchorSequence" placeholder="Insert the Anchor Sequence">
			</div>	

			<div class="label">&nbsp;</div>	
			<div class="detail">
				<input type="button" value="Add" id="saveBtn" onclick="saveAnchor();">
			</div>	
		</div>
	
			
</div>

<script src="http://lstatic1.craftsvilla.com/skin/frontend/default/craftsvilla2015/js/jquery.min.js"></script>
<script type="text/javascript">
	$( "#AddNew" ).click(function() {
	  $( "#AddNewContet" ).toggle( "slow", function() {
	   
	  });
	});

	function saveAnchor() {
		var xhttp;
		var anchorTitle= document.getElementById("anchorTitle").value;
		var rawAnchorLink= document.getElementById("anchorLink").value;
		var anchorLink = encodeURIComponent(rawAnchorLink); 
		var anchorType= document.getElementById("pageType").value;
		var pageDetail= document.getElementById("pageDetail").value;
		var anchorSequence= document.getElementById("anchorSequence").value;
		if (pageDetail.length == 0) { 
			alert('Page Detail Not valid');
			return;
		}
		xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			var responseData = JSON.parse(xhttp.responseText);
			document.getElementById("populateAnchor").innerHTML =responseData.d;
			document.getElementById("txtHint").innerHTML =responseData.m;
		}
		};

		xhttp.open("POST", "saveAnchor.php?pageDetail="+pageDetail+"&anchor_type="+anchorType+"&anchorLink="+anchorLink+"&anchorTitle="+anchorTitle+"&anchorSequence="+anchorSequence, true);
		xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhttp.send();  
	}
	function cancelConfirm(str) {
		if(confirm("Are you sure you want to remove the anchor ?")) {
			removeAnchor(str);
			}
			return false;
	}
	function removeAnchor(str) {
		var xhttp;
		if (str.length == 0) { 
			document.getElementById("txtHint").innerHTML = "";
		return;
		}
		var anchorType= document.getElementById("pageType").value;
		var pageDetail= document.getElementById("pageDetail").value;
		xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			var responseData = JSON.parse(xhttp.responseText);
			document.getElementById("populateAnchor").innerHTML =responseData.d;
			document.getElementById("txtHint").innerHTML =responseData.m;
		}
		};
		xhttp.open("GET", "deleteAnchor.php?q="+str+"&pageDetail="+pageDetail+"&anchorType="+anchorType, true);
		xhttp.send();  
	}

	function getAnchor(str) {
	  var xhttp;
	  if (str.length == 0) { 
		document.getElementById("txtHint").innerHTML = "";
		return;
	  }
	  var anchorType= document.getElementById("pageType").value;
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
		  var responseData = JSON.parse(xhttp.responseText);
		  document.getElementById("populateAnchor").innerHTML =responseData.d;
		  document.getElementById("txtHint").innerHTML =responseData.m;
		}
	  };
	  xhttp.open("POST", "getAnchorData.php?q="+str+"&anchor_type="+anchorType, true);
	  xhttp.send();   
	}
</script>

<style>
	.icon {
	position: relative;
	width:16px;
	height:16px;
	margin: 16px;
	}
	.icon-plus {
	background-color: #000;
	border-radius:8px;
	-webkit-border-radius:8px;
	-moz-border-radius:8px;
	width: 16px;
	height: 16px;
	position: relative;
	top:0;
	left:0;
	}
	.icon-plus:after {
	background-color: #fff;
	width: 8px;
	height: 2px;
	border-radius: 1px;
	-webkit-border-radius: 1px;
	-moz-border-radius: 1px;
	position: absolute;
	top:7px;
	left: 4px;
	  content:"";
	}
	.icon-plus:before {
	background-color: #fff;
	width: 2px;
	height: 8px;
	border-radius: 1px;
	-webkit-border-radius: 1px;
	-moz-border-radius: 1px;
	position: absolute;
	top:4px;
	left: 7px;
	  content:"";
	}
	.label {
		width: 174px;
		float: left;
	}
	.detail {
		margin-left: 10px;
		margin-bottom: 10px;
	}
	.well {
		  min-height: 20px;
			margin-bottom: 20px;
			background-color: #f5f5f5;
			border: 1px solid #e3e3e3;
			border-radius: 4px;
			-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
			box-shadow: inset 0 1px 1px rgba(0,0,0,.05);
			background: #fff;
			float: left;
			width: 96%;
			padding-left: 2%;
			padding-right: 2%;
	}
	ul {
		list-style: none;
	}
	.SubCategoryListView li {
		float: left;
		width: 25%;
		padding: 4px;
	}
	a:link, a:visited {
		color: #666;
		text-decoration: none;
	}
	body {
		font-family: Lato!important;
		font-size: 14px;
		line-height: 19px;
		background-color: #f8f8f8;
		padding: 0;
	}
</style>
