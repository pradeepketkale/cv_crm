<?php
$mageFilename = 'app/Mage.php';
require_once $mageFilename;
umask(0);
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "This script Started at time: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody('Hi This is Good');
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format

try {
$mail->send();
Mage::getSingleton('core/session')->addSuccess('Your request has been sent');
}
catch (Exception $e) {
Mage::getSingleton('core/session')->addError('Unable to send.');
}
?>
