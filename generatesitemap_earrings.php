<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Earrings Sitemap Script Started at Time:: ".$currentTime;

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

		$categoryArray = array('jewellery-earrings.xml');
		$idArray = array(34);

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
//				$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
				$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n");

				
				//$collection = Mage::getModel('catalog/category')->load($_idArray)->getProductCollection();
				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
                                $collectionQuery = "SELECT cpe.`entity_id` as entity_id FROM `catalog_product_entity` as cpe ,`catalog_category_product` as ccp WHERE cpe.`entity_id` = ccp.`product_id` AND ccp.`category_id` = '".$_idArray."'";
                                $collection = $read->query($collectionQuery)->fetchAll();
                                echo "Total Products:";
                                echo sizeof($collection);
				//echo '<pre>';
				//print_r($collection);
				$ind = 1;
				foreach ($collection as $item) {
					if(($ind > $i*20000) && ($ind < ($i+1)*20000))
					{
						//$pid = $item->getEntityId();
						$pid = $item['entity_id'];
						$getUrl = Mage::getModel('catalog/product')->load($pid)	;
						$purl = $getUrl->getUrlPath();
						if (empty($purl)) $purl = 'catalog/product/view/id/'.$pid;
 						$visibility = $getUrl->getVisibility();
						$imageloc = xml_encode(Mage::getBaseUrl('media').'catalog/product'.$getUrl->getImage());
                                                $imagename = xml_encode($getUrl->getName());
                                                $geolocation = "India";
                                        	if ($visibility != '1')
                                        	{
						//	$xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
					//			htmlspecialchars('http://www.craftsvilla.com/'.$purl),
					//			$date,
					//			$changefreq,
					//			$priority
					//			);
 							$xml = sprintf('<url><loc>%s</loc><image:image><image:loc>%s</image:loc><image:caption>%s</image:caption><image:title>%s</image:title><image:geo_location>%s</image:geo_location></image:image><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                                                htmlspecialchars('http://www.craftsvilla.com/'.$purl),
                                                $imageloc,
                                                $imagename,
                                                $imagename,
                                                $geolocation,
                                                $date,
                                                $changefreq,
                                                $priority
                                                );
							$io->streamWrite($xml);
						}
					} 
					$ind++;
					if (($ind%1000) == 0) echo $ind.'-';
				}
				$io->streamWrite('</urlset>');
				$io->streamClose();
				echo 'done: '.$categoryArray[$i];
				$i++;
				unset($collection);
				unset($io);
			}

$currentTime = date("d-m-Y H:i:s", Mage::getModel('core/date')->timestamp(time()));
$message = "Earrings Sitemap Script Ended at Time:: ".$currentTime;
$mail->setBody($message);
$mail->setSubject($message);
$mail->send();
function xml_encode($string)
{
    $string1 = utf8_for_xml($string);
    $string1=preg_replace("/&/", "&amp;", $string1);
    $string1=preg_replace("/</", "&lt;", $string1);
    $string1=preg_replace("/>/", "&gt;", $string1);
    $string1=preg_replace("/\"/", "&quot;", $string1);
    $string1=preg_replace("/%/", "&#37;", $string1);
    $string1=preg_replace("/]/", "", $string1);
    $string1=preg_replace("/!/", ".", $string1);

    return utf8_encode($string1);
}
function utf8_for_xml($string)
{
    return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}
?>

