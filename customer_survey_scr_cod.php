<?php
	error_reporting(E_ALL & ~E_NOTICE);
	require_once 'app/Mage.php';
	Mage::app();
		$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
		//$write = Mage::getSingleton('core/resource')->getConnection('core_write');
		
		$baseUrl =  Mage::getBaseUrl(); 
		
        $orderDetail = "SELECT sfs.entity_id, sfs.order_id, sfs.udropship_status, sfs.increment_id, sfs.updated_at, sfop.parent_id, sfop.method FROM  `sales_flat_shipment` AS sfs LEFT JOIN  `sales_flat_order_payment` AS sfop ON sfs.order_id = sfop.parent_id WHERE sfop.method ='cashondelivery' AND sfs.udropship_status =7 AND sfs.updated_at >= DATE_SUB( NOW( ) , INTERVAL 7 DAY ) "; 

		$ordersRes = $readcon->query($orderDetail)->fetchAll(); 
 //print_r($ordersRes);  
      // print_r ($ordersRes) ; exit;


foreach($ordersRes as $_ordersRes) {

      $customerShipmentItemHtml = '';

	   $eid = $_ordersRes['entity_id']; 

	$productDetails = Mage::getModel('sales/order_shipment')->load($eid);
	   $vendorid =$productDetails->getUdropshipVendor(); 
	   $incrementid=$productDetails->getIncrementId();

	$vdname =Mage::getModel('udropship/vendor')->load($vendorid);
	   $vname = $vdname['vendor_name']; 
	   $randNum = rand(pow(10,13),pow(10,14)-1);


		$surveyPath =  $baseUrl."craftsvillacustomer/index/customersurvey?shipid=".$eid."&ref_n=".$randNum; //echo $surveyPath; exit;
 
    
		$surveybutton ="<div align='center'> <a href='".$surveyPath."'><button style='width:300px; height:45px; margin:10px auto 0;padding-bottom: 4px;color: #;  background-color:FF9900; font-size: 15px;  font-weight: bold;  background-position: 1px -1937px;'> Click here to provide feedback</button></a>	</div><br>";

         
		$customerShipmentItemHtml .= "<table align='center' border='1' bordercolor=#ccc  style='overflow:auto; border-collapse:collapse;'><tr><td align='center' style='font-size: 13px;font-family: Arial,Helvetica,sans-serif;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#;'></td><td align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 13px; height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#;'>Product Name</td><td align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 13px;height: 26px;padding:11px;vertical-align:top;background:#F2F2F2;color:#;'>Quantity</td></tr>";

	//print_r($customerShipmentItemHtml); exit;

	$_items	= $productDetails->getAllItems();
	
      foreach ($_items as $_item)
		{
			 $_item['product_id'];
			
			$productX = Mage::helper('catalog/product')->loadnew($_item['product_id']);
		
			try{
			$image="<img src='".Mage::helper('catalog/image')->init($productX,
			'image')->resize(166, 166)."' alt='' width='154' border='0'
			style='float:center; border:2px solid #ccc; margin:0 20px 20px;' />";
			}catch(Exception $e){
				
			}
	
			
							$pname=$_item->getName();
							$qty=(int)$_item['qty'];
							

		   $customerShipmentItemHtml .= "<tr><td align='center' style='font-family: Arial,Helvetica,sans-serif;font-size:13px;height:26px;padding: 0px;vertical-align:;background:#F2F2F2;color:#;'>".$image."</td><td align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 13px;height: 26px;padding:0px;vertical-align:top;background:#F2F2F2;color:#;'>".$pname."</td><td align='center' style='font-family: Arial,Helvetica,sans-serif;font-size: 13px;height: 26px;padding:0px;vertical-align:top;background:#F2F2F2;color:#;'>".$qty."</td></tr>";

		}					
	
$customerShipmentItemHtml .= "</table>";

      $emailDetails = 'SELECT sfo.customer_email FROM sales_flat_order as sfo WHERE sfo.entity_id  = '.$_ordersRes['order_id'];

		$queryRes = $readcon->query($emailDetails)->fetchAll();
	  //print_r($queryRes);exit;    

			foreach ($queryRes as $_queryRes) {
		
			         // $customerEmail = $_queryRes['customer_email'];
                     $customerEmail = 'g.reddy7854@gmail.com';
 			   
							$templateId ='survey_email_template';
							$sender = Array('name'=> 'Craftsvilla',
							'email' => 'dileswar@craftsvilla.com');
							$translate  = Mage::getSingleton('core/translate');
							//$translate->setTranslateInline(false);
							$_email = Mage::getModel('core/email_template');
							$mailSubject = 'Your Feedback For Recent Order No. '.$incrementid;

							$vars=Array('pname'=>$pname,'surveybutton'=>$surveybutton,
										'customerShipmentItemHtml'=>$customerShipmentItemHtml);

            echo "sending Email";

			//	print_r($vars); //exit;	
		
					$email=	$_email->setTemplateSubject($mailSubject)
							 	->setReplyTo($sender['email'])
								
								->sendTransactional($templateId, $sender,$customerEmail,'', $vars);
								//->sendTransactional($templateId, $sender,'g.reddy7854@gmail.com','', $vars); 
       

            if($email){
               
               $cse='CSE';
         $hlp1 = Mage::helper('generalcheck');
         $statsconn = $hlp1->getStatsdbconnection();
         $insertSurveyForCustomerEmail="INSERT INTO craftsvilla_auth(shipment_id,ref_id,auth_ref) VALUES('$eid','$randNum','$cse')";
         $res=mysql_query($insertSurveyForCustomerEmail,$statsconn);
         mysql_close($statsconn);

            }


}

}

echo "email sent successfully to your email";   

?>


