<?php
class Craftsvilla_Productmanagement_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

    public function getsearchDetailAction()
    {	
    	
		$entityarray=array();
		//$entityIds=array();	
		$selectedSearch=$_REQUEST['selectedSearch'];
		$str=trim($_REQUEST['searchtext'],",");
		$entityIds=explode("," ,$str);			
		$numbrow=count($entityIds);
		$entityidArray = array_map(create_function('$value', 'return (int)$value;'),$entityIds);
		$data=array('entityIds'=>$entityidArray);
		$jsondata=json_encode($data);
		

		
		$bodyhtml .="<style>
		`		ul{
					background: none repeat scroll 0 0 #F2F5FB;
				 }
		
				.product_image img{
				  display: block;
				  width: 166px;
				  height: 166px;
				  padding: 3px 3px 2px 3px;

				  }
				.shopbrief{
				height: 17px;
				color:#8c7049;
				overflow: hidden;
			       font-weight: bold;
					}
				.vendorname  {
				  color: #878787;
				  font-size: 13px;
				  font-weight: bold;
				}
				.price-box{
				display: inline-flex;
				}
				.price {
				  color: #8C714A;
				  font-weight: bold;
				padding-right:30px;
				   font-size: 14px;
				}
				.chk{
					margin-top: 40%;
					margin-left: 2%;

				}
	    			</style>";

	    	
	    if($selectedSearch == 'productId'){

			$bodyhtml .='<div class="grid_box" style="display: inline-block;width:100%">';
				$model= Mage::getStoreConfig('craftsvilla_config/service_api');
				$urlProduct = $model['host'].':'.$model['port'].'/productLite';
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $urlProduct);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$jsondata);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$response  = curl_exec($ch);
				//echo '<pre>';print_r($response);exit;
				$status_code=curl_getinfo($ch, CURLINFO_HTTP_CODE);
				curl_close($ch);
				if($status_code == 200){
					$result = json_decode($response,true);
					$res=$result['data'];
					
				}else{
			//$result = 0;
					return false;
				}	


			foreach($res as $productDetails)
			{		//print_r($productDetails);exit;
					$pid=$productDetails['entity_id'];
					$productImage=$productDetails['image'];
					$productName=$productDetails['name'];
					$vendorName=$productDetails['vendor_name'];
					$vendorUrl=Mage::getBaseUrl().$productDetails['vendor_url'];
					$productPrice=$productDetails['price'];
					$productDisPrice=$productDetails['discounted_price'];
					$url=Mage::getBaseUrl()."catalog/product/view/id/".$pid;
					if(!empty($productDetails)){		
					$bodyhtml .=	'<div style="display: inline-block;width:20%">
					<center>
					<ul>	  
					<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
					<div style="display: flex;">
					<input type="checkbox" name="qualitycheck[]"  value="'.$pid.'" id="'.$pid.'"  class="chk" >
					<div class="product_image">
					<img src="http://img1.craftsvilla.com/thumb/166x166'.$productImage.'" alt="" title=""></div></div>
							<p class="shopbrief"><a href="'.$url.'" target="_blank">'.$productName.'</a></p>
							<p class="vendorname">By:<a href="'.$vendorUrl.'" target="_blank"> '.$vendorName.'</a></p>
							<div class="price-box">
								      
					        <p class="special-price">
							<span class="price-label"></span>
							<span class="price" id="product-price-2484240">'.$productDisPrice.'</span>
						  </p>
					                <p class="old-price">
								<span class="price-label"></span>
								<span class="price" id="old-price-2484240"> '.$productPrice.'</span>
							 </p>
					</div>
					</li>
					</ul>	
					</center>
					</div>';
				}	
			}
				

					$bodyhtml .='</div>';

					echo $bodyhtml;
				}elseif($selectedSearch=='vendorName'){
					$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
			 		$vendorQuery="SELECT `vendor_id`,`vendor_name`,`url_key` FROM `udropship_vendor` WHERE `vendor_name` Like '%".$str."%' ";
					$vendorQueryRes = $readcon->query($vendorQuery)->fetch();
					//print_r($vendorQueryRes);
					if(empty($vendorQueryRes)){
				
						echo "No such vendors Found";exit;
		
					}else{
						$vendorName=$vendorQueryRes['vendor_name'];
						$vendor_id=$vendorQueryRes['vendor_id'];
						$shopUrl=Mage::getBaseUrl() .$vendorQueryRes['url_key'];			
						//echo $shopUrl;exit;
				 		$sqlproductIdQuery="SELECT `entity_id` FROM `catalog_product_entity_int` WHERE `attribute_id` = 531 and `value` = '".$vendor_id."' ";

						$vendorRes = $readcon->query($sqlproductIdQuery)->fetchAll();
						//print_r($vendorRes);
						$bodyhtml .='<div class="grid_box" style="display: inline-block;width:100%">';
							for($i=0;$i<count($vendorRes);$i++){ 

								$bodyhtml .='<div style="display: inline-block;width:20%"><ul >';
								//echo '<pre>';print_r($vendorRes[$i]['entity_id']);		
								$model = Mage::helper('catalog/product'); 
								 $entyid=$vendorRes[$i]['entity_id'];
								 $_product = $model->loadnew($entyid);
								 $productName=$_product->getName();
								 $productPrice =$_product->getPrice();
								 $productDisPrice=$_product->getSpecialPrice(); 
								// $productImage=$_product->getThumbnailUrl();
								$productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();
					 			$url=Mage::getBaseUrl()."catalog/product/view/id/".$entyid;
								$bodyhtml .=	'
										<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
											<div style="display: flex;">
											<input type="checkbox" name="qualitycheck[]"  value="'.$entyid.'" id="'.$entyid.'"  class="chk" >
											<div class="product_image">
											<img src="'.$productImage.'" alt="" title=""></div></div>
													<p class="shopbrief"><a href="'.$url.'" target="_blank">'.$productName.'</a></p>
													<p class="vendorname">By:<a href="'.$shopUrl.'" target="_Blank"> '.$vendorName.'</a></p>
											<div class="price-box">
														      
											<p class="special-price">
												<span class="price-label"></span>
												<span class="price" id="product-price-2484240">'.$productDisPrice.'</span>
											 </p>
											<p class="old-price">
												<span class="price-label"></span>
												<span class="price" id="old-price-2484240"> '.$productPrice.'</span>
											 </p>

											</div>
										</li>';
		
								$bodyhtml .='</ul></div>';
		
							}




				
								$bodyhtml .='</div>';
								echo $bodyhtml;exit;
					}



				}elseif($selectedSearch=='productName'){
					$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
					$sqlpidquery ="SELECT `entity_id` FROM `catalog_product_entity_varchar` WHERE `value` Like '%".$str ."%'";
					$res = $readcon->query($sqlpidquery)->fetchAll();
					//echo '<pre>';print_r($res[0]['entity_id']);exit;
					$readcon->closeConnection();
					$bodyhtml .='<div class="grid_box" style="display: inline-block;width:100%">';
							for($i=0;$i<count($res);$i++){ 

								$bodyhtml .='<div style="display: inline-block;width:20%"><ul >';
								//echo '<pre>';print_r($vendorRes[$i]['entity_id']);		
								$model = Mage::helper('catalog/product'); 
								 $entyid=$res[$i]['entity_id'];
								 $_product = $model->loadnew($entyid);
								 $productName=$_product->getName();
								 $productPrice =$_product->getPrice();
								 $productDisPrice=$_product->getSpecialPrice();
								 $vendorId=$_product->getUdropshipVendor();
								 $vendorinfo= Mage::helper('udropship')->getVendor($vendorId);
								 $vendorName=$vendorinfo->getVendorName();
							     $shopUrl = Mage::getBaseUrl().$vendorinfo->getUrlKey();
								$productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();
					 			$url=Mage::getBaseUrl()."catalog/product/view/id/".$entyid;
								$bodyhtml .=	'
										<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
											<div style="display: flex;">
											<input type="checkbox" name="qualitycheck[]"  value="'.$entyid.'" id="'.$entyid.'"  class="chk" >
											<div class="product_image">
											<img src="'.$productImage.'" alt="" title=""></div></div>
													<p class="shopbrief"><a href="'.$url.'" target="_blank">'.$productName.'</a></p>
													<p class="vendorname">By:<a href="'.$shopUrl.'" target="_Blank"> '.$vendorName.'</a></p>
											<div class="price-box">
														      
											<p class="special-price">
												<span class="price-label"></span>
												<span class="price" id="product-price-2484240">'.$productDisPrice.'</span>
											 </p>
											<p class="old-price">
												<span class="price-label"></span>
												<span class="price" id="old-price-2484240"> '.$productPrice.'</span>
											 </p>

											</div>
										</li>';
		
								$bodyhtml .='</ul></div>';
		
							}




				
								$bodyhtml .='</div>';
								echo $bodyhtml;exit;



				}else{

					echo "No Records found";exit;

				}
	}
      
     public function disableSelectedAction()
	{
		//echo "Hello";

			$productId=trim($_REQUEST['pid'],","); //exit;
			$productId=str_getcsv($productId,',','""');
			$hlp = Mage::helper('generalcheck');
	   		$hlp->productUpdateNotify_retry($productId);
	   		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
	   		$sql= "UPDATE `catalog_product_entity_int` SET  `value` = '2' WHERE `entity_id` ='".$productId."'";
	   		$executeQuery=$write->query($sql);//exit;
	   		if($executeQuery){
	   			echo "Product Disabled Successfully";

	   		}

	}	

}
