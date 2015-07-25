<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Track
{
    //####################################

    /**
     * Function calling after adding tracking number for magento shipping.
     * This working only for eBay imported sales
     *
     * @param Varien_Event_Observer $observer
     * @return
     */
    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            $track = $observer->getEvent()->getTrack();
            $order = $track->getShipment()->getOrder();

            if ($order->getShippingMethod() != 'm2eproshipping_m2eproshipping') {
                // Not eBay imported order, skip
                return;
            }

            if (is_null($orderId = $order['entity_id'])) {
                return;
            }

            // Check for mapping
            $collection = Mage::getModel('M2ePro/EbayOrders')->getCollection()->addFieldToFilter('magento_order_id', $orderId);
            if ($collection->getSize() < 1) {
                return;
            }

            // first item from collection
            /** @var $loadedEbayOrder Ess_M2ePro_Model_EbayOrders */
            $loadedEbayOrder = $collection->getFirstItem();
            if (!$loadedEbayOrder || !$loadedEbayOrder->getId()) {
                return;
            }

            $trackingDetails = array(
                'carrier_code' => $track->getCarrierCode(),
                'tracking_number' => $track->getNumber()
            );

            $result = $loadedEbayOrder->markEbaySalesShipped($trackingDetails);

            foreach ($result['messages'] as $message) {
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('M2ePro')->__($message));

                if (method_exists(new Mage(), 'getVersionInfo')) {
                    $order->addStatusHistoryComment($message, 'complete')->setIsCustomerNotified(false);
                } else {
                    $order->addStatusToHistory('complete', $message, false);
                }
            }

            foreach ($result['errors'] as $error) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('M2ePro')->__($error));

                // Adding notice to magento order log
                if (method_exists(new Mage(), 'getVersionInfo')) {
                    $order->addStatusHistoryComment($error, 'complete')->setIsCustomerNotified(false);
                } else {
                    $order->addStatusToHistory('complete', $error, false);
                }
            }

            if (isset($result['success']) && !$result['success'] && !count($result['errors'])) {
                $message = Mage::helper('M2ePro')->__('Tracking Number for eBay Order cannot be added. Reason: eBay Failure.');
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