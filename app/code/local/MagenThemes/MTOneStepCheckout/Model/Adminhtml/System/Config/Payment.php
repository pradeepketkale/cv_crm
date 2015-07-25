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
class MagenThemes_MTOneStepCheckout_Model_Adminhtml_System_Config_Payment extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    public function _getFieldHtml($fieldset)
    {
        $content = 'Setup Payment method <a href="'.$this->getUrl('*/*/*').'">Magentheme.com</a>';
        return $content;
    }
}
