<?php

define('ADMIN_USERNAME','craftsjunglee');    // Admin Username
define('ADMIN_PASSWORD','craftsjge');   // Admin Password


////////// END OF DEFAULT CONFIG AREA /////////////////////////////////////////////////////////////

///////////////// Password protect ////////////////////////////////////////////////////////////////
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
           $_SERVER['PHP_AUTH_USER'] != ADMIN_USERNAME ||$_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD) {
                        Header("WWW-Authenticate: Basic realm=\"Junglesite Login\"");
                        Header("HTTP/1.0 401 Unauthorized");

                        echo <<<EOB
                                <html><body>
                                <h1>Rejected!</h1>
                                <big>Wrong Username or Password!</big>
                                </body></html>
EOB;
                        exit;
}

?>

<html>
<body>
<form name="myForm" action="jungle.php" method="post">
Category: 
<select name = "category_id">
  <option value="0">Anklets</option>
  <option value="1">Rings</option>
  <option value="2">Necklaces</option>
  <option value="3">Earrings</option>
  <option value="4">Bracelets</option>
  <option value="5">Sarees</option>
  <option value="6">Home Decor</option>
  <option value="7">Rakhi Gifts</option>
  <option value="8">Bags</option>
  <option value="9">Salwar Suits</option>
  <option value="10">Home Furnishing</option>
  <option value="11">Dress Material</option>
  <option value="12">Gifts</option>
  <option value="13">Spiritual</option>
  <option value="14">Flowers</option>
  <option value="15">Leggings</option>
  <option value="16">Blouse</option>
  <option value="17">Scarves</option>
  <option value="18">Stoles</option>
  <option value="19">Shawls</option>
  <option value="20">Skirts</option>
  <option value="21">Tops</option>
  <option value="22">Kurtas</option>
  <option value="23">Footwear</option>
  <option value="24">Belts</option>
  <option value="25">Hand Bags</option>
  <option value="26">Accessories</option>
</select><br>
Product Date(format YYYY-MM-DD): From : <input type="text" name="category_id_from" > To :<input type="text" name="category_id_to" >

<input align="right" type= "submit" value = "submit" >
</form></body></html>
