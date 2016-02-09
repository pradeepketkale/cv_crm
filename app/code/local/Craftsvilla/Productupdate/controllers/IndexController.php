<?php
class Craftsvilla_Productupdate_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }
    

    public function getsearchDataAction()
    {
		$entityarray=array();
		//$entityIds=array();		
		$searchtxt=$_REQUEST['searchtext'];//print($searchtxt);exit;
		$selectedSearch=$_REQUEST['selectedSearch'];
			$bodyhtml .=" 
				<script type='text/javascript'>
				function selectAll(name, value) 
				{
				
                	var forminputs  = document.getElementsByTagName('input'); 
                  
                    for (i = 0; i < forminputs.length; i++) 
                    {
                    	var regex = new RegExp(name, 'i');
                    	if (regex.test(forminputs[i].getAttribute('name'))) {
	                        if (value == '1') {
	                            forminputs[i].checked = true;
	                        } else {
	                            forminputs[i].checked = false;
	                        }
                    	}
                	}
           		}
				     </script>";
		$bodyhtml .="<style>
				ul{
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
			
		
		
		if(!empty($searchtxt)){

		
		///from vendor name
		if($selectedSearch=='vendorName'){
			$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
			 $vendorQuery="SELECT `vendor_id`,`vendor_name`,`url_key` FROM `udropship_vendor` WHERE `vendor_name` Like '%".$searchtxt."%' ";
		
			$vendorQueryRes = $readcon->query($vendorQuery)->fetch();
			
			
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
				
	
 
				
				
				
			$bodyhtml .='<div class="grid_box" style="display: inline-block;width:100%">
			';
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
 			
			$bodyhtml .=	'
					<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
						<div style="display: flex;">
						<input type="checkbox" name="qualitycheck[]"  value="'.$entyid.'" id="'.$entyid.'"  class="chk" >
						<div class="product_image">
						<img src="'.$productImage.'" alt="" title=""></div></div>
								<p class="shopbrief">'.$productName.'</p>
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




				
				$bodyhtml .='
						
					
					</div>';
									
						
				echo $bodyhtml;exit;
		}
			
					
		
		}elseif($selectedSearch=='productId'){

		$model = Mage::helper('catalog/product'); 
 		$_product = $model->loadnew($searchtxt);
		$productName=$_product->getName();
		$pid=$_product->getId();
		$productSku =$_product->getSku();
		if($productSku !=''){
		$productPrice =$_product->getPrice();
		$productDisPrice=$_product->getSpecialPrice(); 
		$productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();

		
		$bodyhtml .='<div class="grid_box" style="display: inline-block;width:100%">';
		
					
		$bodyhtml .=	'<div style="display: inline-block;width:20%">
		<center>
		<ul>	  
		<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
		<div style="display: flex;">
		<input type="checkbox" name="qualitycheck[]"  value="'.$pid.'" id="'.$pid.'"  class="chk" >
		<div class="product_image">
		<img src="'.$productImage.'" alt="" title=""></div></div>
				<p class="shopbrief">'.$productName.'</p>
				<p class="vendorname">By:<a href="#"> '.$vendorName.'</a></p>
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
		
			$bodyhtml .='</div>';

			echo $bodyhtml;
			}else{ echo "Please enter productId ";}
			}elseif($selectedSearch=='productName'){
					$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
					$sqlpidquery ="SELECT `entity_id` FROM `catalog_product_entity_varchar` WHERE `value` Like '%".$str ."%'";
					$res = $readcon->query($sqlpidquery)->fetchAll();
					$readcon->closeConnection();
					//echo '<pre>';print_r($res[0]['entity_id']);exit;
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
								// $productImage=$_product->getThumbnailUrl();
								$productImage="http://img1.craftsvilla.com/thumb/166x166".$_product->getImage();
					 			
								$bodyhtml .=	'
										<li style="list-style-type: none;border: 1px solid #DEDEDE;margin-right: 15px;margin-bottom: 15px;">
											<div style="display: flex;">
											<input type="checkbox" name="qualitycheck[]"  value="'.$entyid.'" id="'.$entyid.'"  class="chk" >
											<div class="product_image">
											<img src="'.$productImage.'" alt="" title=""></div></div>
													<p class="shopbrief">'.$productName.'</p>
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
				echo "Please select options";

				}
			}else
			{
				echo "No records Found";

			}																																																				
	
	


    }
	

    
     public function reIndexSelectedAction()
	{
			 
			 $productId=trim($_REQUEST['pid'],",");
			$productId=str_getcsv($productId,',','""');				
			//print_r($productId);exit;
			$hlp = Mage::helper('generalcheck');
	   		$hlp->productUpdateNotify_retry($productId);
			echo "success";
	}
}
