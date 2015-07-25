<?php
class Craftsvilla_Mobileapp_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Mobileapp"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("mobileapp", array(
                "label" => $this->__("Mobileapp"),
                "title" => $this->__("Mobileapp")
		   ));

      $this->renderLayout(); 
	  
    }

public function getHomeProductsAction(){
		$cat_id = 991; // set desired category id
		$minPriceMob = $this->getRequest()->getParam('priceMin');
        $maxPriceMob = $this->getRequest()->getParam('priceMax');
        $sortDirection = mysql_escape_string($this->getRequest()->getParam('sortDirection'));
        $priceorderMob = mysql_escape_string($this->getRequest()->getParam('sortBy'));
        $offsetMob = $this->getRequest()->getParam('offset');
        $limitMob = $this->getRequest()->getParam('limit');		
		$CacheIdProductMobileKey = "homemobile-".$minPriceMob.'-'.$maxPriceMob.'-'.$sortDirection.'-'.$priceorderMob.'-'.$offsetMob.'-'.$limitMob;
		$lifetime = 3600;		

		if ($cacheContent = Mage::app()->loadCache($CacheIdProductMobileKey)){ 
			echo $cacheContent;exit;
			}
		else{        
		$category = Mage::getModel('catalog/category')->load($cat_id);
		
        if(!is_numeric($offsetMob))$offsetMob = 0;
        if(!is_numeric($limitMob))$limitMob = 50;
		$whereSelect = '';
        if($minPriceMob == 0) $minPriceMob = 10;
        if(!is_numeric($minPriceMob)&&($minPriceMob)) $minPriceMob = 100;
        if(!is_numeric($maxPriceMob) && empty($maxPriceMob)) $maxPriceMob = 100000;

        if($minPriceMob && $maxPriceMob)
                {
                                $whereSelect = " min_price > ".$minPriceMob." AND min_price < ".$maxPriceMob;
                }
        if($sortDirection)
                {
                                $whereSortDIrection = " ORDER BY min_price ".$sortDirection ;
                }
        else{
                                $whereSortDIrection = " ORDER BY id ASC" ;
                }
        //if($priceorderMob)
                //{
                        //$orderSelect = "ORDER BY min_price ".$priceorderMob;
                //}
$offsetSelect = " LIMIT ".$limitMob." OFFSET ".$offsetMob;
$mobilehlp = Mage::helper('generalcheck');
$statsconnMobile = $mobilehlp->getStatsdbconnection();
//$readP = Mage::getSingleton('core/resource')->getConnection('core_read');
//      $products1 = $category->getProductCollection()->addCategoryFilter($category)->addAttributeToSelect('*')->addAttributeToFilter('status', 1)->addAttributeToFilter('visibility', array('2','3','4'));
$products1 = "SELECT `product_id` FROM  `craftsvilla_trending` WHERE `cod` = '1' AND ". $whereSelect . $orderSelect . $whereSortDIrection . $offsetSelect ;
$resultProduct = mysql_query($products1,$statsconnMobile);
        //$resultProduct = $readP->query($products1)->fetchAll();
        //echo '<pre>';print_r($resultProduct);exit;

//echo '<pre>';print_r($products1->getData());
$jsondataarray1 = array();
$k = 0;
$jsondataarray1 =array();
//foreach($resultProduct as $product)
while($product =  mysql_fetch_array($resultProduct))
{
	$productId = $product['product_id'];
	$productdataCollection = Mage::helper('catalog/product')->loadnew($productId);
	//echo '<pre>';print_r($productdataCollection->getData());
	$name = $productdataCollection->getName();
	$regPrice = $productdataCollection->getPrice();
	$offerPrice =  $productdataCollection->getSpecialPrice();
	$description =  $productdataCollection->getDescription();
	$domshippingcost = $productdataCollection->getShippingcost();
	$internationalshippingcost = $productdataCollection->getIntershippingcost();
	$vendorId = $productdataCollection->getUdropshipVendor();
	$vendorName = Mage::getModel('udropship/vendor')->load($vendorId)->getVendorName();
	$image = Mage::helper('catalog/image')->init($productdataCollection,'image')->resize(166, 166);
	$image1 = mysql_escape_string($image);
	$media_gallery = $productdataCollection->getMediaGalleryImages();
	$gallery = array();
	foreach($media_gallery as $_media_gallery){
	$gallery[] =  $_media_gallery->getUrl();
	}
	//echo '<pre>';print_r($media_gallery);
		    $jsondataarray = array('count' => '50',
		                                      'data' => array('id' => $productId,
		             					      'name' => $name,
		                                      'regularPrice'=>$regPrice,
		                                      'offerPrice'=>$offerPrice,
		                                      'imageDisplay' => $image1,
		                                      'gallery' =>array($gallery[0],$gallery[1],$gallery[2]),
		                                      'shippingCostIndia' => $domshippingcost,
		                            			'shippingCostWorld' => $internationalshippingcost,
		                            'seller' => array('id' => $vendorId,'name' => $vendorName)
		                            ));


array_push($jsondataarray1, $jsondataarray);
	
}
//print_r($jsondataarray1);exit;
$result = json_encode($jsondataarray1);
$cacheContent = $result;

Mage::app()->saveCache($cacheContent, $CacheIdProductMobileKey, $tags, $lifetime);
echo $cacheContent;
exit;
}

}



public function getProductsDetailAction(){

$productuniqueId = $this->getRequest()->getParam('productuniqueId');
$CacheIdProductvieMobileKey = "productmobile-".$productuniqueId;
$lifetime = 3600;

if ($cacheContent = Mage::app()->loadCache($CacheIdProductvieMobileKey)){ 
		echo $cacheContent;exit;
	}
else{        
$productdataCollection = Mage::helper('catalog/product')->loadnew($productuniqueId);
//echo '<pre>';print_r($productdataCollection->getData());
$sku = $productdataCollection->getSku();
$productName = $productdataCollection->getName().'-'.$sku;
$productPrice = $productdataCollection->getPrice();
$offerPrice =  $productdataCollection->getSpecialPrice();
//$image = $productdataCollection->getImage();
$description =  $productdataCollection->getDescription().'-'.$sku;
$domshippingcost = $productdataCollection->getShippingcost();
$internationalshippingcost = $productdataCollection->getIntershippingcost();
$vendorId = $productdataCollection->getUdropshipVendor();
$vendorName = Mage::getModel('udropship/vendor')->load($vendorId)->getVendorName();
$image = Mage::helper('catalog/image')->init($productdataCollection,'image')->resize(154, 154);

$media_gallery = $productdataCollection->getMediaGalleryImages();
$gallery = array();
foreach($media_gallery as $_media_gallery){
$gallery[] =  $_media_gallery->getUrl();
}

//echo '<pre>';print_r($media_gallery);
$image1 = mysql_escape_string($image);

$prdouctJsonArray = array('id'=> $productuniqueId,
                                                  'name' => $productName,
                                'regularPrice' => $productPrice,
                                'offerPrice' => $offerPrice,
                                'imageDisplay'=> $image1,
                                'desription'    => $description,
                                'gallery' =>array($gallery[0],$gallery[1],$gallery[2]),
                                'shippingCostIndia' => $domshippingcost,
                                'shippingCostWorld' => $internationalshippingcost,
                                'seller' => array('id' => $vendorId,'name' => $vendorName),

);


$resultprdouct = json_encode($prdouctJsonArray);
//return $resultprdouct;
//print_r($resultprdouct);
$cacheContent = $resultprdouct;

Mage::app()->saveCache($cacheContent, $CacheIdProductvieMobileKey, $tags, $lifetime);
echo $cacheContent;
exit;
}
}

public function checkPincodeAction()
        {
        $pincode = $this->getRequest()->getParam('zip');
        try{
                        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                        $delhivery = Mage::getStoreConfig('courier/general/delhivery');
                        if($delhivery=='1')
                        {
                        $pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Delhivery%'";
                        }
                        else
                        {
                        $pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Aramex%'";
                        }
                $rquery = $read->query($pincodeQuery)->fetch();
                        if($rquery)
                                {
                                $check_pin = array('deliver' => true);
                                }
                        else
                        {
                                $check_pin = array('deliver' => false);
                        }
        $resultone = json_encode($check_pin);
        print_r($resultone);exit;
                }
        catch(Exception $e){
        //echo 'helllooo';
        }
}


public function getCurrencyAction(){

//$getCurrencyValues = $this->getRequest()->getParam('getCurrency');
$jsonCurrencyString = array(array('id'=> 'INR', 'name' => 'Rupee'),
							array('id'=> 'EUR', 'name'=> 'Euro'),
							array('id'=> 'USD', 'name'=>'Dollar'),
							array('id'=> 'AUD', 'name'=> 'Australian Dollar'),
							array('id'=> 'GBP', 'name'=> 'British Pound Sterling'),
							array('id'=> 'CAD', 'name'=> 'Canadian Dollar'),
							array('id'=> 'MYR', 'name'=> 'Malayasian Ringit'),);						
			
$currencyAll = json_encode($jsonCurrencyString);
print_r($currencyAll);exit;
}

public function prepareCustomerOrderAction(array $productOrderarray)
    {

$shippingMethod = 'udropship_Per Item Shipping';
  //echo '<pre>';print_r($productOrderarray);
        $customerEmail = $productOrderarray->shipping_address->email;//'dileswar@craftsvilla.com';
        $shoppingCart = $productOrderarray->products;
        $quoteObj = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
        //$customerObj = $quoteObj->setCustomerEmail($customerEmail);
        $productMsg = array();
        $quoteMsg = array('quoteId'=>'','quotestatus'=>'0','prodmsg'=>$productMsg);

        //print_r($shoppingCart);exit;
        foreach($shoppingCart as $part){
                $productId = $part->product_id;
                $quantity = $part->qty;
            $productModel = Mage::getModel('catalog/product');
        $productObj = $productModel->setStore($storeId)->setStoreId($storeId)->load($productId);
                //echo '<pre>';print_r($productObj->getData());exit;

        $productObj->setSkipCheckRequiredOption(true);
            try{
                $quoteItem = $quoteObj->addProduct($productObj);
                $quoteItem->setPrice($quoteObj->getPrice());
                $quoteItem->setQty($quantity);
                $quoteItem->setQuote($quoteObj);
               // $quoteObj->addItem($quoteItem);
                                $productMsg[$productId]['status'] = 1;
                                //echo '<pre>';print_r($quoteObj);exit;
            } catch (exception $e) {
                                echo 'Quote was not created';
                                $productMsg[$productId]['status'] = 0;
                                $quoteMsg['prodmsg'] = $productMsg;
                                return $quoteMsg;
            }

            $productObj->unsSkipCheckRequiredOption();
            $quoteItem->checkData();
    }
// coupon code

		
		 $couponCode = $productOrderarray->coupon;
        if(!empty($couponCode)) $quoteObj->setCouponCode($couponCode);

        // addresses
                        $firstname = $productOrderarray->shipping_address->name;
                        //$lastname = $this->getRequest()->getParam('lastname');
                        $street1 =$productOrderarray->shipping_address->address1;
                        $street2 = $productOrderarray->shipping_address->address2;
                        $street = $street1.' '.$street2;
                        $city = $productOrderarray->shipping_address->city;
			$state = $productOrderarray->shipping_address->state;
                        $country = $productOrderarray->shipping_address->country;
						if(strtolower($country) == 'india'){$countryId = 'IN';} else { $countryId = 'IN'; }
                        $region_name = ucwords($state);
                        $regionModel = Mage::getModel('directory/region')->loadByName($region_name, $country);
                        $regionId = $regionModel->getId();
                        $zip = $productOrderarray->shipping_address->postalCode;
                        $telephone = $productOrderarray->shipping_address->phone;

                        $shippingAddress = array('firstname' => $firstname,
                                                                        'lastname' => $firstname,
                                                                        'street' => $street,
                                                                        'city' => $city,
                                                                        'region_id' => $regionId,
                                                                        'region' => $region_name,
                                                                        'postcode' => $zip,
                                                                        'country_id' => $countryId, /* Croatia */
                                                                        'telephone' => $telephone,);

        $quoteShippingAddress = new Mage_Sales_Model_Quote_Address();
        $quoteShippingAddress->setData($shippingAddress);
       //$quoteBillingAddress = new Mage_Sales_Model_Quote_Address();
        //$quoteBillingAddress->setData($shippingAddress);
        $quoteObj->setShippingAddress($quoteShippingAddress);
        $quoteObj->setBillingAddress($quoteShippingAddress);



                //$shippingMethod = 'Drop Shipping - Best Shipping Way';
        // shipping method an collect
	$quoteObj->setCheckoutMethod('guest')
	      ->setCustomerId(null)
	  ->setCustomerFirstname($firstname)
	  ->setCustomerLastname($lastname)
     	  ->setCustomerEmail($customerEmail)
         ->setCustomerIsGuest(true)
         ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);
        $quoteObj->getShippingAddress()->setShippingMethod($shippingMethod);
        $quoteObj->getShippingAddress()->setCollectShippingRates(true);
        $quoteObj->getShippingAddress()->collectShippingRates();
        $quoteObj->collectTotals(); // calls $address->collectTotals();
        $quoteObj->setIsActive(0);
        $quoteObj->save();
                $quoteMsg['quotestatus']  = 1;
                $quoteMsg['quoteId']  = $quoteObj->getId();
                $quoteMsg['prodmsg'] = $productMsg;
                return $quoteMsg;

}
public function createOrderAction()
{

$orderjsonData = file_get_contents("php://input");
$productOrderarray =  json_decode($orderjsonData);

//echo '<pre>';print_r($productOrderarray);exit;
//$productsA['products'] = array(array('productId' => '26066','qty' => '2'),array('productId' => '26065','qty' => '2'));
        //      $shippingAddressA['shippingAddress'] = array('name' => 'Dileswar','address1' => 'Row house','address2' => 'Kanidvali','city' => 'Mumbai','country' => 'INDIA','postalCode'=>'765011','phone' =>'80975274//50','email' => 'dileswar@craftsvilla.com' );
        //      $couponArrray = array('coupon' => 'FLAT50');
                //$prodOrderA = array($productsA);
                //$productOrderarray = array($productsA,$shippingAddressA,$couponArrray);

                $quoteMsg = $this->prepareCustomerOrderAction($productOrderarray);
                 $quoteId = $quoteMsg['quoteId'];
                if(!empty($quoteId))
                {
                        //echo '<pre>';print_r($quoteMsg);exit;
                        $quoteObj = Mage::getModel('sales/quote')->load($quoteId); // Mage_Sales_Model_Quote
                    $items = $quoteObj->getAllItems();

                    $quoteObj->reserveOrderId();

                      // set payment method
                        $paymentMethod = 'cashondelivery';
                    $quotePaymentObj = $quoteObj->getPayment(); // Mage_Sales_Model_Quote_Payment
                    $quotePaymentObj->setMethod($paymentMethod);
                    $quoteObj->setPayment($quotePaymentObj);

                    // convert quote to order
                    $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                    $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                    $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);

                    // convert quote addresses
                    $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                    $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));

                    // set payment options
                    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                    //if ($paymentData) {
                    //$orderObj->getPayment()->setCcNumber($paymentData->ccNumber);
                    //$orderObj->getPayment()->setCcType($paymentData->ccType);
                    //$orderObj->getPayment()->setCcExpMonth($paymentData->ccExpMonth);
                    //$orderObj->getPayment()->setCcExpYear($paymentData->ccExpYear);
                    //$orderObj->getPayment()->setCcLast4(substr($paymentData->ccNumber,-4));
                   // }

		    // convert quote items
                    foreach ($items as $item) {
                        // @var $item Mage_Sales_Model_Quote_Item
                        $orderItem = $convertQuoteObj->itemToOrderItem($item);

                        $options = array();
                    if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {

                        $options = $productOptions;
                    }
                    if ($addOptions = $item->getOptionByCode('additional_options')) {
                        $options['additional_options'] = unserialize($addOptions->getValue());
                    }
                    if ($options) {
                        $orderItem->setProductOptions($options);
                    }
                        if ($item->getParentItem()) {
                            $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                        }
                        $orderObj->addItem($orderItem);
                    }

                    $orderObj->setCanShipPartiallyItem(false);

                    try {
                        $orderObj->place();
                    } catch (Exception $e){
                        Mage::log($e->getMessage());
                        Mage::log($e->getTraceAsString());
                    }

                    $k = $orderObj->save();
                        //echo '<pre>';print_r($k);
                    $orderObj->sendNewOrderEmail();
                    $orderObj->getId();
                        $gId = $orderObj->getIncrementId();

                $mobilehlp = Mage::helper('generalcheck');
         		$statsconnMobile = $mobilehlp->getStatsdbconnection();
                $insertorderquery = "INSERT INTO `craftsvilla_mobileapp` (`order_id`, `getOrderFRom`,`created_at`) VALUES ('" . $gId . "', 'MApp',NOW())";
				$mobileRes=mysql_query($insertorderquery,$statsconnMobile);
                mysql_close($statsconnMobile);

                        
			//echo '<pre>';print_r($orderObj->getData());exit;
			$productOrderarray->orderId =$orderObj->getIncrementId();
                        $productOrderarray->grossPrice = $orderObj->getBaseGrandTotal();
                        $productOrderarray->discount = $orderObj->getDiscountAmount();
                        $productOrderarray->discountPercent = '';
                        $productOrderarray->netPrice = $orderObj->getBaseGrandTotal();
                        $productOrderarray->expectedDispatchDate = '';
                        $productOrderarray->expectedDeliveryDate = '';
                        $productOrderarray->orderstatus = 1;
                        $productOrderarray->statusmsg = $quoteMsg['prodmsg'];

		}

                else
                {
                        $productOrderarray->grossPrice = 0;
                        $productOrderarray->discount = 0;
                        $productOrderarray->discountPercent = 0;
                        $productOrderarray->netPrice = 0;
                        $productOrderarray->expectedDispatchDate = 0;
                        $productOrderarray->expectedDeliveryDate = 0;
                        $productOrderarray->orderstatus = 0;
                        $productOrderarray->statusmsg = $quoteMsg['prodmsg'];
                }
                $k = json_encode($productOrderarray);
//$password1 = $k;
//$file = "/var/www/html/var/import/pass1.txt";
//$fp = fopen($file,'wr');
//fwrite($fp,$password1.':passwordd');
                echo $k;exit;
    }

public function getCategoryProductsAction(){
		
        $cat_id = $this->getRequest()->getParam('category'); // set desired category id
        //$category = Mage::getModel('catalog/category')->load($cat_id);
        $minPriceMob = $this->getRequest()->getParam('priceMin');
        $maxPriceMob = $this->getRequest()->getParam('priceMax');
        $sortDirection = mysql_escape_string($this->getRequest()->getParam('sortDirection'));
        $priceorderMob = mysql_escape_string($this->getRequest()->getParam('sortBy'));
        $offsetMob = $this->getRequest()->getParam('offset');
        $limitMob = $this->getRequest()->getParam('limit');
		$CacheIdCategoryMobileKey = "categorymobile-".$minPriceMob.'-'.$maxPriceMob.'-'.$sortDirection.'-'.$priceorderMob.'-'.$offsetMob.'-'.$limitMob.'-'.$cat_id;
		$lifetime = 3600;		

		if ($cacheContent = Mage::app()->loadCache($CacheIdCategoryMobileKey)){ 
			echo $cacheContent;exit;
			}
		else{
	
	
        if(!is_numeric($offsetMob))$offsetMob = 0;
        if(!is_numeric($limitMob))$limitMob = 50;
        $whereSelect = '';
        if($minPriceMob == 0) $minPriceMob = 10;
        if(!is_numeric($minPriceMob)&&($minPriceMob)) $minPriceMob = 100;
        if(!is_numeric($maxPriceMob) && empty($maxPriceMob)) $maxPriceMob = 100000;

        if($minPriceMob && $maxPriceMob)
                {
                                $whereSelect = " AND min_price > ".$minPriceMob." AND min_price < ".$maxPriceMob;
                }
        if($sortDirection)
                {
                                $whereSortDIrection = " ORDER BY min_price ".$sortDirection ;
                }
        else{
                                $whereSortDIrection = " ORDER BY entity_id DESC" ;
                }
        //if($priceorderMob)
                //{

                        //$orderSelect = "ORDER BY min_price ".$priceorderMob;
                //}
        $offsetSelect = " LIMIT ".$limitMob." OFFSET ".$offsetMob;
$readP = Mage::getSingleton('core/resource')->getConnection('core_read');

//      $products1 = $category->getProductCollection()->addCategoryFilter($category)->addAttributeToSelect('*')->addAttributeToFilter('status', 1)->addAttributeToFilter('visibility', array('2','3','4'));

        $products1 = "SELECT `entity_id` FROM `catalog_product_craftsvilla3` WHERE `cod` = '1' AND visibility IN (1,4) AND (category_id1 ='".$cat_id."' OR category_id2 = '".$cat_id."' OR category_id3 = '".$cat_id."' OR category_id4= '".$cat_id."') ". $whereSelect . $orderSelect . $whereSortDIrection . $offsetSelect ;
        $resultProduct = $readP->query($products1)->fetchAll();
        //echo '<pre>';print_r($resultProduct);exit;
//echo '<pre>';print_r($products1->getData());
$jsondataarray1 = array();
$k = 0;

        foreach($resultProduct as $product){
$productId = $product['entity_id'];
$productdataCollection = Mage::helper('catalog/product')->loadnew($productId);
//echo '<pre>';print_r($productdataCollection->getData());
$name = $productdataCollection->getName();
$regPrice = $productdataCollection->getPrice();
$offerPrice =  $productdataCollection->getSpecialPrice();
$description =  $productdataCollection->getDescription();
$domshippingcost = $productdataCollection->getShippingcost();
$internationalshippingcost = $productdataCollection->getIntershippingcost();
$vendorId = $productdataCollection->getUdropshipVendor();
$vendorName = Mage::getModel('udropship/vendor')->load($vendorId)->getVendorName();
$image = Mage::helper('catalog/image')->init($productdataCollection,'image')->resize(154, 154);
$image1 = mysql_escape_string($image);
$media_gallery = $productdataCollection->getMediaGalleryImages();
$gallery = array();
foreach($media_gallery as $_media_gallery){
$gallery[] =  $_media_gallery->getUrl();
}
//echo '<pre>';print_r($media_gallery);
			$jsondataarray = array('count' => '50',
				'data' => array('id' => $productId,
				'name' => $name,
				'regularPrice'=>$regPrice,
				'offerPrice'=>$offerPrice,
				'imageDisplay' => $image1,
				'gallery' =>array($gallery[0],$gallery[1],$gallery[2]),
				'shippingCostIndia' => $domshippingcost,
				'shippingCostWorld' => $internationalshippingcost,
				'seller' => array('id' => $vendorId,'name' => $vendorName)
			));
$jsondataarray1[] = $jsondataarray;
}
$result = json_encode($jsondataarray1);
//print_r($result);
$cacheContent = $result;
Mage::app()->saveCache($cacheContent, $CacheIdCategoryMobileKey, $tags, $lifetime);
echo $cacheContent;
}
exit;
}

public function createOrderPrepaidAction()
{
$orderjsonData = file_get_contents("php://input");
$productOrderarray =  json_decode($orderjsonData);
//echo '<pre>';print_r($productOrderarray);exit;
//$productsA['products'] = array(array('productId' => '26066','qty' => '2'),array('productId' => '26065','qty' => '2'));
        //      $shippingAddressA['shippingAddress'] = array('name' => 'Dileswar','address1' => 'Row house','address2' => 'Kanidvali','city' => 'Mumbai','country' => 'INDIA','postalCode'=>'765011','phone' =>'80975274//50','email' => 'dileswar@craftsvilla.com' );
        //      $couponArrray = array('coupon' => 'FLAT50');
                //$prodOrderA = array($productsA);
                //$productOrderarray = array($productsA,$shippingAddressA,$couponArrray);
                $quoteMsg = $this->prepareCustomerOrderAction($productOrderarray);
                 $quoteId = $quoteMsg['quoteId'];
                if(!empty($quoteId))
                {
                        //echo '<pre>';print_r($quoteMsg);exit;
                        $quoteObj = Mage::getModel('sales/quote')->load($quoteId); // Mage_Sales_Model_Quote
                    $items = $quoteObj->getAllItems();
                    $quoteObj->reserveOrderId();
                      // set payment method
                    $paymentMethod = 'payucheckout_shared';
                    $quotePaymentObj = $quoteObj->getPayment(); // Mage_Sales_Model_Quote_Payment
                    $quotePaymentObj->setMethod($paymentMethod);
                    $quoteObj->setPayment($quotePaymentObj);
                    // convert quote to order
                    $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
                    $orderObj = $convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
                    $orderPaymentObj = $convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
                    // convert quote addresses
                    $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
                    $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
                    // set payment options
                    $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
                    //if ($paymentData) {
                    //$orderObj->getPayment()->setCcNumber($paymentData->ccNumber);
                    //$orderObj->getPayment()->setCcType($paymentData->ccType);
                   //$orderObj->getPayment()->setCcExpMonth($paymentData->ccExpMonth);
                    //$orderObj->getPayment()->setCcExpYear($paymentData->ccExpYear);
                    //$orderObj->getPayment()->setCcLast4(substr($paymentData->ccNumber,-4));

                   // }
// convert quote items
                    foreach ($items as $item) {
                        // @var $item Mage_Sales_Model_Quote_Item
                        $orderItem = $convertQuoteObj->itemToOrderItem($item);
                        $options = array();
                    if ($productOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct())) {
                        $options = $productOptions;
                    }
                    if ($addOptions = $item->getOptionByCode('additional_options')) {
                        $options['additional_options'] = unserialize($addOptions->getValue());
                    }
                    if ($options) {
                        $orderItem->setProductOptions($options);
                    }
                        if ($item->getParentItem()) {
                            $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                        }
                        $orderObj->addItem($orderItem);
                    }

                    $orderObj->setCanShipPartiallyItem(false);
				try {
                        $orderObj->place();
                    } catch (Exception $e){
                        Mage::log($e->getMessage());
                        Mage::log($e->getTraceAsString());
                    }
                    $k = $orderObj->save();
                        //echo '<pre>';print_r($k);
                    //$orderObj->sendNewOrderEmail();
                    $orderObj->getId();
                        $gId = $orderObj->getIncrementId();
                $mobilehlp = Mage::helper('generalcheck');
                        $statsconnMobile = $mobilehlp->getStatsdbconnection();
                $insertorderquery = "INSERT INTO `craftsvilla_mobileapp` (`order_id`, `getOrderFRom`,`created_at`) VALUES ('" . $gId . "', 'MApp',NOW())";
                                $mobileRes=mysql_query($insertorderquery,$statsconnMobile);
                mysql_close($statsconnMobile);
                        //echo '<pre>';print_r($orderObj->getData());exit;
                                                $productOrderarray->orderId =$orderObj->getIncrementId();
                        $productOrderarray->grossPrice = $orderObj->getBaseGrandTotal();
                        $productOrderarray->discount = $orderObj->getDiscountAmount();
                        $productOrderarray->discountPercent = '';
                        $productOrderarray->netPrice = $orderObj->getBaseGrandTotal();
                        $productOrderarray->expectedDispatchDate = '';
                        $productOrderarray->expectedDeliveryDate = '';
                        $productOrderarray->orderstatus = 1;
                       $productOrderarray->statusmsg = $quoteMsg['prodmsg'];
                }
                else
                {
                        $productOrderarray->orderId ='';
                        $productOrderarray->grossPrice = 0;
                        $productOrderarray->discount = 0;
                        $productOrderarray->discountPercent = 0;
                        $productOrderarray->netPrice = 0;
                        $productOrderarray->expectedDispatchDate = 0;
                        $productOrderarray->expectedDeliveryDate = 0;
                        $productOrderarray->orderstatus = 0;
                        $productOrderarray->statusmsg = $quoteMsg['prodmsg'];
                }
                $k = json_encode($productOrderarray);
//$password1 = $k;
//$file = "/home/amit/doejofinal/var/import/pass1.txt";
//$fp = fopen($file,'wr');
//fwrite($fp,$productOrderarray->orderId.':passwordd');
              echo $k;exit;
    }

//public function convertOrdertoProcessingAction(){

//$orderjsonData = file_get_contents("php://input");
//$convertOrderarray =  json_decode($orderjsonData);
//$orderIdd = $convertOrderarray->order_id;
//$status = $convertOrderarray->status;
//$order = Mage::getModel('sales/order')->loadByIncrementId($orderIdd);
//$state = 'processing';
//$status1 = $state;
//$comment = "AWRP, status changes to  to $status Status Through Mobileapp ";
//$isCustomerNotified = false; //whether customer to be notified
//if($orderIdd != '')
//{
//$order->setState($state, $status1, $comment, $isCustomerNotified);
//$order->save();
//}
//$status = array('status'=>$status);
//$ku = json_encode($status);
//echo $ku;exit;
//}

public function convertOrdertoProcessingAction(){
	$orderjsonData = file_get_contents("php://input");
	$convertOrderarray =  json_decode($orderjsonData);
	$orderIdd = $convertOrderarray->order_id;
	$status = 0;
	$returnstatus = $convertOrderarray->status;
	if($returnstatus && ($orderIdd != ''))
	{
	if($this->verifyPayupayment($orderIdd)){
	$order = Mage::getModel('sales/order')->loadByIncrementId($orderIdd);
	$state = 'processing';
	$status1 = $state;
	$comment = "Status changed to processing Through Mobileapp and Payu ";
	$isCustomerNotified = false; //whether customer to be notified
	$order->setState($state, $status1, $comment, $isCustomerNotified);    
	$order->save();
	$order->sendNewOrderEmail();
	$status = 1;
	}
}
$status = array('status'=>$status);
$ku = json_encode($status);
echo $ku;exit;
}

public function verifyPayupayment($transactionid)
{
      //$transactionid = "100128123";
      // $key = "C0Dr8m";
     //$salt = "3sf0jURk";
	$key = "ZwzD3B";
	$salt = "8HLsQdw4";
	$command = "verify_payment";
	$hash_str = $key  . '|' . $command . '|' . $transactionid . '|' . $salt ;
	$hash = strtolower(hash('sha512', $hash_str));
    // $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command, 'var2' => $salt, 'var3' => $amount);
       $r = array('key' => $key , 'hash' =>$hash , 'var1' => $transactionid , 'command' => $command);
   
		$qs= http_build_query($r);
		$wsUrl = "https://info.payu.in/merchant/postservice.php";
	//	$wsUrl = "https://info.payu.in/merchant/postservice.php";
		$c = curl_init();
		curl_setopt($c, CURLOPT_URL, $wsUrl);
		curl_setopt($c, CURLOPT_POST, 1);
		curl_setopt($c, CURLOPT_POSTFIELDS, $qs);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
		$o = curl_exec($c);
		if (curl_errno($c)) {
			   $sad = curl_error($c);
			   throw new Exception($sad);
			}
			curl_close($c);
			$valueSerialized = @unserialize($o);
			if($o === 'b:0;' || $valueSerialized !== false) {
				if($valueSerialized['transaction_details'][$transactionid]['status'] == 'success')
	                return 1;else return 0;
				}
		return 0;

}


public function viewSimilarProductsAction(){
exit;
 $skuProduct = $this->getRequest()->getParam('sku');

$url = 'https://craftsvilla.madstreetden.com/more';
        $fields = array('productID' => $skuProduct,'appID' =>'2062685169','appSecret'=>'ckjp6jr5w5go6ehi2ssu','numResults'=>'5');
        $fields_string = '';

        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
         }

        rtrim($fields_string, '&');
        $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL, $url);
                curl_setopt($ch,CURLOPT_POST, count($fields));
                curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);

        $result = json_decode($result,true);
$hlp = Mage::helper('generalcheck');
$mainconn = $hlp->getMaindbconnection();
$product_id = array();


foreach($result['data'] as $mrktngProduct_sku){

                $mrktngproduct_SubQuery = mysql_query("SELECT `entity_id` FROM `catalog_product_entity` WHERE `sku` = '".$mrktngProduct_sku."'",$mainconn);

                $mrktngproduct_SubResult = mysql_fetch_array($mrktngproduct_SubQuery);

                array_push($product_id, $mrktngproduct_SubResult[0]);

                }

mysql_close($mainconn);


curl_close($ch);

        $allproduct_count = count($product_id);
$view_id = $product_id;
$i = 0;
$_columnCount = 4;
$prod_count = min(count($view_id),5);
$k = 1;
$product_qty = array();
for($m=1;$m<$prod_count;$m++)
        {
        if($view_id[$m] != null){
                array_push($product_qty,$view_id[$m]);
                }
}


        if(!empty($product_qty[$k])){

                $bodyhtml .= "<div class='clear'></div><br><br><div><h2>View Similar Products</h2><br></div>

                <div style='margin-left:auto;margin-right: auto;'>";


foreach($product_qty as $_resultProduct)
        {
$product = Mage::helper('catalog/product')->loadnew($_resultProduct);
   if($product->getPrice() != null)
{
                if ($i++%$_columnCount==0)
                        {
                                $bodyhtml .= "<ul class='products-grid first odd you_like'>";
                        }

                                $prodImage = Mage::helper('catalog/image')->init($product, 'image')->resize(500,500);
                                $firstlast = "";
                        if(($i-1)%$_columnCount==0)
                {
                                $firstlast = "first";
                }

                 elseif($i%$_columnCount==0){
                        $firstlast = "last";
                }
                        $bodyhtml .= "<li class='item ".$firstlast."'>";
                        $bodyhtml .= "<div >";
                        $bodyhtml .= "<a class='' title='".$prodImage."' href='".$product->getProductUrl()."'><img width='100%' height='auto' src='$prodImage' alt=''></a>";
                        $bodyhtml .= "<p class='shopbrief' ><a title='' href='".$product->getProductUrl()."'>".substr($product->getName(),0,25)."..</a></p>";

                                        $storeurl = '';
                                        $vendorinfo= Mage::helper('udropship')->getVendor($product);
                                        $storeurl = Mage::helper('umicrosite')->getVendorUrl($vendorinfo->getData('vendor_id'));
                                        $storeurl = substr($storeurl, 0, -1);
                                        if($storeurl != '') {

                        $bodyhtml .= "<p class='vendorname'>by <a target='_blank' href='".$storeurl."'>".$vendorinfo->getVendorName()."</a></p>";
                 }
if(!$product->getSpecialPrice()):
                        $bodyhtml .= "<div class='products price-box' > <span id='product-price-80805' class='regular-price'> <span class='price 123' > Rs. ".number_format($product->getPrice(),0)."</span> </span> </div>";
                else:
                        $bodyhtml .= "<div class='products price-box'>";
                        $bodyhtml .= "<p class='old-price' > <span class='price-label'></span> <span id='old-price-77268' class='price' >Rs. ".number_format($product->getPrice(),0)."</span> </p>";
                        $bodyhtml .= "<p class='special-price' > <span class='price-label'></span> <span id='product-price-77268' class='price' >Rs. ".number_format($product->getSpecialPrice(),0)."</span> </p>";
                        $bodyhtml .= "</div>";
                endif;
                        $bodyhtml .= "<div class='clear'></div>";
                        $bodyhtml .= "</div>";
                        $bodyhtml .= "</li>";

                 if ($i%$_columnCount==0){
                        $bodyhtml .= "</ul>";
                }
}
} }$k++;

        $bodyhtml .= "</div>";

echo $bodyhtml;exit;

}
}

