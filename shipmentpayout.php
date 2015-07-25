<?php //exit;
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Shipment Payout Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();


$con = Mage::getSingleton('core/resource')->getConnection('core_read');

if($con)
{
    // $sql         = "select increment_id, order_increment_id from sales_flat_shipment_grid";
    	$sql         = "select increment_id, order_increment_id from sales_flat_shipment_grid WHERE `created_at` between DATE_SUB(NOW(),INTERVAL 5 DAY) AND NOW()";
	//$results     = mysql_query($sql);
        $results     = $db->query($sql)->fetchAll();
    if($results && mysql_num_rows($results))
    {
        //while ($row = mysql_fetch_array($results)) {
	 foreach($results as $row){
            $sql2 = mysql_query("select * from shipmentpayout where shipment_id = '".$row['increment_id']."'")->fetch();
            if($sql2)
            {
                if(mysql_num_rows($sql2)==0)
                {
                    echo "<br />NUMBER:".mysql_num_rows($sql2);
                    $sql3 = "insert into shipmentpayout (shipment_id, order_id) values('".$row['increment_id']."', '".$row['order_increment_id']."')";
                   // $results3     = mysql_query($sql3);
		     $results3     = $db->query($sql3);
                    echo ":inserted row".$row['increment_id']."<br/>";
                }    
            }
        }    
    }
}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Shipment Payout Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>
