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
class MagenThemes_MTOneStepCheckout_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    protected function _beforeToHtml(){
        if(Mage::helper('mtonestepcheckout')->enableFSockOpen()) {
            if(Mage::helper('mtonestepcheckout')->isActive()) {
                if(Mage::helper('mtonestepcheckout')->useGeoIp()) {
                    $this->setTemplate('mtonestepcheckout/sales/order/view/info.phtml');
                }
            }
        } else {
            if(Mage::helper('mtonestepcheckout')->isActive()) {
                if(Mage::helper('mtonestepcheckout')->useGeoIp()) {
                    Mage::getSingleton('core/session')->addError('Your server is not support fsockopen, Please disable GeoIp of module MT OneStepCheckOut.');
                }
            }
        }
        parent::_beforeToHtml();
    }
}
