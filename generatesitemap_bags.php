<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Bags Sitemap Script Started at Time:: ".$currentTime;

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
//	$idArray = array(4,33,34,54,55,214,248,553,961,664,74,5,1070,1103,1150,9,284,69,8,190,255,614,962,1071,412,960,1127,1130,1114);

//	$categoryArray = array('clothing.xml','jewellery-necklaces.xml','jewellery-earrings.xml','jewellery-anklets.xml','jewellery-bracelet-n-bangles.xml','jewellery-rings.xml','jewellery-pendants.xml','jewellery-maang-tikkas.xml','jewellery-brooches.xml','jewellery-curated-jewellery.xml','sarees.xml','homedecor.xml','homefurnishing.xml','diwali.xml','wholesale.xml','bags.xml','bathbeauty.xml','foodhealth.xml','accessories.xml','gifts.xml','kids.xml','books.xml','footwear.xml','wedding.xml','spiritual.xml','supplies.xml','flowers.xml','musicalinstruments.xml','rakhigifts.xml');

		$categoryArray = array('bags.xml');
		$idArray = array(9);

		$changefreq = (string)Mage::getStoreConfig('sitemap/page/changefreq', $storeId);
	        $priority   = (string)Mage::getStoreConfig('sitemap/page/priority', $storeId);
		$i = 0;
		foreach($idArray as $_idArray)
			{
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
				
				$collection = Mage::getModel('catalog/category')->load($_idArray)->getProductCollection();
				//echo '<pre>';
				//print_r($collection);
				foreach ($collection as $item) {
					$pid = $item->getEntityId();
					$getUrl = Mage::getModel('catalog/product')->load($pid)	;
					$purl = $getUrl->getUrlPath();
				if (empty($purl)) $purl = 'catalog/product/view/id/'.$pid;		
				$xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
				htmlspecialchars('http://www.craftsvilla.com/'.$purl),
				$date,
				$changefreq,
				$priority
				);
				$io->streamWrite($xml);
				}
				$io->streamWrite($xml);
				$io->streamWrite('</urlset>');
				$io->streamClose();
				echo 'done: '.$categoryArray[$i];
				$i++;
				unset($collection);
			}
$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Bags Sitemap Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
?>

