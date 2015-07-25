<?php
/******************************************************
 * @package MT OneStepCheckOut module for Magento 1.4.x.x and Magento 1.5.x.x
 * @version 1.5.2.1
 * @author http://www.magentheme.com
 * @copyright (C) 2011- MagenTheme.Com
 * @license PHP files are GNU/GPL
*******************************************************/
?>
<?php
class MagenThemes_MTOneStepCheckout_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * @return boolean : The module is enable or disable
     */
    public function isActive() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/general/active');
    }
    /*
     * @return string : Default ZIP/Postal Code
     */
    public function defaultZipCode() {
        return Mage::getStoreConfig('mtonestepcheckout/general/default_zipcode');
    }
    /*
     * @return string : Default shipping method
     */
    public function defaultShippingMethod() {
        return Mage::getStoreConfig('mtonestepcheckout/shipping/default_shipping_method');
    }
    /*
     * @return string : Default payment method
     */
    public function defaultPaymentMethod() {
        return Mage::getStoreConfig('mtonestepcheckout/payment/default_payment_method');
    }
    /*
     * @return string : Checkout title
     */
    public function checkoutTitle() {
        return Mage::getStoreConfig('mtonestepcheckout/general/checkout_title');
    }
    /*
     * @return string : Checkout Description
     */
    public function checkoutDescription() {
        return Mage::getStoreConfig('mtonestepcheckout/general/checkout_description');
    }
    /*
     * @return boolean : Reload Payment method when saved shipping method or not
     */
    public function isReloadPayment() {
        return Mage::getStoreConfig('mtonestepcheckout/shipping/reload_payment');
    }
    /*
     * @return boolean : Use ajax to save payment method or not
     */
    public function isUseAjaxSavePayment() {
        return Mage::getStoreConfig('mtonestepcheckout/payment/use_ajax');
    }
    /*
     * @return boolean : Exclude Shipping Address or not
     */
    public function excludeShippingAddress() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/shipping_address');
    }
    /*
     * @return boolean : Exclude City Field or not
     */
    public function excludeFax() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/fax');
    }
    /*
     * @return boolean : Exclude Telephone Field or not
     */
    public function excludeTelephone() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/telephone');
    }
    /*
     * @return boolean : Exclude Company Field or not
     */
    public function excludeCompany() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/company');
    }
    /*
     * @return boolean : Exclude City Field or not
     */
    public function excludeCity() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/city');
    }
    /*
     * @return boolean : Exclude Postcode Field or not
     */
    public function excludePostcode() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/postcode');
    }
    /*
     * @return boolean : Exclude Region Field or not
     */
    public function excludeRegion() {
        return Mage::getStoreConfig('mtonestepcheckout/exclude/region');
    }
    /*
     * @return boolean : Reload Shipping method when change country
     */
    public function onChangeCountry() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/shipping/change_country');
    }
    /*
     * @return boolean : Reload Shipping method when change Postcode
     */
    public function onChangePostcode() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/shipping/change_postcode');
    }
    /*
     * @return boolean : Reload Shipping method when change Region
     */
    public function onChangeRegion() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/shipping/change_region');
    }
    /*
     * @return boolean : Use GeoIp or not
     */
    public function useGeoIp() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/general/use_geoip');
    }
    /*
     * @return string : Socket database of geoIP
     */
    public function geoIpDatabase() {
        return Mage::getStoreConfig('mtonestepcheckout/general/geoip_database');
    }
    /*
     * @return boolean : Include Newsletter or not
     */
    public function includeNewsletter() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/exclude/is_subscribed');
    }
    /*
     * @return boolean : Include Coupon or not
     */
    public function includeCoupon() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/exclude/coupon');
    }
    /*
     * @return boolean : Server allow fsockopen or not
     */
    public function enableFSockOpen() {
        if(!function_exists("fsockopen")) {
            return false;
        }
        return true;
    }
    /*
     * @return boolean : Enable Update Qty or not
     */
    public function enableUpdateQty() {
        return (boolean) Mage::getStoreConfig('mtonestepcheckout/exclude/update_qty');
    }
}
