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
class MagenThemes_MTOneStepCheckout_Model_Observer extends Mage_Core_Controller_Varien_Action {
    public function __construct() {

    }

    public function initController($observer) {
        if (Mage::helper('mtonestepcheckout')->isActive()) {
            $observer->getControllerAction()->_redirect('mtonestepcheckout');
        }
    }

    public function admin_system_config_changed_section_onestepcheckout ($observer) {
	$defaultCountry = Mage::getStoreConfig('mtonestepcheckout/general/default_country');
	$guestCheckout = Mage::getStoreConfig('mtonestepcheckout/general/guest_checkout');
	$enableAgreements = Mage::getStoreConfig('mtonestepcheckout/general/enable_agreements');
	$allowOrder = Mage::getStoreConfig('mtonestepcheckout/general/allow_order');
	$allowItems = Mage::getStoreConfig('mtonestepcheckout/general/allow_items');
	Mage::getModel('core/config')->saveConfig('general/country/default', $defaultCountry);
	Mage::getModel('core/config')->saveConfig('checkout/options/guest_checkout', $guestCheckout);
	Mage::getModel('core/config')->saveConfig('checkout/options/enable_agreements', $enableAgreements);
	Mage::getModel('core/config')->saveConfig('sales/gift_messages/allow_order', $allowOrder);
	Mage::getModel('core/config')->saveConfig('sales/gift_messages/allow_items', $allowItems);
	//Fix on Magento 1.5.0.0
	Mage::getModel('core/config')->saveConfig('sales/gift_options/allow_order', $allowOrder);
	Mage::getModel('core/config')->saveConfig('sales/gift_options/allow_items', $allowItems);
	//-----------------------//
    }

    public function admin_system_config_changed_section_general($observer) {
	$defaultCountry = Mage::getStoreConfig('general/country/default');
	Mage::getModel('core/config')->saveConfig('mtonestepcheckout/general/default_country', $defaultCountry);
    }

    public function admin_system_config_changed_section_checkout($observer) {
	$guestCheckout = Mage::getStoreConfig('checkout/options/guest_checkout');
	$enableAgreements = Mage::getStoreConfig('checkout/options/enable_agreements');
	Mage::getModel('core/config')->saveConfig('mtonestepcheckout/general/guest_checkout', $guestCheckout);
	Mage::getModel('core/config')->saveConfig('mtonestepcheckout/general/enable_agreements', $enableAgreements);
    }

    public function admin_system_config_changed_section_sales($observer) {
	$allowOrder = Mage::getStoreConfig('sales/gift_messages/allow_order');
	$allowItems = Mage::getStoreConfig('sales/gift_messages/allow_items');
	//Fix on Magento 1.5.0.0
	$allowOrder = Mage::getStoreConfig('sales/gift_options/allow_order');
	$allowItems = Mage::getStoreConfig('sales/gift_options/allow_items');
	//-----------------------//
	Mage::getModel('core/config')->saveConfig('mtonestepcheckout/general/allow_order', $allowOrder);
	Mage::getModel('core/config')->saveConfig('mtonestepcheckout/general/allow_items', $allowItems);
    }
}
