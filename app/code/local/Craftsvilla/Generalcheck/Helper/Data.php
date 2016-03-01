<?php

class Craftsvilla_Generalcheck_Helper_Data extends Mage_Core_Helper_Abstract
{

	public function getMaindbconnection()
	{   
               
		return Mage::getSingleton('core/resource')->getConnection('core_write');
	}	
	public function getStatsdbconnection()
	{
               
		return Mage::getSingleton('core/resource')->getConnection('statsdb_connection');
	}	         

	public function getproductStatsCraftsvillaTable()                                       //tables
	{
		$tablename = "product_stats_craftsvilla";
		return $tablename;
	}
	public function getsalesFlatShipmentTable()
	{
		$tablename = "sales_flat_shipment";
		return $tablename;
	}
	public function getcatalogProductCraftsvilla3Table()
	{
		$tablename = "catalog_product_craftsvilla3";
		return $tablename;
	}
	public function getshipmentpayoutTable()
	{
		$tablename = "shipmentpayout";
		return $tablename;
	}
	public function getsalesFlatOrderPaymentTable()
	{
		$tablename = "sales_flat_order_payment";
		return $tablename;
	}
	public function getudropshipVendorTable()
	{
		$tablename = "udropship_vendor";
		return $tablename;
	}
	public function getsalesFlatOrderTable()
	{
		$tablename = "sales_flat_order";
		return $tablename;
	}
	public function getcatalogProductEntityTable()
	{
		$tablename = "catalog_product_entity";
		return $tablename;
	}
	public function getcustomerEntityTable()
	{
		$tablename = "customer_entity";
		return $tablename;
	}
	public function getsalesFlatOrderItemTable()
	{
		$tablename = "sales_flat_order_item";
		return $tablename;
	}
	public function getsalesFlatOrderAddressTable()
	{
		$tablename = "sales_flat_order_address";
		return $tablename;
	}

	public function getvendorInfoCraftsvillaTable()
	{
		$tablename = "vendor_info_craftsvilla";
		return $tablename;
	}
	public function getCouponrequestTable()
	{
		$tablename = "couponrequest";
		return $tablename;
	}  

	public function ismobile() {
    $is_mobile = '0';

    if(preg_match('/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
        $is_mobile=1;
    }   

    if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
        $is_mobile=1;
    }

    $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
    $mobile_agents = array('w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');

    if(in_array($mobile_ua,$mobile_agents)) {
        $is_mobile=1;
    }

    if (isset($_SERVER['ALL_HTTP'])) {
        if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
            $is_mobile=1;
        }
    }

    if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
        $is_mobile=0;
    }

   return $is_mobile;
	//return 0;
}

	public function homeMobileView()
	{
	
	$statsconn = $this->getStatsdbconnection();
	$_columnCount = 2;
	$i=0; 

$bodyhtml = "<div style='margin-left:auto;margin-right: auto;'>";

/**
$queryProductHome = mysql_query("SELECT `entity_id` FROM `catalog_product_craftsvilla3` WHERE `visibility` IN (1,4) AND (`category_id1` ='991' OR `category_id2` = '991' OR `category_id3` = '991' OR `category_id4` = '991')",$statsconn);
**/
$resultProduct = array();
/**
	while($resultProductHome = mysql_fetch_array($queryProductHome))
	{

		array_push($resultProduct,$resultProductHome['entity_id']);

	}
**/
$queryProductTrend = mysql_query("SELECT `product_id` FROM `craftsvilla_trending` ORDER BY id ASC",$statsconn);

mysql_close($statsconn);

	while($resultProductTrend = mysql_fetch_array($queryProductTrend))
	{

		array_push($resultProduct,$resultProductTrend['product_id']);

	}

foreach($resultProduct as $_resultProduct)
	{

	if ($i++%$_columnCount==0)
		{ 
			$bodyhtml .= "<ul class='products-grid first odd you_like' /**style ='float: right;**/'>";
		} 
			$product = Mage::helper('catalog/product')->loadnew($_resultProduct);

			$prodImage = Mage::helper('catalog/image')->init($product, 'image')->resize(500,500);
			$firstlast = "";
		if(($i-1)%$_columnCount==0)
	{
			$firstlast = "first";
	}

	 elseif($i%$_columnCount==0){
		$firstlast = "last";
	}
		$lazyLoder = Mage::getDesign()->getSkinUrl('images/lazy_image_loader/loader.gif');
		$bodyhtml .= "<li class='item ".$firstlast."' style='width:48%'>";
		$bodyhtml .= "<div >";
		$bodyhtml .= "<a class='' title='".$prodImage."' href='".$product->getProductUrl()."'><img  width='100%' height='auto' src = '".$prodImage."'  alt=''></a>";
		$bodyhtml .= "<p class='shopbrief' style='padding-top: 25px;padding-bottom:15px'><a title='' style='font-size:30px;line-height: 26px;' href='".$product->getProductUrl()."'>".substr($product->getName(),0,25)."..</a></p>";

				$storeurl = '';
				$vendorinfo= Mage::helper('udropship')->getVendor($product);
				$storeurl = Mage::helper('umicrosite')->getVendorUrl($vendorinfo->getData('vendor_id'));
				$storeurl = substr($storeurl, 0, -1);
				if($storeurl != '') {
		 
		$bodyhtml .= "<p class='vendorname' style='padding-bottom:30px;'><font size='+3'>by </font><a target='_blank' href='".$storeurl."'><font size='+3'>".substr($vendorinfo->getVendorName(),0,25)."</font></a></p>";
	 } 

	if(!$product->getSpecialPrice()):
		$bodyhtml .= "<div class='products price-box' style='padding-bottom:15px'> <span id='product-price-80805' class='regular-price'> <span class='price 123' style='font-size: 35px;padding-bottom:35px'> Rs. ".number_format($product->getPrice(),0)."</span> </span> </div>";
	else:
		$bodyhtml .= "<div class='products price-box'>";
		$bodyhtml .= "<p class='old-price' style='margin-right: 15%;float:left;padding-bottom:35px'> <span class='price-label'></span> <span id='old-price-77268' class='price' style='font-size: 35px;'>Rs. ".number_format($product->getPrice(),0)."</span> </p>";
		$bodyhtml .= "<p class='special-price' style='padding-bottom:15px'> <span class='price-label'></span> <span id='product-price-77268' class='price' style='font-size: 35px;'>Rs. ".number_format($product->getSpecialPrice(),0)."</span> </p>";
		$bodyhtml .= "</div>";
	endif;
		$bodyhtml .= "<div class='clear'></div>";
		$bodyhtml .= "</div>";
		$bodyhtml .= "</li>";
	
	 if ($i%$_columnCount==0){
		$bodyhtml .= "</ul>";
	 } 
} 

	$bodyhtml .= "</div>";
$cvmobilehtml = $bodyhtml;
return $cvmobilehtml;
	}


public function productUpdateCurlCall($productId){
	$model= Mage::getStoreConfig('craftsvilla_config/service_api');
	$url=$model['host'].':'.$model['port'].'/productUpdateNotification';
	//$count=sizeof($productId);
	//print_r($productId);
	//echo $count;exit;
	
		//$productId=str_getcsv($productId,',','""');
		//echo "arr".count($productId);exit;
		
		$data = array("productId"=>$productId,"apiVersionCode"=>"2"); 
		$data_string = json_encode($data);//print_r($data_string);exit;
		$handle = curl_init($url); 
		curl_setopt($handle, CURLOPT_POST, true);
		curl_setopt($handle, CURLOPT_POSTFIELDS, $data_string); 
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_string)));
		curl_exec($handle);
		//print_r($res);exit;
		$http_status_code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
		curl_close($handle);
		return $http_status_code;


			
	}
public function productUpdateNotify_retry($productId)
	{	
	$statusCode=$this->productUpdateCurlCall($productId);	
	if($statusCode != 200)
					{	
						$numRetry=3;
						while($numRetry>0)
						{	
								
									
									$statusCode=$this->productUpdateCurlCall($productId);
									if($statusCode == 200)
									{	
										break;
									
									}
								$numRetry--;
						}
								//echo	$numRetry;exit;
						if($numRetry==0)
							{
									$email_mod= Mage::getStoreConfig('craftsvilla_config/productupdate_q');
									$templateId='productupdate_notify';
									
									$toEmail = $email_mod['email_to'];
									$toEmailCC = $email_mod['email_bcc'];
									$emailFrom= $email_mod['email_from'];
									
									$vars = array();
									$sender = Array('name'  => 'Craftsvilla',
											'email' =>$emailFrom);
									$storeId = 1;

									$transactionalEmail = Mage::getModel('core/email_template')
									            ->setDesignConfig(array('area' => 'frontend', 'store' => $storeId));                 
									$transactionalEmail
									        ->getMail();
									$transactionalEmail->setTemplateSubject('Product Notifiaction');
									$transactionalEmail->sendTransactional($templateId, $sender, $toEmail,  $vars);
									$transactionalEmail->sendTransactional($templateId, $sender, $toEmailCC, $vars);
									
							}
									
					}else
					{	
						return true;
						//echo "Yess";exit;
						 
					}
			
			
	}

	
                            
}
	 
