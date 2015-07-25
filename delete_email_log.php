<?php

$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Delete Email Log Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();

error_reporting(E_ALL & ~E_NOTICE);



$db = Mage::getSingleton('core/resource')->getConnection('core_read');

$sql = "DELETE from `smtppro_email_log` where `log_at` < DATE_SUB(NOW() , INTERVAL 5 DAY) ";
$result = $db->query($sql);
echo "Rows deleted succesfully";
?>
<?php
mysql_close();
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Delete Email Log Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>
