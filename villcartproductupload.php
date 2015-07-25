<?php
//Added By Gayatri to upload the data from xml
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

$xml = '/var/www/html/media/vendorxml/Villcartupload11092013.xml';

//$savexml = file_get_contents('http://www.suratdiamond.com/sdjfeed/craft.xml');
//file_put_contents('C:/wamp/www/doejofinal/media/vendorxml/villcart.xml', $savexml);

$doc = new DOMDocument();
  $doc->load($xml);

$importedproductarray = $doc->getElementsByTagName("product"); 

$vendorName = 'Villcart';
$catarray = array();
$path = Mage::getBaseDir('media') . DS . 'vendorimages'. DS;
$vendorid = '68';  
    
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID));
$i=0;        
	foreach ($importedproductarray as $product)
	{
		if(($i > 500) && ($i < 560))
	 {
		
        $itemid = $product->getElementsByTagName("product count")->item(0)->nodeValue;
        $cateid = $product->getElementsByTagName("Villcart_category")->item(0)->nodeValue;
		$prname = $product->getElementsByTagName("name")->item(0)->nodeValue;
		$icode = $product->getElementsByTagName("sku")->item(0)->nodeValue;
		$price = $product->getElementsByTagName("price")->item(0)->nodeValue;
		$shortdesc = $product->getElementsByTagName("meta_description")->item(0)->nodeValue;
		$description = $product->getElementsByTagName("description")->item(0)->nodeValue;
		$metakeyword = $product->getElementsByTagName("meta_keywords")->item(0)->nodeValue;
		$prweight = $product->getElementsByTagName("weight")->item(0)->nodeValue;
		$prqty = $product->getElementsByTagName("qty")->item(0)->nodeValue;
		$img = $product->getElementsByTagName("image_file")->item(0)->nodeValue;
		$sku = "M".strtoupper(substr($vendorName,0,4)).rand(1111111111,9999999999)."0";
		if($cateid=='Furnishing')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
			
		}
		elseif($cateid=='Kitchen')
		{
			$catarray=array(5,51);
			$attributeSetId='137';
		}		
		elseif($cateid=='Key Holders')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		elseif($cateid=='Ganesha Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Candles')
		{
			$catarray=array(5,241);
			$attributeSetId='33';
		}
		elseif($cateid=='Earrings')
		{
			$catarray=array(6,34);
			$attributeSetId='92';
		}
		elseif($cateid=='Show Pieces')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		elseif($cateid=='Clocks')
		{
			$catarray=array(5,798);
			$attributeSetId='73';
		}
		elseif($cateid=='Hangings')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		elseif($cateid=='Spiritual Artifacts')
		{
			$catarray=array(412,595);
			$attributeSetId='76';
		}
		elseif($cateid=='Toys')
		{
			$catarray=array(5,170);
			$attributeSetId='120';
		}
		elseif($cateid=='Cushion Covers')
		{
			$catarray=array(1070,206);
			$attributeSetId='151';
		}
		elseif($cateid=='Newspaper and Letter Holders')
		{
			$catarray=array(5,45);
			$attributeSetId='78';
		}
		elseif($cateid=='Clothes Hangers')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		elseif($cateid=='Incense Sticks')
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		elseif($cateid=='Stationery')
		{
			$catarray=array(5,45);
			$attributeSetId='78';
		}
		elseif($cateid=='Flower Vases')
		{
			$catarray=array(5,227);
			$attributeSetId='145';
		}
		elseif($cateid=='Jewellery Sets')
		{
			$catarray=array(6,1140);
			$attributeSetId='255';
		}
		elseif($cateid=='Lamps')
		{
			$catarray=array(5,228);
			$attributeSetId='98';
		}
		elseif($cateid=='Saibaba Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Other Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Buddha Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Krishna Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Hanuman Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Shiva Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Vishnu Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Laxmi Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Kuber Idols')
		{
			$catarray=array(412,595);
			$attributeSetId='199';
		}
		elseif($cateid=='Locks')
		{
			$catarray=array(5,484);
			$attributeSetId='147';
		}
		elseif($cateid=='Paintings')
		{
			$catarray=array(5,203);
			$attributeSetId='97';
		}
		elseif($cateid=='Support')
		{
			$catarray=array(9,90);
			$attributeSetId='123';
		}
		
		else
		{
			$catarray=array(5,168);
			$attributeSetId='55';
		}
		$collection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('vendorsku', $icode)->getFirstItem();
		if($collection->getId()){
		  echo "This product already exist: ".$icode;
		}else{
		   
		
		$newimagename = $path.$sku.'-'.$cateid.'-Craftsvilla'.'.jpg';
		file_put_contents($newimagename, file_get_contents($img));
		$newproduct = Mage::getModel('catalog/product');
		$newproduct->setTypeId('simple');
		$newproduct->setWeight($prweight); //Unit weight
		$newproduct->setVisibility(4);
		$newproduct->setStatus(1);
		$newproduct->setSku($sku); //Product custom SKU
		$newproduct->setTaxClassId(2);
		$newproduct->setStoreIDs(array(0));
		$newproduct->setStockData(array(
			'is_in_stock' => 1,
			'qty' => $prqty,
			'manage_stock' => 0));
		$newproduct->setAttributeSetId($attributeSetId);
		$newproduct->setName($prname); // Name of the Product
		$newproduct->setCategoryIds($catarray); // array of categories it will relate to
		$newproduct->setDescription($description);
		$newproduct->setShortDescription($shortdesc);
		$newproduct->setMetaDescription(substr($shortdesc,0,255));
		$newproduct->setMetaKeyword($metakeyword);
		$newproduct->setPrice($price);
		//$newproduct->setSpecialPrice($specialprice);
		$newproduct->setManufacturer($vendorName);
		$newproduct->setUdropshipVendor($vendorid);
        $newproduct->setMediaGallery (array('images'=>array (), 'values'=>array ()));
		$newproduct->addImageToMediaGallery ($newimagename, array ('image','small_image','thumbnail'), false, false);
		$newproduct->setAddedFrom(1);
        $newproduct->setMetaTitle(substr($prname,0,35).' - by '.$vendorName.' - Buy Online Jewellery - '.$icode);
		$newproduct->setWebsiteIds(array(1));
		$newproduct->setShippingcost(0);
		$newproduct->setVendorsku($icode);
		$newproduct->setIntershippingcost(500);
        $newproduct->setInterShippingTablerate(500);
		
    try
    {
        if (is_array($errors = $newproduct->validate()))
        {
            $strErrors = array();
            foreach ($errors as $code => $error)
            {
                $strErrors[] = ($error === true) ?
				Mage::helper('catalog')->__('Attribute "%s" is invalid.', $code) :
				$error;
            }
            $this->_fault('data_invalid', implode("\n", $strErrors));
        }
		 
        $newproduct->save(); 
	echo ' Product added: '.$i;
		 
    }
	
    catch (Mage_Core_Exception $e)
    {
        $this->_fault('data_invalid', $e->getMessage());
    }
		  
	
}

	 }
//	 else
//	 {break;}
	 
     $i++;
//	echo "Products successfully Uploaded";
	
}

?>
