<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Block_Vendor_Shipment_Info extends Mage_Sales_Block_Items_Abstract
{
    protected function _construct()
    {
        Mage_Core_Block_Template::_construct();
        $this->addItemRender('default', 'sales/order_item_renderer_default', 'sales/order/shipment/items/renderer/default.phtml');
    }

    public function getShipment()
    {
        if (!$this->hasData('shipment')) {
            $id = (int)$this->getRequest()->getParam('id');
            $shipment = Mage::getModel('sales/order_shipment')->load($id);
            $this->setData('shipment', $shipment);
            Mage::helper('udropship')->assignVendorSkus($shipment);
        }
        return $this->getData('shipment');
    }

    public function getRemainingWeight()
    {
        $weight = 0;
        foreach ($this->getShipment()->getAllItems() as $item) {
            $weight += $item->getWeight()*$item->getQty();
        }
        foreach ($this->getShipment()->getAllTracks() as $track) {
            $weight -= $track->getTotalWeight();
        }
        return $weight;
    }

    public function getRemainingValue()
    {
        $value = 0;
        foreach ($this->getShipment()->getAllItems() as $item) {
            $value += $item->getPrice()*$item->getQty();
        }
        foreach ($this->getShipment()->getAllTracks() as $track) {
            $value -= (float)$track->getTotalValue();
        }
        return $value;
    }

    public function getUdpo($shipment)
    {
        if (Mage::helper('udropship')->isUdpoActive()) {
            return Mage::helper('udpo')->getShipmentPo($shipment);
        } else {
            return false;
        }
    }

}
