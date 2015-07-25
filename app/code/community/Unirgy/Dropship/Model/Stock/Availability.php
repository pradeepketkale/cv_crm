<?php

class Unirgy_Dropship_Model_Stock_Availability extends Varien_Object
{
    public function alwaysAssigned($items)
    {
        // needed only for external stock check
        $this->collectStockLevels($items);
    }

    public function localIfInStock($items)
    {
        $this->collectStockLevels($items, array('request_local'=>true));

        $localVendorId = Mage::helper('udropship')->getLocalVendorId();
        foreach ($items as $item) {
            if ($item->getUdropshipVendor()==$localVendorId) {
                continue;
            }
            $stock = $item->getUdropshipStockLevels();
            if (!empty($stock[$localVendorId]['status'])) {
                $item->setUdropshipVendor($localVendorId);
            }
        }
    }

    /**
    * Retrieve configuration flag whether to ship from local when in stock
    *
    * @param mixed $store
    * @param int|Unirgy_Dropship_Model_Vendor
    * @return boolean
    */
    public function getUseLocalStockIfAvailable($store=null, $vendor=null)
    {
        // if vendor is supplied
        if (!is_null($vendor)) {
            // get vendor object
            if (is_numeric($vendor)) {
                $vendor = Mage::helper('udropship')->getVendor($vendor);
            }
            $result = $vendor->getUseLocalStock();
            // if there's vendor specific configuration, use it
            if (!is_null($result) && $result!==-1) {
                return $result;
            }
        }
        // otherwise return store configuration value
        return Mage::getStoreConfig('udropship/stock/availability', $store)=='local_if_in_stock';
    }

    /**
    * Should we get the real inventory status or augmented by local stock?
    *
    * @return boolean
    */
    public function getTrueStock()
    {
        $area = Mage::getDesign()->getArea();
        $controller = Mage::app()->getRequest()->getControllerName();

        // when creating order in admin, always use the true stock status
        if (!Mage::registry('inApplyStockAvailability') && $area=='adminhtml' && $controller!='sales_order_create') {
            return true;
        }
        // alwyas use trueStock if configuration says so
        if (!$this->getData('true_stock') && !$this->getUseLocalStockIfAvailable()) {
            $this->setTrueStock(true);
        }

        return $this->getData('true_stock');
    }

    /**
     * Collect stock levels for all vendors of the product
     *
     * By default retrieves stock level for assigned vendor and local, if needed
     *
     * @param mixed $items
     */
    public function collectStockLevels($items, $options=array())
    {
        $hlp = Mage::helper('udropship');
        // get $quote and $order objects
        foreach ($items as $item) {
            if (empty($quote)) {
                $quote = $item->getQuote();
                $order = $item->getOrder();
                break;
            }
            /*
            $product = $item->getProduct();
            $pId = $item->getProductId();
            if (!$product || !$product->hasUdropshipVendor()) {
                // if not available, load full product info to get product vendor
                $product = Mage::getModel('catalog/product')->load($pId);
                $item->setData('product', $product);
            }

            $qty = $item->getQty();
            if (($options = $item->getQtyOptions()) && $qty > 0) {
                $qty = $product->getTypeInstance(true)->prepareQuoteItemQty($qty, $product);
                $item->setData('qty', $qty);
            }
            break;
            */
        }
        if (!$quote && !$order) {
            $this->setStockResult(array());
            return $this;
        }
        $store = $quote ? $quote->getStore() : $order->getStore();
        $localVendorId = Mage::helper('udropship')->getLocalVendorId($store);

        $requests = array();
        foreach ($items as $item) {
            if ($item->getHasChildren()) {
                //$product->getTypeId()=='bundle' || $product->getTypeId()=='configurable') {
                continue;
            }
            $product = $item->getProduct();
            $pId = $item->getProductId();
            if (!$product || !$product->hasUdropshipVendor()) {
                // if not available, load full product info to get product vendor
                $product = Mage::getModel('catalog/product')->load($pId);
            }
            $vId = $product->getUdropshipVendor() ? $product->getUdropshipVendor() : $localVendorId;
            $v = $hlp->getVendor($vId);
            $sku = $product->getVendorSku() ? $product->getVendorSku() : $product->getSku();
            $requestVendors = array(
            	$vId=>array(
            		'sku'=>$sku, 
            		'zipcode_match' => $v->isZipcodeMatch($hlp->getZipcodeByItem($item)),
            	)
            );
            if (!empty($options['request_local'])) {
                $requestVendors[$localVendorId] = array(
                	'sku'=>$product->getSku(),
                	'zipcode_match' => $hlp->getVendor($localVendorId)->isZipcodeMatch($hlp->getZipcodeByItem($item)),
                );
            }

            $method = $v->getStockcheckMethod() ? $v->getStockcheckMethod() : 'local';
            $cb = $v->getStockcheckCallback($method);
            if (!$cb) {
                continue;
            }
            if (empty($requests[$method])) {
                $requests[$method] = array(
                    'callback' => $cb,
                    'products' => array(),
                );
            }
            if (empty($requests[$method]['products'][$pId])) {
                $requests[$method]['products'][$pId] = array(
                    'stock_item' => $product->getStockItem(),
                    'qty_requested' => 0,
                    'vendors' => $requestVendors,
                );
            }
            
            $requests[$method]['products'][$pId]['qty_requested'] += $hlp->getItemStockCheckQty($item);
            
            /*
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $product = $child->getProduct();
                    if ($product->getTypeInstance()->isVirtual()) {
                        continue;
                    }
                    $pId = $child->getProductId();
                    if (empty($requests[$method]['products'][$pId])) {
                        $requests[$method]['products'][$pId] = array(
                            'stock_item' => $product->getStockItem(),
                            'qty_requested' => 0,
                            'vendors' => $requestVendors,
                        );
                    }
                    $requests[$method]['products'][$pId]['qty_requested'] += $item->getQty()*$child->getQty();
                }
            }
            */
        }

        $result = $this->processRequests($items, $requests);
        $this->setStockResult($result);
        return $this;
    }

    public function processRequests($items, $requests)
    {
        $stock = array();
        foreach ($requests as $request) {
            try {
                $result = call_user_func($request['callback'], $request['products']);
            } catch (Exception $e) {
                Mage::log(__METHOD__.': '.$e->getMessage());
                continue;
            }
            if (!empty($result)) {
                foreach ($result as $pId=>$vendors) {
                    foreach ($vendors as $vId=>$v) {
                        $stock[$pId][$vId] = $v;
                    }
                }
            }
        }

        foreach ($items as $item) {
            $pId = $item->getProductId();
            $item->setUdropshipStockLevels(!empty($stock[$pId]) ? $stock[$pId] : array());
            if ($item->getHasChildren()) {
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                foreach ($children as $child) {
                    $pId = $child->getProductId();
                    $child->setUdropshipStockLevels(!empty($stock[$pId]) ? $stock[$pId] : array());
                }
            }
        }
        
        $this->addStockErrorMessages($items, $stock);

        return $stock;
    }

    public function checkLocalStockLevel($products)
    {
        $this->setTrueStock(true);
        $result = array();
        $_hlp = Mage::helper('udropship');
        $ignoreStockStatusCheck = Mage::registry('reassignSkipStockCheck');
        foreach ($products as $pId=>$p) {
            $stockItem = !empty($p['stock_item']) ? $p['stock_item']
                : Mage::getModel('catalog/product')->load($pId)->getStockItem();
            $status = !$stockItem->getManageStock()
                || $stockItem->getIsInStock() && $stockItem->checkQty($p['qty_requested']);
            if ($ignoreStockStatusCheck) $status = true;
            foreach ($p['vendors'] as $vId=>$dummy) {
                $result[$pId][$vId]['status'] = $status && (!isset($dummy['zipcode_match']) || $dummy['zipcode_match']!==false);
                $result[$pId][$vId]['zipcode_match'] = (!isset($dummy['zipcode_match']) || $dummy['zipcode_match']!==false);
            }
        }
        $this->setTrueStock(false);
        return $result;
    }

    public function addStockErrorMessages($items, $stock)
    {
        $ciHlp = Mage::helper('cataloginventory');
        $quote = null;
        $hasOutOfStock = false;
        $allZipcodeMatch = true;
        foreach ($stock as $pId=>$vendors) {
            foreach ($items as $item) {
                if (empty($quote)) {
                    if ($item->getOrder()) {
                        return $this;
                    }
                    $quote = $item->getQuote();
                }
                if ($item->getProductId()!=$pId) {
                    continue;
                }
                $outOfStock = true;
                $zipCodeMatch = true;
                foreach ($vendors as $vId=>$v) {
                	$zipCodeMatch = $zipCodeMatch && (!isset($v['zipcode_match']) || $v['zipcode_match']!==false);
                    if ($this->getUseLocalStockIfAvailable($quote->getStoreId(), $vId)) {
                        $outOfStock = false;
                        break;
                    }
                    if (!empty($v['status'])) {
                        $outOfStock = false;
                        break;
                    }
                }
                $allZipcodeMatch = $allZipcodeMatch && $zipCodeMatch;
                if ($outOfStock && !$item->getHasError() && !$item->getMessage()) {
                    $hasOutOfStock = true;
                    $item->setUdmultiOutOfStock(true);
                    $message = $item->getMessage() ? $item->getMessage().'<br/>' : '';
                    $message .= $zipCodeMatch 
                    	? $ciHlp->__('This product is currently out of stock.')
                    	: Mage::helper('udropship')->__('This item is not available for your zipcode.');
                    $item->setHasError(true)->setMessage($message);
                    if ($item->getParentItem()) {
                        $item->getParentItem()->setMessage($message);
                    }
                }
            }
        }
        if ($hasOutOfStock && !$quote->getHasError() && !$quote->getMessages()) {
            $message = $allZipcodeMatch
            	? $ciHlp->__('Some of the products are currently out of stock')
            	: Mage::helper('udropship')->__('Some items are not available for your zipcode.');
            $quote->setHasError(true)->addMessage($message);
        }
        return $this;
    }
}