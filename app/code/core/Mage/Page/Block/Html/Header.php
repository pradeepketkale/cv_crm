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
 * @package     Mage_Page
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Html page block
 *
 * @category   Mage
 * @package    Mage_Page
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Page_Block_Html_Header extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('page/html/header.phtml');
    }

    /**
     * Check if current url is url for home page
     *
     * @return true
     */
    public function getIsHomePage()
    {
        return $this->getUrl('') == $this->getUrl('*/*/*', array('_current'=>true, '_use_rewrite'=>true));
    }

    public function setLogo($logo_src, $logo_alt)
    {
        $this->setLogoSrc($logo_src);
        $this->setLogoAlt($logo_alt);
        return $this;
    }

    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = Mage::getStoreConfig('design/header/logo_src');
        }
        return $this->getSkinUrl($this->_data['logo_src']);
    }

    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = Mage::getStoreConfig('design/header/logo_alt');
        }
        return $this->_data['logo_alt'];
    }

    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            if (Mage::isInstalled() && Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_data['welcome'] = $this->__('Welcome, %s!', $this->htmlEscape(Mage::getSingleton('customer/session')->getCustomer()->getName()));
            } else {
                $this->_data['welcome'] = Mage::getStoreConfig('design/header/welcome');
            }
        }

        return $this->_data['welcome'];
    }


function desktopHeader()
	{
$head_class = $this;

$baseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
//$readCount =  Mage::getSingleton('core/resource')->getConnection('core_read');
//$countVendorQuery = "SELECT count(*) as `cntvnd` FROM `udropship_vendor`";
//$resultVendor =  $readCount->query($countVendorQuery)->fetch();
$resultVendorCount = '12547';
//$countProductsQuery = "SELECT count(*) as `cntprd` FROM `catalog_product_entity`";
//$resultProducts = $readCount->query($countProductsQuery)->fetch();
//$resultProductsCount = $resultProducts['cntprd'];
$resultProductsCountstring = number_format(1829213);
//$header_class = Mage::getModel('page/html_header');
//print_r($header_class->getChildHtml('currency'));exit;
//$skin_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);echo $skin_url;exit;
$deskHeader = '';

$deskHeader .= '<div class="header_wrapper">
  <div class="header">
    <div class="inner">
      <div class="top">
        <ul class="navigation">
<li style="margin-top:-1px;" id="sellerlogin20032014"> </li>
            <!--<li id="agentlogin20102015">-->
         </li>
        </ul>
        <ul class="actions">
       
		<li id="customerregister12131321">	
          </li>
        <!-- Start of code ----Below Lines commented By Dileswar On dated 20-03-2014 for convert to ajax-->  
          <li id="cartcountdispval">
            </li>
          
          <li class="curency positionrelative"> <a rel="nofollow" class="currencyselect spriteimg" href="#">Select Currency</a>
            <div class="submenu_new accodianheight submenuinner">
              <div class="submenuright">
                <div class="submenuleft">
                  <div class="submenuback">'.$head_class->getChildHtml('currency').'</div>
                </div>
              </div>
            </div>
          </li>
         
        </ul>
      </div>
<div class="bottom">
        <div class="fleft"> <a href="'.$baseurl.'" class="icon icon-logo" title="CraftsVilla.com - The Marketplace to Discover India"></a>
          <div style="width:287px;" class="spriteimg countscss clr">'.$resultVendorCount.'+ Shops | '.$resultProductsCountstring .'+ Products | One Million+ Customers</div>
          <ul id="categories_dropdown" class="clr">
            <li class="dropdown_btn"><a href="#" class="spriteimg btndropdown"></a>
              <ul>
                <li><a href="http://www.craftsvilla.com/jewellery-jewelry.html">Jewellery</a>
                  <ul class="level_two level_big">
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/necklaces.html">Necklaces</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/earrings.html">Earrings</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/anklets.html">Anklets</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/bracelets-bangles.html">Bracelets n Bangles</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/rings.html">Rings</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/pendants.html">Pendants</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/maang-tikka-1.html">Maang Tikkas</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/brooches.html">Brooches</a></li>
                    <li><a href="http://www.craftsvilla.com/jewellery-jewelry/curated-jewelry.html">Curated Jewelry</a></li>
                   <div style="float: right;margin: 19px;padding: 20px;margin-top: -108px;display: inline;"><a href="http://www.craftsvilla.com/maitricrafts"> <img src="'.$head_class->getSkinUrl('img/home-page-category-banner-maitri-1.jpg').'" width="125px" height="125px"></a><br></div>
</ul>
                </li>
                <li><a href="http://www.craftsvilla.com/sarees-sari.html">Sarees</a>
				<ul class="level_two level_big">
					<li><a href="http://www.craftsvilla.com/sarees-sari/bollywood-sarees.html">Bollywood Sarees</a></li>                    
                    <li><a href="http://www.craftsvilla.com/sarees-sari/designer.html">Designer Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/georgette.html">Georgette Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/chiffon.html">Chiffon Sarees</a></li>
					<li><a href="http://www.craftsvilla.com/sarees-sari/silk.html">Silk Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/wedding.html">Wedding Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/net.html">Net Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/jacquard.html">Jacquard Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/cotton.html">Cotton Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/heavy-work.html">Heavy Work Sarees</a></li>
					<li><a href="http://www.craftsvilla.com/sarees-sari/banarasi.html">Banarasi Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/bandhani.html">Bandhani Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/cotton-silk.html">Cotton Silk Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/handwoven.html">Handwoven Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/kanchivaram.html">Kanchivaram</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/leheriya.html">Leheriya Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/satin.html">Satin Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/half-sarees.html">Half Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/uppada-sarees-32.html">Uppada Sarees</a></li>
                    <li><a href="http://www.craftsvilla.com/sarees-sari/paithani-sarees.html">Paithani Sarees</a></li>
                  <div style="float: right;margin: -10px;padding: 20px;display: inline;margin-top: -197px;"><a href = "http://www.craftsvilla.com/harshikadesigner"> <img src="'.$head_class->getSkinUrl('img/home-page-category-banner-harshika-1.jpg').'" width="125px" height="125px"></a><br><br><a href="http://www.craftsvilla.com/mksynthetics"><img src="'.$head_class->getSkinUrl('img/home-page-category-banner-mksynthetics-1.jpg').'" width="125px" height="125px"></a></div>
</ul>
					
			</li>
				<li><a href="http://www.craftsvilla.com/clothing/lehnga.html">Bridal Lehenga</a></li>
				<li><a href="http://www.craftsvilla.com/clothing/salwar-suit.html">Salwar Suits</a></li>
			    <li><a href="http://www.craftsvilla.com/clothing/blouse.html">Blouses</a></li>					
				<li class="sale nav"><a href="http://www.craftsvilla.com/rakhi-gifts.html">Rakhi Gifts</a></li>
    <li class="sale nav"><a href="http://www.craftsvilla.com/discounts-n-offers-craftsvilla.html">Guarantee Lowest price</a></li>
				<li><a href="http://www.craftsvilla.com/clothing.html">Clothing</a>
                  <ul class="level_two level_big">
					<div><li><a href="http://www.craftsvilla.com/clothing/salwar-suit.html">Salwar Suit</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/lehnga.html">Bridal Lehenga</a></li>                    
                    <li><a href="http://www.craftsvilla.com/clothing/blouse.html">Blouse</a></li>					
					<li><a href="http://www.craftsvilla.com/clothing/dress-material.html">Dress Material</a></li>					
					<li><a href="http://www.craftsvilla.com/clothing/kurtis-craftsvilla.html">Kurtis</a></li>					
                    <li><a href="http://www.craftsvilla.com/clothing/tops-craftsvilla.html">Tops</a></li>
					<li><a href="http://www.craftsvilla.com/clothing/designer-collections-craftsvilla.html">Designer Collections</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/t-shirts-for-men.html">Tshirts (Men)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/tshirts-women.html">Tshirts (Women)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/evening-wear-craftsvilla.html">Evening Wear</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/casual-dresses-craftsvilla.html">Casual Dresses</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/skirts-craftsvilla.html">Skirts</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/teenage-girl.html">Teenage (Girl)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/teenage-boy.html">Teenage (Boy)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/pants-male.html">Pants (Men)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/pants-women.html">Pants (Women)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/jacket.html">Jacket</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/jump-suit.html">Jump Suit</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/winterwear.html">Winterwear (Female)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/kids.html">Kids Clothing</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/winterwear-male.html">Winterwear (Male)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/organic-eco-friendly-clothing.html">Organic Clothing</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/kurta-men.html">Kurta (Men)</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/infants.html">Infant Clothing</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/legging.html">Legging</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/salwar.html">Salwar</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/men-s-shirt.html">Men Shirt</a></li>
                    <li><a href="http://www.craftsvilla.com/clothing/kaftans.html">Kaftans</a></li></div>
                  <div style="float: right;margin: -0.9px;padding: 13px;margin-top: -274px;display: inline;"><a href="http://www.craftsvilla.com/Muhenera"><img src="'.$head_class->getSkinUrl('img/home-page-category-banner-muhenera-1.jpg').'" width="125px" height="125px"></a><br/><br/><a href="http://www.craftsvilla.com/FABFIZA">
<img src="'.$head_class->getSkinUrl('img/home-page-category-banner-fabfiza-1.jpg').'" width="125px" height="125px"></a></div>
</ul>
                </li>
                <li><a href="http://www.craftsvilla.com/bags.html">Bags</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/bags/hand-bags.html">Hand Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/jhola-bags.html">Jhola Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/jute-bags.html">Jute Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/clutch-bags.html">Clutches</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/wallets.html">Wallets</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/mobile-covers.html">Mobile Covers</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/bangle-bags-handbag-jhola-sling-wallet-purse-tote-potli-pouches-mobile-laptop.html">Bangle Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/potli-bags.html">Potli Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/tote-bags.html">Tote Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/designer-bags.html">Designer Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/pouches.html">Pouches</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/eco-friendly-green-bags.html">Eco-Friendly Bags</a></li>
                    <li><a href="http://www.craftsvilla.com/bags/kids-bag-handbag-jhola-sling-wallet-purse-tote-potli-pouches-mobile-laptop.html">Kids Bags</a></li>
                  </ul>
                </li>
              
               <li class="sale nav"><a href="http://www.craftsvilla.com/shopcoupon"><img src="'.$head_class->getSkinUrl('img/Heart_symbol_c00_10px.png').'">Discounts &amp; Coupons</a></li>  
                                    
                <li><a href="http://www.craftsvilla.com/home-decor-products.html">Home Decor</a>
                  <ul class="level_two level_big">
                    <li><a href="http://www.craftsvilla.com/home-decor-products/decoratives.html">Decoratives</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/kitchen-accessories.html">Kitchen Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/outdoor-dacor.html">Outdoor Decor</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/tableware-craftsvilla.html">Tableware</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/kids-room-craftsvilla.html">Games n Toys</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/stationary.html">Paper n Stationery</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/boxes-baskets.html">Boxes n Baskets</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/art-wall-paintings.html">Art Wall n Paintings</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/palm-leaf-products-craftsvilla.html">Palm Leaf Products</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/paperproducts-craftsvilla.html">Paper Products</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/grass-products-craftsvilla.html">Grass Products</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/coir-products-craftsvilla.html">Coir Products</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/vase.html">Vase</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/lighting-lamps-shades.html">Lighting n Lamps</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/mirror.html">Mirror</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/frames.html">Photo Frames</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/furniture.html">Furniture</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/candles-fragrances.html">Candles n Fragrances</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/clocks.html">Clocks</a></li>
                    <li><a href="http://www.craftsvilla.com/home-decor-products/magnets.html">Magnets</a></li>
                  <div style="float: right;margin: -10px;padding: 20px;display: inline;margin-top: -197px;"><a href="http://www.craftsvilla.com/twomoustaches"><img src="'.$head_class->getSkinUrl('img/home-page-category-banner-twoms-1.jpg').'" width="125px" height="125px"></a><br><br>
<a href="http://www.craftsvilla.com/karigaari"><img src="'.$head_class->getSkinUrl('img/home-page-category-banner-karigari-1.jpg').'" width="125px" height="125px"></a></div></ul>
                </li>
                
                <li><a href="http://www.craftsvilla.com/accessories.html">Accessories</a>
                  <ul class="level_two level_big">
                    <li><a href="http://www.craftsvilla.com/accessories/stoles-craftsvilla.html">Stoles</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/belts-craftsvilla.html">Belts</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/keychain-craftsvilla.html">Key Chains</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/scarf.html">Scarves</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/dupattas.html">Dupattas</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/winter-accessories.html">Winter Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/hair-accessories.html">Hair Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/kids-accessories.html">Kids Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/shawls.html">Shawls</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/handkerchief.html">Handkerchief</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/female-watch.html">Watches</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/card-holder.html">Card Holders</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/brooch-saree-pin.html">Brooch/ Saree Pin</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/umbrella.html">SubCategory</a></li>
                    <li><a href="http://www.craftsvilla.com/accessories/mens-accessories.html">Mens Accessories</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/home-furnishing.html">Home Furnishing</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/home-furnishing/pillow-covers.html">Pillow Covers</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/bedding.html">Bedding</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/kids-cushion.html">Kids Cushion</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/throw-craftsvilla.html">Throws</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/kids-bedding.html">Kids Bedding</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/rugs.html">Rugs</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/bathroom-products-craftsvilla.html">Bathroom Products</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/cushion-covers.html">Cushion Covers</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/curtains.html">Curtains</a></li>
                    <li><a href="http://www.craftsvilla.com/home-furnishing/packing-covers.html">Packing Covers</a></li>
                  </ul>
                </li>
                
                <!--<li><a href="http://www.craftsvilla.com/services.html">Services</a>
                
                 <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/services/dance.html">Dance</a></li>
                    <li><a href="http://www.craftsvilla.com/services/music.html">Music</a></li>
                    <li><a href="http://www.craftsvilla.com/services/art-craft.html">Art n Craft</a></li>
                    <li><a href="http://www.craftsvilla.com/services/others.html">Other Services</a></li>
                  </ul>
                
                </li>-->                  
                <li><a href="http://www.craftsvilla.com/bath-beauty.html">Bath &amp; Beauty</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/bath-beauty/soap-rose-tulsi-neem-sandalwood.html">Soaps</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/makeup.html">Makeup</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/oil.html">Oil</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/skin-care.html">Skin Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/body-care.html">Body Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/hair-care.html">Hair Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/face-care.html">Face Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/lip-care.html">Lip Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/bath-salt.html">Bath Salt</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/eye-care.html">Eye Care</a></li>
                    <li><a href="http://www.craftsvilla.com/bath-beauty/perfume.html">Perfume</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates.html">Food &amp; Health</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/chocolates-handmade.html">Chocolates</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/herbs-spices-india-masala.html">Herbs &amp; Spices</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/tea-green-chai-masala-ginger.html">Beverages</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/health-supplements-ayurvedic-ashwagandha-weight-loss-hair-loss-musli-power.html">Health Supplements</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/healthy-snack-diet-weight-loss.html">Healthy Snack</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/dry-fruits-almonds-raisin-cashew-walnut-pista-kismis-kesar.html">Dry Fruits</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/snacks.html">Snacks</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/cookies.html">Cookies</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/chutney.html">Chutney</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/pickles.html">Pickles</a></li>
                    <li><a href="http://www.craftsvilla.com/food-spices-herbs-tea-chocolates/jam.html">Jam</a></li>
                  </ul>
                </li>
				<li class="sale nav"><a href="http://www.craftsvilla.com/i-like-ganesha.html">Ganesha Gifts</a></li>
                <li><a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding.html">Gifts</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding/gift-him-birthday-anniversary-wedding.html">For Him</a></li>
                    <li><a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding/gift-her-birthday-anniversary-wedding.html">For Her</a></li>
                    <li><a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding/gift-kids-birthday-anniversary-wedding.html">For Kids</a></li>
                    <li><a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding/gift-house-warming-wedding.html">For Home</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/kids-baby-names-toy.html">Kids</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/kids-baby-infant-clothing.html">Kids Clothing</a></li>
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/kids-baby-infant-accessories.html">Kids Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/kids-baby-toys-games.html">Toys</a></li>
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/kids-room-games-toys.html">Kids Room</a></li>
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/kids-bedding-quilts-bedsheet.html">Bedding</a></li>
                    <li><a href="http://www.craftsvilla.com/kids-baby-names-toy/babies-accessories.html">Babies Accessories</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/books-india.html">Books</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/books-india/comics.html">Comics</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/recipes.html">Recipes</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/other-books.html">Other Books</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/all-books.html">All Books</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/religious.html">Religious</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/kids.html">Kids Books</a></li>
                    <li><a href="http://www.craftsvilla.com/books-india/music.html">Music</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/footwear-1.html">Footwear</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/footwear-1/footwear-women.html">Footwear (Women)</a></li>
                    <li><a href="http://www.craftsvilla.com/footwear-1/footwear-craftsvilla.html">Footwear (Men)</a></li>
                  </ul>
                </li>
                <!--<li><a href="http://www.craftsvilla.com/new-arrivals-new-product-launches.html">New Arrivals</a> </li>-->
                <li><a href="http://www.craftsvilla.com/marriage-n-love.html">Wedding</a></li>
                <li><a href="http://www.craftsvilla.com/spiritual-books-pooja.html">Spiritual</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/spiritual-books-pooja/spiritual-books.html">Spiritual Books</a></li>
                    <li><a href="http://www.craftsvilla.com/spiritual-books-pooja/spiritual-statues-decor.html">Statues</a></li>
                    <li><a href="http://www.craftsvilla.com/spiritual-books-pooja/spiritual-figurines-decoratives.html">Figurines n Decoratives</a></li>
                    <li><a href="http://www.craftsvilla.com/spiritual-books-pooja/pooja-accessories-thali.html">Pooja Accessories</a></li>
                    <li><a href="http://www.craftsvilla.com/spiritual-books-pooja/temples-pooja-mandir.html">Temples</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/supplies.html">Supplies</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/supplies/fabrics-dress-material.html">Fabrics</a></li>
                    <li><a href="http://www.craftsvilla.com/supplies/applique.html">Appliques</a></li>
                    <li><a href="http://www.craftsvilla.com/supplies/jewelry-supplies.html">Jewelry Supplies</a></li>
                  </ul>
                </li>
                <li><a href="http://www.craftsvilla.com/flowers.html">Flowers</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/flowers.html?cat=1128">Fresh Flowers</a></li>
                    <li><a href="http://www.craftsvilla.com/flowers.html?cat=1129">Artificial Flowers</a></li>
                  </ul>
                </li>
                <li class="lstchild"><a href="http://www.craftsvilla.com/musical-instruments.html">Musical Instruments</a>
                  <ul class="level_two">
                    <li><a href="http://www.craftsvilla.com/musical-instruments.html?cat=1131">Flutes</a></li>
                  </ul>
                </li>
              </ul>
            </li>
          </ul>
        </div>
<div style="width: 200px ;float: right; height: 46px;border-radius: 15px;border:2px solid black; text-align: justify;overflow:hidden;">
<a href="https://play.google.com/store/apps/details?id=com.craftsvilla.app&hl=en" style="float: left;text-decoration: none;" target="_blank">
<img src="'.Mage::getDesign()->getSkinUrl('mobileimg/icon-playstore.png').'"  style="margin-left: 9px; width=46px; height: 48px; float: left;"/>
  Click to Download <br>Craftsvilla App</a></div>
       


 <div class="search_block">';            

				 	$form_action='';
					 	if(!isset($_GET['searchby'])) {
					 		$form_action='/searchresults';
					 	}
					 	else if($_GET['searchby'] == 'all') {
					 		$form_action='/searchresults';
					 	}
					 	else if($_GET['searchby'] == 'product') {
					 		$form_action='/searchresults';
					 	}
					 	else if($_GET['searchby'] == 'shop') {
					 		$form_action='/shopsearch';
					 	}



$deskHeader .='<form id="search_mini_form" action="'.$form_action.'" method="get">
            
            <input type="hidden" name="searchby" id="searchby"';

 					if(!isset($_GET['searchby'])) {
						 $deskHeader .='value="product"';	
					 }else {
						$deskHeader .='value="'.$_GET['searchby'].'"';
					 }
$deskHeader .='>
	<div class="selectsearch" style="width:294px;">
	<h4>Search for:</h4>
		<ul>
		<li>';

					if(!isset($_GET['searchby']) || $_GET['searchby'] == 'product') { 
						$product_class = 'class="kettle-hover"'; 
					}else { 
						$product_class = 'class="icon_wrapper"';
					}




$deskHeader .='
		<a onclick="menu(1);" id="kettleic"'.$product_class.'><span class="icon_wrapper"><span class="icon-kettle"></span></span><strong>Products</strong></a> </li>
		<li class="v-line-search"></li>
		<li>';

					if($_GET['searchby'] == 'shop') { 
						$shop_class = 'class="search_shop-hover"'; 
					}else { 
						$shop_class = 'class="icon_wrapper"';
					}
				
$deskHeader .='<a onclick="javascript:window.location.replace(/shopsearch?searchby=shop&q=Search All Shops)" id="shopic" '.$shop_class.'><span class="icon_wrapper"><span class="icon-search_shop"></span></span><strong>Shops</strong></a> </li>
          
	</ul>
	</div>';


$deskHeader .='<div class="input_wrapper icon-search_bare"> 
              
              <input unbxdattr="sq" id="search" type="text" name="'.$head_class->helper('catalogsearch')->getQueryParamName().'" ';
					
if($head_class->helper('catalogsearch')->getEscapedQueryText()) {
						 $deskHeader .= ' value = "'.$head_class->helper("catalogsearch")->getEscapedQueryText().'"';
					 } else 
						{ $deskHeader .= ' value= "Search Entire Store"';}


$deskHeader .=' class="input-text" onclick="javascript: if('."this.value == 'Search All Shops' || this.value == 'Search Entire Store' || this.value == 'Search All Products') { this.value = ''; }".'" onblur="javascript: fillvalues();"  />
              <input unbxdattr="sq_bt" type="submit" value="" title="Search" class="icon-search_glass" />';


$deskHeader .='</div>
	</form>
	<div class="clr keywords"><span>Popular Searches:</span> <a rel="nofollow" href="http://www.craftsvilla.com/searchresults?searchby=product&q=kareena+kapoor">Kareena Kapoor Sarees</a> | <a rel="nofollow" href="http://www.craftsvilla.com/searchresults?searchby=product&q=katrina+kaif">Katrina Kaif Sarees</a> | <a rel="nofollow" href="http://www.craftsvilla.com/shopcoupon">Discounts &amp; offers</a> | <a rel="nofollow" href="http://www.craftsvilla.com/junkkart">Junk Kart Jewellery</a></div>
	</div>
	</div>
	</div>
	</div>
	</div>';

$deskHeader .='<div id="signUpForm">
	<div id="welcome_img"><span class="spriteimg"></span></div>
	<div id="bg_welcome">
	<div id="form">
	<form action="/customer/account/createpost/" method="post" id="form-validate">
		<input type="text" name="firstname" id="firstname" placeholder="First Name*" />
		<input type="text" name="lastname" id="lastname" placeholder="Last Name*" />
		<input type="text" id="telephone" name="telephone" placeholder="Mobile Number" />
		<input type="text" name="email" id="email_address" placeholder="Email Address*" />
		<input type="password" name="password" id="password" placeholder="Password*" />
		<input type="password" id="confirmation" name="confirmation" placeholder="Re-Enter Password*" />

	<div class="checkboxcss">
		<input type="checkbox" id="terms" name="agree" class="checkbox required-entry" />
		<label class="floatl"> I agree that I have read and understood the <a href="';
Mage::getBaseUrl().'terms-and-conditions-craftsvilla/';
$deskHeader .='">Craftsvilla User Agreement</a> &amp; <a href="';
Mage::getBaseUrl().'privacy-policy-craftsvilla/';
$deskHeader .='">the Privacy Policy</a></label>
	</div>
	<div id="form-button"> 
		<button type="submit" title="'.$head_class->__('Submit').'" class="spriteimg continueMdm">REGISTER</button>
	</div>
	</form>
	</div>
	<div id="formText">
		<p>Craftsvilla.com is the largest online marketplace for unique Indian products including Handmade, Vintage, Ethnic, Organic and Natural products. Register to get regular updates from Craftsvilla.com on new products, sellers and rewards. You can also register using your facebook account below.</p>
	<div id="formText-img" class="spriteimg"></div>
	<div id="formText-button">
		<button rel="facebook-connect" class="spriteimg facebook_login" type="submit">Register with Facebook</button>
		<a rel="facebook-connect"></a> </div>
	</div>
	</div>
	</div>';
 $currentUrl = $head_class->helper('core/url')->getCurrentUrl();


$deskHeader .='<div id="login">
	<div id="welcome_img"><span class="spriteimg"></span></div>
	<div id="bg_welcome">
	<div id="form">
		<form id="login-top" method="post" action="'.$baseurl.'craftsvillacustomer/account/loginPost/">
		<input type="text" name="login[username]" id="email" placeholder="Email Address*" />
		<input type="password" name="login[password]" id="pass" placeholder="Password*" />
		<input type="hidden" name="login[currenturl]" id="url" value="'.$currentUrl.'" />
		<a style="margin-left: 5px;" class="links" href="/customer/account/forgotpassword/">Forgot Your Password?</a>
	<div> 

		<button style="float:none;display: block;" class="spriteimg continueMdm" type="submit" title="'.$head_class->__('Login').'">'. $head_class->__('Login').'</button>
	</div>
	</form>
	</div>
	<div id="formText">
		<p>Craftsvilla.com is the largest online marketplace for unique Indian products including Handmade, Vintage, Ethnic, Organic and Natural products. Register to get regular updates from Craftsvilla.com on new products, sellers and rewards. You can also register using your facebook account below.</p>
	<div id="formText-img" class="spriteimg"></div>
		<button rel="facebook-connect" class="spriteimg facebook_login" type="submit">Login with Facebook</button>
		<a rel="facebook-connect"></a> </div>
	</div>
	</div>';


return $deskHeader;
	}
 	


	function mobileHeader()
	{
$head_class = $this;
$baseurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
//$readCount =  Mage::getSingleton('core/resource')->getConnection('core_read');
//$countVendorQuery = "SELECT count(*) as `cntvnd` FROM `udropship_vendor`";
//$resultVendor =  $readCount->query($countVendorQuery)->fetch();
$resultVendorCount = '12347';
//$countProductsQuery = "SELECT count(*) as `cntprd` FROM `catalog_product_entity`";
//$resultProducts = $readCount->query($countProductsQuery)->fetch();
//$resultProductsCount = $resultProducts['cntprd'];
$resultProductsCountstring = number_format(1889102);


$mobileHeader = '';


$mobileHeader .= '<div class="header_wrapper" style="background:none;background-color:#E3D7C8; width: 100%;height: 80px;">
	<div class="header" style="width: 100%;">
	<div class="inner" style="width: 100%;">
	<a href="'.$baseurl.'" style="text-decoration:none">
	<div style="margin-left:10%;position: absolute;margin-top: 12px;">
	<img src="'.Mage::getDesign()->getSkinUrl('mobileimg/logo_craftsvilla_mobile.png').'" width=58px style="margin-top: 0px";>
	</div><div id="cmpnyName" style="margin-left:18%;position: absolute;margin-top: -10px;"><br><br>
	<font size="+4" color="#492B26">Craftsvilla.com</font></div>
	</a>
	<div class="top">
	<ul class="navigation" style="margin-right: 20px;float:right;margin-top: 35px;font-size: 25px;">
	<li>
	<div class="search_block" style="padding-top:0px;margin-top:-18px;margin-left: 0%;">';

 $mobileHeader .= '<form id="search_mini_form" action="http://search.craftsvilla.com/" method="get">
         <input type="hidden" name="searchby" id="searchby"';
					if(!isset($_GET['searchby'])) {
						 $mobileHeader .=  'value="product"'; }
					else {   $mobileHeader .=  'value="'.$_GET['searchby'].'"';}
$mobileHeader .= '>
	<input name="q" type="text" size="40" id="search" name="'.$head_class->helper('catalogsearch')->getQueryParamName().'" value="';
					if($head_class->helper('catalogsearch')->getEscapedQueryText()) {
						$mobileHeader .= $head_class->helper('catalogsearch')->getEscapedQueryText(); }
					else { $mobileHeader .= "Search Entire Store";}
$mobileHeader .= '" class="input-text" onclick="javascript: if('."this.value == 'Search All Shops' || this.value == 'Search Entire Store' || this.value == 'Search All Products') { this.value = ''; }".'" onblur="javascript: fillvalues();return f2();" onFocus="fillvalues();f1();" />

	</form></div>
	</li>
		<li style="margin-top:-1px;" id="sellerlogin20032014"> 
	</li>
	</li>
		<li id="customerregister12131321">	
	</li>
		<li id="cartcountdispval"> 
	</li>
	</ul>
	<ul class="actions">
	</ul>
	</div>';

$mobileHeader .= '<div class=""  style="padding-bottom: 0px;margin-top: -78px;">
	<div id="container123" onclick="btn1()" onmouseout="btn2()">
	<div></div>
			
	<div></div>
			
	<div></div>
	</div>
	<div id="show123" onclick="btn3()" onmouseout="btn4()">
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/jewellery-jewelry.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Jewellery 
	</a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/sarees-sari.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Saree 
	</a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/clothing/lehnga.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Bridal Lehenga 
	</a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
        <a href="http://www.craftsvilla.com/rakhi-gifts.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Rakhi Gifts
	  </a>
        </li>

	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/clothing/blouse.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Blouses 
	</a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/clothing/salwar-suit.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Salwar Suit 
	</a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/clothing.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Clothing
	 </a>
	</li>
	<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/home-decor-products.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Home Decor </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/accessories.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Accessories </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/home-furnishing.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Home Furnishing </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/gifts-birthday-anniversary-wedding.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Gifts </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/spiritual-books-pooja.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Spiritual </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/supplies.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Supplies </a>
	</li>
<li style="height:74px;border:2px solid  #fff;list-style: none;font-size: 42px; text-align: center;">
	<a href="http://www.craftsvilla.com/marriage-n-love.html" style="text-decoration:none;color:#492B26;position: relative;top: 25px;display: block;"> Wedding </a>
	</li>
	</div>
	</div>                   
	</div>';

$mobileHeader .= '<br><div style="text-align: center;width: 96%;height: 56px;border-radius: 15px;border:2px solid black;margin-left: 10px;"><table><tr><td><img src="'.Mage::getDesign()->getSkinUrl('mobileimg/icon-playstore.png').'" width=46px; style="margin-left: 25px;margin-top: -24px;"></td><td><font size="+4" >
<a href="https://play.google.com/store/apps/details?id=com.craftsvilla.app&hl=en" style="padding: 5px;text-decoration: none;line-height: 54px;" target="_blank"> Click to Download Craftsvilla App</a> </font></td></tr></table></div>';

$mobileHeader .= '<br><a href="http://www.craftsvilla.com/moodboard/collections_mobile.html?utm_source=craftsvilla&utm_medium=moodboard&utm_campaign=homepage_mobile"><img src="http://assets1.craftsvilla.com/moodboard/responsive_img/img/mobile_new.png" width=100% height="auto" /><a>';
$mobileHeader .= '<div id="signUpForm">
  <div id="welcome_img"><span class="spriteimg"></span></div>
  <div id="bg_welcome">
    <div id="form">
      <form action="/customer/account/createpost/" method="post" id="form-validate">
        <input type="text" name="firstname" id="firstname" placeholder="First Name*" />
        <input type="text" name="lastname" id="lastname" placeholder="Last Name*" />
        <input type="text" id="telephone" name="telephone" placeholder="Mobile Number" />
        <input type="text" name="email" id="email_address" placeholder="Email Address*" />
        <input type="password" name="password" id="password" placeholder="Password*" />
        <input type="password" id="confirmation" name="confirmation" placeholder="Re-Enter Password*" />
        <!-- <input type="text" id="birthdate" name="birthdate" placeholder="Birthdate (00/00/0000)" /> -->
        
        <div class="checkboxcss">
          <input type="checkbox" id="terms" name="agree" class="checkbox required-entry" />
          <label class="floatl"> I agree that I have read and understood the <a href="';
Mage::getBaseUrl().'terms-and-conditions-craftsvilla/';

$mobileHeader .= '">Craftsvilla User Agreement</a> &amp; <a href="';
Mage::getBaseUrl().'privacy-policy-craftsvilla/';
$mobileHeader .= '">the Privacy Policy</a></label>
	</div>
	<div id="form-button"> 
		<button type="submit" title="'.$head_class->__('Submit').'" class="spriteimg continueMdm">REGISTER</button>
	</div>
	</form>
	</div>
	<div id="formText">
		<p>Craftsvilla.com is the largest online marketplace for unique Indian products including Handmade, Vintage, Ethnic, Organic and Natural products. Register to get regular updates from Craftsvilla.com on new products, sellers and rewards. You can also register using your facebook account below.</p>
	<div id="formText-img" class="spriteimg"></div>
	<div id="formText-button">
		<button rel="facebook-connect" class="spriteimg facebook_login" type="submit">Register with Facebook</button>
		<a rel="facebook-connect"></a> </div>
	</div>
	</div>
	</div>';
$currentUrl = $head_class->helper('core/url')->getCurrentUrl();
$mobileHeader .= '<div id="login">
	<div id="welcome_img"><span class="spriteimg"></span></div>
	<div id="bg_welcome">
	<div id="form">
		<form id="login-top" method="post" action="'.$baseurl.'craftsvillacustomer/account/loginPost/">
		<input type="text" name="login[username]" id="email" placeholder="Email Address*" />
		<input type="password" name="login[password]" id="pass" placeholder="Password*" />
		<input type="hidden" name="login[currenturl]" id="url" value="'.$currentUrl.'" />
		<a style="margin-left: 5px;" class="links" href="/customer/account/forgotpassword/">Forgot Your Password?</a>
	<div> 
		<button style="float:none;display: block;" class="spriteimg continueMdm" type="submit" title="'.$head_class->__('Login').'">'.$head_class->__('Login').'</button>
	</div>
	</form>
	</div>
	<div id="formText">
		<p>Craftsvilla.com is the largest online marketplace for unique Indian products including Handmade, Vintage, Ethnic, Organic and Natural products. Register to get regular updates from Craftsvilla.com on new products, sellers and rewards. You can also register using your facebook account below.</p>
	<div id="formText-img" class="spriteimg"></div>
		<button rel="facebook-connect" class="spriteimg facebook_login" type="submit">Login with Facebook</button>
		<a rel="facebook-connect"></a> </div>
	</div>
	</div>';


return $mobileHeader;
	}

}
