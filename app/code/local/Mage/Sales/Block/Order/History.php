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

class Mage_Sales_Block_Order_History extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/history.phtml');

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

        $this->setOrders($collection);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'sales.order.history.pager')
            ->setCollection($this->getOrders());
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getTrackUrl($order)
    {
        return $this->getUrl('*/*/track', array('order_id' => $order->getId()));
    }

    public function getReorderUrl($order)
    {
        return $this->getUrl('*/*/reorder', array('order_id' => $order->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
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
}
