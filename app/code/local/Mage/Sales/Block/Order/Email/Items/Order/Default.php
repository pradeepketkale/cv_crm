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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Email items default renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Block_Order_Email_Items_Order_Default extends Mage_Core_Block_Template
{
    /**
     * Retrieve current order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->getItem()->getOrder();
    }

    public function getItemOptions()
    {
        $result = array();
        if ($options = $this->getItem()->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    public function getValueHtml($value)
    {
        if (is_array($value)) {
            return sprintf('%d', $value['qty']) . ' x ' . $this->htmlEscape($value['title']) . " " . $this->getItem()->getOrder()->formatPrice($value['price']);
        } else {
            return $this->htmlEscape($value);
        }
    }

    public function getSku($item)
    {
        if ($item->getProductOptionByCode('simple_sku'))
            return $item->getProductOptionByCode('simple_sku');
        else
            return $item->getSku();
    }
    
    public function getAdditionalData($item)
    {
    	if($item->getAdditionalData())
    	$custmsg1 = '<br><table cellpadding="0px" cellspacing="0px"><td style="border-style:solid;border-color:#4B7B9F;border-width:1px;border-height:1px;font-size:11px;padding:3px 9px"><font color="green">Message From Customer: </font><font color = "gray">'.$item->getAdditionalData().'</font></td></table>';
    	return $custmsg1;
    }
    
    public function getVendorDetails($item)
    {
        $vendorDetails = Mage::helper('udropship')->getVendor($item->getUdropshipVendor());
		// Craftsvilla Comment By Amit Pitre (11/09/2012) to hide vendor email and telephone in order sucess email.
        //$data = $vendorDetails->getVendorName()."<br/>".$vendorDetails->getEmail()."<br/>".$vendorDetails->getTelephone();
		$data = $vendorDetails->getVendorName();
        return $data;
    }
	//Below function added By dileswar on date : 07-10-2013  To add email & telephone of vendor on email
	public function getVendorTelephone($item)
    {
        $vendorDetails = Mage::helper('udropship')->getVendor($item->getUdropshipVendor());
		$data = "Seller Email: ".$vendorDetails->getEmail()."<br/>"."Seller Telephone: ".$vendorDetails->getTelephone();
		return $data;
    }
	

    /**
     * Return product additional information block
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }
}
