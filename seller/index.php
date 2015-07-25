<?php
$utmSource   = $_GET['utm_source'];
$utmCampaign = $_GET['utm_campaign'];
$utmMedium   = $_GET['utm_medium'];


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Craftsvilla - Seller</title>
<link href="css/css.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="fixwrap">
  <div class="content"> <a class="logo" href="#"></a>
    <div class="floatl leftsec">
      <h2 class="heading paddtop">Why Open a Shop on Craftsvilla:</h2>
      <iframe src="http://www.slideshare.net/slideshow/embed_code/9619160" width="370" height="309" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe>
    </div>
    <div class="formFields">
     <form action="landing.php" method="post"  name="fm" onsubmit="return valid()">
      <h2 class="heading headingblue">Open Your Shop. Bring India Alive:</h2>
      <ul>
        <li>
          <label>Name<span>*</span></label>
          <p>
            <input type="text" name="name" />
          </p>
        </li>
        <li id="right">
          <label>Email<span>*</span></label>
          <p>
            <input type="text" name="email" />
          </p>
        </li>
        <li>
          <label>Number<span>*</span></label>
          <p>
            <input type="text" name="number" />
          </p>
        </li>
        <li id="right">
          <label>City</label>
          <p>
            <input type="text" name="city" />
          </p>
        </li>
        <li id="right">
          
          <p>
            <input type="hidden" name="utm_source" value="<?php echo $utmSource; ?>" />
          </p>
        </li>
        <li id="right">
          
          <p>
            <input type="hidden" name="utm_medium" value="<?php echo $utmMedium; ?>"/>
          </p>
        </li>
        <li id="right">
          
          <p>
            <input type="hidden" name="utm_campaign" value="<?php echo $utmCampaign; ?>"/>
          </p>
        </li>
        
	<li class="clrleft"><!--<label><input name="checkbox" type="checkbox" />I agree terms and conditions</label>--></li>
    
        <li id="right">
          <input name="submit" type="submit" id="submit" value="" />
        </li>
      </ul>
      </form>
      
<script language="javascript" type="text/javascript">

function valid()
{
	
var name=document.fm.name.value;
	if(name=="")
	{
		alert("Please Enter Name");
		document.fm.name.focus();
		return(false);
	}
	if(name.length<4)
	{
		alert("Please Enter 4 Char ");
		document.fm.name.focus();
		return(false);
	}
	if(name.length>30)
	{
		alert("Please Enter 30 Char ");
		document.fm.name.focus();
		return(false);
	}
var email=document.fm.email.value;
var ck_email = /^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/;
if (document.fm.email.value.search(ck_email)==-1)
{
	alert("That Email Address Is Not Valid.");
	return false;
}

var number = document.fm.number.value;
if(isNaN(number)||number.indexOf(" ")!=-1)
           {
              alert("Enter Numeric Value")
              return false; 
           }
           if (number.length>10)
           {
                alert("Enter 10 Digit Number");
				document.fm.number.focus();
                return false;
           }
           //if (number.charAt(0)!="9")
           //{
            //alert("it should start with 9 ");
           //return false
           //}


	
	var city=document.fm.city.value;
	if(city=="")
	{
		alert("Please Enter City Name");
		document.fm.city.focus();
		return(false);
	}
var checkbox=document.fm.checkbox.value;
if (!document.fm.checkbox.checked) 
	{
	// box is not checked
	alert("Please Check This Box To Accept Our Agreement and Conditions");
	return false;
	}
return true ;
}


</script>

      
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
      <li><img src="images/midday.jpg" alt="Mid Day" title="Mid Day">Shop for echo-friendly
        itesm online</li>
      <li><img src="images/the_economic_times.jpg" alt="The Economic Times" title="The Economic Times">Shop for echo-friendly
        itesm online</li>
      <li class="mintli"><img src="images/livemint.jpg" alt="Live mint.com" title="Live mint.com">Shop for echo-friendly
        itesm online</li>
      <li><img src="images/etnow.jpg" alt="ET NOW" title="ET NOW">Shop for echo-friendly
        itesm online</li>
    </ul>
  </div>
</div>
<p class="copyright fixwrap">&copy; craftsvilla.com. All rights reserved.</p>
</body>
</html>
