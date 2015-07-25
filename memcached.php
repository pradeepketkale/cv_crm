<?php
$memcache = new Memcache;
//echo "<pre>"; print_r($memcache); exit;
$memcache->connect('10.134.226.28', 11211) or die ("Could not connect"); //connect to memcached server
 
$mydata = "i want to cache this line"; //your cacheble data
 
$memcache->set('key', $mydata, false, 100); //add it to memcached server
 
$get_result = $memcache->get('key'); //retrieve your data
 
var_dump($get_result); //show it
?>
