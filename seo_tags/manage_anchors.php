<?php
// error_reporting(E_ALL & ~E_NOTICE);
// require_once '../app/Mage.php';
// Mage::app();
?>
<div class="container">
	<div class="formMain">
		<div class="label">
			Select Page Type 
		</div>
		<div class="detail">
			<select id="pageType" onchange="getAnchor(this.value)">
				<option>Please select</option>
				<option>Category</option>
				<!--option>FeedPage</option-->
				<!--option>VendorPage</option-->
				<option>ProductPage</option>
				<option>HomePage</option>
				<!--option>Search page</option-->
				<!--option>Common</option-->
				<!--option>Others</option-->
			</select>
		</div>
		<div class="label">
			Enable Anchor:
		</div>
		<div class="detail">
			<input type="checkbox" id="activate_anchor" name="activate_anchor" value="">
		</div>
		<div class="label">
			&nbsp;
		</div>
		<div class="detail">
			<input type="button" value="Save" id="saveBtn" onclick="activateAnchor();">
		</div>
		<div class="label">
			&nbsp;
		</div>
		<div class="detail">
			<div id="response"></div>
		</div>
	</div>	
</div>



<script src="http://lstatic1.craftsvilla.com/skin/frontend/default/craftsvilla2015/js/jquery.min.js"></script>
<script type="text/javascript">
	
	function activateAnchor() {
		var xhttp;
		if(document.getElementById('activate_anchor').checked) {
			var activate_anchor = 1;
		} else {
			var activate_anchor = 0;
		}
		var pageSelect = document.getElementById("pageType");
		var selectedPage = pageSelect.options[pageSelect.selectedIndex].text;
		var pageDetail= document.getElementById("pageType").value;
		if (pageDetail == 'Please select') { 
			alert('Page Detail Not valid');
			return;
		} 
		xhttp = new XMLHttpRequest();
		xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
			var responseData = JSON.parse(xhttp.responseText);
			document.getElementById("response").innerHTML =responseData.m;
		}
		};
		xhttp.open("GET", "activateAnchor.php?pageDetail="+pageDetail+"&activate_anchor="+activate_anchor, true);
		xhttp.send();  
	}
	
	function getAnchor(str) {
	  var xhttp;
	  if (str == 'Please select') { 
		return;
	  }
	  var anchorType= str; 
	 
	  xhttp = new XMLHttpRequest();
	  xhttp.onreadystatechange = function() {
		if (xhttp.readyState == 4 && xhttp.status == 200) {
		  var responseData = JSON.parse(xhttp.responseText);
		  if (responseData.d=='Y') {
		  	 	document.getElementById("activate_anchor").checked =true;	
		  } else {
		  		document.getElementById("activate_anchor").checked =false;	
		  }
		  
		}
	  };
	  xhttp.open("GET", "getAnchorStatus.php?q="+str, true);
	  xhttp.send();   
	}
</script>

<style>
	
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
