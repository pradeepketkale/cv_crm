<?php
//Added By Gayatri to upload the data from xml
error_reporting(E_ALL & ~E_NOTICE);
require_once 'app/Mage.php';

Mage::app();

$xml = '/var/www/html/media/vendorxml/suratdiamond.xml';

//$savexml = file_get_contents('http://www.suratdiamond.com/sdjfeed/craft.xml');
//file_put_contents('/var/www/html/media/vendorxml/suratdiamond.xml', $savexml);
//exit;
$doc = new DOMDocument();
  $doc->load($xml);
	
$importedproductarray = $doc->getElementsByTagName("ProductResult");
$vendorName = 'Surat Diamond';
$catarray = array();
$path = Mage::getBaseDir('media') . DS . 'vendorimages'. DS;
$vendorid = '1659';  
//$vendorid = '1';    
Mage::app()->setCurrentStore(Mage::getModel('core/store')->load(Mage_Core_Model_App::ADMIN_STORE_ID)); 
$i=0;        
echo sizeof($importedproductarray);
	foreach ($importedproductarray as $product)
	{
		if(($i > 5100) && ($i<5340))
	 {
		$itemid = $product->getElementsByTagName("itemId")->item(0)->nodeValue;
		$cateid = $product->getElementsByTagName("Categoryid")->item(0)->nodeValue;
		$prname = $product->getElementsByTagName("ProductName")->item(0)->nodeValue;
		$icode = $product->getElementsByTagName("itemCode")->item(0)->nodeValue;
		$price = $product->getElementsByTagName("ListPrice")->item(0)->nodeValue;
		$shortdesc = $product->getElementsByTagName("shortDescription")->item(0)->nodeValue;
		$description = $product->getElementsByTagName("Description")->item(0)->nodeValue;
		$prweight = $product->getElementsByTagName("Productweight")->item(0)->nodeValue;
		$prqty = $product->getElementsByTagName("qty")->item(0)->nodeValue;
		$img = $product->getElementsByTagName("imageTag")->item(0)->nodeValue;
		$sku = "M".strtoupper(substr($vendorName,0,4)).rand(1111111111,9999999999)."0";
		if($cateid=='214-Rings')
		{
			$catarray=array(6,214);
			$attributeSetId='94';
			
		}
		elseif($cateid=='214-Bridal Rings')
		{
			$catarray=array(6,214);
			$attributeSetId='94';
		}		
		elseif($cateid=='Necklaces')
		{
			$catarray=array(6,33);
			$attributeSetId='93';
		}
		elseif($cateid=='34-Earrings')
		{
			$catarray=array(6,34);
			$attributeSetId='92';
		}
		elseif($cateid=='248-Pendants')
		{
			$catarray=array(6,248);
			$attributeSetId='95';
		}
		elseif($cateid=='55-Bangles')
		{
			$catarray=array(6,55);
			$attributeSetId='68';
		}
		elseif($cateid=='31-Jewellery Set')
		{
			$catarray=array(6,1140);
			$attributeSetId='255';
		}
		elseif($cateid=='43-Fashion Jewellery')
		{
			$catarray=array(6,1140);
			$attributeSetId='255';
		}
		else
		{
			$catarray=array(6,33);
			$attributeSetId='93';
		}
		$collection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('vendorsku', $icode)->getFirstItem();
		if($collection->getId()){
		  echo "This product already exist: ".$icode;
		}else{
		   
		
		$newimagename = $path.$sku.'-'.$cateid.'-Craftsvilla'.'.jpg';
		file_put_contents($newimagename, file_get_contents($img));
		if (filesize($newimagename) > 10000)
		{
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
		$newproduct->setMetaKeyword('Buy Online Jewellery, Designer Jewellery, Buy Online Earrings, Buy Online Necklace, Designer Jewellery from India, Polki Jewellery, Kundan Jewellery, Party Jewellery, Bridal Jewellery');
		$newproduct->setPrice($price);
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
	 echo 'Uploaded: '.$i;
    }
    catch (Mage_Core_Exception $e)
    {
        $this->_fault('data_invalid', $e->getMessage());
    }
	}
else {
	echo 'Image File Size Less Than 10kB: '.$i;
}
}
	 }
     $i++;
}
?>
