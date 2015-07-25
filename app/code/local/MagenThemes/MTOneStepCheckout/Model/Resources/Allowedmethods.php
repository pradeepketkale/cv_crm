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
class MagenThemes_MTOneStepCheckout_Model_Resources_Allowedmethods
{
    public function toOptionArray()
    {
        return Mage::getModel('adminhtml/system_config_source_shipping_allmethods')->toOptionArray(true);
    }
}
