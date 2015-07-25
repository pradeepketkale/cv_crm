<?php 
error_reporting(E_ALL & ~E_NOTICE);


$db = Mage::getSingleton('core/resource')->getConnection('core_read');
$vendorId = 1;

while($vendorId < 5600)
		{
		$_vendorId = $vendorId++;
		
		 $vendorDtl = "SELECT `url_key` FROM `udropship_vendor` WHERE `vendor_id` = '".$_vendorId."'";
		 //$vendorDtl = "SELECT `url_key` FROM `udropship_vendor` WHERE `vendor_id` = '1'";
		$result = $db->query($vendorDtl)->fetchAll();
		
		//$fetchQuery1 = mysql_fetch_array($result);
		//echo $fetchQuery['url_key'];exit;
				//while($fetchQuery1 = mysql_fetch_array($result))
				 foreach($result as $fetchQuery1)
				{
				//sleep(2);
				$url ='http://www.craftsvilla.com/'.$fetchQuery1['url_key'];
				echo 'Vendor Id:'.$_vendorId.' URL: '.$url.' ';
				$ch = curl_init();
						curl_setopt($ch, CURLOPT_VERBOSE, 0); 
						curl_setopt($ch,CURLOPT_URL, $url);
						//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
						//curl_setopt( $ch, CURLOPT_POST, 1);
						//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
						curl_setopt( $ch, CURLOPT_HEADER, 0);
						curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
						//curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						 $response = curl_exec($ch); 
						if(curl_errno($ch))
							{		
								print curl_error($ch);
							}
						
							
							curl_close($ch);
					//		$i = 1;
					//	while($i < 2){	
					//	$url1 =str_replace(' ','','http://www.craftsvilla.com/'.$fetchQuery1['url_key'].'?p='.$i++);
					//	$ch1 = curl_init();
					//	curl_setopt($ch1, CURLOPT_VERBOSE, 0); 
					//	curl_setopt($ch1,CURLOPT_URL, $url1);
					//	//curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
					//	//curl_setopt( $ch, CURLOPT_POST, 1);
					//	//curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
					//	curl_setopt( $ch1, CURLOPT_HEADER, 0);
					//	curl_setopt( $ch1, CURLOPT_RETURNTRANSFER, 1);
					//	//curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
					//	curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
					//	 $response1 = curl_exec($ch1);
					//	
					//	if(curl_errno($ch1))
					//		{		
					//			print curl_error($ch1);
					//		}
					//								
					//		curl_close($ch1);
							
		//			}
				
				
				
				}
		
		}
mysql_close();
?>
