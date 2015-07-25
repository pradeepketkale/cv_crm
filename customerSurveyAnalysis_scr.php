<?php
require_once 'app/Mage.php';
	Mage::app();
$hlp1 = Mage::helper('generalcheck');
$statsconn = $hlp1->getStatsdbconnection();
 $selQueryTotal ="SELECT count(*) as totalSurvey FROM `customer_survey`";
 $exeQueryTotal =mysql_query($selQueryTotal,$statsconn);
 $resultTotal=mysql_fetch_array($exeQueryTotal);
 echo "Total Survey"; echo $totalRecords = $resultTotal[0]; 


         $selQuery0="SELECT count(Q1) as VeryExpensiveCountValue FROM customer_survey WHERE Q1=0";
         $selQuery1="SELECT count(Q1) as ExpensiveCountValue FROM customer_survey WHERE Q1=1";
         $selQuery2="SELECT count(Q1) as OkCountValue FROM customer_survey WHERE Q1=2";
         $selQuery3="SELECT count(Q1) as GoodvalueCountValue FROM customer_survey WHERE Q1=3";
         $selQuery4="SELECT count(Q1) as ExcellentValueCountValue FROM customer_survey WHERE Q1=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

         $result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);


         $table="";

         $table1 .="<br><table border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td align='center'>Very Expensive</td><td> Expensive</td><td>OK</td><td>Good Value</td><td>Excellent Value </td></tr>";


         $table1 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table1 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table1 .="</table>";
 $table .="<b>Q1. How would you rate the price of the product?<br></b>".$table1."<br><br>";



         $selQuery0="SELECT count(Q2) as VeryPoorCountValue FROM customer_survey WHERE Q2=0";
         $selQuery1="SELECT count(Q2) as PoorCountValue FROM customer_survey WHERE Q2=1";
         $selQuery2="SELECT count(Q2) as OkCountValue FROM customer_survey WHERE Q2=2";
         $selQuery3="SELECT count(Q2) as HighQualityCountValue FROM customer_survey WHERE Q2=3";
         $selQuery4="SELECT count(Q2) as ExcellentQualityCountValue FROM customer_survey WHERE Q2=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

 $result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);


       

         $table2 .="<table  border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Very Poor</td><td> Poor</td><td>OK</td><td>High Quality</td><td>Excellent Quality</td></tr>";


         $table2 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table2 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table2 .="</table>";
         $table .="<b>Q2. How would you rate the quality of the product?<br></b><br>".$table2."<br><br>";




         $selQuery0="SELECT count(Q3) as VeryPoorFitCountValue FROM customer_survey WHERE Q3=0";
         $selQuery1="SELECT count(Q3) as PoorFitCountValue FROM customer_survey WHERE Q3=1";
         $selQuery2="SELECT count(Q3) as OkCountValue FROM customer_survey WHERE Q3=2";
         $selQuery3="SELECT count(Q3) as GoodFitCountValue FROM customer_survey WHERE Q3=3";
         $selQuery4="SELECT count(Q3) as ExcellentFitCountValue FROM customer_survey WHERE Q3=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

$result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);

       

         $table3 .="<table border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Very Poor Fit</td><td> Poor Fit</td><td>OK</td><td>Good Fit</td><td>Excellent Fit</td></tr>";


         $table3 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table3 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table3 .="</table>";
         $table .="<b>Q3. How was the sizing of the product (for apparel)?<br></b><br>".$table3."<br><br>";

     


         $selQuery0="SELECT count(Q4) as VeryPoorCountValue FROM customer_survey WHERE Q4=0";
         $selQuery1="SELECT count(Q4) as PoorCountValue FROM customer_survey WHERE Q4=1";
         $selQuery2="SELECT count(Q4) as OkCountValue FROM customer_survey WHERE Q4=2";
         $selQuery3="SELECT count(Q4) as HighQualityCountValue FROM customer_survey WHERE Q4=3";
         $selQuery4="SELECT count(Q4) as ExcellentQualityCountValue FROM customer_survey WHERE Q4=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

$result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);



       

         $table4 .="<table  border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Very Poor </td><td> Poor </td><td>OK</td><td>High Quality</td><td>Excellent Quality</td></tr>";


         $table4 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table4 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table4 .="</table>";
         $table .="<b>Q4. How was the quality of the packaging?<br></b><br>".$table4."<br><br>";


 $selQuery0="SELECT count(Q5) as VeryUnsatisfiedCountValue FROM customer_survey WHERE Q5=0";
         $selQuery1="SELECT count(Q5) as UnsatisfiedCountValue FROM customer_survey WHERE Q5=1";
         $selQuery2="SELECT count(Q5) as OkCountValue FROM customer_survey WHERE Q5=2";
         $selQuery3="SELECT count(Q5) as SatisfiedCountValue FROM customer_survey WHERE Q5=3";
         $selQuery4="SELECT count(Q5) as VerySatisfiedCountValue FROM customer_survey WHERE Q5=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

$result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);


        

         $table5 .="<table  border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Very  Unsatisfied </td><td> Unsatisfied </td><td>OK</td><td>Satisfied</td><td>Very Satisfied</td></tr>";


         $table5 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table5 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table5 .="</table>";
         $table .="<b>Q5. How satisfied were you with the delivery time for your product?<br></b><br>".$table5."<br><br>";



         $selQuery0="SELECT count(Q4) as VeryPoorCountValue FROM customer_survey WHERE Q4=0";
         $selQuery1="SELECT count(Q4) as PoorCountValue FROM customer_survey WHERE Q4=1";
         $selQuery2="SELECT count(Q4) as OkCountValue FROM customer_survey WHERE Q4=2";
         $selQuery3="SELECT count(Q4) as GoodCountValue FROM customer_survey WHERE Q4=3";
         $selQuery4="SELECT count(Q4) as ExcellentCountValue FROM customer_survey WHERE Q4=4";

         $exeQuery0=mysql_query($selQuery0,$statsconn);
         $exeQuery1=mysql_query($selQuery1,$statsconn);
         $exeQuery2=mysql_query($selQuery2,$statsconn);
         $exeQuery3=mysql_query($selQuery3,$statsconn);
         $exeQuery4=mysql_query($selQuery4,$statsconn);

         $result0=mysql_fetch_array($exeQuery0);
         $result1=mysql_fetch_array($exeQuery1);
         $result2=mysql_fetch_array($exeQuery2);
         $result3=mysql_fetch_array($exeQuery3);
         $result4=mysql_fetch_array($exeQuery4);

$result0per = round(($result0[0]/$totalRecords)*100,0);
         $result1per = round(($result1[0]/$totalRecords)*100,0);
         $result2per = round(($result2[0]/$totalRecords)*100,0);
         $result3per = round(($result3[0]/$totalRecords)*100,0);
         $result4per = round(($result4[0]/$totalRecords)*100,0);



         

         $table6 .="<table  border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Very Poor </td><td> Poor </td><td>OK</td><td>Good</td><td>Excellent </td></tr>";


         $table6 .="<tr align='center'><td>".$result0[0]."</td><td>".$result1[0]."</td><td>".$result2[0]."</td><td>".$result3[0]."</td><td>".$result4[0]."</td></tr>";
         $table6 .="<tr align='center'><td>".$result0per."%</td><td>".$result1per."%</td><td>".$result2per."%</td><td>".$result3per."%</td><td>".$result4per."%</td></tr>";
                      
         $table6 .="</table>";
         $table .="<b>Q6. Overall how would you rate your purchase experience?<br><br></b>".$table6;

//$table=$table1."<br>".$table2."<br>".$table3."<br>".$table4."<br>".$table5."<br>".$table6;

//echo $table;exit;

$hlp1 = Mage::helper('generalcheck');
$statsconn = $hlp1->getStatsdbconnection();

$selShipId="SELECT `survey_id`,`shipment_id` FROM customer_survey WHERE Q2=0";
$exeShipId=mysql_query($selShipId,$statsconn);
mysql_close($statsconn);
               $table7="";
                     
               $table7 .="<table  border='1' cellspacing='2px' cellpadding='5px' bordercolor=#ccc style='border-collapse:collapse;table-layout:auto;'><tr align='center'><td>Image</td><td> Vendor Name </td><td>Price</td></tr>";

while($result=mysql_fetch_array($exeShipId)){

echo   $shipId=$result['shipment_id'];
   $orderDetails="SELECT * FROM sales_flat_shipment_item WHERE parent_id='".$shipId."'";
$readcon = Mage::getSingleton('core/resource')->getConnection('core_read');
   $ordersRes = $readcon->query($orderDetails)->fetchAll(); 
$readcon->closeConnection();

   foreach($ordersRes as $_ordersRes) {

            $price=(int)$_ordersRes['price'];
                
            $product_id = $_ordersRes['product_id'];

            $shipmentDetails = Mage::getModel('sales/order_shipment')->load($shipId);

        echo    $vendorid =  $shipmentDetails['udropship_vendor']; 


	         $vendorDetails =Mage::getModel('udropship/vendor')->load($vendorid);
            echo $vname = $vendorDetails['vendor_name']; 

            $productX = Mage::helper('catalog/product')->loadnew($_ordersRes['product_id']);
		
			      try{
			      $image="<img src='".Mage::helper('catalog/image')->init($productX,
			      'image')->resize(166, 166)."' alt='' width='166' border='0'
			      style='float:center; border:2px solid #ccc; margin:0 20px 20px;' />";
			      }catch(Exception $e){
				
			      }
      $table7 .="<tr align='center'><td>".$image."</td><td>".$vname."</td><td>".$price."</td></tr>";
   }
}

$table7 .="</table>";


$table .="<b>List of Poor Quality Products</b><br><br>".$table7;

       $mail = Mage::getModel('core/email');
                    $mail->setToName('Craftsvilla');
                    $mail->setToEmail('manoj@craftsvilla.com');
                    $mail->setBody($table);
                    $mail->setSubject('Craftsvilla Customer Survey Analysis: Total Results:'.$totalRecords);
                    $mail->setFromEmail('places@craftsvilla.com');
                    $mail->setFromName('Craftsvilla Places');
                    $mail->setType('html');

      echo "Sending Mail....";
                    if($mail->send())
                    {
			$mail->setToEmail('monica@craftsvilla.com');
			$mail->send();
			$mail->setToEmail('ranjit@craftsvilla.com');
			$mail->send();
                      echo 'Email has been send successfully';
                    }
                    else{
                      echo "error";
                    }
       
?>

