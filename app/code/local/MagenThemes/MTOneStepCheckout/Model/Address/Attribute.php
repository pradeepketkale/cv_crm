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
class MagenThemes_MTOneStepCheckout_Model_Address_Attribute extends Mage_Customer_Model_Attribute
{
  public function getIsRequired()
  {
    if(Mage::helper('mtonestepcheckout')->excludeCity() && $this->getName() == 'city') {
      return false;
    }
    
    if(Mage::helper('mtonestepcheckout')->excludeTelephone() && $this->getName() == 'telephone') {
      return false;
    }
    
    if(Mage::helper('mtonestepcheckout')->excludePostcode() && $this->getName() == 'postcode') {
      return false;
    }
    
    if(Mage::helper('mtonestepcheckout')->excludeRegion() && ($this->getName() == 'region' || $this->getName() == 'region_id')) {
      return false;
    }
    return $this->_getScopeValue('is_required');
  }
}