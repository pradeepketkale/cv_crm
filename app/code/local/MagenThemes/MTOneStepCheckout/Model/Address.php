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
class MagenThemes_MTOneStepCheckout_Model_Address extends Mage_Sales_Model_Quote_Address
{
    public function validate()
    {
        $errors = array();
        $helper = Mage::helper('customer');
        $this->implodeStreetAddress();
        if (!Zend_Validate::is($this->getFirstname(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter first name.');
        }

        if (!Zend_Validate::is($this->getLastname(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter last name.');
        }

        if (!Zend_Validate::is($this->getStreet(1), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter street.');
        }

        if (!Zend_Validate::is($this->getCity(), 'NotEmpty') && !Mage::helper('mtonestepcheckout')->excludeCity()) {
            $errors[] = $helper->__('Please enter city.');
        }

        if (!Zend_Validate::is($this->getTelephone(), 'NotEmpty') && !Mage::helper('mtonestepcheckout')->excludeTelephone()) {
            $errors[] = $helper->__('Please enter telephone.');
        }

        $_havingOptionalZip = Mage::helper('directory')->getCountriesWithOptionalZip();
        if (!in_array($this->getCountryId(), $_havingOptionalZip) && !Zend_Validate::is($this->getPostcode(), 'NotEmpty') && !Mage::helper('mtonestepcheckout')->excludePostcode()) {
            $errors[] = $helper->__('Please enter zip/postal code.');
        }

        if (!Zend_Validate::is($this->getCountryId(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter country.');
        }

        if ($this->getCountryModel()->getRegionCollection()->getSize()
               && !Zend_Validate::is($this->getRegionId(), 'NotEmpty') && !Mage::helper('mtonestepcheckout')->excludeRegion()) {
            $errors[] = $helper->__('Please enter state/province.');
        }

        if (empty($errors) || $this->getShouldIgnoreValidation()) {
            return true;
        }
        return $errors;
    }
}
