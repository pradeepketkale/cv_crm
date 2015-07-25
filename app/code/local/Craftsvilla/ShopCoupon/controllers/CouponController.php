<?php


class Craftsvilla_ShopCoupon_CouponController extends Mage_Core_Controller_Front_Action{
    
	public function coupongetvaluesAction()
	{
		$vendorid=$this->getRequest()->getParam('vendor_id');
		$vendorcouponcache['cache'] = '';
		if($vendorid=='')
		{
			$CacheCouponId = "couponcache1-craftsvilla";
		}
		else
		{
			$CacheCouponId = "couponcache1-craftsvilla-".$vendorid;
		}
		
		$lifetime = 86400;
		if ($cacheContentCoupon = Mage::app()->loadCache($CacheCouponId)){ 
    		$vendorcouponcache['cache']=$cacheContentCoupon;
		}
		else
		{ 
		    $couponread = Mage::getSingleton('core/resource')->getConnection('core_read');
			if($vendorid=='')
			{
			$coupon="SELECT sc.`code` , sr.`name` , sr.`description` , uv.`vendor_name`, sc.`expiration_date`, uv.`url_key`, uv.`vendor_id` as vendor_id, sr.`is_active` FROM  `salesrule_coupon` AS sc,  `salesrule` AS sr,  `udropship_vendor` AS uv
			WHERE sr.`rule_id` = sc.`rule_id` AND uv.`vendor_id` = sr.`vendorid` AND sr.`is_active`=1 AND sc.`expiration_date`>NOW() order by sc.`rule_id` desc";
			}
			else
			{
			$coupon="SELECT sc.`code` , sr.`name` , sr.`description` , uv.`vendor_name`, sc.`expiration_date`, uv.`url_key`, uv.`vendor_id` as vendor_id, sr.`is_active` FROM  `salesrule_coupon` AS sc,  `salesrule` AS sr,  `udropship_vendor` AS uv
			WHERE sr.`rule_id` = sc.`rule_id` AND uv.`vendor_id` = sr.`vendorid` AND sr.`is_active`=1 AND sc.`expiration_date`>NOW() AND uv.`vendor_id`='".$vendorid."' order by sc.`rule_id` desc";
			}
			$result=$couponread->query($coupon)->fetchAll();
			$vendorcoupon['vendor_name']=array();
			$vendorcoupon['code']=array();
			$vendorcoupon['description']=array();
			$vendorcoupon['url_key']=array();
			$vendorcoupon['small_image']=array();
			$vendorcoupon['expiration_date']=array();
			//while($row = mysql_fetch_array($result))
			foreach($result as $row)
			{
				
				$vendorcoupon['vendor_name'][] = substr($row['vendor_name'],0,30);
				$vendorcoupon['code'][] =$row['code'];
				$vendorcoupon['description'][] =$row['description'];
				$vendorcoupon['expiration_date'][] =Mage::helper('core')->formatDate($row['expiration_date'], 'medium', false);
				$vendorcoupon['url_key'][] =Mage::getBaseUrl().$row['url_key'];
				$productquery = "select `entity_id` from `catalog_product_flat_1` where `udropship_vendor`= ".$row['vendor_id']." ORDER BY  `catalog_product_flat_1`.`entity_id` DESC limit 1 ";
				$productresult=$couponread->query($productquery)->fetchAll();
				$vendorcoupon['small_image'][]= Mage::getModel('catalog/product')->load($productresult[0]['entity_id'])->getImageUrl();
				
			}
			
	        $html_value = '';
			
			$html_value .= '<div id="coupon-search"><div class="shop-info v5"><h2>Coupon Description</h2></div><div class="shop-info v6"><h2>Coupon Code</h2></div><div class="shop-info v9"><h2>Expiry Date</h2></div><div class="shop-info v7"><h2>Vendor Name</h2></div><div class="shop-info v7"><h2>Products</h2></div></div><br>';
		    
		    if(sizeof($vendorcoupon['vendor_name'])>0)
		    {  
				for ($i = 0; $i < sizeof($vendorcoupon['vendor_name']); $i++) 
				{
					
					$html_value .= '<div id="coupon-search"><div class="shop"><div class="shop-info v2"><div class="coupon-details">'.$vendorcoupon['description'][$i].'</div></div>';
					$html_value .= '<div class="shop-info v3"><div class="couponcode-details">'.$vendorcoupon['code'][$i].'</div></div><div class="shop-info v4"><div class="expiry-details">'.$vendorcoupon['expiration_date'][$i].'</div></div><div class="shop-info v10"><div class="shop-ownername"><a href="'.$vendorcoupon['url_key'][$i].'" target="_blank">'.$vendorcoupon['vendor_name'][$i].'</a></div></div></div>';
													
							
					$html_value .= '<div class="shop-info v1"> <a href="'.$vendorcoupon['url_key'][$i].'" target="_blank"><img height="75" alt="" src="'.$vendorcoupon['small_image'][$i].'"></a> </div></div>';
					
				}
				
			}
			else
			{
					$html_value .='<br><br><p><center><strong>Sorry there are no coupons available from this vendor. Please check the coupons from other vendor<a href="http://www.craftsvilla.com/shopcoupon"> here</a></strong></center></p><br><br>';
			}
		   
		
			$cacheContentCoupon = $html_value;
			$tags = array(
							//Mage_Catalog_Model_Product::CACHE_TAG,
							//Mage_Catalog_Model_Product::CACHE_TAG . '_' . $productIdCache
						);
			Mage::app()->saveCache($cacheContentCoupon, $CacheCouponId, $tags, $lifetime);
			$vendorcouponcache['cache']=$cacheContentCoupon;
		}
		
		$encode_json_str = json_encode($vendorcouponcache);
		
		echo $encode_json_str;
		exit();

	}
}
