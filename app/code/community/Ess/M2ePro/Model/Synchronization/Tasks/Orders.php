<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
*/

class Ess_M2ePro_Model_Synchronization_Tasks_Orders extends Ess_M2ePro_Model_Synchronization_Tasks
{
    const PERCENTS_START = 0;
    const PERCENTS_END = 100;
    const PERCENTS_INTERVAL = 100;

    protected $_orderFixedItemTransactionInfo = array();
    protected $_orderAuctionItemTransactionInfo = array();

    protected $_iterationNumber = 0;
    protected $_percentInIteration = 0;

    protected $_configGroup = '/synchronization/settings/orders/';

    //####################################

    public function process()
    {
        // PREPARE SYNCH
        //---------------------------
        $this->prepareSynch();
        //$this->createEbayActions();
        //---------------------------

        // RUN SYNCH
        //---------------------------
        $this->execute();
        //---------------------------

        // CANCEL SYNCH
        //---------------------------
        //$this->executeEbayActions();
        $this->cancelSynch();
        //---------------------------
    }

    //####################################

    private function prepareSynch()
    {
        $this->_lockItem->activate();
        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_ORDERS);

        $this->_profiler->addEol();
        $this->_profiler->addTitle('Orders Synchronization');
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->addTimePoint(__CLASS__, 'Total time');
        $this->_profiler->increaseLeftPadding(5);

        $this->_lockItem->setTitle(Mage::helper('M2ePro')->__('Orders Synchronization'));
        $this->_lockItem->setPercents(self::PERCENTS_START);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Orders Synchronization" is started. Please wait...'));
    }

    private function cancelSynch()
    {
        $this->_lockItem->setPercents(self::PERCENTS_END);
        $this->_lockItem->setStatus(Mage::helper('M2ePro')->__('Task "Orders Synchronization" is finished. Please wait...'));

        $this->_profiler->decreaseLeftPadding(5);
        $this->_profiler->addEol();
        $this->_profiler->addTitle('--------------------------');
        $this->_profiler->saveTimePoint(__CLASS__);

        $this->_logs->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Logs::SYNCH_TASK_UNKNOWN);
        $this->_lockItem->activate();
    }

    //####################################

    private function execute()
    {
        // get last update time
        $lastSinceTime = $this->_getEbayCheckSinceTime();
        // Get accounts with enabled order syncrhonization
        $accounts = Mage::getModel('M2ePro/Accounts')->getCollection()
                                                     ->addFieldToFilter('orders_mode', Ess_M2ePro_Model_Accounts::ORDERS_MODE_YES)
                                                     ->getItems();

        if (!count($accounts)) {
            return;
        }
        
        $this->_iterationNumber = 0;
        $this->_percentInIteration = self::PERCENTS_INTERVAL / count($accounts);

        $this->_lockItem->activate();
        $lastSuccessTime = null;
        foreach ($accounts as $account) {
            
            $this->_lockItem->setPercents(self::PERCENTS_START + $this->_iterationNumber * $this->_percentInIteration);

            $this->_profiler->addEol();
            $this->_profiler->addTitle('Starting account "' . $account->getTitle() . '"');
            $this->_profiler->addTimePoint(__CLASS__ . "account" . $account->getTitle(), 'Synchronize Orders for account ' . $account->getTitle());

            $tempString = str_replace('%acc%',$account->getTitle(),Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is started. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            $this->_profiler->addTimePoint(__CLASS__ . "fetch_transaction_info" . $account->getTitle(), 'Fetch Transactions Info');

            $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                    ->processVirtual('sales', 'get', 'list',
                                     array(
                                          'account' => $account->getServerHash(),
                                          'last_update' => $lastSinceTime,
                                     ));

            $totalSalesEvents = array();
            $lastSuccessTime = $lastSinceTime;

            if (isset($response['sales']) && isset($response['updated_to'])) {
                $totalSalesEvents = $response['sales'];
                $lastSuccessTime = $response['updated_to'];
            }

            if (count($totalSalesEvents) <= 0) {
                continue;
            }

            $this->_profiler->saveTimePoint(__CLASS__ . 'fetch_transaction_info' . $account->getTitle());

            $this->_lockItem->setPercents(self::PERCENTS_START + ($this->_iterationNumber + 0.4) * $this->_percentInIteration);
            $this->_lockItem->activate();

            $tempString = str_replace('%acc%',$account->getTitle(),Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is in data processing state. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            $this->_profiler->addTimePoint(__CLASS__ . 'proccess_ebay_data' . $account->getTitle(), 'Proccess eBay Data');

            // Append account information, updated status, mapping status
            $this->_fillSalesEvents($totalSalesEvents, $account);

            // Save events to DB
            $this->_saveSalesEvents($totalSalesEvents);

            $this->_profiler->saveTimePoint(__CLASS__ . 'proccess_ebay_data' . $account->getTitle());

            $this->_lockItem->setPercents(self::PERCENTS_START + ($this->_iterationNumber + 0.75) * $this->_percentInIteration);
            $this->_lockItem->activate();

            // ---- There we have full item info from eBay
            // ---- And can procceess Import to Magento Order

            $tempString = str_replace('%acc%',$account->getTitle(),Mage::helper('M2ePro')->__('Task "Orders Synchronization" for eBay account: "%acc%" is in order creation state. Please wait...'));
            $this->_lockItem->setStatus($tempString);

            $this->_profiler->addTimePoint(__CLASS__ . 'import_orders' . $account->getTitle(), 'Orders Import');

            $cnt = count($totalSalesEvents);
            if ($cnt == 0) {
                $cnt = 1;
            }
            $percentToStep = 0.2 / $cnt;

            $stepNum = 0;
            foreach ($totalSalesEvents as &$eBaySale) {
                if (isset($eBaySale['id']) && (int)$eBaySale['id'] > 0) {
                    Mage::helper('M2ePro/Module')->getConfig()->setGroupValue('/synchronization/orders/', 'current_order_id', $eBaySale['id']);
                }

                $createOrderResult = Mage::getModel('M2ePro/EbayOrders')->createMagentoOrder($eBaySale, $account);
                if ($createOrderResult['success'] != false) {
                    $eBaySale['magento_order_id'] = $createOrderResult['id'];
                }
                $this->_lockItem->setPercents(self::PERCENTS_START + ($this->_iterationNumber + 0.75 + $stepNum * $percentToStep) * $this->_percentInIteration);
                $stepNum++;

                if (isset($eBaySale['id']) && (int)$eBaySale['id'] > 0) {
                    Mage::helper('M2ePro/Module')->getConfig()->deleteGroupValue('/synchronization/orders/', 'current_order_id');
                }
            }

            $this->_profiler->saveTimePoint(__CLASS__ . 'import_orders' . $account->getTitle());

            $this->_lockItem->setPercents(self::PERCENTS_START + ($this->_iterationNumber + 0.95) * $this->_percentInIteration);
            $this->_lockItem->activate();


            $this->_saveSalesEvents($totalSalesEvents);

            $this->_profiler->saveTimePoint(__CLASS__ . 'account' . $account->getTitle());

            $this->_iterationNumber++;

        } // foreach accounts

        if ($lastSuccessTime != null) {
            $this->_setEbayCheckSinceTime($lastSuccessTime);
        }
    }

    protected function _setEbayCheckSinceTime($sinceTime)
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue($this->_configGroup, 'since_time', Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($sinceTime));
    }

    protected function _getEbayCheckSinceTime()
    {
        $lastSinceTime = Mage::helper('M2ePro/Module')->getConfig()->getGroupValue($this->_configGroup, 'since_time');

        if (is_null($lastSinceTime)) {
            $lastSinceTime = new DateTime();
            $lastSinceTime->modify('-1 year');
        } else {
            $lastSinceTime = new DateTime($lastSinceTime);
        }
        //------------------------

        // Get min should for synch
        //------------------------
        $minShouldTime = new DateTime();
        $minShouldTime->modify('-1 month');
        //------------------------

        // Prepare last since time
        //------------------------
        if ((int)$lastSinceTime->format('U') < (int)$minShouldTime->format('U')) {
            $lastSinceTime = new DateTime();
            //if (Mage::helper('M2ePro/Module')->isInstalledM2eLastVersion()) {
                $lastSinceTime->modify('-1 hour');
            //} else {
            //    $lastSinceTime->modify("-10 days");
            //}
            $this->_setEbayCheckSinceTime($lastSinceTime);
        }

        return Ess_M2ePro_Model_Connectors_Ebay_Abstract::ebayTimeToString($lastSinceTime);
    }

    /**
     * Fill status for sales events
     * - Append account id
     * - Set modify status (for existing adding id)
     * - Set product mapping (import new product)
     *
     * Check and update status for eBay sales events algorithm
     * 1) Get exiting events from table
     * 2) Check update time
     *
     * For updated events append events id value
     *
     * @return void
     */
    protected function _fillSalesEvents(&$receiveSalesEvents, $account)
    {
        // This action spend about 28 percent of time

        $eBayOrdersModel = new Ess_M2ePro_Model_EbayOrders;

        $currentStep = 1;
        $cnt = count($receiveSalesEvents);
        if ($cnt == 0) {
            $cnt = 1;
        }

        $percentInEachStep = 0.35 / $cnt;

        foreach ($receiveSalesEvents as $key => $singleSales) {

            $this->_lockItem->setPercents(self::PERCENTS_START + ($this->_iterationNumber + 0.4 + $currentStep * $percentInEachStep) * $this->_percentInIteration);
            $currentStep++;

            $paidTime = null;
            if ($singleSales['payment_time']) {
                $paidTime = $singleSales['payment_time'];
            }

            $transactionStatus = $eBayOrdersModel->getSalesStatus($singleSales['ebay_order_id'], $singleSales['update_time'], $paidTime);

            if ($transactionStatus == Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_NOT_MODIFY) {
                unset($receiveSalesEvents[$key]);
                continue;
            }

            if ($transactionStatus == Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_NEW && count($singleSales['transaction_info']) > 1) {
                // This is NEW combined order. We need to check that don't have such transactions is list

                // When for account activated 'force incomplete', and for founded transaction created order - do nothing with this item
                // Otherwise we just remove such transaction and create separate eBay order
                $orderItemsModel = Mage::getModel('M2ePro/EbayOrdersItems');
                foreach ($singleSales['transaction_info'] as $transactionKey => $singleTransaction) {
                    $findEbayOrderId = $orderItemsModel->findExistingTransaction($singleTransaction['transaction_id'], $singleTransaction['item_id']);
                    if ($findEbayOrderId !== false) {
                        // Found some eBay order
                        if ($account->getOrdersStatusCheckoutIncomplete()) {
                            // Enabled mode - Force order on 'incomplete checkout'. Remove such transaction from order
                            unset($singleSales['transaction_info'][$transactionKey]);
                        } else {
                            // Standard mode (create order on completed checkout)
                            // Delete already create 'eBay order' with all assigned items, transactions and logs
                            $eBayOrdersModel->load($findEbayOrderId)->delete();
                        }
                    }
                }
                if (count($singleSales['transaction_info']) == 0) {
                    // We remove all items from order. This happens only when enabled 'incomplete checkout'.
                    // And already has created order
                    unset($receiveSalesEvents[$key]);
                    continue;
                }

            }

            if (isset($singleSales['is_refund']) && $singleSales['is_refund']) {
                // it's refund transaction just ignore it
                // @todo in next version we will handle this transaction and display it to customer
                unset($receiveSalesEvents[$key]);
                continue;
            }

            if ($transactionStatus == Ess_M2ePro_Helper_Sales::TRANSACTION_STATUS_UPDATE) {
                $receiveSalesEvents[$key]['id'] = $eBayOrdersModel->loadedSale->getId();
                $receiveSalesEvents[$key]['magento_order_id'] = $eBayOrdersModel->loadedSale->getMagentoOrderId();
            }

            // Append account and marketplace information
            $receiveSalesEvents[$key]['account_id'] = $account->getId();
            $receiveSalesEvents[$key]['marketplace_id'] = null;
            foreach ($singleSales['transaction_info'] as $itemKey => $singleOrderItem) {
                $mappedInfo = $this->_getMappedProductInfo($singleOrderItem, $account);
                if (!is_array($mappedInfo)) {
                    $mappedInfo = array(
                        'product_id' => NULL,
                        'store_id' => NULL
                    );
                }
                $receiveSalesEvents[$key]['transaction_info'][$itemKey] += $mappedInfo;

                if (is_null($receiveSalesEvents[$key]['marketplace_id'])) {
                    $marketplaceId = Mage::getModel('M2ePro/Marketplaces')->load($singleOrderItem['item_site'], 'code')->getId();

                    if (!is_null($marketplaceId)) {
                        $receiveSalesEvents[$key]['marketplace_id'] = (int)$marketplaceId;
                    }
                }
                unset($receiveSalesEvents[$key]['transaction_info'][$itemKey]['item_site']);
            }

            if (!is_null($receiveSalesEvents[$key]['marketplace_id'])) {
                $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
                $tableDictShipping = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_shippings');
                $tableDictMarketplace = Mage::getSingleton('core/resource')->getTableName('m2epro_dictionary_marketplaces');

                $dbSelect = $connRead->select()
                                     ->from($tableDictShipping,'title')
                                     ->where('`marketplace_id` = ?',(int)$receiveSalesEvents[$key]['marketplace_id'])
                                     ->where('`ebay_id` = ?', $receiveSalesEvents[$key]['shipping_selected_service']);
                $shipping = $connRead->fetchRow($dbSelect);

                if (is_array($shipping) && isset($shipping['title'])) {
                    $receiveSalesEvents[$key]['shipping_selected_service'] = $shipping['title'];
                }

                $dbSelect = $connRead->select()
                                     ->from($tableDictMarketplace,'payments')
                                     ->where('`marketplace_id` = ?',(int)$receiveSalesEvents[$key]['marketplace_id']);
                $marketplace = $connRead->fetchRow($dbSelect);

                if (!is_array($marketplace) || !isset($marketplace['payments'])) {
                    continue;
                }

                $payments = (array)json_decode($marketplace['payments'], true);
                foreach ($payments as $payment) {
                    if ($payment['ebay_id'] == $receiveSalesEvents[$key]['payment_used']) {
                        $receiveSalesEvents[$key]['payment_used'] = $payment['title'];
                        break;
                    }
                }
            }

        }
    }

    /**
     * Get magento product connected to eBay transaction.
     * Retrieve mapping by itemId<->productId, sku, create new product
     *
     * @param  $orderItemInfo
     * @param $account assigned account to checking
     * @return array|null
     */
    protected function _getMappedProductInfo($orderItemInfo, $account)
    {
        // There we need analise order imported settings
        // depend from it find mapped product id

        // On mappingInfo -> [productId, storeId]
        $productMappingInfo = null;

        if ($account->getOrdersListingsMode() == Ess_M2ePro_Model_Accounts::ORDERS_LISTINGS_MODE_YES) {
            $productMappingInfo = $this->_getMappedProductInfoByItemId($orderItemInfo['item_id']);
        }
        if ($productMappingInfo == null &&
            $account->getOrdersEbayMode() == Ess_M2ePro_Model_Accounts::ORDERS_EBAY_MODE_YES) {
            $productMappingInfo = $this->_getMappedProductInfoBySku($orderItemInfo['item_sku']);
        }
        if ($productMappingInfo == null &&
            $account->getOrdersEbayMode() == Ess_M2ePro_Model_Accounts::ORDERS_EBAY_MODE_YES &&
            $account->getOrdersEbayCreateProduct() == Ess_M2ePro_Model_Accounts::ORDERS_EBAY_CREATE_PRODUCT_YES) {

            $productMappingInfo = $this->_getNewMappedProductInfo($orderItemInfo['item_id'], $account);
        }

        return $productMappingInfo;
    }

    /**
     * Find mapped magento product by eBay itemId
     *
     * @param  $eBayItemId
     * @return array|null
     */
    protected function _getMappedProductInfoByItemId($eBayItemId)
    {
        $mappedItems = Mage::getModel('M2ePro/EbayItems')
                ->getCollection()
                ->addFieldToFilter('item_id', $eBayItemId)
                ->getItems();

        if (!is_array($mappedItems)) {
            return null;
        }

        foreach ($mappedItems as $item) {
            return array(
                'id' => $item->getId(),
                'product_id' => $item->getProductId(),
                'store_id' => $item->getStoreId(),
                'not_m2e_product' => false
            );
        }
        return null;
    }

    /**
     * Find mapped magento product by eBay product SKU
     *
     * @param  $eBayItemSku
     * @return array|null
     */
    protected function _getMappedProductInfoBySku($sku)
    {
        if (!$sku) {
            return null;
        }

        $product = Mage::getModel('catalog/product')->loadByAttribute('sku', substr($sku, 0, 64));

        if ($product && $product->getId()) {
            return array(
                'product_id' => $product->getId(),
                'store_id' => Mage::helper('M2ePro/Sales')->getProductFirstStoreId($product),
                'not_m2e_product' => true
            );
        }

        return null;
    }

    /**
     * Create new product on magento using eBay product information.
     *
     * @return array|null
     */
    protected function _getNewMappedProductInfo($eBayItemId, $account)
    {
        $response = Mage::getModel('M2ePro/Connectors_Ebay_Dispatcher')
                ->processVirtual('item', 'get', 'info',
                                 array(
                                      'account' => $account->getServerHash(),
                                      'item_id' => $eBayItemId,
                                 ));


        if (!isset($response['result'])) {
            return null;
        }

        $itemInfoFromEbay = $response['result'];

        // Get storeId from settings for non m2e product
        //        $account->getOrdersListingsStoreMode();
        if ($storeId = $account->getOrdersListingsStoreId()) {
            $itemInfoFromEbay['storeId'] = $storeId;
        } else {
            $itemInfoFromEbay['storeId'] = Mage::helper('M2ePro/Sales')->getDefaultStoreId();
        }

        $resultOfCreateProduct = null;

        try {
            $resultOfCreateProduct = Mage::getModel('M2ePro/Import_Product')->importProduct($itemInfoFromEbay);
        } catch (Exception $exception) {}

        if ($resultOfCreateProduct != null) {
            return array(
                'product_id' => $resultOfCreateProduct['id'],
                'store_id' => $resultOfCreateProduct['storeId'],
                'not_m2e_product' => true
            );
        }

        return null;
    }

    /**
     * Save sales events list to DB
     *
     * @param  $receiveSalesEvents
     *
     * @return id list of modify events
     */
    protected function _saveSalesEvents(&$receiveSalesEvents)
    {
        $eBayOrdersModel = new Ess_M2ePro_Model_EbayOrders;
        foreach ($receiveSalesEvents as $key => $singleSales) {
            try {
                $rId = $eBayOrdersModel->saveSalesEvent($singleSales);
                if ($rId) {
                    $receiveSalesEvents[$key]['id'] = $rId;
                }
            } catch (Exception $exception) {
                // @todo handle saving error
            }
        }
    }

    //####################################
}