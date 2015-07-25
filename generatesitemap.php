<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();
		
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
       	$io->open(array('path' => '/var/www/html/media/'));
		
        if ($io->fileExists('sitemap.xml') && !$io->isWriteable('sitemap.xml')) {
        	Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', 'sitemap.xml', '/var/www/html/media/'));
        }

        $io->streamOpen('sitemap.xml');

        $io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
        $io->streamWrite('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'. "\n");

        $storeId = '1';
        $date    = Mage::getSingleton('core/date')->gmtDate('Y-m-d');
        //$baseUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
	$idArray = array(33,34,54,55,214,248,553,961,664,74,5,1070,1103,1150,9,284,69,8,190,255,614,962,1071,412,960,1127,1130,1114,388,1007,1136,731,37,126,14,21,732,555,759,244,1109,20,72,218,1151);

	$categoryArray = array('jewellery-necklaces.xml','jewellery-earrings.xml','jewellery-anklets.xml','jewellery-bracelet-n-bangles.xml','jewellery-rings.xml','jewellery-pendants.xml','jewellery-maang-tikkas.xml','jewellery-brooches.xml','jewellery-curated-jewellery.xml','sarees.xml','homedecor.xml','homefurnishing.xml','diwali.xml','wholesale.xml','bags.xml','bathbeauty.xml','foodhealth.xml','accessories.xml','gifts.xml','kids.xml','books.xml','footwear.xml','wedding.xml','spiritual.xml','supplies.xml','flowers.xml','musicalinstruments.xml','rakhigifts.xml','clothing-dressmaterial.xml','clothing-blouse.xml','clothing-sherwani.xml','clothing-salwar.xml','clothing-kurtis.xml','clothing-tshirts-women.xml','clothing-organic.xml','clothing-tops.xml','clothing-lehnga.xml','clothing-kurta.xml','clothing-legging.xml','clothing-kids.xml','clothing-kaftans.xml','clothing-tshirts-men.xml','clothing-skirts.xml','clothing-pants-women.xml','clothing-innerwear.xml');

		foreach($categoryArray as $_categoryArray)
		{
			$io->streamWrite(' <sitemap>' . "\n");
			$xml = sprintf('<loc>%s</loc><lastmod>%s</lastmod>',
			htmlspecialchars('http://www.craftsvilla.com/media/' .$_categoryArray),
			$date
			);
			$io->streamWrite($xml);
			$io->streamWrite(' </sitemap>' . "\n");
		}
		
		//Category & CMS Sitemap Location
		$io->streamWrite(' <sitemap>' . "\n");
		$xml = sprintf('<loc>%s</loc><lastmod>%s</lastmod>',
		htmlspecialchars('http://www.craftsvilla.com/media/categories_cms.xml'),
		$date
		);
		$io->streamWrite($xml);
		$io->streamWrite(' </sitemap>' . "\n");
			
		$io->streamWrite(' </sitemapindex>');
		$io->streamClose();
	
				
		$changefreq = (string)Mage::getStoreConfig('sitemap/category/changefreq', $storeId);
        $priority   = (string)Mage::getStoreConfig('sitemap/category/priority', $storeId);
        		
		$io = new Varien_Io_File();
		$io->setAllowCreateFolders(true);
		$io->open(array('path' => '/var/www/html/media/'));	
		if ($io->fileExists('categories_cms.xml') && !$io->isWriteable('categories_cms.xml')) 
		{
		Mage::throwException(Mage::helper('sitemap')->__('File "%s" cannot be saved. Please, make sure the directory "%s" is writeable by web server.', $categoryArray[$i], '/var/www/html/media/'));
		}
		$io->streamOpen('categories_cms.xml');
		$io->streamWrite('<?xml version="1.0" encoding="UTF-8"?>' . "\n");
		$io->streamWrite('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");
        $collection = Mage::getResourceModel('sitemap/catalog_category')->getCollection($storeId);
		
		foreach ($collection as $item) {
           $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars('http://www.craftsvilla.com/'. $item->getUrl()),
                $date,
                $changefreq,
                $priority
            
			);
            $io->streamWrite($xml);
		     }
        unset($collection);
		
		$collection = Mage::getResourceModel('sitemap/cms_page')->getCollection($storeId);

	    foreach ($collection as $item) {
             $xml = sprintf('<url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%.1f</priority></url>',
                htmlspecialchars('http://www.craftsvilla.com/'. $item->getUrl()),
                $date,
                $changefreq,
                $priority
            );
            $io->streamWrite($xml);
        }
        unset($collection);	

        $io->streamWrite('</urlset>');
        $io->streamClose();
		
