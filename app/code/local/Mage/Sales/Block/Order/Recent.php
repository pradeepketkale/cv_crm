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
 * Sales order history block
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Sales_Block_Order_Recent extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();

        //TODO: add full name logic
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
            ->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
            ->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addAttributeToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize('5')
            ->load()
        ;

        $this->setOrders($orders);
    }
    
    public function getRecentShipments()
    {
    	if (Mage::helper('udropship')->isSalesFlat()) {
    		$res = Mage::getSingleton('core/resource');
    		$collection = Mage::getResourceModel('sales/order_shipment_grid_collection');
    		$collection->getSelect()->join(
    		array('t'=>$res->getTableName('sales/shipment')),
                            't.entity_id=main_table.entity_id', 
    		array('udropship_vendor', 'udropship_available_at', 'udropship_method',
                                'udropship_method_description', 'udropship_status', 'shipping_amount', 'total_value'
    		)
    		);
    		$collection->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId());
    		$collection->addAttributeToSort('created_at', 'desc');
    		$collection->setPageSize(5);
    		//Mage::log($data[3]);
    		//Mage::log($data[0]);
    		//Mage::log($collection->getSelect()->__toString());
    	} else {
    		$collection = Mage::getResourceModel('sales/order_shipment_collection')
    		->addAttributeToSelect('increment_id')
    		->addAttributeToSelect('created_at')
    		->addAttributeToSelect('total_qty')
    		->addAttributeToSelect('udropship_status')
    		->addAttributeToSelect('udropship_vendor')
    		->addAttributeToSelect('udropship_method_description')
    		->addAttributeToSelect('shipping_amount')
    		->joinAttribute('shipping_firstname', 'order_address/firstname', 'shipping_address_id', null, 'left')
    		->joinAttribute('shipping_lastname', 'order_address/lastname', 'shipping_address_id', null, 'left')
    		->joinAttribute('order_increment_id', 'order/increment_id', 'order_id', null, 'left')
    		->joinAttribute('order_created_at', 'order/created_at', 'order_id', null, 'left')
    		->joinAttribute('base_currency_code', 'order/base_currency_code', 'order_id', null, 'left')
    		;
    	}
    
    	return $collection;
    }
    

    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', array('order_id' => $order->getId()));
    }

    public function getTrackUrl($order)
    {
        return $this->getUrl('sales/order/track', array('order_id' => $order->getId()));
    }
    
    public function getShipmetUrl($order)
    {
      // sales/order/shipment/order_id/879/
        return $this->getUrl('sales/order/shipment', array('order_id' => $order->getOrderId(), 'shipment_id' => $order->getId()));
    }
    
    public function getShipmetPrintUrl($order)
    {
    	// sales/order/shipment/order_id/879/
    	return $this->getUrl('sales/order/printShipment/shipment_id/', array('shipment_id' => $order->getId()));
    }

    protected function _toHtml()
    {
        if ($this->getOrders()->getSize() > 0) {
            return parent::_toHtml();
        }
        return '';
    }

    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', array('order_id' => $order->getId()));
    }
}
