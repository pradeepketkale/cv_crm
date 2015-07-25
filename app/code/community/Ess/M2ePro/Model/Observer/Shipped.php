<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Shipped
{
    //####################################
    
    /**
     * Call after create shipping for magento order
     * 
     * Work only for order imported from eBay Sale
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::registry('m2epro_skip_shipping_observer') != null) {
                // Not process invoice observer when set such flag
                Mage::unregister('m2epro_skip_shipping_observer');
                return;
            }

            $shipment = $observer->getEvent()->getShipment();
            $order = $shipment->getOrder();

            if (is_null($orderId = $order['entity_id'])) {
                return;
            }

            $collection = Mage::getModel('M2ePro/EbayOrders')->getCollection()->addFieldToFilter('magento_order_id', $orderId);
            if ($collection->getSize() < 1) {
                return;
            }

            // first item from collection
            $loadedEbayOrder = $collection->getFirstItem();
            if (!$loadedEbayOrder || !$loadedEbayOrder->getId()) {
                return;
            }

            $track = $shipment->getTracksCollection()->getFirstItem();
            $trackingDetails = array();

            if (count($track->getData())) {
                $ebayCarriers = array('dhl' => 'DHL', 'fedex' => 'FedEx', 'ups' => 'UPS', 'usps' => 'USPS');
                $carrier = strtolower($track->getCarrierCode());
                $carrier = isset($ebayCarriers[$carrier]) ? $ebayCarriers[$carrier] : 'Other';

                $trackingDetails = array(
                    'carrier_code' => $carrier,
                    'tracking_number' => $track->getNumber()
                );
            }

            $result = $loadedEbayOrder->markEbaySalesShipped($trackingDetails);

            foreach($result['messages'] as $message) {
                Mage::getSingleton('adminhtml/session')->addSuccess($message);

                // Adding notice to magento order log
                if (method_exists(new Mage(), 'getVersionInfo')) {
                    $order->addStatusHistoryComment($message, 'complete')->setIsCustomerNotified(false);
                } else {
                    $order->addStatusToHistory('complete', $message, false);
                }
            }

            foreach ($result['errors'] as $error) {
                Mage::getSingleton('adminhtml/session')->addError($error);

                // Adding notice to magento order log
                if (method_exists(new Mage(), 'getVersionInfo')) {
                    $order->addStatusHistoryComment($error, 'complete')->setIsCustomerNotified(false);
                } else {
                    $order->addStatusToHistory('complete', $error, false);
                }
            }

            if (isset($result['success']) && !$result['success'] && !count($result['errors'])) {
                $message = Mage::helper('M2ePro')->__('Shipping status for eBay Order cannot be updated. Reason: eBay Failure.');
                Mage::getSingleton('adminhtml/session')->addError($message);
                Mage::getModel('M2ePro/EbayOrdersLogs')->addLogMessage($loadedEbayOrder->getId(), $message, null, Ess_M2ePro_Model_EbayOrdersLogs::MESSAGE_CODE_ERROR);
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################
}