<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product View block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
class Mage_Catalog_Block_Product_View extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
//New function added for call meta title in header page on dated 21-03-2014-------by dileswar     
	   /* $this->getLayout()->createBlock('catalog/breadcrumbs');
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            //$productId = $this->getProduct()->getEntityId();
			$productId = (int) $this->getRequest()->getParam('id');
			$product = Mage::getModel('catalog/product')
            ->load($productId);
			Mage::unregister('product');
			Mage::register('product', $product);
            $category_arr = $product->getCategoryIds();
            
    		unset($product_category_name);
    		unset($product_vendor_name);

     		if($category_arr[0] == 2)
     		{
     			$category = Mage::getModel('catalog/category')->load($category_arr[1]);
     		}
     		else {
     			$category = Mage::getModel('catalog/category')->load($category_arr[0]);
     		}
    
     		$product_category_name = $category->getName();
     
     		$product_vendor_name = Mage::helper('udropship')->getVendorName($product->getUdropshipVendor());
     		
            //$title = $product->getMetaTitle()."-".$product_category_name."-".$product_vendor_name;
            $title = $product->getName()."-".$product_category_name."-".$product_vendor_name;
            if ($title) {
                $headBlock->setTitle($title);
            }
            $keyword = $product->getMetaKeyword();
            $currentCategory = Mage::registry('current_category');
            if ($keyword) {
                $headBlock->setKeywords($keyword);
            } elseif($currentCategory) {
                $headBlock->setKeywords($product->getName());
            }
            $description = $product->getMetaDescription();
            if ($description) {
                $headBlock->setDescription( ($description) );
            } else {
                $headBlock->setDescription(Mage::helper('core/string')->substr($product->getDescription(), 0, 255));
            }
            if ($this->helper('catalog/product')->canUseCanonicalTag()) {
                $params = array('_ignore_category'=>true);
                $headBlock->addLinkRel('canonical', $product->getUrlModel()->getUrl($product, $params));
            }
        }*/

        return parent::_prepareLayout();
    }
	
	 
    /**
     * Retrieve current product model
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!Mage::registry('product') && $this->getProductId()) {
            $product = Mage::getModel('catalog/product')->load($this->getProductId());
            Mage::register('product', $product);
        }
        return Mage::registry('product');
    }

    /**
     * Check if product can be emailed to friend
     *
     * @return bool
     */
    public function canEmailToFriend()
    {
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        return $sendToFriendModel && $sendToFriendModel->canEmailToFriend();
    }

    /**
     * Retrieve url for direct adding product to cart
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = array())
    {
        if ($this->getRequest()->getParam('wishlist_next')){
            $additional['wishlist_next'] = 1;
        }

        return $this->helper('checkout/cart')->getAddUrl($product, $additional);
    }

    /**
     * Get JSON encripted configuration array which can be used for JS dynamic
     * price calculation depending on product options
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $config = array();
        if (!$this->hasOptions()) {
            return Mage::helper('core')->jsonEncode($config);
        }

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest(false, false, false);
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $defaultTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_request = Mage::getSingleton('tax/calculation')->getRateRequest();
        $_request->setProductClassId($this->getProduct()->getTaxClassId());
        $currentTax = Mage::getSingleton('tax/calculation')->getRate($_request);

        $_regularPrice = $this->getProduct()->getPrice();
        $_finalPrice = $this->getProduct()->getFinalPrice();
        $_priceInclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice, true);
        $_priceExclTax = Mage::helper('tax')->getPrice($this->getProduct(), $_finalPrice);

        $config = array(
            'productId'           => $this->getProduct()->getId(),
            'priceFormat'         => Mage::app()->getLocale()->getJsPriceFormat(),
            'includeTax'          => Mage::helper('tax')->priceIncludesTax() ? 'true' : 'false',
            'showIncludeTax'      => Mage::helper('tax')->displayPriceIncludingTax(),
            'showBothPrices'      => Mage::helper('tax')->displayBothPrices(),
            'productPrice'        => Mage::helper('core')->currency($_finalPrice, false, false),
            'productOldPrice'     => Mage::helper('core')->currency($_regularPrice, false, false),
            /**
             * @var skipCalculate
             * @deprecated after 1.5.1.0
             */
            'skipCalculate'       => ($_priceExclTax != $_priceInclTax ? 0 : 1),
            'defaultTax'          => $defaultTax,
            'currentTax'          => $currentTax,
            'idSuffix'            => '_clone',
            'oldPlusDisposition'  => 0,
            'plusDisposition'     => 0,
            'oldMinusDisposition' => 0,
            'minusDisposition'    => 0,
        );

        $responseObject = new Varien_Object();
        Mage::dispatchEvent('catalog_product_view_config', array('response_object'=>$responseObject));
        if (is_array($responseObject->getAdditionalOptions())) {
            foreach ($responseObject->getAdditionalOptions() as $option=>$value) {
                $config[$option] = $value;
            }
        }

        return Mage::helper('core')->jsonEncode($config);
    }

    /**
     * Return true if product has options
     *
     * @return bool
     */
    public function hasOptions()
    {
        if ($this->getProduct()->getTypeInstance(true)->hasOptions($this->getProduct())) {
            return true;
        }
        return false;
    }

    /**
     * Check if product has required options
     *
     * @return bool
     */
    public function hasRequiredOptions()
    {
        return $this->getProduct()->getTypeInstance(true)->hasRequiredOptions($this->getProduct());
    }

    /**
     * Define if setting of product options must be shown instantly.
     * Used in case when options are usually hidden and shown only when user
     * presses some button or link. In editing mode we better show these options
     * instantly.
     *
     * @return bool
     */
    public function isStartCustomization()
    {
        return $this->getProduct()->getConfigureMode() || Mage::app()->getRequest()->getParam('startcustomization');
    }

    /**
     * Get default qty - either as preconfigured, or as 1.
     * Also restricts it by minimal qty.
     *
     * @param null|Mage_Catalog_Model_Product
     *
     * @return int|float
     */
    public function getProductDefaultQty($product = null)
    {
        if (!$product) {
            $product = $this->getProduct();
        }

        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }

public function desktopProductView()
	{

$view_class = $this;
//$view_class1 = Mage::getData()->getLayout()->getBlockSingleton('catalog/product_view_media');
//echo $view_class1->getChildHtml('media');exit;
$desktopView = '';

$desktopView .= '
	<div id="wishthis">
	<div id="welcome_img" class="popuphead"><span class="popuoheading">Add to Wishlist</span></div>
	<div id="bg_welcome" class="shortpop">
		<ul class="followshop">
			<li>
				<h2 class="greyheading">Already Member</h2>
				<a href="#login" class="spriteimg bluesmallbtn fancybox">Login</a></li>
			<li class="nobor">
				<h2 class="greyheading">New Customer </h2>
				<a class="spriteimg bluesmallbtn fancybox" href="#signUpForm">Sign Up</a></li>
		</ul>
	<p class="clr"></p>
	<p align="center" class="clr margintoptwenty"><span class="required">Note:</span>To Wish this product You need to login or register.</p>
	</div>
	</div>

	<script src="http://static.ak.fbcdn.net/connect.php/js/FB.Share" type="text/javascript"></script>
	<script src="http://platform.twitter.com/widgets.js" type="text/javascript" ></script>';

	$_product = $view_class->getProduct();
//echo "<pre>";print_r($_product);exit;
	$my_tier_rocks = $view_class->getTierPrices($_product);
	$_currencyCodeProduct = Mage::app()->getStore()->getCurrentCurrencyCode();
	$productIdCache = $_product->getId();
	$viewProduct_id = $_product->getId();
//echo $productIdCache;exit;
	$CacheIdProduct = "productcache-$productIdCache-currency-$_currencyCodeProduct";
	$lifetime = 864000;
	$_helpv = $view_class->helper('udropship');
	$vendorIfo = $_helpv->getVendor($_product);
	$cats = $_product->getCategoryIds();
	$catid2 = array();
	foreach ($cats as $category_id){
	
	    $_cat = Mage::getModel('catalog/category')->load($category_id) ;
	    $catid2[] = $_cat->getName();
	}
	$catTitle = $catid2[1];

	$skuProduct = $_product->getSku();
	$qty = $_product->getQty();
	$productshortdescext = 'Online Shopping for ';
	$shopdesc = $vendorIfo->getData('shop_description');
	$productnameext = ' - Online Shopping for '.$catTitle.' by '.$vendorIfo->getData('vendor_name');
	$newProducTitle = $_product->getProductName().$productnameext;

$desktopView .= '<div id="messages_product_view">'.$view_class->getMessagesBlock()->getGroupedHtml().'</div>';
       
        $_helper = $view_class->helper('catalog/output');
        $storeId = (int) $view_class->getStoreId();

$desktopView .= '<script type="text/javascript">
    var optionsPrice = new Product.OptionsPrice('.$view_class->getJsonConfig().')
</script>
	<div class="detailCotainer">';

		if($_product->getOptions()):
			$multiData='enctype="multipart/form-data"';
		else:
			$multiData='';
		endif;

$desktopView .= '<form action="'.$view_class->getAddToCartUrl($_product).'" method="post" id="product_addtocart_form"'.$multiData.'>
	<div class="details">
	<p class="heading_css">'.$_helper->productAttribute($_product, $newProducTitle, 'name').'</p>
	<div class="breadcrumbs_bottom"></div>
	<p class="sort_description">';
	      if ($_product->getShortDescription()):
		   $desktopView .= $_helper->productAttribute($_product, nl2br($productshortdescext.$_product->getShortDescription().' SKU :'.$skuProduct), 'short_description');
	      endif;

$desktopView .= '</p>
		 <ul class="shipcost">';


$_shipcost = array();
		if ($_product->getTypeId() == "configurable") {
			$associated_products = $_product->getTypeInstance()->getUsedProducts();
			$i=0;
			$j=0;
		$stockConfig=array();    
			foreach ($associated_products as $assoc)
			{

				$assocProduct =Mage::getModel('catalog/product')->load($assoc->getId());
				$__shipcost = $assocProduct->getData('shippingcost');
				$stockConfig .=$assocProduct->isSaleable();
				$__intershipcost = $assocProduct->getData('intershippingcost');

				if (empty($__shipcost))
					$_shipcosta[$i]= 0;
				else
					$_shipcosta[$i]= $__shipcost;
				$i++;

				if (empty($__intershipcost))
					$__intershipcosta[$j]= 0;
				else
					$__intershipcosta[$j]= $__intershipcost;
				$j++;

			}

		$_shippingcost = max($_shipcosta);

		$_intershippingcost = max($__intershipcosta);

		} else 
		{
		$_shippingcost = $_product->getData('shippingcost');

		$_intershippingcost = $_product->getData('intershippingcost');  

		}  

$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		if($_shippingcost>0)
		{
			$_shippingcost = Mage::helper('directory')->currencyConvert($_shippingcost, $baseCurrencyCode, $currentCurrencyCode);
		}

		if($_intershippingcost>0)
		{
			$_intershippingcost = Mage::helper('directory')->currencyConvert($_intershippingcost, $baseCurrencyCode, $currentCurrencyCode);
		}

		if(empty($_shippingcost) || $_shippingcost=='0')
		{
			$desktopView .='<li>Shipping Cost in India : <span>FREE</span></li>';
		}    
		else
		{
			$_currSym=Mage::app()->getLocale()->currency($currentCurrencyCode)->getSymbol();
			$_numformatShippingCost=number_format($_shippingcost,2);
			$desktopView .='<li>Shipping Cost in India ('.$_currSym.'): <span>'.$_numformatShippingCost.'</span></li>';
		}

		if(!empty($_intershippingcost) && $_intershippingcost!='0'){		

			$_currSym=Mage::app()->getLocale()->currency($currentCurrencyCode)->getSymbol();
			$_numforInterShippingCost=number_format($_intershippingcost,2);
			$desktopView .='<li>Shipping Cost Outside of India ('.$_currSym.'): <span>'.$_numforInterShippingCost.'</span></li>';
		}


        $_helpm = $view_class->helper('umicrosite');
	
        $storeurl = $_helpm->getVendorUrl($vendorIfo->getData('vendor_id'));
		if(substr($storeurl, -1) == '/')
		{
			$storeurl = substr($storeurl, 0, -1);
		}
        $desktopView .= '<li>See similar products from this Seller: ';
		if($storeurl):
		    $storeurl=$storeurl;
		else:
		    $storeurl='#';
		endif;
        $desktopView .=
            '<span><a href="'.$storeurl.'" target="_blank">.'.$vendorIfo->getData('vendor_name').'</a></span>
        </li>';
		if($_product->getInternationalShipping()==1)
		    $desktopView .='<li>This Product is available for International Shipping.</li>';
		else
		    $desktopView .='<li>This Product is not available for shipping outside India.</li>';

			$vendorAdminPaymentMethods = $vendorIfo->getVendoradminPaymentMethods();
			$vendorCOD = true;
			foreach ($vendorAdminPaymentMethods as $method)
				{
				   if($method == 'cashondelivery')
					{
						$vendorCOD = false;
						break;
					}
				
				}


		$my_tier_rocks = $view_class->getTierPrices($_product);
		if (!empty($my_tier_rocks))
			{
			$my_tier_rocks = $view_class->getTierPrices($_product);
			foreach($my_tier_rocks as $_tierprice)
				{
				$my_tier_rocks1 = $_tierprice['formated_price_incl_tax'];
				$my_tier_rocks2 = $_tierprice['price_qty'];
				}
			$desktopView .='<li>Wholesale Price : <span class="price">'.$my_tier_rocks1.'</span> &nbsp;Min Order Qty : <span class="price">'.$my_tier_rocks2.'</span></span></li>';
			}
$desktopView .='</ul>
	<div id="dtlPrice">
	<div id="dtlSize">';
          	$group = $_product->isComposite();
		if ($group != true) { 

		} else {

			if ($view_class->hasOptions()):
				$desktopView .= $view_class->getChildChildHtml('container2', '', true, true);
			endif;
		}
$desktopView .='</div>
        <div id="dtlQty">';
		
	
            if(!$_product->isGrouped()):
$desktopView .='<label for="qty">'.$view_class->__('Quantity:').'</label>';

$desktopView .='<input type="text" name="qty" id="qty" maxlength="12" value="1" />';
            endif;

$desktopView .=
	'<div id="numbers">
		<div id="num_up">
			<script type="text/javascript">
				var cur_value_plus = jQuery("#qty").attr("value");
				jQuery("#num_up").click(function(){
					jQuery(".num_down").css("display","block");
					cur_value_plus++;
					jQuery("#qty").attr("value",cur_value_plus);
				});
			
			</script> 
		</div>
		<div id="num_down" class="num_down" style="display:none;">			
			<script type="text/javascript">
				var cur_value_plus = jQuery("#qty").attr("value");
			
				jQuery(".num_down").click(function(){
					cur_value_plus--;
					jQuery("#qty").attr("value",cur_value_plus);
					if(cur_value_plus==1){
					jQuery(".num_down").css("display","none");
					}
				});
			</script> </div>
	</div>';

$desktopView .= '<div id="quantity"><input type="hidden" name="pid" id="qtyproductid12" value="'.$productIdCache.'" /></div>';
$desktopView .='</div>
	<div id="Price">'.$view_class->getPriceHtml($_product,true).' 
	</div>
	<br />
	<div style="clear:both;"></div>
	<div class="fleft">';

	$stockConfigProd=strpos($stockConfig,'1');

		if($_product->getIsInStock() == 1 && $_product->getTypeId() != "configurable"){

			$desktopView .= '<br/><button class="newaddcartbutton12092014" unbxdattr="AddToCart" unbxdparam_sku="'.$skuProduct.'" type="submit">BUY NOW</button>';
		}
		elseif($stockConfigProd!=false && $_product->getTypeId() == "configurable"){

			$desktopView .= '<br/><button class="newaddcartbutton12092014" unbxdattr="AddToCart" unbxdparam_sku="'.$skuProduct.'" type="submit" >BUY NOW</button>';
		}        
		else{
			$desktopView .= '<p class="spriteimg pro_soldout" title="Sold Out"></p>';
		}        
			$desktopView .= '</div>';

$desktopView .='<div id="dtlSocialLink">
             Confused?';
           
                $desktopView .='<a rel="nofollow" class="contactseller" onclick="contactseller()" >Contact Seller</a>';
          
		$metaTitle = $_product->getData('meta_title');

$desktopView .='</div>



	<div class="clear"></div><br>
	<div class="tabs_social">
	<ul id="socielicon" class="visible fleft">';
	$_wishlistSubmitUrl = $view_class->helper('wishlist')->getAddUrl($_product);

$desktopView .='<li class="wishit" onclick="wishthisprod()" ><input type="button" id="imgwish" class="wishit_buttons" /></li>';
$desktopView .='<li><a class="addthis_button_facebook_like" fb:like:layout="button_count" addthis:url="'.$_product->getProductUrl().'"></a></li>';
$desktopView .='<li><a class="addthis_button_tweet"></a><script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4f34adfd2fa3a766"></script></li>';
$prodImage = $view_class->helper('catalog/image')->init($_product, 'image')->resize(166, 166);
$desktopView .='<li><a href="http://pinterest.com/pin/create/button/?url='.$_product->getProductUrl().'&media='.$prodImage.'&description='.$metaTitle.'" class="pin-it-button" count-layout="none" target="_blank"><img border="0" src="//assets.pinterest.com/images/PinExt.png"/></a></li>';
$desktopView .='</ul>
    </div>
	
             <p class="clr"></p>';
			  if ($vendorCOD)
				{
					$desktopView .= "<div id='cash'>";
 $desktopView .= "<div id='craftsvilla_cash_on_delivery'><div class='active'>Cash On Delivery Available</div></div><br>";

$desktopView .= "</form><form action='#pincode' method='POST'>
 <br><div id='pincode_form'>
 <label id='check'><font size='-1'>
 Check Cash On Delivery Below:</font></label><br>
 <input type='text' name='pincode_value' placeholder='Enter Pincode' id='pincode_value' style='width:90px;height:30px'>
  
<button  class='craftsvilla_pincode' type='submit' onClick='submitform();return false;'> CHECK </button>
</div><br>";
$desktopView .="<div id='pincode' style='display:none'></div>";
$desktopView .= "</div>";
$desktopView .='<div id="display"></div>';
		}

$desktopView .='<div class="clearer"></div><br>';
$desktopView .='<div class="clearer"></div><br>';
$desktopView .= '<div id="policy"><img src="'.Mage::getDesign()->getSkinUrl('images/5-shipping-icon1.png').'" class="hovertext" />
<div><p><strong>Shipping Policy:</strong><br>
  We deliver in India  within 10 days and outside of India within 21 days.<br> Currently our average delivery time in India is around 4 days and outside<br> of India within 18 days. You will get email and sms of tracking details of <br>domestic shipments within 5-7 days. You can also request seller for<br> expedited shipping by clicking the "Contact Seller" button on the product<br> page after you place the order. We deliver to over 100 countries<br> globally including USA, UK, Australia and Canada. </p>
</div>&nbsp;&nbsp;
<img src="'.Mage::getDesign()->getSkinUrl('images/return-icon.jpg').'" class="hovertext1" />
<div><p><strong>Refund Policy:</strong><br>
We have a friendly return policy whereby we refund for products which are damaged,<br> broken, have manufacturing defects, wrong size or does not match with photo/description<br> as given on Craftsvilla. The customer/buyer has to get back to Craftsvilla customer care through<br> phone/email within 24 hours of receipt of damaged goods or goods not meeting description.<br> Craftsvilla will then log a dispute and initiate a process of resolution between the artisan/designer<br> and the customer. Customer will have to send an image of product on email in case she is claiming<br> that the product is damaged or not meeting description. </p>
</div>&nbsp;
<img src="'.Mage::getDesign()->getSkinUrl('images/40by40.png').'" class="hovertext2" />
<div><p><strong>100% Secured Payments:</strong><br>
We guarantee that your payment is 100% secured with us as your payment details<br> are never shared to anyone during the payment process. You transparently pay through<br> our payment gateway or Paypal and all the data is transferred through high level encryption<br> technology. Please note you pay to "Kribha Handicrafts Pvt Ltd" since that is the company <br>which owns Craftsvilla.com brand and online store. Craftsvilla.com is a brand you can<br> 100% trust. We have been convered widely in Indian and Western Media including newspapers<br> like Timesofindia, Economic Times, Hindu ad TV channels like CNBC, ETNow and Zee and<br> Western Media like Dow Jones and Techcruch.</p>
</div></div>';
$desktopView .=  '<div id="dtlGift">';
$couponread = Mage::getSingleton('core/resource')->getConnection('core_read');

			$coupon="select * from `salesrule` where `vendorid`='".$vendorIfo['vendor_id']."' AND `is_active`=1 AND `to_date` > NOW()";
			$result=$couponread->query($coupon)->fetchAll();
			if($result)
			{
				$vendorcoupon['code']=array();
				$vendorcoupon['description']=array();
				$vendorcoupon['vendor_name']=array();
				$ruleid = $result[0]['rule_id'];
				$coupondesc = "select `code` from `salesrule_coupon` where `rule_id` = '".$ruleid."'";
				$resultc=$couponread->query($coupondesc)->fetchAll();
				$vendorcoupon['vendor_name'] = substr($result[0]['vendor_name'],0,30);
				$vendorcoupon['code'] =$resultc[0]['code'];
				$vendorcoupon['description'] =$result[0]['description'];
				$expirydate = $result[0]['to_date'];
		
				 if(sizeof($vendorcoupon['vendor_name'])>0)
		        {  
					$desktopView .= '<div id="dtlCompanyLogo"><table border="1"><tr><td rowspan="2"><div id="craftsvilla_coupons_product"><div class="active">Offers</div></div></td>';
					$desktopView .= '<td style="padding-right: 6px;padding-left: 6px;vertical-align: center"><font size="2px">COUPON</font>: <strong>'.$vendorcoupon['code'].'</strong></td></tr><tr><td style="padding-right: 6px;padding-left: 6px;"><font size="1px"><strong>'.$vendorcoupon['description'].'</strong></td></tr></table>';
					$desktopView .= '</div>';
			}
			}
          
$desktopView .='</div>
		<div id="dtlCompanyLogo">';
		$url= $_helpm->getVendorUrl($data->vendor_id);
		$storeurl = $_helpm->getVendorUrl($vendorIfo->getData('vendor_id'));
		$vendorLogo = $vendorIfo->getShopLogo()!='' ? $vendorIfo->getShopLogo() : 'vendor/noimage/noimage.jpg';

		$Feedback = Mage::getModel('feedback/vendor_feedback')->getCollection()
		->addFieldToFilter('vendor_id',$vendorIfo->getData('vendor_id'))
		->addFieldToFilter('feedback_type',1)
		->addExpressionFieldToSelect('total','count({{feedback_id}})','feedback_id');
$desktopView .='          
          <span class="seller_image"><a href="'.(substr($storeurl, 0, -1)).'" target="_blank"><img src="'.($_helpm->getResizedUrl($vendorLogo,70)).'" /></a></span>
          <div class="fleft seller-right">';
$desktopView .='<p class="vendorName"><a href="'.(substr($storeurl, 0, -1)).'" target="_blank">'.($vendorIfo['vendor_name']).'</a></p>
            <span class="dtlLink-select">';

                $countries = Mage::getModel('directory/country_api')->items();
                $country = '';
                $region = '';
                $country = $vendorIfo["country_id"];
                if($country!=''){
                    $state_list = Mage::getModel('directory/region_api')->items($country);
                    $region = $vendorIfo['region_id'];
                    if($region!=''){
                        foreach($state_list as $key => $value) {
                            if ($value['region_id'] == $region) {
                                $state_name = $value['name'];
                                break;
                            }
                        }
                    }
                } 
$desktopView .='<span>'.($vendorIfo['city']).','.$state_name.'</span></span>';


            $vendorTotaldata = Mage::getModel('udropship/vendor')->load($vendorIfo['vendor_id']);
			$feedbackRatingVendor = $vendorTotaldata->getData('feedback_vendorrating');


			$positiveFeedbackPercent = $feedbackRatingVendor;
            
$desktopView .='<a'.$feedbck.'>
            <div class="classification">
              <div class="tooltip">
                <p class="arowtooltip spriteimg"></p>';
$desktopView .='Feedback:'.$_totalFeedback.",".$positiveFeedbackPercent.'% pos. </div>
              <div class="cover"></div>
              <div class="progress" style="width:'.$positiveFeedbackPercent.'%;"></div>
            </div>
            </a>';
//$desktopView .='</div> <div class="clr follo-countainer">';

$desktopView .='<p class="links"><a rel="nofollow" href="'.$storeurl.'thoughtyard/policies/" target="_blank">Seller Policies</a></p>
	</div>
	</div>';
$desktopView .= $view_class->getChildHtml('other');
			if ($_product->isSaleable() && $view_class->hasOptions()):

			endif;

$desktopView .='<div class="clearer"></div>';

$desktopView .='</div>
      </div>';
 
$desktopView .='<div class="product-view setwidth">
      <div>
        <div>
		   <input type="hidden" name="product" value="'.$_product->getId().'" />
          <input type="hidden" name="related_product" id="related-products-field" value="" />
        </div>';
$desktopView .='<div class="product-img-box">'.$view_class->getChildHtml('media').'
        </div>
      </div>
	 <div id="dtlText">';
			foreach ($view_class->getChildGroup('detailed_info', 'getChildHtml') as $alias => $html):
				if ($title = $view_class->getChildData($alias, 'title')):
					$desktopView .= $view_class->escapeHtml($title);
			endif;
$desktopView .= $html;
        		endforeach;
$desktopView .='</div>
	</div>

	<div class="clear"></div>

	</form>
	</div> ';

echo $this->getChildHtml('contactseller');
/*
$hlp = Mage::helper('Catalog');

$recomended_product = $hlp->recomendedDesktopProducts($viewProduct_id);


$hlp = Mage::helper('Mobileapp');
echo $hlp->displaySlides($skuProduct);exit;
$otherProducts = 1;
	if($otherProducts){
		$view_similar_product = $hlp->viewSimilarProducts($skuProduct);
	}
*/



$desktopView .="<div><script type='text/javascript'>

 function displaySlides(){

      var params = new Object();
         
      params.productID = '".$skuProduct."';

      jQuery.ajax({
                  url: 'http://www.craftsvilla.com/mobileapp/index/viewSimilarProducts?sku=".$skuProduct."',
                  type: 'GET',
                  
                  crossDomain: true,


                  success: function (data, textStatus, xhr) {

                      jsonData = data;
	document.getElementById('view_similar_products').innerHTML = jsonData;
                     
                  },

                  error: function (xhr, textStatus, errorThrown) {

                      console.log('Error at ajax call ' + errorThrown);
                  }
              });
      };
window.onload = displaySlides();
</script></div>";

//$desktopView .= "<div onload='displaySlides('".$skuProduct."');"."'></div>";

$hlp = Mage::helper('Catalog');

$recomended_product = $hlp->recomendedDesktopProducts($viewProduct_id);

$view_similar_products = "<div id='view_similar_products'></div>";

$cacheContentProduct = $desktopView.$recomended_product.$view_similar_products;
return $cacheContentProduct;

	}
	  
	public function mobileProductView()
	{

$view_class = $this;
$baseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$readCount =  Mage::getSingleton('core/resource')->getConnection('core_read');
$countVendorQuery = "SELECT count(*) as `cntvnd` FROM `udropship_vendor`";
$resultVendor =  $readCount->query($countVendorQuery)->fetch();
$resultVendorCount = $resultVendor['cntvnd'];
$countProductsQuery = "SELECT count(*) as `cntprd` FROM `catalog_product_entity`";
$resultProducts = $readCount->query($countProductsQuery)->fetch();
$resultProductsCount = $resultProducts['cntprd'];
$resultProductsCountstring = number_format($resultProductsCount);

$mobileView = '';

	$_product = $view_class->getProduct();
	$my_tier_rocks = $view_class->getTierPrices($_product);
	$_currencyCodeProduct = Mage::app()->getStore()->getCurrentCurrencyCode();
	$productIdCache=$_product->getId();
	$viewProduct_id = $_product->getId();
	$CacheIdProduct = "productcache-$productIdCache-currency-$_currencyCodeProduct";
	$lifetime = 864000;
	$_helpv = $view_class->helper('udropship');
	$vendorIfo = $_helpv->getVendor($_product);
	$cats = $_product->getCategoryIds();
	$catid2 = array();
		foreach ($cats as $category_id){
			$_cat = Mage::getModel('catalog/category')->load($category_id) ;
			$catid2[] = $_cat->getName();
		}
	$catTitle = $catid2[1];
	$skuProduct = $_product->getSku();
	$qty = $_product->getQty();
	$productshortdescext = 'Online Shopping for ';
	$shopdesc = $vendorIfo->getData('shop_description');
	$productnameext = ' - Online Shopping for '.$catTitle.' by '.$vendorIfo->getData('vendor_name');
	$newProducTitle = $_product->getProductName().$productnameext;

$mobileView .= '<div id="messages_product_view" style="z-index:100;">'.$view_class->getMessagesBlock()->getGroupedHtml().'</div>';

        $_helper = $view_class->helper('catalog/output');
        $storeId = (int) $view_class->getStoreId();
$mobileView .=
	'<script type="text/javascript">
	    var optionsPrice = new Product.OptionsPrice('.$view_class->getJsonConfig().')
	</script>';

$mobileView .= '<div class="detailCotainer1">';
		if($_product->getOptions()):
		    $multiData='enctype="multipart/form-data"';
		else:
		    $multiData='';
		endif;
$mobileView .='<form action="'.$view_class->getAddToCartUrl($_product).'" method="post" id="product_addtocart_form																																																						"'.$multiData.'>';
$mobileView .='<div class="bunch"><div class="prdctimg">';

$mobileView .='<div class="product-view1 setwidth1">
	<div>
	<div>
		<input type="hidden" name="product" value="'.$_product->getId().'" />
		<input type="hidden" name="related_product" id="related-products-field" value="" />
	</div>';

$mobileView .='<div class="product-img-box1">
	<img src="'.$view_class->helper('catalog/image')->init($_product, 'image').'" width="100%" >
	</div>
	</div>
	</div></div><br><br>';

 $mobileView .='<div id="dtlQty1">';
            	if(!$_product->isGrouped()):
			$mobileView .='<label for="qty" style="font-size: 25px;">'.$view_class->__('Quantity:').'</label>&nbsp&nbsp&nbsp';
$mobileView .='<input type="text" name="qty" id="qty" maxlength="12" value="1" style="width:20%; padding:10px;font-size:30px" />';
            endif;
   
$mobileView .=
'<div id="numbers">
	<script type="text/javascript">
	var cur_value_plus = jQuery("#qty").attr("value");
	jQuery("#num_up").click(function(){
		jQuery(".num_down").css("display","block");
		cur_value_plus++;
		jQuery("#qty").attr("value",cur_value_plus);
	});
	jQuery(".num_down").click(function(){
		cur_value_plus--;
		jQuery("#qty").attr("value",cur_value_plus);
		if(cur_value_plus==1){
		jQuery(".num_down").css("display","none");
		}
	});
	</script> 
</div>
</div>';

$mobileView .= '<div id="quantity"><input type="hidden" name="pid" id="qtyproductid12" value="'.$productIdCache.'" /></div>';

$mobileView .= '<div id="Price1">'.$view_class->getPriceHtml($_product,true).' 
        </div>';

$mobileView .='<div class="prdctdetail">';

$mobileView .='<div class="details" style="width:100%">
      <p class="heading_css" style="font-size: 30px; padding-top: 25px;">'.$_helper->productAttribute($_product, $newProducTitle, 'name').'</p>
      <div class="breadcrumbs_bottom"></div>
      <p class="sort_description" style="font-size: 22px;">';
$mobileView .= 'SKU :  '.$skuProduct;
$mobileView .='</p>
      <ul class="shipcost">';

 $_shipcost = array();
      if ($_product->getTypeId() == "configurable") {
      $associated_products = $_product->getTypeInstance()->getUsedProducts();
    
	      $i=0;

	      $j=0;

	  $stockConfig=array();    
          foreach ($associated_products as $assoc)
            {

				 $assocProduct =Mage::getModel('catalog/product')->load($assoc->getId());
		         $__shipcost = $assocProduct->getData('shippingcost');
                 $stockConfig .=$assocProduct->isSaleable();

                $__intershipcost = $assocProduct->getData('intershippingcost');

		if (empty($__shipcost))
			$_shipcosta[$i]= 0;
		else
			$_shipcosta[$i]= $__shipcost;
		$i++;

		if (empty($__intershipcost))
			$__intershipcosta[$j]= 0;
		else
			$__intershipcosta[$j]= $__intershipcost;
		$j++;

            }
            
            $_shippingcost = max($_shipcosta);
            $_intershippingcost = max($__intershipcosta);

        } else 
        {
            $_shippingcost = $_product->getData('shippingcost');
            $_intershippingcost = $_product->getData('intershippingcost');  

        }  
        
        $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
		if($_shippingcost>0)
		{
			$_shippingcost = Mage::helper('directory')->currencyConvert($_shippingcost, $baseCurrencyCode, $currentCurrencyCode);
		}
		
        if($_intershippingcost>0)
		{
			$_intershippingcost = Mage::helper('directory')->currencyConvert($_intershippingcost, $baseCurrencyCode, $currentCurrencyCode);
		}
		
		if(empty($_shippingcost) || $_shippingcost=='0')
                {
                    $mobileView .='<li style="font-size: 14px;">Shipping Cost in India : <span>FREE</span></li>';
                }    
                else
                {
                    $_currSym=Mage::app()->getLocale()->currency($currentCurrencyCode)->getSymbol();
                    $_numformatShippingCost=number_format($_shippingcost,2);
                    $mobileView .='<li style="font-size: 14px;">Shipping Cost in India ('.$_currSym.'): <span>'.$_numformatShippingCost.'</span></li>';
                }

        if(!empty($_intershippingcost) && $_intershippingcost!='0'){		
 
            $_currSym=Mage::app()->getLocale()->currency($currentCurrencyCode)->getSymbol();
            $_numforInterShippingCost=number_format($_intershippingcost,2);
            $mobileView .='<li style="font-size: 14px;">Shipping Cost Outside of India ('.$_currSym.'): <span>'.$_numforInterShippingCost.'</span></li>';
        }

         $_helpm = $view_class->helper('umicrosite');

        $storeurl = $_helpm->getVendorUrl($vendorIfo->getData('vendor_id'));
        if(substr($storeurl, -1) == '/')
        {
        	$storeurl = substr($storeurl, 0, -1);
        }
        $mobileView .= '<li style="font-size: 14px;">See similar products from this Seller: ';
        if($storeurl):
            $storeurl=$storeurl;
        else:
            $storeurl='#';
        endif;
        $mobileView .=
            '<span><a href="'.$storeurl.'" target="_blank">.'.$vendorIfo->getData('vendor_name').'</a></span>
        </li>';
        if($_product->getInternationalShipping()==1)
            $mobileView .='<li style="font-size: 14px;">This Product is available for International Shipping.</li>';
	else
            $mobileView .='<li style="font-size: 14px;">This Product is not available for shipping outside India.</li>';

			$vendorAdminPaymentMethods = $vendorIfo->getVendoradminPaymentMethods();
			$vendorCOD = true;
			foreach ($vendorAdminPaymentMethods as $method)
				{
				   if($method == 'cashondelivery')
					{
						$vendorCOD = false;
						break;
					}
				
				}
		$my_tier_rocks = $view_class->getTierPrices($_product);
		if (!empty($my_tier_rocks))
			{
			$my_tier_rocks = $view_class->getTierPrices($_product);
			foreach($my_tier_rocks as $_tierprice)
				{
				$my_tier_rocks1 = $_tierprice['formated_price_incl_tax'];
				$my_tier_rocks2 = $_tierprice['price_qty'];
				}
			$mobileView .='<li style="font-size: 14px;">Wholesale Price : <span class="price">'.$my_tier_rocks1.'</span> &nbsp;Min Order Qty : <span class="price">'.$my_tier_rocks2.'</span></span></li>';
			} 
     $mobileView .='</ul>
      <div id="dtlPrice">';

$mobileView .=
      '<div id="dtlSize">';
          	$group = $_product->isComposite();
		if ($group != true) { 

           } else {

		if ($view_class->hasOptions()):
                $mobileView .= $view_class->getChildChildHtml('container2', '', true, true);
          endif;
          }
$mobileView .='</div>';
   
$mobileView .='</div>
        <div class="fleft1">';
	 $stockConfigProd = strpos($stockConfig,'1');

			if($_product->getIsInStock() ==1 && $_product->getTypeId() != "configurable"){

				$mobileView .= '<br/><button class="buttonBye" unbxdattr="AddToCart" unbxdparam_sku="'.$skuProduct.'"
 type="submit">BUY NOW</button>';
			}
			elseif($stockConfigProd!=false && $_product->getTypeId() == "configurable"){
				
                                $mobileView .= '<br/><button class="buttonBye" unbxdattr="AddToCart" unbxdparam_sku="'.$skuProduct.'"
 type="submit" >BUY NOW</button>';
                        }        
                        else{
				$mobileView .= '<p class="spriteimg pro_soldout" title="Sold Out"></p>';
                        }        
$mobileView .= '</div>';
$mobileView .='<div id="dtlSocialLink1">
             Confused?';
                $mobileView .='<a rel="nofollow" class="contactseller" onclick="contactseller()" >Contact Seller</a>';
		$metaTitle = $_product->getData('meta_title');
$mobileView .='</div>
	<div class="clear"></div><br>
             <p class="clr"></p>';
			  if ($vendorCOD)
				{
					$mobileView .= "<div id='cash1'>";
					$mobileView .= "<div id='craftsvilla_cash_on_delivery1'><div class='active'>Cash On Delivery Available</div></div><br>";

					$mobileView .= "
					<br><div id='pincode_form'>
					<label id='check'><font size='5'>
					Check Cash On Delivery Below:</font></label><br>
					&nbsp&nbsp&nbsp<input type='text' name='pincode_value' placeholder='Enter Pincode' id='pincode_value' style='width:75%;height:50px;font-size: 20px;'>


					<a href='#pincode' class='craftsvilla_pincode' onClick='submitform();return false;' style='width:18%;height: 50px;line-height: 48px;margin-top: 20px;'> CHECK </a>
					</div><br>";
					$mobileView .="<div id='pincode' style='display:none'></div>";
					$mobileView .= "</div>";
					$mobileView .='<div id="display"></div>';
				}

$mobileView .='<div class="clearer"></div>';
$mobileView .='<div class="clearer"></div>';

$mobileView .=  '<div id="dtlGift">';

$couponread = Mage::getSingleton('core/resource')->getConnection('core_read');
			$coupon="select * from `salesrule` where `vendorid`='".$vendorIfo['vendor_id']."' AND `is_active`=1 AND `to_date` > NOW()";
			$result=$couponread->query($coupon)->fetchAll();
			if($result)
			{
				$vendorcoupon['code']=array();
				$vendorcoupon['description']=array();
				$vendorcoupon['vendor_name']=array();
				$ruleid = $result[0]['rule_id'];
				$coupondesc = "select `code` from `salesrule_coupon` where `rule_id` = '".$ruleid."'";
				$resultc=$couponread->query($coupondesc)->fetchAll();
				$vendorcoupon['vendor_name'] = substr($result[0]['vendor_name'],0,30);
				$vendorcoupon['code'] =$resultc[0]['code'];
				$vendorcoupon['description'] =$result[0]['description'];
				$expirydate = $result[0]['to_date'];
		
				 if(sizeof($vendorcoupon['vendor_name'])>0)
		    {  
					$mobileView .= '<div id="dtlCompanyLogo1"><table border="1"><tr><td rowspan="2"><div id="craftsvilla_coupons_product"><div class="active">Offers</div></div></td>';
					$mobileView .= '<td style="padding-right: 6px;padding-left: 6px;vertical-align: center"><font size="2px">COUPON</font>: <strong>'.$vendorcoupon['code'].'</strong></td></tr><tr><td style="padding-right: 6px;padding-left: 6px;"><font size="1px"><strong>'.$vendorcoupon['description'].'</strong></td></tr></table>';

					 $mobileView .= '</div>';
			}
			}

$mobileView .='</div>
        <div id="dtlCompanyLogo1">';
            $url= $_helpm->getVendorUrl($data->vendor_id);
            $storeurl = $_helpm->getVendorUrl($vendorIfo->getData('vendor_id'));
            $vendorLogo = $vendorIfo->getShopLogo()!='' ? $vendorIfo->getShopLogo() : 'vendor/noimage/noimage.jpg';
		$Feedback = Mage::getModel('feedback/vendor_feedback')->getCollection()
                                    ->addFieldToFilter('vendor_id',$vendorIfo->getData('vendor_id'))
                                    ->addFieldToFilter('feedback_type',1)
                                    ->addExpressionFieldToSelect('total','count({{feedback_id}})','feedback_id');
									
$mobileView .='          
          <span class="seller_image"><a href="'.(substr($storeurl, 0, -1)).'" target="_blank"><img src="'.($_helpm->getResizedUrl($vendorLogo,70)).'" /></a></span>

          <div class="fleft seller-right" style="width:50%">';
$mobileView .='<p class="vendorName1"><a href="'.(substr($storeurl, 0, -1)).'" target="_blank">'.($vendorIfo['vendor_name']).'</a></p>
            <span class="dtlLink-select">';

                $countries = Mage::getModel('directory/country_api')->items();
                $country = '';
                $region = '';
                $country = $vendorIfo["country_id"];
                if($country!=''){
                    $state_list = Mage::getModel('directory/region_api')->items($country);
                    $region = $vendorIfo['region_id'];
                    if($region!=''){
                        foreach($state_list as $key => $value) {
                            if ($value['region_id'] == $region) {
                                $state_name = $value['name'];
                                break;
                            }
                        }
                    }
                } 

$mobileView .='<span>'.($vendorIfo['city']).','.$state_name.'</span></span>';
	$vendorTotaldata = Mage::getModel('udropship/vendor')->load($vendorIfo['vendor_id']);
	$feedbackRatingVendor = $vendorTotaldata->getData('feedback_vendorrating');
	$positiveFeedbackPercent = $feedbackRatingVendor;
           
$mobileView .='<a'.$feedbck.'>
	<div class="classification">
	<div class="tooltip">
	<p class="arowtooltip spriteimg"></p>';
$mobileView .='Feedback:'.$_totalFeedback.",".$positiveFeedbackPercent.'% pos. </div>
	<div class="cover"></div>
	<div class="progress" style="width:'.$positiveFeedbackPercent.'%;"></div>
	</div>
	</a>';

 $mobileView .='<p class="links"><a rel="nofollow" href="'.$storeurl.'thoughtyard/policies/" target="_blank">Seller Policies</a></p>
	 </div>';
$mobileView .='</div>';
$mobileView .=$view_class->getChildHtml('other');
 
			if ($_product->isSaleable() && $view_class->hasOptions()):

			endif;

$mobileView .='<div class="clearer"></div>';
$mobileView .='</div>
      </div></div>';
$mobileView .='<div id="dtlText1">';
  
		foreach ($view_class->getChildGroup('detailed_info', 'getChildHtml') as $alias => $html):
			if ($title = $view_class->getChildData($alias, 'title')):
				$mobileView .= $view_class->escapeHtml($title);
            		endif;
$mobileView .= $html;
		endforeach;
$mobileView .='</div>
	</div>
	<div class="clear"></div>
	</div>
	</form>
<br><br><br>
	</div><div class="clear"></div> ';

$hlp = Mage::helper('Catalog');

$recomended_product = $hlp->recomendedMobileProducts($viewProduct_id);
$cacheContentProduct = $mobileView.$recomended_product;

$cacheContentProduct .= $view_class->getChildHtml('contactseller');

	return $cacheContentProduct;
	}
}
