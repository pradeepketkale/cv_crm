<?php 
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$productId = '1939153';        

                $memcache_host = 'memcache5.beuas5.cfg.apse1.cache.amazonaws.com';
$i=0;
while($i < 1000)
{
echo $productId += $i;
$id=strtoupper('mage_fullproductcache_'.$productId.'_currency_INR_1');
$idm=strtoupper('mage_fullproductcache_'.$productId.'_currency_INR_0');
 $id1=strtoupper('mage_fullproductcache_'.$productId.'_currency_USD');
           $memcache_obj = memcache_connect($memcache_host, 11211);
        if(!$memcache_obj)

           {
                   echo 'Cannot connect to server: ';
           }
           else
           {
                 memcache_delete($memcache_obj, $id);
                 memcache_delete($memcache_obj, $idm);
                 $memcache_obj->close();
           }
$i++;
}      

