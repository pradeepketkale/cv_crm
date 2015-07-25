<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Vendors Sitemap Script Started at Time:: ".$currentTime;

$mail = Mage::getModel('core/email');
$mail->setToName('Manoj Gupta');
$mail->setToEmail('manoj@craftsvilla.com');
$mail->setBody($message);
$mail->setSubject($message);
$mail->setFromEmail('places@craftsvilla.com');
$mail->setFromName("Manoj Gupta");
$mail->setType('html');// YOu can use Html or text as Mail format
$mail->send();
		
        $storeId = '1';
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');


		$categoryArray = array('vendors.xml');

		$changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
	        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
		$i = 0;
		$vendorId = 1;
				$io = new Varien_Io_File();
				$io->setAllowCreateFolders(true);
				$io->open(array('path' => '/var/www/html/media/'));	
				if ($io->fileExists($categoryArray[$i]) && !$io->isWriteable($categoryArray[$i])) 
				{
				Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $categoryArray[$i], '/var/www/html/media/'));
				}
				$io->streamOpen($categoryArray[$i]);
				$io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
				$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
				$ind = 1;
                		while($vendorId < 5500)
				{
					if($vendorId != 1078){
					$getUrl = Mage::getModel('udropship/vendor')->load($vendorId);
					$purl = $getUrl->getUrlKey();
					$xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
								htmlspecialchars('http://www.craftsvilla.com/'.$purl),
								$date,
								$changefreq,
								$priority
								);
					$io->streamWrite($xml);
					}
					$vendorId++;
				}
				$io->streamWrite('</urlset>');
				$io->streamClose();
				echo 'done: '.$categoryArray[$i];
				$i++;
				unset($io);

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Vendors Sitemap Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>

