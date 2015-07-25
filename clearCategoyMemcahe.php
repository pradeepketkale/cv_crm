<?php
error_reporting(E_ALL ^ E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
$memcache_host = 'memcache-1.beuas5.cfg.apse1.cache.amazonaws.com';
$id=strtoupper('mage_jewelleryjewelryhtml_catalogcategoryviewcategorypathjewelleryjewelryhtmlcategoryjewelleryjewelryINR');
$id1=strtoupper('mage_sareessarihtml_catalogcategoryviewcategorypathsareessarihtmlcategorysareessariINR');
$id2= strtoupper('mage__cmsindexindexcmshomehomepageINR');
$memcache_obj = memcache_connect($memcache_host, 11211);
      if(!$memcache_obj)
	   {
		   $session->addError($this->__('Cannot connect to server: '.$memcache_host));
	   }
	   else
	   {
		 memcache_delete($memcache_obj, $id);
		 memcache_delete($memcache_obj, $id1);
		memcache_delete($memcache_obj, $id2);
		 $memcache_obj->close();
	   }
?>
