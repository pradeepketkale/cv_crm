<?php
/*$utmSource   = $this->getRequest()->getParam('utm_source');
$utmCampaign = $this->getRequest()->getParam('utm_campaign');
$utmMedium   = $this->getRequest()->getParam('utm_medium');*/

//Read This :
//Above three lines are equal to :- 
$utmSource   = $_REQUEST['utm_source'];
$utmCampaign = $_REQUEST['utm_campaign'];
$utmMedium   = $_REQUEST['utm_medium'];


$utm_crafts = $utmSource."-".$utmCampaign."-".$utmMedium;

if(isset($utmSource)|| isset($utmCampaign)|| isset($utmMedium))
{
	setcookie("utm_crafts", $utm_crafts, time()+7776000, "/");
}

// Now check accountcontroller.php file how this cookies are added into database into a new table with customers id - u will find that code from line number 340
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Craftsvilla - Landing Page</title>
<link href="css/css.css" rel="stylesheet" type="text/css" />
<script src="jquery.js" type="text/javascript"></script>
<script src="jquery.metadata.js" type="text/javascript"></script>
<script src="jquery.validate.js" type="text/javascript"></script>
<script type="text/javascript">

	
$.metadata.setType("attr", "validate");
$(document).ready(function() {
	$("#form1").validate();
});
</script>
<style type="text/css">
.block { display: block; }
form1.cmxform label.error { display: none; }	
</style>
</head>
<body>
<div class="fixwrap">
  <div class="content"> <a class="logo" href="#"></a>
    <div class="floatl leftsec">
     <h2 class="heading paddtop">Why Us:</h2>
     
     <ul class="whyus">
       <li><h1>Unique</h1><p>Handcrafted for you</p></li>
       <li class="truck"><h1>48 Hours</h1><p>Dispatch in India</p></li>
       <li class="arow"><h1>7 Day</h1><p>Return Policy</p></li>
       <li class="phone"><h1>Prompt</h1><p>Customer Serive</p></li>
     </ul>
	</div>
    <div class="formFields">
    <form name="addform" class="cmxform" id="form1" action="http://www.craftsvilla.com/customer/account/createpost/" method="post">
    <h2 class="heading headingblue">Sign Up &amp; Get Rs.250</h2>
    	<ul>
        	<li><label>First Name<span>*</span></label><p><input type="text" name="firstname" id="firstname" class="required"/></p></li>
            <li id="right"><label>Last Name<span>*</span></label><p><input type="text" name="lastname" id="lastname" class="required"/></p></li>
            <li><label>Email Address<span>*</span></label><p><input type="text" name="email" id="email_address" class="required email" /></p></li>
            <li id="right"><label>Mobile No<span>*</span></label><p><input type="text" name="telephone" id="telephone" class="required number"/></p></li>
            <li><label>Password<span>*</span></label><p><input type="password" name="password" id="password" class="required password"/></p></li>
            <li id="right"><label>Confirm Password<span>*</span></label><p><input type="password" name="confirmation" id="c_password" class="required c_password"/></p></li>
            <li>I agree terms and conditions</li>
            <li id="right"><input name="submit" type="submit" id="submit" value=""/></li>
        </ul>
    </form>   
    
    	
        
    </div>
    
  </div>
  <div class="footimgae">
     
       <p class="punchline"><span>The place to discover</span><br />
Indian products from Designers handpicked for you...</p>
   
   </div>
  
  
  
</div>
<div id="footer">
  <div class="fixwrap">
  
   
  
  
    <ul class="logoes">
      <li><img src="images/midday.jpg" alt="Mid Day" title="Mid Day">Shop for eco-friendly items online</li>
      <li><img src="images/the_economic_times.jpg" alt="The Economic Times" title="The Economic Times">Start-up cos finds hiring right talent at right price an uphill task</li>
      <li class="mintli"><img src="images/livemint.jpg" alt="Live mint.com" title="Live mint.com">Craftsvilla.com gets early-stage funding</li>
      <li><img src="images/etnow.jpg" alt="ET NOW" title="ET NOW">Starting Up: Craftsvilla - An online handicraft portal in India</li>
    </ul>
    </div>
  </div>
 <p class="copyright fixwrap">&copy; craftsvilla.com. All rights reserved.</p> 
</body>
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
    })();

    var _gaq = _gaq || [];

_gaq.push(['_setAccount', 'UA-20414065-1']);
_gaq.push(['_trackPageview']);


//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
  var _kmq = _kmq || [];
  function _kms(u){
    setTimeout(function(){
      var s = document.createElement('script'); var f = document.getElementsByTagName('script')[0]; s.type = 'text/javascript'; s.async = true;
      s.src = u; f.parentNode.insertBefore(s, f);
    }, 1);
  }
  _kms('//i.kissmetrics.com/i.js');_kms('//doug1izaerwt3.cloudfront.net/319a0179551de1a2e35f62f9f246b81c015f96ea.1.js');
</script>
<!--chart beat -->
<script type="text/javascript">
var _sf_async_config={uid:27315,domain:"craftsvilla.com"};
(function(){
  function loadChartbeat() {
    window._sf_endpt=(new Date()).getTime();
    var e = document.createElement('script');
    e.setAttribute('language', 'javascript');
    e.setAttribute('type', 'text/javascript');
    e.setAttribute('src',
       (("https:" == document.location.protocol) ? "https://a248.e.akamai.net/chartbeat.download.akamai.com/102508/" : "http://static.chartbeat.com/") +
       "js/chartbeat.js");
    document.body.appendChild(e);
  }
  var oldonload = window.onload;
  window.onload = (typeof window.onload != 'function') ?
     loadChartbeat : function() { oldonload(); loadChartbeat(); };
})();

</script>
<!-- end chart beat -->
</html>
