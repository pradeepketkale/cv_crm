<?php

error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';
Mage::app();


$db = Mage::getSingleton('core/resource')->getConnection('core_read');

/*$savexml = file_get_contents($courierUrl.'waybill/api/fetch/json/?token='.$tokenKey);
$encode_json_str = json_encode($savexml);
$encode = str_replace('\"','',$encode_json_str);*/
$ordernumber = $_GET['shipment'];


		
			$barcodeOptions = array(
                    'text' => $ordernumber,
					'drawText' => TRUE,
                    'align' => 'left'
                );
			
			
			$rendererOptions = array('imageType' => 'jpg');
			$renderer = Zend_Barcode::render(
				'code39', 'image', $barcodeOptions, $rendererOptions
			);
		
	
	//}
	
	
	
	
 //$pdf = new Zend_Pdf(); 
	//$page = new Zend_Pdf_Page(Zend_Pdf_Page::SIZE_A4);
	/*	$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),36);
          Zend_Barcode::setBarcodeFont('helvetica.ttf');*/
   
	


?>
