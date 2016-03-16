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

class Unirgy_Dropship_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
    * Vendors cache
    *
    * @var array
    */
    protected $_vendors = array();

    /**
    * Regions cache
    *
    * @var array
    */
    protected $_regions = array();

    /**
    * Writable flag to show true stock status or not
    *
    * @var boolean
    */
    protected $_trueStock = false;

    /**
    * Collection of order shipments for the vendor interface
    *
    * @var mixed
    */
    protected $_vendorShipmentCollection;

    /**
    * Carrier Methods Cache
    *
    * @var array
    */
    protected $_carrierMethods = array();

    protected $_version;

    protected $_localVendorId;

    public function getVersion()
    {
        if (!$this->_version) {
            $this->_version = (string)Mage::getConfig()->getNode('modules/Unirgy_Dropship/version');
        }
        return $this->_version;
    }

    public function isActive($store=null)
    {
        $udropship = Mage::getStoreConfigFlag('carriers/udropship/active', $store);
        $udsplit = Mage::getStoreConfigFlag('carriers/udsplit/active', $store);
        return $udropship || $udsplit;
    }

    public function isModuleActive($code)
    {
        $module = Mage::getConfig()->getNode("modules/$code");
        $model = Mage::getConfig()->getNode("global/models/$code");
        return $module && $module->is('active') || $model;
    }

    public function isUdpayoutActive()
    {
        return Mage::helper('udropship')->isModuleActive('Unirgy_DropshipPayout');
    }

    public function isUdpoActive()
    {
        return $this->isModuleActive('Unirgy_DropshipPo')
            && $this->hasMageFeature('sales_flat') && Mage::helper('udpo')->isActive();
    }

    public function isUdmultiActive()
    {
        return Mage::helper('udropship')->isModuleActive('Unirgy_DropshipMulti') && Mage::helper('udmulti')->isActive();
    }

    public function isUdmultiAvailable()
    {
        return ($multi = Mage::getConfig()->getNode('modules/Unirgy_DropshipMulti')) && $multi->is('active');
    }

    public function isUdpoMpsAvailable($carrierCode, $vendor=null)
    {
        return in_array($carrierCode, array('fedex')) && Mage::helper('udropship')->isModuleActive('Unirgy_DropshipPoMps');
    }

    /**
    * Retrieve local vendor id
    *
    * @param integer $store
    */
    public function getLocalVendorId($store=null)
    {
        if (is_null($this->_localVendorId)) {
            $this->_localVendorId = Mage::getStoreConfig('udropship/vendor/local_vendor', $store);
            // can't proceed if not configured
            if (!$this->_localVendorId) {
                #Mage::throwException('Local vendor is not set, please configure correctly');
                $this->_localVendorId = 0;
            }
        }
        return $this->_localVendorId;
    }

    /**
    * Get vendor object for vendor ID or Name and cache it
    *
    * If argument is product model, get udropship_vendor value
    *
    * @param integer|string|Mage_Catalog_Model_Product $id
    * @return Unirgy_Dropship_Model_Vendor
    */
    public function getVendor($id)
    {
        if ($id instanceof Unirgy_Dropship_Model_Vendor) {
            if (empty($this->_vendors[$id->getId()])) {
                $this->_vendors[$id->getId()] = $id;
            }
            return $id;
        }
        if ($id instanceof Mage_Catalog_Model_Product) {
            $id = $this->getProductVendorId($id);
        }
        $vendor = Mage::getModel('udropship/vendor');
        if (empty($id)) {
            return $vendor;
        }
        if (empty($this->_vendors[$id])) {
            if (!is_numeric($id)) {
                $vendor->load($id, 'vendor_name');
                if ($vendor->getId()) {
                    $this->_vendors[$vendor->getId()] = $vendor;
                }
            } else {
                $vendor->load($id);
                if ($vendor->getId()) {
                    $this->_vendors[$vendor->getVendorName()] = $vendor;
                }
            }
            $this->_vendors[$id] = $vendor;
        }
        return $this->_vendors[$id];
    }

    public function getVendorName($id)
    {
        $v = $this->getVendor($id);
        if ($v->getId()) {
            return $v->getVendorName();
        }
        return false;
    }

    public function getVendorDecisionModel()
    {

    }

    public function getVendorForgotPasswordUrl()
    {
        return Mage::getUrl('udropship/vendor/password');
    }

    /**
    * Get shipment status name from shipment object
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    * @return string
    */
    public function getShipmentStatusName($shipment)
    {
        $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
        $id = $shipment->getUdropshipStatus();
        return isset($statuses[$id]) ? $statuses[$id] : 'Unknown';
    }

    /**
    * Get shipment method name from shipment object
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    * @param boolean $full whether to prefix with carrier name
    * @return string
    */
    public function getShipmentMethodName($shipment, $full=false)
    {
        $vendor = $this->getVendor($shipment->getUdropshipVendor());
        $method = $shipment->getOrder()->getShippingMethod();
        return $vendor->getShippingMethodName($method, $full);
    }

    /**
    * Return vendor ID for a product object
    *
    * @param mixed $product
    * @param boolean $forceReal
    */
    public function getProductVendorId($product, $forceReal=false)
    {
        $storeId = $product->getStoreId();
        $localVendorId = $this->getLocalVendorId($storeId);
        $vendorId = $product->getUdropshipVendor();

        // product doesn't have vendor specified
        if (!$vendorId) {
            return $localVendorId;
        }
        // force real product vendor
        if ($forceReal) {
            return $vendorId;
        }

        // all other cases
        return $vendorId;
    }

    /**
    * Return vendor ID for quote item based on requested qty
    *
    * if $qty===true, always return dropship vendor id
    * if $qty===false, always return local vendor id
    * otherwise return local vendor if enough qty in stock
    *
    * @param Mage_Sales_Model_Quote_Item $item
    * @param integer|boolean $qty
    * @return integer
    * @deprecated since 1.6.0
    */
    public function getQuoteItemVendor($item, $qty=0)
    {
        $product = $item->getProduct();
        if (!$product || !$product->hasUdropshipVendor()) {
            // if not available, load full product info to get product vendor
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
        }
        $store = $item->getQuote() ? $item->getQuote()->getStore() : $item->getOrder()->getStore();

        $localVendorId = $this->getLocalVendorId($store);
        $vendorId = $product->getUdropshipVendor();
        // product doesn't have vendor specified OR force local vendor
        if (!$vendorId || $qty===false) {
            return $localVendorId;
        }
        // force real vendor
        if ($qty===true) {
            return $vendorId;
        }

        // local stock is available
        if (Mage::getSingleton('udropship/stock_availability')->getUseLocalStockIfAvailable($store, $vendorId) && $product->getStockItem()->checkQty($qty)) {
            return $localVendorId;
        }

        // all other cases
        return $vendorId;
    }

    /**
    * Get vendors collection for quote items
    *
    * @deprecated
    * @param Mage_Sales_Model_Mysql4_Quote_Item_Collection $items
    * @return Unirgy_Dropship_Mysql4_Vendor_Collection
    */
    public function collectQuoteItemsVendors($items)
    {
        $productQtys = array();
        foreach ($items as $item) {
            $id = $item->getProductId();
            if (isset($productQtys[$id])) {
                $productQtys[$id] += $item->getQty();
            } else {
                $productQtys[$id] = $item->getQty();
            }
        }
        $vendors = Mage::getModel('udropship/vendor')->getCollection()
            ->addProductFilter(array_keys($productQtys), 1);
        return $vendors;
    }

    /**
    * Mark shipment as complete and shipped
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    */
    public function setShipmentComplete($shipment)
    {
        $this->completeShipment($shipment, true);
        $this->completeUdpoIfShipped($shipment, true);
        $this->completeOrderIfShipped($shipment, true);
        return $this;

        $items = array();
        $order = $shipment->getOrder();#Mage::getModel('sales/order')->load($shipment->getOrderId());
        $orderItems = $order->getItemsCollection();
        foreach ($shipment->getAllItems() as $item) {
            $orderItem = Mage::helper('udropship')->getOrderItemById($order, $item->getOrderItemId());
            $orderItem->setQtyShipped($orderItem->getQtyOrdered());
        }
        $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
        Mage::helper('udropship')->addShipmentComment(
            $shipment,
            Mage::helper('udropship')->__('Marked as shipped')
        );

        $transaction = Mage::getModel('core/resource_transaction');
        foreach ($orderItems as $item) {
            $transaction->addObject($item);
        }
        $transaction->addObject($shipment)->save();

        return $this;
    }

    public function sendPasswordResetEmail($email)
    {
        $vendor = Mage::getModel('udropship/vendor')->load($email, 'email');
        if (!$vendor->getId()) {
            return $this;
        }
        $vendor->setRandomHash(sha1(rand()))->save();

        $store = Mage::app()->getStore();
        $this->setDesignStore($store);
        Mage::getModel('core/email_template')->sendTransactional(
            $store->getConfig('udropship/vendor/vendor_password_template'),
            $store->getConfig('udropship/vendor/vendor_email_identity'),
            $email, $email, array(
                'store_name' => $store->getName(),
                'vendor_name' => $vendor->getVendorName(),
                'url' => Mage::getUrl('udropship/vendor/password', array(
                    'confirm' => $vendor->getRandomHash(),
                ))
            )
        );
        $this->setDesignStore();

        return $this;
    }

    /**
    * Send notification to vendor about new order
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    */
    public function sendVendorNotification($shipment)
    {
        $vendor = $this->getVendor($shipment->getUdropshipVendor());
        $method = $vendor->getNewOrderNotifications();

        if (!$method || $method=='0') {
            return $this;
        }

        $data = compact('vendor', 'shipment', 'method');
        if ($method=='1') {
            $vendor->sendOrderNotificationEmail($shipment);
        } else {
            $config = Mage::getConfig()->getNode('global/udropship/notification_methods/'.$method);
            if ($config) {
                $cb = explode('::', (string)$config->callback);
                $obj = Mage::getSingleton($cb[0]);
                $method = $cb[1];
                $obj->$method($data);
            }
        }
        Mage::dispatchEvent('udropship_send_vendor_notification', $data);

        return $this;
    }

    public function sendShipmentCommentNotificationEmail($shipment, $comment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        $vendor = $this->getVendor($shipment->getUdropshipVendor());

        $hlp = Mage::helper('udropship');
        $data = array();

        $hlp->setDesignStore($store);

        $data += array(
            'shipment'        => $shipment,
            'order'           => $order,
            'vendor'          => $vendor,
            'comment'         => $comment,
            'store_name'      => $store->getName(),
            'vendor_name'     => $vendor->getVendorName(),
            'shipment_id'     => $shipment->getIncrementId(),
            'shipment_status' => $this->getShipmentStatusName($shipment),
            'order_id'        => $order->getIncrementId(),
            'shipment_url'    => Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$order->getIncrementId().'&filter_order_id_to='.$order->getIncrementId())),
            'packingslip_url' => Mage::getUrl('udropship/vendor/pdf', array('shipment_id'=>$shipment->getId())),
        );

        if ($this->isUdpoActive() && ($po = Mage::helper('udpo')->getShipmentPo($shipment))) {
            $data['po']     = $po;
            $data['po_id']  = $po->getIncrementId();
            $data['po_url'] = Mage::getUrl('udpo/vendor/', array('_query'=>'filter_po_id_from='.$po->getIncrementId().'&filter_po_id_to='.$po->getIncrementId()));
        }

        $template = $store->getConfig('udropship/vendor/shipment_comment_vendor_email_template');
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $vendor->getNewOrderCcEmails();
        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
        } else {
            $email = $vendor->getEmail();
        }
        Mage::getModel('udropship/email')->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);

        $hlp->setDesignStore();
    }

    /**
    * Send vendor comment to store owner
    *
    * @param Mage_Sales_Model_Order_Shipment $shipment
    * @param string $comment
    */
    public function sendVendorComment($shipment, $comment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();
        $to = $store->getConfig('udropship/admin/vendor_comments_receiver');
        $subject = $store->getConfig('udropship/admin/vendor_comments_subject');
        $template = $store->getConfig('udropship/admin/vendor_comments_template');
        $vendor = $this->getVendor($shipment->getUdropshipVendor());
        $ahlp = Mage::getModel('adminhtml/url');

        if ($subject && $template && $vendor->getId()) {
            $toEmail = $store->getConfig('trans_email/ident_'.$to.'/email');
            $toName = $store->getConfig('trans_email/ident_'.$to.'/name');
            $data = array(
                'vendor_name'   => $vendor->getVendorName(),
                'order_id'      => $order->getIncrementId(),
                'shipment_id'   => $shipment->getIncrementId(),
                'vendor_url'    => $ahlp->getUrl('udropship/adminhtml_vendor/edit', array(
                    'id'        => $vendor->getId()
                )),
                'order_url'     => $ahlp->getUrl('adminhtml/sales_order/view', array(
                    'order_id'  => $order->getId()
                )),
                'shipment_url'  => $ahlp->getUrl('adminhtml/sales_order_shipment/view', array(
                    'shipment_id'=> $shipment->getId(),
                    'order_id'  => $order->getId(),
                )),
                'comment'      => $comment,
            );
            if ($this->isUdpoActive() && ($po = Mage::helper('udpo')->getShipmentPo($shipment))) {
                $data['po_id'] = $po->getIncrementId();
                $data['po_url'] = $ahlp->getUrl('udpoadmin/order_po/view', array(
                    'udpo_id'  => $po->getId(),
                    'order_id' => $order->getId(),
                ));
                $template = preg_replace('/{{isPoAvailable}}(.*?){{\/isPoAvailable}}/s', '\1', $template);
            } else {
                $template = preg_replace('/{{isPoAvailable}}.*?{{\/isPoAvailable}}/s', '', $template);
            }
            foreach ($data as $k=>$v) {
                $subject = str_replace('{{'.$k.'}}', $v, $subject);
                $template = str_replace('{{'.$k.'}}', $v, $template);
            }

            $mail = Mage::getModel('core/email')
                ->setFromEmail($vendor->getEmail())
                ->setFromName($vendor->getVendorName())
                ->setToEmail($toEmail)
                ->setToName($toName)
                ->setSubject($subject)
                ->setBody($template)
                ->send();
            //mail('"'.$toName.'" <'.$toEmail.'>', $subject, $template, 'From: "'.$vendor->getVendorName().'" <'.$vendor->getEmail().'>');
        }

        Mage::helper('udropship')->addShipmentComment(
            $shipment,
            $this->__($vendor->getVendorName().': '.$comment)
        );
        $shipment->getCommentsCollection()->save();

        return $this;
    }

    /**
    * Collect renderers for different product types for admin item grids
    *
    * @param string|array $handle
    * @param string|Mage_Core_Block_Abstract $block
    * @param mixed $condition not used
    */
    public function applyItemRenderers($handle, $block, $condition=null, $conditionValue=true)
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->addHandle($handle)->load();
        $layout->generateXml();
        $renderers = $layout->getXpath("//action[@method='addItemRender']");

        if (is_string($block)) {
            $block = $layout->createBlock($block);
        }

        foreach ($renderers as $r) {
            if (is_null($condition) || preg_match($condition, (string)$r->block) == $conditionValue) {
                $block->addItemRender((string)$r->type, (string)$r->block, (string)$r->template);
            }
        }

        return $block;
    }

    /**
    * Get file name of label image for shipment tracking
    *
    * @todo make flexible enough for EPL
    * @param Mage_Sales_Model_Order_Shipment_Track $track
    * @return string
    */
    public function getTrackLabelFileName($track)
    {
        $shipment = $track->getShipment();
        return Mage::getConfig()->getVarDir('label').DS.$track->getNumber().'.png';
    }

    /**
    * In case customer object is missing in order object, retrieve
    *
    * @param Mage_Sales_Model_Order $order
    * @return Mage_Customer_Model_Customer
    */
    public function getOrderCustomer($order)
    {
        if (!$order->hasCustomer()) {
            $order->setCustomer(Mage::getModel('customer/customer')->load($order->getCustomerId()));
        }
        return $order->getCustomer();
    }

    /**
    * Get collection of order shipments for vendor interface
    *
    */
    public function getUsedMethodsByPoCollection($collection)
    {
        $allIds = $collection->getAllIds();
        $res  = Mage::getSingleton('core/resource');
        $read = $res->getConnection('core_read');
        if (!$this->isSalesFlat()) {
            $attr = Mage::getSingleton('eav/config')->getAttribute('shipment', 'udropship_method');
            return $read->fetchCol(
                $read->select()->distinct(true)
                    ->from($attr->getBackend()->getTable(), array('value'))
                    ->where('attribute_id=?', $attr->getId())
                    ->where('entity_id in (?)', $allIds)
            );
        } else {
            if ($collection instanceof Unirgy_DropshipPo_Model_Mysql4_Po_Collection) {
                return $read->fetchCol(
                    $read->select()->distinct(true)
                        ->from($res->getTableName('udpo/po'), array('udropship_method'))
                        ->where('entity_id in (?)', $allIds)
                );
            } else {
                return $read->fetchCol(
                    $read->select()->distinct(true)
                        ->from($res->getTableName('sales/shipment'), array('udropship_method'))
                        ->where('entity_id in (?)', $allIds)
                );
            }
        }
    }
    public function getVendorShipmentCollection()
    {
        if (!$this->_vendorShipmentCollection) {
            $vendorId = Mage::getSingleton('udropship/session')->getVendorId();
            $vendor = Mage::helper('udropship')->getVendor($vendorId);
            $collection = Mage::getModel('sales/order_shipment')->getCollection();
																
			/*echo '<pre>';
			print_r($collection->getData());exit;*/
            $sqlMap = array();
            if (!$this->isSalesFlat()) {
                $collection
                    ->addAttributeToSelect(array('order_id', 'total_qty', 'udropship_status', 'udropship_method', 'udropship_method_description'))
                    ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                    ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                    ->joinAttribute('shipping_method', 'order/shipping_method', 'order_id');
            } else {

      // Commented below 4 line and added one line   'increment_id' => 'main_table.increment_id', to reduce join on dated 18-03-2014      
			    /*$orderTableQted = $collection->getResource()->getReadConnection()->quoteIdentifier('sales/order');
                $sqlMap['order_increment_id'] = "$orderTableQted.increment_id";
                $sqlMap['order_created_at']   = "$orderTableQted.created_at";
                $collection->join('sales/order', "$orderTableQted.entity_id=main_table.order_id", array(
                    'order_increment_id' => 'increment_id',
                    'order_created_at' => 'created_at',
                    'shipping_method',
                ));*/
                //$shipmentPayoutTable = $collection->getResource()->getReadConnection()->quoteIdentifier('shipmentpayout/shipmentpayout');

//commnented on date 27-03-2014 for hide the payout filter

				/*$collection->getSelect()->joinLeft('shipmentpayout', "main_table.increment_id=shipmentpayout.shipment_id", array(
                    'payout_status' => 'shipmentpayout_status',
					'increment_id' => 'main_table.increment_id',
                ));*/
            }

            $collection->addAttributeToFilter('udropship_vendor', $vendorId);


            $r = Mage::app()->getRequest();

			if (($v = $r->getParam('filter_order_id_from'))) {
                //$collection->addAttributeToFilter($this->mapField('order_increment_id', $sqlMap), array('gteq'=>$v));
			$collection->addAttributeToFilter('main_table.increment_id', array('gteq'=>$v));
			
            }
            if (($v = $r->getParam('filter_order_id_to'))) {
                //$collection->addAttributeToFilter($this->mapField('order_increment_id', $sqlMap), array('lteq'=>$v));
			$collection->addAttributeToFilter('main_table.increment_id', array('lteq'=>$v));
            }

            if (($v = $r->getParam('filter_order_date_from'))) {
				$datetimestamp = Mage::getModel('core/date')->timestamp(strtotime($v));
				$curdate= date("Y-d-m H:i:s",$datetimestamp);
				
                $_filterDate = Mage::app()->getLocale()->date();
				
                $_filterDate->set($curdate, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
				
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
				
                //$collection->addAttributeToFilter($this->mapField('order_created_at', $sqlMap), array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
			$collection->addAttributeToFilter('main_table.created_at', array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
			// Added By Manoj sir On dated 11-12-12
			else
			{
				$_filterDate = Mage::app()->getLocale()->date();
				
				$curdate= date("Y-d-m H:i:s", Mage::getModel('core/date')->timestamp(time()));
				//echo 'dateelse'.$curdate;
				$_filterDate->set($curdate, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
				$_filterDate->subDay(30);
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
				
                $collection->addAttributeToFilter('main_table.created_at', array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
			}
			
            if (($v = $r->getParam('filter_order_date_to'))) {
				$datetimestamp = Mage::getModel('core/date')->timestamp(strtotime($v));
				$curdate= date("Y-d-m H:i:s",$datetimestamp);
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($curdate, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->addDay(1);
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                //$collection->addAttributeToFilter($this->mapField('order_created_at', $sqlMap), array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
			$collection->addAttributeToFilter('main_table.created_at', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));

            }

            if (($v = $r->getParam('filter_shipment_date_from'))) {
				
                $curdate= date("Y-d-m H:i:s", $v);
				$_filterDate = Mage::app()->getLocale()->date();
                //$_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->set($curdate, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
				$_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                
				$collection->addAttributeToFilter('main_table.created_at', array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }  
			
			
			
            if (($v = $r->getParam('filter_shipment_date_to'))) {
                $_filterDate = Mage::app()->getLocale()->date();
                $_filterDate->set($v, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
                $_filterDate->addDay(1);
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
                $collection->addAttributeToFilter('main_table.created_at', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }

            if (!$r->getParam('apply_filter') && $vendor->getData('vendor_po_grid_status_filter')) {
                $filterStatuses = $vendor->getData('vendor_po_grid_status_filter');
                $filterStatuses = array_combine($filterStatuses, array_fill(0, count($filterStatuses), 1));
                $r->setParam('filter_status', $filterStatuses);
            }

            if (!$this->isSalesFlat()) {
                if (($v = $r->getParam('filter_method'))) {
                    $collection->addAttributeToFilter('udropship_method', array('in'=>array_keys($v)));
                }
                if (($v = $r->getParam('filter_status'))) {
                    $collection->addAttributeToFilter('udropship_status', array('in'=>array_keys($v)));
                }
            } else {
                if (($v = $r->getParam('filter_method'))) {
                    $collection->addAttributeToFilter('main_table.udropship_method', array('in'=>array_keys($v)));
                }
                if (($v = $r->getParam('filter_status'))) {
                    $collection->addAttributeToFilter('main_table.udropship_status', array('in'=>array_keys($v)));
                }
            }
			if ($r->getParam('filter_payout')!='') {
					$v = $r->getParam('filter_payout');
                  //commnented on date 27-03-2014 for hide the payout filter 
				    //$collection->addAttributeToFilter('shipmentpayout.shipmentpayout_status', $v);
            }
            if (!$r->getParam('sort_by') && $vendor->getData('vendor_po_grid_sortby')) {
                $r->setParam('sort_by', $vendor->getData('vendor_po_grid_sortby'));
                $r->setParam('sort_dir', $vendor->getData('vendor_po_grid_sortdir'));
            }

            if (($v = $r->getParam('sort_by'))) {
				$map = array('order_date'=>'order_created_at', 'shipment_date'=>'created_at');
                if (isset($map[$v])) {
                    $v = $map[$v];
                }
				if($v == 'order_increment_id') $v = 'increment_id';
                $collection->setOrder($v, $r->getParam('sort_dir'));
			}
//			
            $this->_vendorShipmentCollection = $collection;
        }
		
        return $this->_vendorShipmentCollection;
    }

    public function mapField($field, $map)
    {
        return isset($map[$field]) ? $map[$field] : $field;
    }

    /**
    * Retrieve all shipping methods for carrier code
    *
    * Made for UPS which has CGI and XML methods
    *
    * @param string $carrierCode
    */
    public function getCarrierMethods($carrierCode, $allowedOnly=false)
    {
        if (empty($this->_carrierMethods[$allowedOnly][$carrierCode])) {
            $carrier = Mage::getSingleton('shipping/config')
                ->getCarrierInstance($carrierCode);
            if ($carrierCode=='ups') {
                $upsMethods = Mage::getSingleton('udropship/source')
                    ->setPath('ups_shipping_method_combined')
                    ->toOptionHash();
                $upsMethods = $upsMethods['UPS XML'] + $upsMethods['UPS CGI'];
                if ($allowedOnly) {
                    $allowed = explode(',', $carrier->getConfigData('allowed_methods'));
                    $methods = array();
                    foreach ($allowed as $m) {
                        $methods['ups_'.$m] = $upsMethods[$m];
                    }
                } else {
                    $methods = $upsMethods;
                }
            } else {
                if ($allowedOnly) {
                    $methods = $carrier->getAllowedMethods();
                } else {
                    try {
                        $methods = $carrier->getCode('methods');
                    } catch (Exception $e) {
                        $methods = null;
                    }
                    if (!$methods) {
                        $methods = $carrier->getAllowedMethods();
                    }
                }
            }
            $this->_carrierMethods[$allowedOnly][$carrierCode] = $methods;
        }
        return $this->_carrierMethods[$allowedOnly][$carrierCode];
    }

    /**
    * Not used, for future use.
    *
    * @param mixed $allowedOnly
    */
    public function getAllCarriersMethods($allowedOnly=false)
    {
        $allCarrierMethods = array();
        $carrierNames = Mage::getSingleton('udropship/source')->getCarriers();
        foreach ($carrierNames as $code=>$carrier) {
            $allCarrierMethods[$code] = $this->getCarrierMethods($code, $allowedOnly);
        }
        return $allCarrierMethods;
    }

    public function getCarrierTitle($code)
    {
        $carrierNames = Mage::getSingleton('udropship/source')->getCarriers();
        return !empty($carrierNames[$code]) ? $carrierNames[$code] : Mage::helper('udropship')->__('Unknown');
    }

    /**
    * Region cache
    *
    * @param integer $regionId
    * @return Mage_Directory_Model_Region
    */
    public function getRegion($regionId)
    {
        if (!isset($this->_regions[$regionId])) {
            $this->_regions[$regionId] = Mage::getModel('directory/region')->load($regionId);
        }
        return $this->_regions[$regionId];
    }

    public function getCountry($countryId)
    {
        if (!isset($this->_countries[$countryId])) {
            $this->_countries[$countryId] = Mage::getModel('directory/country')->load($countryId);
        }
        return $this->_countries[$countryId];
    }

    /**
    * Get region code by region ID
    *
    * @param integer $regionId
    * @return string
    */
    public function getRegionCode($regionId)
    {
        return $this->getRegion($regionId)->getCode();
    }

    public function getCountryName($countryId)
    {
        return $this->getCountry($countryId)->getName();
    }

    public function getLabelCarrierInstance($carrierCode)
    {
        $carrierCode = strtolower($carrierCode);

        $labelConfig = Mage::getConfig()->getNode('global/udropship/labels/'.$carrierCode);
        if (!$labelConfig) {
            Mage::throwException('This carrier is not supported for label printing ('.$carrierCode.')');
        }

        $labelModel = Mage::getSingleton((string)$labelConfig->model);
        if (!$labelModel) {
            Mage::throwException('Invalid label model for this carrier ('.$carrierCode.')');
        }

        return $labelModel;
    }

    public function getLabelTypeInstance($labelType)
    {
        $labelType = strtolower($labelType);

        $labelConfig = Mage::getConfig()->getNode('global/udropship/label_types/'.$labelType);
        if (!$labelConfig) {
            Mage::throwException('This label type is not supported ('.$labelType.')');
        }

        $labelModel = Mage::getSingleton((string)$labelConfig->model);
        if (!$labelModel) {
            Mage::throwException('Invalid label model for this type ('.$labelType.')');
        }

        return $labelModel;
    }

    public function curlCall($url, $request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec ($ch);
#echo "<xmp>"; echo $response; exit;

        //check for error
        if (($error = curl_error($ch)))  {
            throw new Exception(Mage::helper('udropship')->__('Error connecting to API: %s', $error));
        }
        curl_close($ch);

        return $response;
    }

    public function sendDownload($fileName, $content, $contentType)
    {
        Mage::app()->getResponse()
            ->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', strlen($content))
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
            ->setHeader('Last-Modified', date('r'))
            ->setBody($content)
            ->sendResponse();

        exit;
    }

    /**
    * Calculate total shipping price + handling fee
    *
    * For future use (doctab)
    *
    * @param float $cost
    * @param array $params
    */
    public function getShippingPriceWithHandlingFee($cost, array $params)
    {
        $numBoxes = !empty($params['num_boxes']) ? $params['num_boxes'] : 1;
        $handlingFee = $params['handling_fee'];
        $finalMethodPrice = 0;
        $handlingType = $params['handling_type'];
        if (!$handlingType) {
            $handlingType = Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_FIXED;
        }
        $handlingAction = $params['handling_action'];
        if (!$handlingAction) {
            $handlingAction = Mage_Shipping_Model_Carrier_Abstract::HANDLING_ACTION_PERORDER;
        }

        if($handlingAction == Mage_Shipping_Model_Carrier_Abstract::HANDLING_ACTION_PERPACKAGE)
        {
            if ($handlingType == Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_PERCENT) {
                $finalMethodPrice = ($cost + ($cost * $handlingFee/100)) * $numBoxes;
            } else {
                $finalMethodPrice = ($cost + $handlingFee) * $numBoxes;
            }
        } else {
            if ($handlingType == Mage_Shipping_Model_Carrier_Abstract::HANDLING_TYPE_PERCENT) {
                $finalMethodPrice = ($cost * $numBoxes) + ($cost * $numBoxes * $handlingFee/100);
            } else {
                $finalMethodPrice = ($cost * $numBoxes) + $handlingFee;
            }

        }
        return $finalMethodPrice;
    }

    public function usortByPosition($a, $b)
    {
        return $a['position']<$b['position'] ? -1 : ($a['position']>$b['position'] ? 1 : 0);
    }

    /**
    * vsprintf extended to use associated array key names
    *
    * @link http://us.php.net/manual/en/function.vsprintf.php#87031
    * @param string $format
    * @param array $data
    */
    public function vnsprintf($format, array $data)
    {
        preg_match_all('/ (?<!%) % ( (?: [[:alpha:]_-][[:alnum:]_-]* | ([-+])? [0-9]+ (?(2) (?:\.[0-9]+)? | \.[0-9]+ ) ) ) \$ [-+]? \'? .? -? [0-9]* (\.[0-9]+)? \w/x', $format, $match, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $offset = 0;
        $keys = array_keys($data);
        foreach ($match as &$value) {
            if (($key = array_search( $value[1][0], $keys, TRUE) ) !== FALSE
                || ( is_numeric( $value[1][0] )
                    && ( $key = array_search( (int)$value[1][0], $keys, TRUE) ) !== FALSE)
            ) {
                $len = strlen( $value[1][0]);
                $format = substr_replace( $format, 1 + $key, $offset + $value[1][1], $len);
                $offset -= $len - strlen( 1 + $key);
            }
        }
        return vsprintf($format, $data);
    }

    protected $_storeOrigin = array();
    /**
    * For potential future use
    *
    * @param mixed $store
    * @param Unirgy_Dropship_Model_Vendor $object
    */
    protected function _setOriginAddress($store, $object=null)
    {
        if (!Mage::getStoreConfig('udropship/vendor/tax_by_vendor', $store)) {
            return;
        }
        $origin = null;
        $store = Mage::app()->getStore($store);
        $sId = $store->getId();
        if (is_null($object)) {
            if (!empty($this->_storeOrigin[$sId])) {
                $origin = $this->_storeOrigin[$sId];
                $this->_storeOrigin[$sId] = array();
            }
        } else {
            if (empty($this->_storeOrigin[$sId])) {
                $this->_storeOrigin[$sId] = Mage::getStoreConfig('shipping/origin', $store);
            }
            if ($object instanceof Mage_Sales_Model_Quote_Item || $object instanceof Mage_Sales_Model_Quote_Address_Item) {
                $object = $object->getProduct();
            }
            if ($object instanceof Mage_Catalog_Model_Product || is_numeric($object)) {
                $object = $this->getVendor($object);
            }
            $origin = array(
                'country_id' => $object->getCountryId(),
                'region_id' => $object->getRegionId(),
                'postcode' => $object->getZip(),
            );
        }
        if ($origin) {
            $root = Mage::getConfig()->getNode("stores/{$store->getCode()}/shipping/origin");
            foreach (array('country_id', 'region_id', 'postcode') as $v) {
                $root->$v = $origin[$v];
            }
        }
    }

    protected $_store;
    protected $_oldStore;
    protected $_oldArea;
    protected $_oldDesign;

    public function setDesignStore($store=null)
    {
        if (!is_null($store)) {
            if ($this->_store) {
                return $this;
            }
            $this->_oldStore = Mage::app()->getStore();
            $this->_oldArea = Mage::getDesign()->getArea();
            $this->_store = Mage::app()->getStore($store);

            $store = $this->_store;
            $area = 'frontend';
            $package = Mage::getStoreConfig('design/package/name', $store);
            $design = array('package'=>$package, 'store'=>$store->getId());
            $inline = false;
        } else {
            if (!$this->_store) {
                return $this;
            }
            $this->_store = null;
            $store = $this->_oldStore;
            $area = $this->_oldArea;
            $design = $this->_oldDesign;
            $inline = true;
        }

        Mage::app()->setCurrentStore($store);
        $oldDesign = Mage::getDesign()->setArea($area)->setAllGetOld($design);
        Mage::app()->getTranslator()->init($area, true);
        Mage::getSingleton('core/translate')->setTranslateInline($inline);

        if ($this->_store) {
            $this->_oldDesign = $oldDesign;
        } else {
            $this->_oldStore = null;
            $this->_oldArea = null;
            $this->_oldDesign = null;
        }

        return $this;
    }

    public function addAdminhtmlVersion($module='Unirgy_Dropship')
    {
        $layout = Mage::app()->getLayout();
        $version = (string)Mage::getConfig()->getNode("modules/{$module}/version");

        $layout->getBlock('before_body_end')->append($layout->createBlock('core/text')->setText('
            <script type="text/javascript">$$(".legality")[0].insert({after:"'.$module.' ver. '.$version.', "});</script>
        '));

        return $this;
    }


    public function addTo($obj, $key, $value)
    {
        $new = $obj->getData($key)+$value;
        $obj->setData($key, $new);
        return $new;
    }

    protected $_queue = array();
    public function resetQueue()
    {
        $this->_queue = array();
    }

    public function addToQueue($action)
    {
        $this->_queue[] = $action;
        return $this;
    }

    public function processQueue()
    {
        $transport = null;

        if (Mage::getStoreConfig('udropship/misc/mail_transport')=='sendmail') {
            $sendmail = true;
            $transport = new Zend_Mail_Transport_Sendmail();
        }
        // Integrate with Aschroder_SMTPPro
        elseif ($this->isModuleActive('Aschroder_SMTPPro')) {
            $smtppro = Mage::helper('smtppro');
            $transport = $smtppro->getSMTPProTransport();
        }
        // Integrate with Aschroder_GoogleAppsEmail
        elseif ($this->isModuleActive('Aschroder_GoogleAppsEmail')) {
            $googleappsemail = Mage::helper('googleappsemail');
            $transport = $googleappsemail->getGoogleAppsEmailTransport();
        }
        // integrate with ArtsOnIT_AdvancedSmtp
        elseif ($this->isModuleActive('Mage_Advancedsmtp')) {
            $advsmtp = Mage::helper('advancedsmtp');
            $transport = $advsmtp->getTransport();
        }

        foreach ($this->_queue as $action) {
            if ($action instanceof Zend_Mail) {
                /* @var $action Zend_Mail */
                if (!empty($smtppro) && $smtppro->isReplyToStoreEmail()) {
                    if(method_exists($action, 'setReplyTo')) {
                        $action->setReplyTo($action->getFrom());
                    }else {
                        $action->addHeader('Reply-To', $action->getFrom());
                    }
                }
                if (!empty($sendmail)) {
                    $transport->parameters = '-f'.$action->getFrom();
                }
                $action->send($transport);
            } elseif (is_array($action)) { //array($object, $method, $args)
                call_user_func_array(array($action[0], $action[1]), !empty($action[2]) ? $action[2] : array());
            }
        }
        $this->resetQueue();
        return $this;
    }

    public function getNewVendors($days = 30)
    {
        $vendors = Mage::getModel('udropship/vendor')->getCollection()
            ->addFieldToFilter('created_at', array('gt'=>date('Y-m-d', time()-$days*86400)))
            ->addOrder('created_at', 'desc');
        return $vendors;
    }

    public function loadCustomData($obj)
    {
        // add custom vars
        if ($obj->getCustomVarsCombined()) {
            $varsCombined = $obj->getCustomVarsCombined();
            if (strpos($varsCombined, 'a:')===0) {
                $vars = @unserialize($varsCombined);
            } elseif (strpos($varsCombined, '{')===0) {
                $vars = Zend_Json::decode($varsCombined);
            }
            if (!empty($vars)) {
                $obj->addData($vars);
            }
        }

        // add custom data
        if (($customData = $obj->getData('custom_data_combined'))) {
            $arr = preg_split('#={5}\s+([^=]+)\s+={5}#', $customData, -1, PREG_SPLIT_DELIM_CAPTURE);
            $data = array();
            for ($i=1, $l=sizeof($arr); $i<$l; $i+=2) {
                $obj->setData(trim($arr[$i]), trim($arr[$i+1]));
            }
        }

        // add custom vars defaults
        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            $_key = $node->name ? (string)$node->name : $code;
            if ((string)$node->type=='disabled') {
                continue;
            }
            if (((string)$node->type=='image' || (string)$node->type=='file')
                && $obj->hasData($_key) && is_array($obj->getData($_key)))
            {
                $arr = $obj->getData($_key);
                $obj->setData($_key, $arr['value']);
            }
            if ($node->default && !$obj->hasData($_key)) {
                if ($node->type == 'multiselect') {
                    $defVals = explode(',', (string)$node->default);
                    $obj->setData($_key, $defVals);
                } else {
                    $obj->setData($_key, (string)$node->default);
                }
            }
        }

        return $this;
    }

    public function processPostMultiselects(&$data)
    {
        $fields = Mage::getConfig()->getNode('global/udropship/vendor/fields')->children();

        $visible = Mage::getStoreConfig('udropship/vendor/visible_preferences');
        $visible = $visible ? explode(',', $visible) : array();

        $isAdmin = Mage::app()->getStore()->isAdmin();

        foreach ($fields as $code=>$node) {
            if ((string)$node->type=='multiselect' && empty($data[$code]) && ($isAdmin || empty($visible) || in_array($code, $visible))) {
                $data[$code] = array();
            }
        }

        return $this;
    }

    public function processCustomVars($obj)
    {
        $customVars = array();

        $visible = Mage::getStoreConfig('udropship/vendor/visible_preferences');
        $visible = $visible ? explode(',', $visible) : array();

        foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
            $_key = $node->name ? (string)$node->name : $code;
            switch ((string)$node->type) {
            case 'disabled':
                continue;

            case  'image': case 'file':
                if ($obj->hasData($_key) && is_array($obj->getData($_key))) {
                    $arr = $obj->getData($_key);
                    if (!empty($arr['delete'])) {
                        @unlink(Mage::getConfig()->getBaseDir('media').DS.'vendor'.strtr($arr['value'], '/', DS));
                        $obj->unsetData($_key);
                    } else {
                        $obj->setData($_key, $arr['value']);
                    }
                }
                break;

            case 'multiselect':
                if ($obj->hasData($_key) && !is_array($obj->getData($_key))) {
                    $obj->setData($_key, (array)$obj->getData($_key));
                }
                break;
            }
            if ($obj->hasData($_key)) {
                $customVars[$_key] = $obj->getData($_key);
                $customVars[$code] = $obj->getData($_key);
            }
        }
        $obj->setCustomVarsCombined(Zend_Json::encode($customVars));

        return $this;
    }

    public function addMessageOnce($message, $module='checkout', $method='addError')
    {
        $session = Mage::getSingleton($module.'/session');
        $found = false;
#if (!Mage::app()->getRequest()->isPost()) print_r($message);
        foreach ($session->getMessages(false) as $m) {
#if (!Mage::app()->getRequest()->isPost()) print_r($m);
            if ($m->getCode() == $message) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $session->$method($message);
        }
        return $this;
    }

    public function getNextWorkDayTime($date=null)
    {
        $time = is_string($date) ? strtotime($date) : (is_int($date) ? $date : time());
        $y = date('Y', $time);
        // calculate federal holidays
        $holidays = array();
        // month/day (jan 1st). iteration/wday/month (3rd monday in january)
        $hdata = array('1/1'/*newyr*/, '7/4'/*jul4*/, '11/11'/*vet*/, '12/25'/*xmas*/, '3/1/1'/*mlk*/, '3/1/2'/*pres*/, '5/1/5'/*memo*/, '1/1/9'/*labor*/, '2/1/10'/*col*/, '4/4/11'/*thanks*/);
        foreach ($hdata as $h1) {
            $h = explode('/', $h1);
            if (sizeof($h)==2) { // by date
                $htime = mktime(0, 0, 0, $h[0], $h[1], $y); // time of holiday
                $w = date('w', $htime); // get weekday of holiday
                $htime += $w==0 ? 86400 : ($w==6 ? -86400 : 0); // if weekend, adjust
            } else { // by weekday
                $htime = mktime(0, 0, 0, $h[2], 1, $y); // get 1st day of month
                $w = date('w', $htime); // weekday of first day of month
                $d = 1+($h[1]-$w+7)%7; // get to the 1st weekday
                for ($t=$htime, $i=1; $i<=$h[0]; $i++, $d+=7) { // iterate to nth weekday
                     $t = mktime(0, 0, 0, $h[2], $d, $y); // get next weekday
                     if (date('n', $t)>$h[2]) break; // check that it's still in the same month
                     $htime = $t; // valid
                }
            }
            $holidays[] = $htime; // save the holiday
        }
        for ($i=0; $i<5; $i++, $time+=86400) { // 5 days should be enough to get to workday
            if (in_array(date('w', $time), array(0, 6))) continue; // skip weekends
            foreach ($holidays as $h) { // iterate through holidays
                if ($time>=$h && $time<=$h+86400) continue 2; // skip holidays
            }
            break; // found the workday
        }
        return $time;
    }

    /**
     * Poll carriers tracking API
     *
     * @param mixed $tracks
     */
    public function collectTracking($tracks)
    {
        $requests = array();
        foreach ($tracks as $track) {
            $cCode = $track->getCarrierCode();
            if (!$cCode) {
                continue;
            }
            $vId = $track->getShipment()->getUdropshipVendor();
            $v = Mage::helper('udropship')->getVendor($vId);
            if (!$v->getTrackApi($cCode)) {
                continue;
            }
            $requests[$cCode][$vId][$track->getNumber()][] = $track;
        }
        foreach ($requests as $cCode=>$vendors) {
            foreach ($vendors as $vId=>$trackIds) {
                $v = Mage::helper('udropship')->getVendor($vId);
                try {
                    $result = $v->getTrackApi($cCode)->collectTracking($v, array_keys($trackIds));
                } catch (Exception $e) {
                    $this->_processPollTrackingFailed($trackIds, $e);
                    continue;
                }
#print_r($result); echo "\n";
                $processTracks = array();
                foreach ($result as $trackId=>$status) {
                    foreach ($trackIds[$trackId] as $track) {
                        if ($status==Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING) {
                            $repeatIn = Mage::getStoreConfig('udropship/customer/repeat_poll_tracking', $track->getShipment()->getOrder()->getStoreId());
                            if ($repeatIn<=0) {
                                $repeatIn = 12;
                            }
                            $repeatIn = $repeatIn*60*60;
                            $track->setNextCheck(date('Y-m-d H:i:s', time()+$repeatIn))->save();
                            continue;
                        }

                        $track->setUdropshipStatus($status);
                        switch ($status) {
                        case Unirgy_Dropship_Model_Source::TRACK_STATUS_READY:
                            Mage::helper('udropship')->addShipmentComment(
                                $track->getShipment(),
                                $this->__('Tracking ID %s was picked up from %s', $trackId, $v->getVendorName())
                            );
                            $track->getShipment()->save();
                            break;

                        case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
                            Mage::helper('udropship')->addShipmentComment(
                                $track->getShipment(),
                                $this->__('Tracking ID %s was delivered to customer', $trackId)
                            );
                            $track->getShipment()->save();
                            break;
                        }
                        if (empty($processTracks[$track->getParentId()])) {
                            $processTracks[$track->getParentId()] = array();
                        }
                        $processTracks[$track->getParentId()][] = $track;
                    }
                }
                foreach ($processTracks as $_pTracks) {
                    try {
                        $this->processTrackStatus($_pTracks, true);
                    } catch (Exception $e) {
                        $this->_processPollTrackingFailed($_pTracks, $e);
                        continue;
                    }
                }
            }
        }
    }

    protected function _processPollTrackingFailed($tracks, Exception $e)
    {
        $tracksByStore = array();
        foreach ($tracks as $_track) {
            if (is_array($_track)) {
                foreach ($_track as $__track) {
                    $tracksByStore[$__track->getShipment()->getOrder()->getStoreId()][] = $__track;
                }
            } elseif ($_track instanceof Mage_Sales_Model_Order_Shipment_Track) {
                $tracksByStore[$_track->getShipment()->getOrder()->getStoreId()][] = $_track;
            }
        }
        foreach ($tracksByStore as $_sId => $_tracks) {
            Mage::helper('udropship/error')->sendPollTrackingFailedNotification($_tracks, "$e", $_sId);
        }
        return $this;
    }

    /**
     * Sending email with Invoice data
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function sendTrackingNotificationEmail($track, $comment='')
    {
		
		
        if (is_array($track)) {
            $tracks = $track;
            reset($tracks);
            $track = current($track);
        } else {
            $tracks = array($track);
        }
        $shipment = $track->getShipment();
        $order = $shipment->getOrder();
        $storeId = $order->getStoreId();

        if (!Mage::helper('sales')->canSendNewShipmentEmail($storeId)) {
            return $this;
        }

        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $storeId),
            'store' => $storeId
        ));

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $copyTo = Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_COPY_TO, $storeId);
        if (!empty($copyTo)) {
            $copyTo = explode(',', $copyTo);
        }
        $copyMethod = Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $mailTemplate = Mage::getModel('core/email_template');

        if ($order->getCustomerIsGuest()) {
            $template = Mage::getStoreConfig('udropship/customer/tracking_email_template_guest', $storeId);
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $template = Mage::getStoreConfig('udropship/customer/tracking_email_template', $storeId);
            $customerName = $order->getCustomerName();
        }

        /*Start...
         * Craftsvilla Comment for removing send mail to customer
         * Commented by suresh
         */
        $sendTo[] = array(
            'name'  => $customerName,
            'email' => $order->getCustomerEmail()
        );
        
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(Mage_Sales_Model_Order_Shipment::XML_PATH_EMAIL_IDENTITY, $storeId),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'       => $order,
                        'shipment'    => $shipment,
                        'track'       => $track,
                        'tracks'      => $tracks,
                        'comment'     => $comment,
                        'billing'     => $order->getBillingAddress(),
                        'payment_html'=> $paymentBlock->toHtml(),
                    )
                );
        }

        $translate->setTranslateInline(true);

        Mage::getDesign()->setAllGetOld($currentDesign);

        return $this;
    }

    protected function _processTrackStatusSave($save, $object)
    {
        if ($save===true) {
            $object->save();
        } elseif ($save instanceof Mage_Core_Model_Resource_Transaction) {
            $save->addObject($object);
        }
    }

    /**
     * Process tracking status update
     *
     * Will process only tracks with TRACK_STATUS_READY status
     *
     * @param Mage_Sales_Model_Order_Shipment_Track $track
     * @param boolean|Mage_Core_Model_Resource_Transaction $save
     * @param null|boolean $complete
     */
    public function processTrackStatus($track, $save=false, $complete=null)
    {
        if (is_array($track)) {
            $tracks = $track;
            reset($tracks);
            $track = current($track);
        } else {
            $tracks = array($track);
        }
        $shipment = $track->getShipment();

        $order = $shipment->getOrder();
        $storeId = $order->getStoreId();

        $saveShipment = false;
        $saveOrder = false; //not used yet

        $notifyTracks = array();

        foreach ($tracks as $track) {
            $saveTrack = false;

            // is the track ready to be marked as shipped
            $trackReady = $track->getUdropshipStatus()===Unirgy_Dropship_Model_Source::TRACK_STATUS_READY;
            // is the track shipped
            $shipped = $track->getUdropshipStatus()==Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED;
            // is the track delivered
            $delivered = $track->getUdropshipStatus()===Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED;

            // actions that need to be done if the track is not marked as shipped yet
            if (!$shipped) {
                // if new track record, set initial values
                if (!$track->getUdropshipStatus()) {
                    $vendorId = $shipment->getUdropshipVendor();
                    $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $storeId);
                    $trackApi = Mage::helper('udropship')->getVendor($vendorId)->getTrackApi();
                    if ($pollTracking && $trackApi) {
                        $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
                        $repeatIn = Mage::getStoreConfig('udropship/customer/repeat_poll_tracking', $track->getShipment()->getOrder()->getStoreId());
                        if ($repeatIn<=0) {
                            $repeatIn = 12;
                        }
                        $repeatIn = $repeatIn*60*60;
                        $track->setNextCheck(date('Y-m-d H:i:s', time()+$repeatIn));
                    } else {
                        $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_READY);
                    }
                    $saveTrack = true;
                }
                if ($track->getUdropshipStatus()==Unirgy_Dropship_Model_Source::TRACK_STATUS_READY) {
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
                    $notifyTracks[] = $track;
                    $saveTrack = true;
                }
                if ($saveTrack) {
                    $this->_processTrackStatusSave($save, $track);
                }
            }
        }

        if (!empty($notifyTracks)) {
			
            $notifyOnOld = Mage::getStoreConfig('udropship/customer/notify_on', $storeId);
            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on_tracking', $storeId);
            if ($notifyOn) {
                $this->sendTrackingNotificationEmail($notifyTracks);
                $shipment->setEmailSent(true);
                $saveShipment = true;
            } elseif ($notifyOnOld==Unirgy_Dropship_Model_Source::NOTIFYON_TRACK) {
                $shipment->sendEmail();
                $shipment->setEmailSent(true);
                $saveShipment = true;
            }
        }

        if ($shipment->getUdropshipStatus()==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED || $shipment->getUdropshipStatus()==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED) {
            return;
        }

        if (is_null($complete)) {
            if (Mage::getStoreConfigFlag('udropship/vendor/auto_shipment_complete', $storeId)) {
                switch (Mage::getStoreConfigFlag('udropship/vendor/auto_shipment_complete', $storeId)) {
                    case Unirgy_Dropship_Model_Source::AUTO_SHIPMENT_COMPLETE_ANY:
                        $pickedUpTracks = Mage::getModel('sales/order_shipment_track')->getCollection()
                            ->setShipmentFilter($shipment->getId())
                            ->addAttributeToFilter('udropship_status', array('in'=>array(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED, Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED)))
                        ;
                        $complete = $pickedUpTracks->count()>0;
                        break;
                    default:
                        $pendingTracks = Mage::getModel('sales/order_shipment_track')->getCollection()
                            ->setShipmentFilter($shipment->getId())
                            ->addAttributeToFilter('udropship_status', array('nin'=>array(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED, Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED)))
                        ;
                        $complete = !$pendingTracks->count();
                        break;
                }
            } else {
                $complete = false;
            }
        }

        if ($complete) {
            $this->completeShipment($shipment, $save, $delivered);
            $saveShipment = true;
        } elseif ($shipment->getUdropshipStatus()!=Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL) {

            $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL);
            $saveShipment = true;
        }
        if ($saveShipment) {
            foreach ($shipment->getAllTracks() as $t) {
                foreach ($tracks as $_t) {
                    if ($t->getEntityId()==$_t->getEntityId()) {
                        $t->setData($_t->getData());
                        break;
                    }
                }
            }
            $this->_processTrackStatusSave($save, $shipment);
        }

        if ($complete) {
            $this->completeUdpoIfShipped($shipment, $save);
            $this->completeOrderIfShipped($shipment, $save);
        }

        return $this;
    }

    public function registerShipmentItem($item, $save)
    {
        if ($this->isUdpoActive()) {
            Mage::helper('udpo')->completeShipmentItem($item, $save);
        } else {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy(true)) {
                $item->setQty(1);
            }
            if ($item->getQty()>0) {
                $item->register();
                $this->_processTrackStatusSave($save, $orderItem);
            }
        }
    }

    public function completeShipment($shipment, $save=false, $delivered=false)
    {
        $order = $shipment->getOrder();
        $storeId = $order->getStoreId();

        $newStatus = $delivered
            ? Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED
            : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;

        if ($newStatus == $shipment->getUdropshipStatus()) {
            return $this;
        }
        $shipment->setUdropshipStatus($newStatus);
        Mage::helper('udropship')->addShipmentComment(
            $shipment,
            $this->__('Shipment has been complete')
        );

        foreach ($shipment->getAllItems() as $item) {
            $this->registerShipmentItem($item, $save);
        }

        $notifyOnOld = Mage::getStoreConfig('udropship/customer/notify_on', $storeId);
        $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on_shipment', $storeId);
        if (($notifyOn || $notifyOnOld==Unirgy_Dropship_Model_Source::NOTIFYON_SHIPMENT) && !$delivered) {
            $shipment->sendEmail();
            $shipment->setEmailSent(true);
        }

        $this->_processTrackStatusSave($save, $shipment);

        if ($this->isUdpoActive()) {
            Mage::helper('udpo')->completeShipment($shipment, $save);
        }

        return $this;
    }

    public function completeUdpoIfShipped($shipment, $save=false, $force=true)
    {
        if ($this->isUdpoActive()) {
            Mage::helper('udpo')->completeUdpoIfShipped($shipment, $save, $force);
        }
    }

    public function completeOrderIfShipped($shipment, $save=false, $force=true)
    {
        $order = $shipment->getOrder();

        $pendingShipments = Mage::getModel('sales/order_shipment')->getCollection()
            ->setOrderFilter($order->getId())
            ->addAttributeToFilter('entity_id', array('neq'=>$shipment->getId()))
            ->addAttributeToFilter('udropship_status', array('nin'=>array(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED, Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED)))
        ;

        if (!$pendingShipments->count() && $force) {
            // will not work with 1.4.x
            #$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true);
        }
        $order->setIsInProcess(true);
        $this->_processTrackStatusSave($save, $order);

        return $this;
    }

    public function getVendorSku($item)
    {
        if ($this->isModuleActive('udmulti') && Mage::helper('udmulti')->isActive()) {
            if ($item->getOrderItem()) {
                $item = $item->getOrderItem();
            }
            return Mage::helper('udmulti')->getVendorSku($item->getProductId(), $item->getUdropshipVendor(), $item->getSku());
        } else {
            return $item->getSku();
        }
        return $sku;
    }

    protected $_shippingMethods;
    public function getShippingMethods()
    {
        if (!$this->_shippingMethods) {
            $this->_shippingMethods = Mage::getModel('udropship/shipping')->getCollection();
        }
        return $this->_shippingMethods;
    }

    protected $_systemShippingMethods;
    public function getSystemShippingMethods()
    {
        if (!$this->_systemShippingMethods) {
            $systemMethods = array();
            $shipping = $this->getShippingMethods();
            foreach ($shipping as $s) {
                if (!$s->getSystemMethods()) {
                    continue;
                }
                foreach ($s->getSystemMethods() as $c=>$m) {
                    $systemMethods[$c][$m] = $s;
                }
            }
            $this->_systemShippingMethods = $systemMethods;
        }
        return $this->_systemShippingMethods;
    }

    protected $_multiSystemShippingMethods;
    public function getMultiSystemShippingMethods()
    {
        if (!$this->_systemShippingMethods) {
            $systemMethods = array();
            $shipping = $this->getShippingMethods();
            foreach ($shipping as $s) {
                if (!$s->getSystemMethods()) {
                    continue;
                }
                foreach ($s->getSystemMethods() as $c=>$m) {
                    if (empty($systemMethods[$c][$m])) {
                        $systemMethods[$c][$m] = array();
                    }
                    $systemMethods[$c][$m][] = $s;
                }
            }
            $this->_multiSystemShippingMethods = $systemMethods;
        }
        return $this->_multiSystemShippingMethods;
    }

    public function saveThisVendorProducts($data, $v)
    {
        return $this->_saveVendorProducts($data, $v);
    }
    public function saveVendorProducts($data)
    {
        return $this->_saveVendorProducts($data, Mage::getSingleton('udropship/session')->getVendor());
    }
    protected function _saveVendorProducts($data, $v)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('cost')
            ->addIdFilter(array_keys($data));

        if ($v->getId()==Mage::getStoreConfig('udropship/vendor/local_vendor')) {
            $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
            $products->getSelect()->joinLeft(
                array('_udv'=>$attr->getBackend()->getTable()),
                '_udv.entity_id=e.entity_id and _udv.store_id=0 and _udv.attribute_id='.$attr->getId().' and _udv.value='.$v->getId(),
                array('udropship_vendor'=>'value')
            );
        } else {
            $products->addAttributeToFilter('udropship_vendor', $v->getId());
        }

        if (!$products) {
            return false;
        }
        Mage::getModel('cataloginventory/stock')->addItemsToProducts($products);

        $cnt = 0;
        foreach ($products as $p) {
            if (empty($data[$p->getId()])) {
                continue;
            }
            $d = $data[$p->getId()];
            $updateProduct = false;
            $updateStock = false;
            /*
            if ($p->hasCost() && empty($d['vendor_cost'])) {
                $p->unsCost();
                $updateProduct = true;
            } elseif (!empty($d['vendor_cost']) && $d['vendor_cost']!=$p->getCost()) {
                $p->setCost($d['vendor_cost']);
                $updateProduct = true;
            }
            */
            $ps = $p->getStockItem();
            if (isset($d['stock_status']) && $d['stock_status']!=$ps->getIsInStock()) {
                $ps->setIsInStock($d['stock_status']);
                $updateStock = true;
            }
            if (isset($d['stock_qty']) && $d['stock_qty']!=$ps->getQty()) {
                $ps->setQty($d['stock_qty']);
                $updateStock = true;
            } elseif (!isset($d['stock_qty']) && isset($d['stock_qty_add'])) {
                $ps->setQty($ps->getQty()+$d['stock_qty_add']);
                $updateStock = true;
            }
            if ($updateProduct) {
                $p->save();
            }
            if ($updateStock) {
                $ps->save();
            }
            if ($updateProduct || $updateStock) {
                $cnt++;
            }
        }
        return $cnt;
    }

    public function compareMageVer($ceVer, $eeVer=null, $op='>=')
    {
        $eeVer = is_null($eeVer) ? $ceVer : $eeVer;
        return $this->isModuleActive('Enterprise_Enterprise')
            ? version_compare(Mage::getVersion(), $eeVer, $op)
            : version_compare(Mage::getVersion(), $ceVer, $op);
    }

    protected $_hasMageFeature = array();
    public function hasMageFeature($feature)
    {
        if (!isset($this->_hasMageFeature[$feature])) {
            $flag = false;
            switch ($feature) {
            case 'fedex.soap':
                $flag = $this->compareMageVer('1.6.0.0', '1.11.0', '>=');
                break;
            case 'order_item.base_cost':
                $flag = $this->compareMageVer('1.4.0.1', '1.8.0', '>=');
                break;

            case 'sales_flat':
                $flag = $this->compareMageVer('1.4.1.0', '1.8.0', '>=');
                break;

            case 'wysiwyg_allowed':
                $flag = Mage::getStoreConfig('udropship/vendor/allow_wysiwyg') && $this->compareMageVer('1.4.0');
                break;

            case 'stock_can_subtract_qty':
                $flag = $this->compareMageVer('1.4.1.1', '1.9.0', '>=');
                break;
                
            case 'indexer_1.4':
            case 'table.product_relation':
            case 'table.eav_attribute_label':
            case 'table.catalog_eav_attribute':
            case 'attr.is_wysiwyg_enabled':
                $flag = $this->compareMageVer('1.4', '1.6');
                break;
            case 'track_number':
                $flag = $this->compareMageVer('1.6', '1.11');
                break;
            }
            $this->_hasMageFeature[$feature] = $flag;
        }
        return $this->_hasMageFeature[$feature];
    }

    public function trackNumberField()
    {
        return $this->hasMageFeature('track_number') ? 'track_number' : 'number';
    }

    public function isSalesFlat()
    {
        return $this->hasMageFeature('sales_flat');
    }

    public function isWysiwygAllowed()
    {
        return $this->hasMageFeature('wysiwyg_allowed');
    }

    public function assignVendorSkus($po)
    {
        $storeId = $po->getStoreId();
        $attr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $storeId);
        Mage::helper('udropship')->addVendorSkus($po);
        foreach ($po->getAllItems() as $item) {
            $oItem = $item->getOrderItem();
            $oItemParent = $oItem->getParentItem();
            if ($item->getVendorSku()) {
                $item->setData('__orig_sku', $item->getSku());
                $item->setSku($item->getVendorSku());
                if ($oItem->getProductType() == 'bundle' || ($oItemParent && $oItemParent->getProductType() == 'bundle')) {
                    $oItem->setData('__orig_sku', $oItem->getSku());
                    $oItem->setSku($item->getVendorSku());
                }
            }
            $pOpts = $item->getOrderItem()->getProductOptions();
            if (!empty($pOpts['simple_sku']) && $item->getVendorSimpleSku()) {
                $item->setData('__orig_simple_sku', $pOpts['simple_sku']);
                $pOpts['simple_sku'] = $item->getVendorSimpleSku();
            }
            $item->getOrderItem()->setProductOptions($pOpts);
        }
        if ($po instanceof Mage_Sales_Model_Order_Shipment) {
            Mage::dispatchEvent('udropship_shipment_assign_vendor_skus', array('shipment'=>$po, 'attribute_code'=>$attr));
        } elseif ($po instanceof Unirgy_DropshipPo_Model_Po) {
            Mage::dispatchEvent('udpo_po_assign_vendor_skus', array('udpo'=>$po, 'attribute_code'=>$attr));
        }
        return $this;
    }

    public function unassignVendorSkus($po)
    {
        $storeId = $po->getStoreId();
        $attr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $storeId);
        if ($attr && $attr!='sku') {
            foreach ($po->getAllItems() as $item) {
                $oItem = $item->getOrderItem();
                $oItemParent = $oItem->getParentItem();
                if ($item->hasData('__orig_sku')) {
                    $item->setSku($item->getData('__orig_sku'));
                    if ($oItem->getProductType() == 'bundle' || ($oItemParent && $oItemParent->getProductType() == 'bundle')) {
                        $oItem->setSku($oItem->getData('__orig_sku'));
                    }
                }
                $pOpts = $item->getOrderItem()->getProductOptions();
                if ($item->hasData('__orig_simple_sku')) {
                    $pOpts['simple_sku'] = $item->getData('__orig_simple_sku');
                }
                $item->getOrderItem()->setProductOptions($pOpts);
            }
        }
        if ($po instanceof Mage_Sales_Model_Order_Shipment) {
            Mage::dispatchEvent('udropship_shipment_unassign_vendor_skus', array('udpo'=>$po, 'attribute_code'=>$attr));
        } elseif ($po instanceof Unirgy_DropshipPo_Model_Po) {
            Mage::dispatchEvent('udpo_po_unassign_vendor_skus', array('udpo'=>$po, 'attribute_code'=>$attr));
        }
        return $this;
    }

    public function addVendorSkus($po)
    {
        $storeId = $po->getStoreId();
        $attr = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $storeId);
        $productIds = array();
        $simpleSkus = array();
        foreach ($po->getAllItems() as $item) {
            if (!$item->hasData('vendor_sku')) {
                $item->setFirstAddVendorSkuFlag(true);
                $productIds[] = $item->getProductId();
            }
            if ($item->getOrderItem()->getProductOptionByCode('simple_sku')) {
                if (!$item->hasData('vendor_simple_sku')) {
                    $item->setFirstAddVendorSkuFlag(true);
                    $simpleSkus[$item->getId()] = $item->getOrderItem()->getProductOptionByCode('simple_sku');
                }
            }
        }
        if ($attr && $attr!='sku') {
            $attrFilters = array();
            if (!empty($productIds)) {
                $attrFilters[] = array('attribute' => 'entity_id', 'in' => array_values($productIds));
            }
            if (!empty($simpleSkus)) {
                $attrFilters[] = array('attribute' => 'sku', 'in' => array_values($simpleSkus));
            }
            if (!empty($attrFilters)) {
                $products = Mage::getModel('catalog/product')->getCollection()
                    ->setStoreId($storeId)
                    ->addAttributeToSelect($attr)
                    ->addAttributeToSelect('sku_type')
                    ->addAttributeToFilter($attrFilters);
                foreach ($po->getAllItems() as $item) {
                    $oItem = $item->getOrderItem();
                    if (!$item->hasData('vendor_sku')
                        && ($product = $products->getItemById($item->getProductId()))
                    ) {
                        $item->setVendorSku($product->getData($attr));
                        if ($oItem->getProductType() == 'bundle' && !$product->getSkuType() && $oItem->getChildrenItems()) {
                            $_bundleSkus = array($product->getData($attr) ? $product->getData($attr) : $product->getSku());
                            foreach ($oItem->getChildrenItems() as $oiChild) {
                                if (($childProd = $products->getItemById($oiChild->getProductId()))
                                    && $childProd->getData($attr)
                                ) {
                                    $_bundleSkus[] = $childProd->getData($attr);
                                } else {
                                    $_bundleSkus[] = $oiChild->getSku();
                                }
                            }
                            $item->setVendorSku(implode('-', $_bundleSkus));
                        }
                    } elseif (!$item->hasData('vendor_sku')) {
                        $item->setVendorSku('');
                    }
                    if (!$item->hasData('vendor_simple_sku') && !empty($simpleSkus[$item->getId()])
                        && $item->getOrderItem()->getProductOptionByCode('simple_sku')
                        && ($product = $products->getItemByColumnValue('sku', $simpleSkus[$item->getId()]))
                    ) {
                        $item->setVendorSimpleSku($product->getData($attr));
                    } elseif (!$item->hasData('vendor_simple_sku')) {
                        $item->setVendorSimpleSku('');
                    }
                }
            }
        }
        Mage::dispatchEvent('udropship_po_add_vendor_skus', array('po'=>$po, 'attribute_code'=>$attr));
        foreach ($po->getAllItems() as $item) {
            $item->unsFirstAddVendorSkuFlag();
        }
        return $this;
    }

    public function getVendorShipmentsPdf($shipments)
    {
        foreach ($shipments as $shipment) {
            $this->assignVendorSkus($shipment);
            $tracks = $shipment->getOrder()->getTracksCollection();
            $tracks->load();
            foreach ($tracks as $id=>$track) {
                $tracks->removeItemByKey($id);
            }
        }
        $pdf = Mage::getModel('udropship/pdf_shipment')
            ->setUseFont(Mage::getStoreConfig('udropship/vendor/pdf_use_font'))
            ->getPdf($shipments);
        foreach ($shipments as $shipment) {
            $this->unassignVendorSkus($shipment);
        }
        return $pdf;
    }

    protected $_shipmentComments = array();
    public function getVendorShipmentsCommentsCollection($shipment)
    {
        if (!isset($this->_shipmentComments[$shipment->getId()])) {
            $comments = Mage::getResourceModel('sales/order_shipment_comment_collection')
                ->setShipmentFilter($shipment->getId())
                ->addAttributeToFilter('is_visible_to_vendor', 1)
                ->setCreatedAtOrder();

            if (!Mage::helper('udropship')->isSalesFlat()) {
                $comments->addAttributeToSelect('*');
            }

            if ($shipment->getId()) {
                foreach ($comments as $comment) {
                    $comment->setShipment($shipment);
                }
            }
            $this->_shipmentComments[$shipment->getId()] = $comments;
        }
        return $this->_shipmentComments[$shipment->getId()];
    }

    public function applyEstimateTotalPriceMethod($total, $price, $store=null)
    {
        $totalMethod = Mage::getStoreConfig('udropship/customer/estimate_total_method', $store);
        if ($totalMethod=='max') {
            $total = max($total, $price);
        } else {
            $total += $price;
        }
        return $total;
    }

    public function applyEstimateTotalCostMethod($total, $cost, $store=null)
    {
        $total += $cost;
        return $total;
    }

    public function explodeOrderShippingMethod($order)
    {
        $oShippingMethod = explode('_', $order->getShippingMethod(), 2);
        if (!empty($oShippingMethod[1])) {
            $_osm = explode('___', $oShippingMethod[1]);
            $oShippingMethod[1] = $_osm[0];
            if (!empty($_osm[1]) && false !== strpos($_osm[1], '_')) {
                $__osm = explode('___', $_osm[1]);
                $oShippingMethod[2] = $__osm[0];
            }
        }
        return $oShippingMethod;
    }

    public function initVendorShippingMethodsForHtmlSelect($order, &$vMethods)
    {
        $oShippingMethod = Mage::helper('udropship')->explodeOrderShippingMethod($order);
        $carrierNames = Mage::getSingleton('udropship/source')->getCarriers();
        $shipping = $this->getShippingMethods();
        if ('order' == Mage::getStoreConfig('udropship/vendor/reassign_available_shipping')
            && $oShippingMethod[0] == 'udropship' && !empty($oShippingMethod[1])
        ) {
            $oShipping = $shipping->getItemByColumnValue('shipping_code', $oShippingMethod[1]);
        }
        $oShippingDetails = Zend_Json::decode($order->getUdropshipShippingDetails());
        foreach ($vMethods as $vId => &$vMethod) {
            if ($vMethod === false) continue;
            $v = $this->getVendor($vId);
            $vSMs = $v->getShippingMethods();
            foreach ($vSMs as $sId => $vSM) {
                if (isset($oShipping) && $sId != $oShipping->getId()) continue;
                $s = $shipping->getItemById($sId);
                list($sc, $cc) = array($s->getShippingCode(), $vSM['carrier_code']);
                $ccs = array($cc);
                if ($cc!=$v->getCarrierCode()) $ccs[] = $v->getCarrierCode();
                foreach ($ccs as $i=>$cc) {
                    $mc = $s->getSystemMethods($cc);
                    if (empty($sc) || empty($cc) || empty($mc)) continue;
                    $cMethodNames = $this->getCarrierMethods($cc);
                    if ($mc == '*') {
                        $_mc = is_array($cMethodNames) ? array_keys($cMethodNames) : array();
                    } else {
                        $_mc = array($mc);
                    }
                    foreach ($_mc as $mc) {
                        $vMethod[$sc]['__title'] = $s->getShippingTitle();
                        $ccMcKeys = array(sprintf('%s_%s', $cc, $mc));
                        if ($this->hasExtraChargeMethod($v, $vSM)) {
                            $ccMcKeys[] = sprintf('%s_%s___ext', $cc, $mc);
                        }
                        foreach ($ccMcKeys as $ccMcKey) {
                            if ($oShippingMethod[0] == 'udropship' && !empty($oShippingMethod[1])
                                && $sc==$oShippingMethod[1] && $i==0
                            ) {
                                if (empty($oShippingMethod[2]) || $oShippingMethod[2] == $ccMcKey) {
                                    $vMethod[$sc][$ccMcKey]['__selected'] = true;
                                }
                            } elseif ($oShippingMethod[0] == 'udsplit'
                                && is_array($oShippingDetails)
                                && !empty($oShippingDetails['methods'][$vId]['code'])
                                && $oShippingDetails['methods'][$vId]['code'] == $ccMcKey
                            ) {
                                $vMethod[$sc][$ccMcKey]['__selected'] = true;
                            }
                            if (false !== strpos($ccMcKey, '___ext')) {
                                $vMethod[$sc][$ccMcKey][$ccMcKey] = sprintf('%s - %s %s', $carrierNames[$cc], $cMethodNames[$mc], $this->getExtraChargeData($v, $vSM, 'extra_charge_suffix'));
                            } else {
                                $vMethod[$sc][$ccMcKey][$ccMcKey] = sprintf('%s - %s', $carrierNames[$cc], $cMethodNames[$mc]);
                            }
                        }
                    }
                }
            }
        }
        unset($vMethod);
    }

    public function createOnDuplicateExpr($conn, $fields)
    {
        $updateFields = array();
        foreach ($fields as $k => $v) {
            $field = $value = null;
            if (!is_numeric($k)) {
                $field = $conn->quoteIdentifier($k);
                if ($v instanceof Zend_Db_Expr) {
                    $value = $v->__toString();
                } else if (is_string($v)) {
                    $value = 'VALUES('.$conn->quoteIdentifier($v).')';
                } else if (is_numeric($v)) {
                    $value = $conn->quoteInto('?', $v);
                }
            } else if (is_string($v)) {
                $field = $conn->quoteIdentifier($v);
                $value = 'VALUES('.$field.')';
            }

            if ($field && $value) {
                $updateFields[] = "{$field}={$value}";
            }
        }
        return $updateFields ? (" ON DUPLICATE KEY UPDATE " . join(', ', $updateFields)) : '';
    }

    public function getAdjustmentPrefix($type)
    {
        switch ($type) {
            case 'po_comment':
                return 'po-comment-';
            case 'shipment_comment':
                return 'shipment-comment-';
            case 'statement':
                return 'statement-';
            case 'payout':
                return 'payout-';
            case 'statement:payout':
                return 'statement:payout-';
        }
        return '';
    }

    public function isAdjustmentComment($comment, $store=null)
    {
        $adjTrigger = Mage::getStoreConfig('udropship/statement/adjustment_trigger', $store).':';
        $adjTriggerQ = preg_quote($adjTrigger);
        return preg_match("#({$adjTriggerQ})\\s*([0-9.-]+)\\s*(.*)\$#m", $comment);
    }
    public function collectPoAdjustments($pos, $force=false)
    {
        $adjTrigger = Mage::getStoreConfig('udropship/statement/adjustment_trigger').':';
        $adjTriggerQ = preg_quote($adjTrigger);
        $posToCollect = array();
        foreach ($pos as $po) {
            if (!$po->hasAdjustments() || $force) {
                $posToCollect[$po->getId()] = $po;
            }
        }
        if (!empty($posToCollect)) {
            $poType = $pos instanceof Varien_Data_Collection && $pos->getFirstItem() instanceof Unirgy_DropshipPo_Model_Po
                || reset($pos) instanceof Unirgy_DropshipPo_Model_Po
                ? 'po' : 'shipment';
            $comments = $adjustments = $adjAmounts = array();
            if ($poType == 'po') {
                $commentsCollection = Mage::getModel('udpo/po_comment')->getCollection()
                    ->addAttributeToFilter('parent_id', array('in'=>array_keys($posToCollect)))
                    ->addAttributeToFilter('comment', array('like'=>$adjTrigger.'%'))
                    ->addAttributeToSelect('*')
                    ->addAttributeToSort('created_at');
                $commentsCollection->getSelect()->columns(array('po_id'=>'parent_id', 'adjustment_prefix_type'=>new Zend_Db_Expr("'po_comment'")));
                $comments[] = $commentsCollection;
            }
            $commentsCollection = Mage::getModel('sales/order_shipment_comment')->getCollection()
                ->addAttributeToFilter('comment', array('like'=>$adjTrigger.'%'))
                ->addAttributeToSelect('*')
                ->addAttributeToSort('created_at');
            if ($poType == 'po') {
                $commentsCollection->getSelect()->join(
                    array('sos' => $commentsCollection->getTable('sales/shipment')),
                    'sos.entity_id=main_table.parent_id',
                    array()
                );
                $commentsCollection->getSelect()->where('sos.udpo_id in (?)', array_keys($posToCollect));
                $commentsCollection->getSelect()->columns(array('po_id'=>'sos.udpo_id'));
            } else {
                $commentsCollection->addAttributeToFilter('parent_id', array('in'=>array_keys($posToCollect)));
                $commentsCollection->getSelect()->columns(array('po_id'=>'parent_id'));
            }
            $commentsCollection->getSelect()->columns(array('adjustment_prefix_type'=>new Zend_Db_Expr("'shipment_comment'")));
            $comments[] = $commentsCollection;
            foreach ($comments as $_comments) {
                foreach ($_comments as $comment) {
                    if (!preg_match("#({$adjTriggerQ})\\s*([0-9.-]+)\\s*(.*)\$#m", $comment->getComment(), $match)) {
                        continue;
                    }
                    $sId = $comment->getPoId();
                    if (!isset($adjAmounts[$sId])) {
                        $adjAmounts[$sId] = 0;
                        $adjustments[$sId] = array();
                    }
                    $adjKey = $this->getAdjustmentPrefix($comment->getAdjustmentPrefixType()).$comment->getId();
                    $adjustments[$sId][$adjKey] = array(
                        'adjustment_id' => $adjKey,
                        'po_id' => $posToCollect[$sId]->getIncrementId(),
                        'po_type' => $poType,
                        'amount' => (float)$match[2],
                        'comment' => $match[1].' '.$match[3],
                        'created_at' => $comment->getCreatedAt(),
                    	'username' => $comment->getUsername(),
                    );
                    $adjAmounts[$sId] += (float)$match[2];
                }
            }
            foreach ($posToCollect as $sId => $po) {
                if (isset($adjAmounts[$sId])) {
                    $po->setAdjustmentAmount($adjAmounts[$sId]);
                    $po->setAdjustments($adjustments[$sId]);
                } else {
                    $po->setAdjustmentAmount(0);
                    $po->setAdjustments(array());
                }
            }
        }
        return $this;
    }

    protected $_emptyStatementTotalsAmount = array(
        'subtotal'=>0, 'tax'=>0, 'shipping'=>0, 'handling'=>0,
        'com_amount'=>0, 'trans_fee'=>0, 'adj_amount'=>0, 'total_payout'=>0
    );
    protected $_emptyStatementCalcTotalsAmount = array(
        'total_paid' => 0
    );
    protected $_emptyStatementCalcTotals;
    protected $_emptyStatementTotals;

    protected function _getStatementEmptyTotalsAmount($calc=false, $format=false)
    {
        if (!$calc) {
            $est  = &$this->_emptyStatementTotals;
            $esta = &$this->_emptyStatementTotalsAmount;
        } else {
            $est  = &$this->_emptyStatementCalcTotals;
            $esta = &$this->_emptyStatementCalcTotalsAmount;
        }
        if ($format && is_null($est)) {
            $this->formatAmounts($est, $esta, true);
        }
        return $format ? $est : $esta;
    }

    public function getStatementEmptyTotalsAmount($format=false)
    {
        return $this->_getStatementEmptyTotalsAmount(false, $format);
    }

    public function getStatementEmptyCalcTotalsAmount($format=false)
    {
        return $this->_getStatementEmptyTotalsAmount(true, $format);
    }

    public function formatAmounts(&$data, $defaultAmounts=null, $useDefault=false)
    {
        $core = Mage::helper('core');
        $iter = (is_null($defaultAmounts) ? $data : $defaultAmounts);
        if (is_array($iter)) {
            foreach ($iter as $k => $v) {
                if ($useDefault == 'merge' || $useDefault && !isset($data[$k])) {
                    $data[$k] = $core->formatPrice($v, false);
                } elseif (isset($data[$k])) {
                    $data[$k] = $core->formatPrice($data[$k], false);
                }
            }
        }
        return $this;
    }

    public function getStatementEmptyOrderAmounts($format=false)
    {
        return $this->getStatementEmptyTotalsAmount($format);
    }

    public function getPoOrderIncrementId($po)
    {
        return $po->hasOrderIncrementId() ? $po->getOrderIncrementId() : $po->getOrder()->getIncrementId();
    }

    public function getPoOrderCreatedAt($po)
    {
        return $po->hasOrderCreatedAt() ? $po->getOrderCreatedAt() : $po->getOrder()->getCreatedAt();
    }

    public function getItemStockCheckQty($item)
    {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            if ($item->hasUdpoCreateQty()) {
                return $item->getUdpoCreateQty();
            } {
                return $this->isUdpoActive()
                    ? Mage::helper('udpo')->getOrderItemQtyToUdpo($item, true)
                    : $item->getQtyOrdered()-$item->getQtyCanceled()-$item->getQtyRefunded();
            }
        } else {
            $parentQty = $item->getParentItem() ? $item->getParentItem()->getQty() : 1;
            return $item->getQty()*$parentQty;
        }
    }

	public function getZipcodeByItem($item)
    {
        if ($item instanceof Mage_Sales_Model_Order_Item) {
            return $item->getOrder()->getShippingAddress()
            	? $item->getOrder()->getShippingAddress()->getPostcode()
            	: ($item->getOrder()->getBillingAddress()
            		? $item->getOrder()->getBillingAddress()->getPostcode()
            		: null
            	);
        } else {
	        $address = $item->getQuote() ? $item->getQuote()->getShippingAddress() : null;
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            return $address ? $address->getPostcode() : null;
        }
    }

    public function getItemBaseCost($item, $altCost=null)
    {
        $result = abs($altCost)<0.001 ? (abs($item->getBaseCost())<0.001 ? $item->getBasePrice() : $item->getBaseCost()) : $altCost;
        return abs($altCost)<0.001 ? (abs($item->getBaseCost())<0.001 ? $item->getBasePrice() : $item->getBaseCost()) : $altCost;
    }

    public function getSalesEntityVendors($entity)
    {
        if (!is_callable(array($entity, 'getAllItems'))) return array();
        $products = array();
        foreach ($entity->getAllItems() as $si) {
            $products[$si->getProductId()][] = $si;
        }
        $read = Mage::getSingleton('core/resource')->getConnection('udropship_read');
        $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'udropship_vendor');
        $table = $attr->getBackend()->getTable();
        $select = $read->select()
            ->from($table, array('entity_id', 'value'))
            ->where('attribute_id=?', $attr->getId())
            ->where('entity_id in (?)', array_keys($products));
        $rows = $read->fetchPairs($select);
        $result = array();
        foreach ($products as $pId => $siArr) {
            foreach ($siArr as $item) {
                if (Mage::getStoreConfig('udropship/stock/availability', $entity->getStoreId())=='local_if_in_stock') {
                    $result[$item->getId()][$this->getLocalVendorId($entity->getStoreId())] = true;
                }
                $children = $item->getChildrenItems() ? $item->getChildrenItems() : $item->getChildren();
                if (!empty($children)) {
                    foreach ($children as $child) {
                        if (Mage::getStoreConfig('udropship/stock/availability', $entity->getStoreId())=='local_if_in_stock') {
                            $result[$child->getId()][$this->getLocalVendorId($entity->getStoreId())] = true;
                        }
                        if (!empty($rows[$child->getProductId()])) $result[$item->getId()][$rows[$child->getProductId()]] = true;
                    }
                } else {
                    if (!empty($rows[$item->getProductId()])) $result[$item->getId()][$rows[$item->getProductId()]] = true;
                }
            }
        }
        return $result;
    }

    public function getVendorShipmentStatuses()
    {
        if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
            $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
            if (!is_array($shipmentStatuses)) {
                $shipmentStatuses = explode(',', $shipmentStatuses);
            }
            return Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->getOptionLabel($shipmentStatuses);
        } else {
            return Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
        }
    }

    public function getVendorTracksCollection($shipment)
    {
        return $shipment->getTracksCollection()->setOrder('master_tracking_id');
    }

    public function isUdropshipOrder($order)
    {
    	if (!$order instanceof Mage_Sales_Model_Order) return false;
    	$oSM = Mage::helper('udropship')->explodeOrderShippingMethod($order);
		return in_array($oSM[0], array('udropship', 'udsplit'));
    }

    public function getOrderItemById($order, $itemId)
    {
    	$orderItem = $order->getItemById($itemId);
    	if (!$orderItem) {
	    	foreach ($this->getItemsCollection() as $item) {
	            if ($item->getId()==$itemId) {
	                return $item;
	            }
	        }
    	}
    	return $orderItem;
    }

    public function addShipmentComment($shipment, $comment, $visibleToVendor=true, $isVendorNotified=false, $isCustomerNotified=false)
    {
        if (!$comment instanceof Mage_Sales_Model_Order_Shipment_Comment) {
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
            $comment = Mage::getModel('sales/order_shipment_comment')
                ->setComment($comment)
                ->setIsCustomerNotified($isCustomerNotified)
                ->setIsVendorNotified($isVendorNotified)
                ->setIsVisibleToVendor($visibleToVendor)
                ->setUdropshipStatus(@$statuses[$shipment->getUdropshipStatus()]);
        }
        $shipment->addComment($comment);
        return $this;
    }

    public function processShipmentStatusSave($shipment, $status)
    {
        if ($shipment->getUdropshipStatus() != $status) {
            $oldStatus = $shipment->getUdropshipStatus();
            Mage::dispatchEvent(
                'udropship_shipment_status_save_before',
                array('shipment'=>$shipment, 'old_status'=>$oldStatus, 'new_status'=>$status)
            );
            $comment = sprintf("[Shipment status changed from '%s' to '%s']",
                $shipment->getUdropshipStatus(),
                $status
            );
            $shipment->setUdropshipStatus($status);
            $shipment->getResource()->saveAttribute($shipment, 'udropship_status');
            $this->addShipmentComment($shipment, $comment);
            $shipment->getCommentsCollection()->save();
            Mage::dispatchEvent(
                'udropship_shipment_status_save_after',
                array('shipment'=>$shipment, 'old_status'=>$oldStatus, 'new_status'=>$status)
            );
        }
        return $this;
    }

    public function processPoStatusSave($po, $status)
    {
        if ($po instanceof Unirgy_DropshipPo_Model_Po) {
            Mage::helper('udpo')->processPoStatusSave($po, $status, true);
        } else {
            Mage::helper('udropship')->processShipmentStatusSave($po, $status);
        }
        return $this;
    }

    function array_merge_2(&$array, &$array_i) {
        // For each element of the array (key => value):
        foreach ($array_i as $k => $v) {
            // If the value itself is an array, the process repeats recursively:
            if (is_array($v)) {
                if (!isset($array[$k])) {
                    $array[$k] = array();
                }
                $this->array_merge_2($array[$k], $v);

            // Else, the value is assigned to the current element of the resulting array:
            } else {
                if (isset($array[$k]) && is_array($array[$k])) {
                    $array[$k][0] = $v;
                } else {
                    if (isset($array) && !is_array($array)) {
                        $temp = $array;
                        $array = array();
                        $array[0] = $temp;
                    }
                    $array[$k] = $v;
                }
            }
        }
    }


    function array_merge_n() {
        // Initialization of the resulting array:
        $array = array();

        // Arrays to be merged (function's arguments):
        @$arrays =& func_get_args();

        // Merging of each array with the resulting one:
        foreach ($arrays as $array_i) {
            if (is_array($array_i)) {
                $this->array_merge_2($array, $array_i);
            }
        }

        return $array;
    }

    public function hasExtraChargeMethod($vendor, $vMethod)
    {
        return $vendor->getAllowShippingExtraCharge() && @$vMethod['allow_extra_charge'];
    }
    public function getExtraChargeData($vendor, $vMethod, $field)
    {
        return null !== @$vMethod[$field] ? $vMethod[$field] : $vendor->getData('default_shipping_'.$field);
    }
    public function getExtraChargeRate($request, $rate, $vendor, $vMethod)
    {
        $vendor = $this->getVendor($vendor);
        if ($this->hasExtraChargeMethod($vendor, $vMethod)) {
            $exRate = clone $rate;
            $fields = array();
            foreach (array(
                'extra_charge_suffix','extra_charge_type','extra_charge'
            ) as $field) {
                $fields[$field] = $this->getExtraChargeData($vendor, $vMethod, $field);
            }
            $exRate->setSuffix(' '.$fields['extra_charge_suffix']);
            $exRate->setMethod($exRate->getMethod().'___ext');
            $exRate->setMethodTitle($exRate->getMethodTitle().' '.$fields['extra_charge_suffix']);
            switch ($fields['extra_charge_type']) {
                case 'shipping_percent':
                    $exPrice = $exRate->getPrice()*abs($fields['extra_charge'])/100;
                    break;
                case 'subtotal_percent':
                    $exPrice = $request->getPackageValue()*abs($fields['extra_charge'])/100;
                    break;
                case 'fixed':
                    $exPrice = abs($fields['extra_charge']);
                    break;
            }
            $exRate->setBeforeExtPrice($exRate->getPrice());
            $exRate->setPrice($exRate->getPrice()+$exPrice);
            $exRate->setIsExtraCharge(true);
            $rate->setHasExtraCharge(true);
            $exRate->setHasExtraCharge(true);
            return $exRate;
        }
        return false;
    }
    public function mapSystemToUdropshipMethod($code, $vendor)
    {
        $vendor = Mage::helper('udropship')->getVendor($vendor);
        $systemMethods = Mage::helper('udropship')->getShippingMethods();
        $vendorMethods = $vendor->getShippingMethods();
        $found = false;
        foreach ($vendorMethods as $vendorMethod) {
            if ($code == $vendorMethod['carrier_code'].'_'.$vendorMethod['method_code']) {
                $found = $vendorMethod['shipping_id'];
                break;
            }
        }
        if (!$found) {
            foreach ($systemMethods as $systemMethod) {
                foreach ($systemMethod->getSystemMethods() as $sc=>$sm) {
                    if ($code == $sc.'_'.$sm
                        || $sm == '*' && 0 === strpos($code, $sc.'_')
                    ) {
                        $found = $systemMethod->getId();
                        break;
                    }
                }
            }
        }
        static $unknown;
        if (null === $unknown) {
            $unknown = Mage::getModel('udropship/shipping')->setData(array(
                'shipping_code' => '***unknown***',
                'shipping_title' => '***Unknown***',
            ));
        }
        return $found && $systemMethods->getItemById($found) ? $systemMethods->getItemById($found) : $unknown;
    }
	public function fetchawbgenerate($couriername){
		$courierUrl = Mage::getStoreConfig('courier/general/server_url');
		$tokenKey = Mage::getStoreConfig('courier/general/source');
		if($couriername == 'Delhivery'){
			$savexml = file_get_contents($courierUrl.'/waybill/api/fetch/json/?token='.$tokenKey);
			//$savexml = file_get_contents($courierUrl.'/waybill/api/fetch/json/?token='.$tokenKey);
			//file_put_contents('/wamp/www/doejofinal/media/vendorxml/code.xml', $savexml);
			$encode_json_str = json_encode($savexml);
			$encode = str_replace('\"','',$encode_json_str);
			$awb = str_replace('"','',$encode);
			return $awb;
		}
		return NULL;
	}
	public function fetchawbcreateorder($createcodorder,$shipment){
		$courierUrl = Mage::getStoreConfig('courier/general/server_url');
		$tokenKey = Mage::getStoreConfig('courier/general/source');
		$shipmentcodId =  $shipment->getIncrementId();
		$ordercreatedDate = $shipment->getCreatedAt();
		$orderId =  $shipment->getOrderId();
		$grandTotal = $shipment->getBaseTotalValue();
		//get the customer details
		$order = Mage::getModel('sales/order')->load($orderId);
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		$street = $address['street'];
		$city = $address['city'];
		$name = $address['firstname'].' '.$address['lastname'];
		//get the vendor address details
		$dropship = Mage::getModel('udropship/vendor')->load($shipment->getUdropshipVendor());
		$vendorStreet = $dropship['street'];
		$vendorCity = $dropship->getCity();
		$vendorName = $dropship->getVendorName();
		//echo '<pre>';print_r($dropship);exit;
		
		//get Track number from database....
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$readresultQuery = $read->query("SELECT `number` FROM `sales_flat_shipment_track` WHERE `order_id` ='".$orderId."'");
		$awbillno = $readresultQuery->fetch();
		$trackNum = $awbillno['number'];
		//get the product details
		$_items = $shipment->getAllItems();
		//echo '<pre>';print_r($_items);exit;
		$vendorShipmentItemHtml = "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU/Vendorsku</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>QTY</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		foreach ($_items as $_item)
				{
				$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$shipment->getIncrementId()."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getSku()." / ".$product->getVendorsku()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getQty()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}	
				$vendorShipmentItemHtml .= "</table>";	
				
		//var_dump($address);exit;
		$pickUpLocation = array('add'=>urlencode($vendorStreet),'city'=>urlencode($vendorCity),'country' =>urlencode($dropship->getCountryId()),'name'=>urlencode($vendorName),'phone'=>urlencode($dropship->getTelephone()),'pin'=>urlencode($dropship->getZip()));//,'state'=>$dropship->getRegionId());
		$shipmentDetails = array('waybill'=>$trackNum,'client'=>'Kribha Handicrafts','name'=>$name,'order'=>$shipmentcodId,'order_date'=>$ordercreatedDate,'payment_mode'=>'CashOnDelivery','total_amount'=>$grandTotal,'cod_amount'=>$grandTotal,'add'=>$street,'city'=>$city,'state'=>$address['region'],'country' =>$address['country_id'],'phone'=>$address['telephone'],'pin'=>$address['postcode'],'return_add'=>'','return_city'=>'','return_country'=>'','return_name'=>'','return_phone'=>'','return_pin'=>'','return_state'=>'');
		$credentialApi = array('token' => $tokenKey);
		$dataCollect = array('pickup_location'=>urlencode($pickUpLocation),'shipments'=>urlencode($shipmentDetails));
		foreach($pickUpLocation as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
				rtrim($fields_string, '&');
		
		if($createcodorder == 'Delhivery')
		{
			 $jsonAllDataDetail = json_encode($pickUpLocation);
			 //$savexml = $courierUrl.'/waybill/api/fetch/json/?token='.$tokenKey;
			$savexml = 'http://test.delhivery.com/cmu/push/xml/';
			//$saveCodxml = $courierUrl.'cmu/push/json/?token='.$tokenKey.'&data='.$jsonAllDataDetail;
				$ch = curl_init();
			    curl_setopt($ch, CURLOPT_HTTPHEADERS, array('Content-Type: text/json'));
    			curl_setopt($ch, CURLOPT_VERBOSE, 1);
				curl_setopt($ch, CURLOPT_URL, $savexml);
    			curl_setopt($ch, CURLOPT_POSTFIELDS,array('?token'=>$tokenKey));
		        curl_setopt($ch, CURLOPT_POSTFIELDS,array('&data'=>$jsonAllDataDetail));
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt( $ch, CURLOPT_HEADER, 0);
				curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
    $resulta = curl_exec($ch);
	
    //print_r($resulta);exit;
	if (curl_errno($ch)) {
        print curl_error($ch);
    } else {
		print_r($resulta);
		//$xml = @simplexml_load_string($resulta);
        curl_close($ch);
    }
    return $resulta;
		}
		return NULL;
	}
	
	public function aramaxawbgenerate($aramexcouriername,$shipment){
		
		//echo 'hmmm'.$shipment;exit;
		$baseUrl = Mage::helper('courier')->getWsdlPath();
		//SOAP object
		$soapClient = new SoapClient($baseUrl . 'shipping-services-api-wsdl.wsdl');
		$aramex_errors = false;
		$clientInfo = Mage::helper('courier')->getClientInfo();	
		$shipmentDetail = Mage::getModel('sales/order_shipment')->load($shipment);
		$totalQtyOrdered = $shipmentDetail->getTotalQty();
		$basesubTotal = $shipmentDetail->getBaseTotalValue();
		$baseShippingCost = $shipmentDetail->getItemisedTotalShippingcost();
		$baseCodFee = $shipmentDetail->getBaseCodFee();
		$grandTotal = $basesubTotal+$baseShippingCost+$baseCodFee;
		$incrementId = $shipmentDetail->getIncrementId();
		$orderId = $shipmentDetail->getOrderId();
		$accountId = Mage::getStoreConfig('courier/general/account_number');
		$items = $shipmentDetail->getAllItems();
		$order = Mage::getModel('sales/order')->load($orderId);
		$baseCurrency = $order->getBaseCurrencyCode();
		$cemail1 = $order->getCustomerEmail();
		if($cemail1){
				$cemail = $cemail1;

		}else{
				$cemail = 'orders@craftsvilla.com';
		}

		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		$street = $address['street'];
		$city = $address['city'];
		$postCode = $address['postcode'];
		$telephoneCust = $address['telephone'];
		$cname = $address['firstname'].' '.$address['lastname'];
		$regionIdCust = $address['region_id'];
		$regionc = Mage::getModel('directory/region')->load($regionIdCust);
		$regionNameCustomenr = $regionc->getName();

		$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
			
			$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$shipment)
								  ->columns('SUM(a.base_discount_amount) AS amount');
								  //echo $orderitem->getSelect()->__toString();			  exit;
			$orderitemdata = $orderitem->getData();
			foreach($orderitemdata as $_orderitemdata)
			{
			  $discountamount = $_orderitemdata['amount'];
			
			}					  
		
		
		//get the vendor address details
		$dropship = Mage::getModel('udropship/vendor')->load($shipmentDetail->getUdropshipVendor());
		$vendorStreet = $dropship['street'];
		$vattn = $dropship->getVendorAttn();
		$vendorCity = $dropship->getCity();
		$vendorName = $dropship->getVendorName();
		$regionId = $dropship->getRegionId();
		$zip = $dropship->getZip();
		$vemail = $dropship->getEmail();
		$vtelephone = $dropship->getTelephone();
		$region = Mage::getModel('directory/region')->load($regionId);
		$regionName = $region->getName();
		$totalWeight 	= 0;
		$totalItems 	= 0;
		$custom = Zend_Json::decode($dropship->getCustomVarsCombined());
					$codfee = $custom['cod_fee'];
//calculation of COD		
		$amountCOD = $basesubTotal + $baseShippingCost - $discountamount + $codfee;	
		
		if($aramexcouriername == 'Aramex'){
		$aramex_items1 = array();
		$params = array();
		foreach($items as $item){
			if($item->getId()){
				//get weight
				if($item->getWeight() != 0){
					//$weight =  ($item->getWeight()/100)*$item->getQtyOrdered();
					$weight =  (0.5)*$item->getQtyOrdered();
				} else {
					$weight =  0.5*$item->getQtyOrdered();
				}
		
				// collect items for aramex
				$aramex_items[]	= array(
					'PackageType'	=> 'Box',
					'Quantity'		=> $item->getQtyOrdered(),
					'Weight'		=> array(
					'Value'	=> '0.5',
					'Unit'	=> 'Kg'
					),
					'Comments'		=> mysql_escape_string($item->getName()), //'',
					'Reference'		=> ''
				);

				$totalWeight 	= $weight;
				$aramex_items1[] = substr($item->getName(),0,50). '-Bought On Craftsvilla.com';
				$totalItems += $item->getId();
			}
		}
		
		$params['Shipper'] = array(
		'Reference1' 		=> $incrementId, //'ref11111',
		'Reference2' 		=> '',
		'AccountNumber' 	=> '',//($post['aramex_shipment_info_billing_account'] == 1) ? $post['aramex_shipment_shipper_account'] : $post['aramex_shipment_shipper_account'], //'43871',

		//Party Address
		'PartyAddress'		=> array(
					'Line1'					=> substr($vendorName,0,25).','.substr($vendorStreet,0,20),
					'Line2'					=> substr($vendorStreet,20,65),
					'Line3'					=> substr($vendorStreet,65,110),
					'City'					=> '',//$vendorCity,
					'StateOrProvinceCode'	=> '',//$regionName,
					'PostCode'				=> str_replace(' ','',$zip),
					'CountryCode'			=> 'IN',
		),

		//Contact Info
		'Contact' 			=> array(
					'Department'			=> 'Seller',
					'PersonName'			=> substr(($vattn.','.$vendorName),0,45),
					'Title'					=> 'Mr',
					'CompanyName'			=> $vendorName,
					'PhoneNumber1'			=> $vtelephone,
					'PhoneNumber1Ext'		=> '',
					'PhoneNumber2'			=> '',
					'PhoneNumber2Ext'		=> '',
					'FaxNumber'				=> '',
					'CellPhone'				=> $vtelephone,
					'EmailAddress'			=> $vemail,
					'Type'					=> ''
		),
	);

	//consinee parameters customer
	$params['Consignee'] = array(
		'Reference1' 		=> $incrementId, //'',
		'Reference2'		=> '',
		'AccountNumber'		=> '',

		//Party Address
		'PartyAddress'		=> array(
					'Line1'					=> substr($cname,0,20) .', '.substr($street,0,25),
					'Line2'					=> substr($street,25,70),
					'Line3'					=> substr($street,70,115),
					'City'					=> '',//$city,
					'StateOrProvinceCode'	=> '',//$regionNameCustomenr,
					'PostCode'				=> str_replace(' ','',$postCode),
					'CountryCode'			=> 'IN'
		),

		//Contact Info
		'Contact' 			=> array(
					'Department'			=> 'Consignee',
					'PersonName'			=> $cname,
					'Title'					=> 'Sir/Madam',
					'CompanyName'			=> $cname,
					'PhoneNumber1'			=> substr($telephoneCust,0,25),
					'PhoneNumber1Ext'		=> '',
					'PhoneNumber2'			=> '',
					'PhoneNumber2Ext'		=> '',
					'FaxNumber'				=> '',
					'CellPhone'				=> substr($telephoneCust,0,25),
					'EmailAddress'			=> $cemail,
					'Type'					=> ''
		)
	);

	//new we (craftsvilla)
//$post['aramex_shipment_info_billing_account'] == 3
	if(3){
		$params['ThirdParty'] = array(
			'Reference1' 		=> $incrementId, //'ref11111',
			'Reference2' 		=> '',
			'AccountNumber' 	=> $accountId, //'43871',

			//Party Address
			'PartyAddress'		=> array(
						'Line1'					=> substr(mysql_escape_string(Mage::getStoreConfig('courier/shipperdetail/address')),0,25), //'13 Mecca St',
						'Line2'					=> substr(mysql_escape_string(Mage::getStoreConfig('courier/shipperdetail/address')),25,50),
						'Line3'					=> substr(mysql_escape_string(Mage::getStoreConfig('courier/shipperdetail/address')),50,95),
						'City'					=> '',//Mage::getStoreConfig('courier/shipperdetail/city'), //'Dubai',
						'StateOrProvinceCode'	=> '',//Mage::getStoreConfig('courier/shipperdetail/state'), //'',
						'PostCode'				=> str_replace(' ','',Mage::getStoreConfig('courier/shipperdetail/postalcode')),
						'CountryCode'			=> 'IN', //'AE'
			),

			//Contact Info
			'Contact' 			=> array(
						'Department'			=> '',
						'PersonName'			=> Mage::getStoreConfig('courier/shipperdetail/name'), //'Suheir',
						'Title'					=> '',
						'CompanyName'			=> Mage::getStoreConfig('courier/shipperdetail/company'), //'Aramex',
						'PhoneNumber1'			=> Mage::getStoreConfig('courier/shipperdetail/phone'), //'55555555',
						'PhoneNumber1Ext'		=> '',
						'PhoneNumber2'			=> '',
						'PhoneNumber2Ext'		=> '',
						'FaxNumber'				=> '',
						'CellPhone'				=> Mage::getStoreConfig('courier/shipperdetail/phone'),
						'EmailAddress'			=> Mage::getStoreConfig('courier/shipperdetail/email'), //'',
						'Type'					=> ''
			),
		);

	}
	// Other Main Shipment Parameters
	$params['Reference1'] 				= $incrementId; //'Shpt0001';
	$params['Reference2'] 				= '';
	$params['Reference3'] 				= '';
	$params['ForeignHAWB'] 				= '';

	$params['TransportType'] 			= 0;
	$params['ShippingDateTime'] 		= time();
	$params['DueDate'] 					= time() + (7 * 24 * 60 * 60);
	$params['PickupLocation'] 			= 'Reception';
	$params['PickupGUID'] 				= '';				
	$params['Comments'] 				= '';
	$params['AccountingInstrcutions'] 	= '';
	$params['OperationsInstructions'] 	= '';
	$params['Details'] = array(
					'Dimensions'			=> array(
						'Length'	=> '0',
						'Width'		=> '0',
						'Height'	=> '0',
						'Unit'		=> 'cm'
					),

					'ActualWeight'			=> array(
						'Value'		=> 0.5,
						'Unit'		=> 'KG'
					),

					'ProductGroup'			=> 'DOM', //'EXP',
					'ProductType'			=> 'CDA', //,'PDX'


					'PaymentType'			=> '3',


					'PaymentOptions'		=> '', //$post['aramex_shipment_info_payment_option']


					'Services'				=> 'CODS',

					'NumberOfPieces'		=> $totalQtyOrdered,
					'DescriptionOfGoods'	=> $aramex_items1[0],$aramex_items1[1],$aramex_items1[2],
					'GoodsOriginCountry'	=> 'IN',
					'Items'					=> $aramex_items,
	);

	$params['Details']['CashOnDeliveryAmount'] = array(
			'Value' 		=> $amountCOD, 
			'CurrencyCode' 	=> $baseCurrency
	);

	$params['Details']['CustomsValueAmount'] = array(
			'Value' 		=> '', 
			'CurrencyCode' 	=>  ''
	);

	
	$major_par['Shipments'][]= $params;
	//echo '<pre>';print_r($major_par['Shipments']);exit;
	//$clientInfo = Mage::helper('aramexshipment')->getClientInfo();	
	
	$major_par['ClientInfo'] = $clientInfo;
//echo '<pre>';print_r($major_par);exit;
	$major_par['LabelInfo'] = array(
		'ReportID'		=> 9729, //'9201',
		'ReportType'		=> 'URL'
	);
	
	try {
		//create shipment call
			//	echo '<pre>';print_r($params);exit;
		$auth_call = $soapClient->CreateShipments($major_par);					
	
	//echo '<pre>';print_r($auth_call);exit;
	if($auth_call->HasErrors){
			if(empty($auth_call->Shipments)){
				if(count($auth_call->Notifications->Notification) > 1){
					foreach($auth_call->Notifications->Notification as $notify_error){
						echo ('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message);
					}
				} else {
					Mage::throwException($this->__('Aramex: ' . $auth_call->Notifications->Notification->Code . ' - '. $auth_call->Notifications->Notification->Message));
				}
			} else {
				
				if(count($auth_call->Shipments->ProcessedShipment->Notifications->Notification) > 1){
					$notification_string = '';
					foreach($auth_call->Shipments->ProcessedShipment->Notifications->Notification as $notification_error){
						$notification_string .= $notification_error->Code .' - '. $notification_error->Message . ' <br />';
					}
					Mage::throwException($notification_string);
				} else {
					Mage::throwException($this->__('Aramex: ' . $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Code .' - '. $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Message));Mage::throwException($this->__('Aramex: ' . $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Code .' - '. $auth_call->Shipments->ProcessedShipment->Notifications->Notification->Message));
				}
			}
		} else {
			$awb = $auth_call->Shipments->ProcessedShipment->ID;
			if($order->canShip()) {	   
				
				//$shipmentid = Mage::getModel('sales/order_shipment_api')->create($incrementId, $aramex_items, "AWB No. ".$auth_call->Shipments->ProcessedShipment->ID." - Order No. ".$auth_call->Shipments->ProcessedShipment->Reference1." - <a href='javascript:void(0);' onclick='myObj.printLabel();'>Print Label</a>");
				
				//$ship 		= true;						

				//$ship 		= Mage::getModel('sales/order_shipment_api')->addTrack($shipmentid, 'aramex', 'Aramex', $auth_call->Shipments->ProcessedShipment->ID);
				
				//sending mail
				/*if($ship){
//								if($post['aramex_email_customer'] == 'yes'){


						$fromEmail = $post['aramex_shipment_shipper_email']; // sender email address
						$fromName = $post['aramex_shipment_shipper_name']; // sender name

						$toEmail = $post['aramex_shipment_receiver_email']; // recipient email address
						$toName = $post['aramex_shipment_receiver_name']; // recipient name

						$body = "Your shipment has been created for order id : ".$post['aramex_shipment_info_reference']."<br />Shipment No : ".$auth_call->Shipments->ProcessedShipment->ID."<br />"; // body text
						$subject = "Aramex Shipment";		
						$body = 'Airway bill number: '.$auth_call->Shipments->ProcessedShipment->ID.'<br />Order number: '.$order->getIncrementId().'<br />You can track shipment on <a href="http://www.aramex.com/express/track.aspx">http://www.aramex.com/express/track.aspx</a><br />';
						$mail = new Zend_Mail();
						$mail->setBodyText($body);
						$fromEmail=Mage::getStoreConfig('trans_email/ident_general/email');
						$fromName=Mage::getStoreConfig('trans_email/ident_general/name');
						$mail->setFrom($fromEmail, $fromName);
						$toEmail=$order->getCustomerEmail();
						$toName=$order->getCustomerName();
						$mail->addTo($toEmail, $toName);
						$mail->setSubject($subject);

						try {
							$mail->send();
						}

						catch(Exception $ex) {
							Mage::getSingleton('core/session')
								->addError('Unable to send email.');
						}

//								}
				}*/

				//Mage::getSingleton('core/session')->addSuccess('Aramex Shipment Number: '.$auth_call->Shipments->ProcessedShipment->ID.' has been created.');
				//$order->setState('warehouse_pickup_shipped', true);
			}
		}
	} catch (Exception $e) {
		/*echo 'I m here';
		$aramex_errors = true;
		echo $e->getMessage();*/
	}

	 		return $awb;
			//return $shipmentid;
		}
		return NULL;
	
	}
public function getVendorskucv($productId)
	{
	$readId = Mage::getSingleton('core/resource')->getConnection('core_read');	
	$typeIdnvendorskuquery = "SELECT `attribute_id`,`value` FROM `catalog_product_entity_varchar` WHERE `entity_id` ='".$productId."' AND `attribute_id` = '644' "; //live id = 644, local = 647
	$resultofvendorskunidtype = $readId->query($typeIdnvendorskuquery)->fetch();
	$vendorSku = $resultofvendorskunidtype['value'];
	return $vendorSku;
	}
public function getPayoutDetails($shipmentIdpayout){
		try{		
		$connection = Mage::app()->getConnectionMongo();
		if($connection->connected == 1)
		{
		$dbname = 'craftsvilla_mongo';
		$db = $connection->$dbname;
		$shipmentPyoutdetail = $db->shipmentpayout_mongo;
		$checkselect = array(
			'shipmentid' => $shipmentIdpayout
		);
		$selectpayoutdetails = $shipmentPyoutdetail->findOne($checkselect);
		return $selectpayoutdetails;
		//echo '<pre>';print_r($selectpayoutdetails);exit;		
		//var_dump($selectpayoutdetails);exit;
		}
	}
		catch(Exception $e)
		{
		//echo '';
		}
	}
public function getrefundCv($shipmentIdcust)
	{
		$shipment1 = Mage::getModel('sales/order_shipment');		
		$shipment = $shipment1->load($shipmentIdcust);
		//	echo '<pre>';print_r($shipment);exit;		
		$_order = $shipment->getOrder();
		$entityid = $shipment->getEntityId();
		$orderIdd = $shipment->getOrderId();
		$orderRefund = Mage::getModel('sales/order')->load($orderIdd);
		$orderBaseShippingAmount = $orderRefund->getBaseShippingAmount();
		$baseTotalValue = $shipment->getBaseTotalValue();
		$itemisedtotalshippingcost = $shipment->getItemisedTotalShippingcost();
		$domshippingcost = floor($itemisedtotalshippingcost);
		//basetotalshipping amount of ordere value
		$lastFinalbaseshipamt = Mage::helper('udropship')->baseShippngAmountofOrder($orderIdd,$orderBaseShippingAmount);
		$countrycode = $orderRefund->getShippingAddress()->getCountryId();
		$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
		$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$entityid)
								  ->columns('SUM(a.base_discount_amount) AS amount');

		//echo $orderitem->getSelect()->__toString();exit;
			$orderitemdata = $orderitem->getData();
			
			foreach($orderitemdata as $_orderitemdata)
			{
				$discountamount = floor(($_orderitemdata['amount']));
			}
			if($countrycode == 'IN')
			{
			$totalAmounttoRefund = $baseTotalValue + $domshippingcost - $discountamount;
			}
			else{
			$totalAmounttoRefund = $baseTotalValue + $lastFinalbaseshipamt - $discountamount;
			}
			return $totalAmounttoRefund;
	}

public function baseShippngAmountofOrder($orderId,$orderBaseShippingAmount)
		{
		$readOrder = Mage::getSingleton('core/resource')->getConnection('core_read');
		$adjustquery = $readOrder->query("select count(*) as cntshipments from `sales_flat_shipment`  where `order_id` = '".$orderId."'")->fetch();
		$shipcnt = $adjustquery['cntshipments'];
		$lastFinalshipamt = $orderBaseShippingAmount/$shipcnt;
		return $lastFinalshipamt;
		}
public function getPickupRqst($id){
		
		$shipment1 = Mage::getModel('sales/order_shipment');		
		$shipment = $shipment1->load($id);
		$incmntId = $shipment->getIncrementId(); 
		//echo '<pre>';print_r($shipment);exit;
		$shipmentCount = count($shipment);//added for aramex
		$totalQtyOrdered = $shipment->getTotalQty();//added for aramex
		$totalQtyWeight = 0.5;//added for aramex
//Get the vendor detail for aramex pick up
		$dropship = Mage::getModel('udropship/vendor')->load($shipment->getUdropshipVendor());
			//print_r($dropship);exit;
			$vendorStreet = $dropship['street'];
			$vendorCity = $dropship->getCity();
			$vendorName = $dropship->getVendorName();
			$vAttn = $dropship->getVendorAttn();
			$vendorPostcode = $dropship->getZip();
			$vendorEmail = $dropship->getEmail();
			$vendorTelephone = $dropship->getTelephone();
			$regionId = $dropship->getRegionId();
			$region = Mage::getModel('directory/region')->load($regionId);
			$regionName = $region->getName();



		$account=Mage::getStoreConfig('courier/general/account_number');		
		$country_code= 'IN';
		$post = '';
		$country = Mage::getModel('directory/country')->loadByCode($country_code);		
		$response=array();
		$clientInfo = Mage::helper('courier')->getClientInfo();		
		try {
						
//		echo $pickupDate = $pickupdateAramex;		
		$pickupDate = time() + (1 * 24 * 60 * 60);		
		$readyTimeH=10;
		$readyTimeM=10;			
		$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
		$closingTimeH=18;
		$closingTimeM=59;
		$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
		$params = array(
		'ClientInfo'  	=> $clientInfo,
								
		'Transaction' 	=> array(
								'Reference1'			=> $incmntId 
								),
								
		'Pickup'		=>array(
								'PickupContact'			=>array(
									'PersonName'		=>html_entity_decode(substr($vAttn.','.$vendorName,0,45)),
									'CompanyName'		=>html_entity_decode($vendorName),
									'PhoneNumber1'		=>html_entity_decode($vendorTelephone),
									'PhoneNumber1Ext'	=>html_entity_decode(''),
									'CellPhone'			=>html_entity_decode($vendorTelephone),
									'EmailAddress'		=>html_entity_decode($vendorEmail)
								),
								'PickupAddress'			=>array(
									'Line1'				=>html_entity_decode($vendorStreet),
									'City'				=>'',//html_entity_decode($vendorCity),
									'StateOrProvinceCode'=>'',//html_entity_decode($regionName),
									'PostCode'			=>html_entity_decode($vendorPostcode),
									'CountryCode'		=>'IN'
								),
								
								'PickupLocation'		=>html_entity_decode('Reception'),
								'PickupDate'			=>$readyTime,
								'ReadyTime'				=>$readyTime,
								'LastPickupTime'		=>$closingTime,
								'ClosingTime'			=>$closingTime,
								'Comments'				=>html_entity_decode('Please Pick up'),
								'Reference1'			=>html_entity_decode($_shipmentId),
								'Reference2'			=>'',
								'Vehicle'				=>'',
								'Shipments'				=>array(
									'Shipment'					=>array()
								),
								'PickupItems'			=>array(
									'PickupItemDetail'=>array(
										'ProductGroup'	=>'DOM',
										'ProductType'	=>'CDA',
										'Payment'		=>'3',										
										'NumberOfShipments'=>$shipmentCount,
										'NumberOfPieces'=>$totalQtyOrdered,										
										'ShipmentWeight'=>array('Value'=>'0.5','Unit'=>'KG'),
										
									),
								),
								'Status' =>'Ready'
							)
	);
	$baseUrl = Mage::helper('courier')->getWsdlPath();
	$soapClient = new SoapClient($baseUrl . 'shipping-services-api-wsdl.wsdl');
	try{
	$results = $soapClient->CreatePickup($params);		
//	echo '<pre>';print_r($results);exit;
	if($results->HasErrors){
		if(count($results->Notifications->Notification) > 1){
			$error="";
			foreach($results->Notifications->Notification as $notify_error){
				$error.=$this->__('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message)."<br>";				
				}
				$response['error']=$error;
			}else{
				$response['error']=$this->__('Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message);
			}
			$response['type']='error';
		}else{
			
			return $results->ProcessedPickup->ID;

			}
		} catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
			}
		}
		catch (Exception $e) {
			$response['type']='error';
			$response['error']=$e->getMessage();			
		}
		json_encode($response);
	
	}
//public function getCurrentCourier()
//{
	//$selectCourier = Mage::getStoreConfig('courier/selectcourierpartner/couriername');
	//$selectCourier = 'Fedex';
//	$selectCourier = 'Aramex';
//	return $selectCourier;
//}

public function getCurrentCourier($vendorPostcode,$postcode)
{
	$readdsdId = Mage::getSingleton('core/resource')->getConnection('core_read');
	$suratPincode = array('394','395','396','390','392');
	$uppincode = array('201','208','209','211','212','221','226','227','243','244','245','247','250','281','282','813','812','800','823','452','453','454','455','462','474','482');
	$vPostCode =  substr($vendorPostcode,0,3);
	$custPostcode= substr($postcode,0,3);
	$checkPincodeAvail = "SELECT * FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postcode."'";
	//$resultforsurat = $readdsdId->query($checkPincodeAvail)->fetch();
	//$resultforsurat['pincode'];
	$selectCourier =  'Aramex';
		if(in_array($vPostCode,$suratPincode))
			{
			$checkPincodeAvail = "SELECT * FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postcode."' AND `carrier` LIKE '%Aramex%'";
			$servicableZip = $readdsdId->query($checkPincodeAvail)->fetch();
			//$readdsdId->closeConnection();
			//echo 'hello';echo print_r($servicableZip);
				if($servicableZip)
				{
					$selectCourier =  'Aramex';
				}
				else
				{
					$selectCourier =  'Fedex';

				} 	
			}
		else{
			if(in_array($custPostcode,$uppincode))
				{
					$selectCourier = 'Aramex';		
				}
			else{
				$checkPincodeAvail1 = "SELECT * FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postcode."' AND `carrier` LIKE '%Fedex%'";
				$servicableZip1 = $readdsdId->query($checkPincodeAvail1)->fetch();
				if($servicableZip1){
						$selectCourier = "Fedex";
					}
				else{
					$selectCourier = "Aramex";
				    }
			}
			}
$readdsdId->closeConnection();
return $selectCourier;
}	

		


public function fedexawbgenerate($aramexcouriername,$shipment){

		$path_to_wsdl = Mage::helper('fedexcourier')->getWsdlPath();
		define('SMARTPOST_LABEL', 'smartpostlabel.png');  // PDF label file. Change to file-extension .png for creating a PNG label (e.g. shiplabel.png)
		ini_set("soap.wsdl_cache_enabled", "0");
		//Get shipment details
		$shipmentDetail = Mage::getModel('sales/order_shipment')->load($shipment);
		$items = $shipmentDetail->getAllItems();
		$totalQtyOrdered = $shipmentDetail->getTotalQty();
		$basesubTotal = $shipmentDetail->getBaseTotalValue();
		$getDiscAmount = $this->getDiscountamt($shipment);	
		$baseShippingCost = $shipmentDetail->getItemisedTotalShippingcost();
		$baseCodFee = $shipmentDetail->getBaseCodFee();
		$grandTotal = $basesubTotal+$baseShippingCost;
		if($getDiscAmount == null) {$getDiscAmount = 0; }
		$amountCOD = $grandTotal - $getDiscAmount;	
		$incrementId = $shipmentDetail->getIncrementId();
		$orderId = $shipmentDetail->getOrderId();
		$vendorIdfed = 	$shipmentDetail->getUdropshipVendor();	


		$client = new SoapClient($path_to_wsdl.'ShipService_v15.wsdl', array('trace' => 1)); // Refer to http://us3.php.net/manual/en/ref.soap.php for more information



		//var_dump($client);exit;
		//echo $path_to_wsdl.'ShipService_v15.wsdl';exit;
		//$clientInfo = Mage::helper('fedexcourier')->getClientInfo();
			$request['WebAuthenticationDetail'] = array(
			'UserCredential' =>array(
				'Key' => 'loM2hp8EMN1WJ06W', 
				'Password' => 'MLbgtBsfYmqARP7YYDf2B8WmL'
			)
			);


			$request['ClientDetail'] = array(

			'AccountNumber' => '619389786', 

			'MeterNumber' => '107640079'

			);

			$request['TransactionDetail'] = array('CustomerTransactionId' => $incrementId);

			

			$request['Version'] = array(

			'ServiceId' => 'ship', 

			'Major' => '15', 

			'Intermediate' => '0', 

			'Minor' => '0'

			);



			$request['RequestedShipment'] = array(

			'ShipTimestamp' => date('c'),

			'DropoffType' => 'REGULAR_PICKUP', // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION

			'ServiceType' => 'STANDARD_OVERNIGHT', // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...

			'PackagingType' => 'YOUR_PACKAGING', // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'Shipper' => $this->addShipper($vendorIdfed),
			'Recipient' => $this->addRecipient($orderId),
			'ShippingChargesPayment' => $this->addShippingChargesPayment('619389786'),
			'ON_DELIVERY'=>'places@craftsilla.com',
			'ON_EXCEPTION'=> 'monica@craftsvilla.com',
			'ON_SHIPMENT' =>'places@craftsilla.com',
			'LabelSpecification' => $this->addLabelSpecification(), 
			'SpecialServicesRequested'  => $this->addSpecialServices($shipment,$grandTotal), 

			'PackageCount' => 1,

			'RequestedPackageLineItems' => array(

				'0' => $this->addPackageLineItem1($incrementId)

			),

			'CustomsClearanceDetail' => array('CommercialInvoice' => array('Purpose' => 'SOLD'),

			'Commodities' => array(

					'0' => $this->addCommodity($items,$shipment,$grandTotal)

				),

			'CustomsValue' => array(

					'Currency' => 'INR',
					'Amount' => $amountCOD

				),

			),

			); 



		

		//echo '<pre>';print_r($request);exit;

				                                                                                                               
	try {
		if($this->setEndpoint('changeEndpoint')){
		$newLocation = $client->__setLocation($this->setEndpoint('endpoint'));
		}
	$response = $client->processShipment($request);  // FedEx web service invocation
	
	$statsconnfedex= Mage::getSingleton('core/resource')->getConnection('statsdb_connection');

$routingone = $response->CompletedShipmentDetail->OperationalDetail->UrsaPrefixCode;

$routingtwo = $response->CompletedShipmentDetail->OperationalDetail->UrsaSuffixCode;

$routingthree = $response->CompletedShipmentDetail->OperationalDetail->AirportId;

$routingfour = $response->CompletedShipmentDetail->OperationalDetail->DestinationLocationStateOrProvinceCode;

$routingfive = $response->CompletedShipmentDetail->OperationalDetail->DestinationServiceArea;

$routingno = $response->CompletedShipmentDetail->OperationalDetail->PostalCode;

$formidone = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->FormId;

$formidtwo = $response->CompletedShipmentDetail->AssociatedShipments->TrackingId->FormId;

$trackCodreturn = $response->CompletedShipmentDetail->AssociatedShipments->TrackingId->TrackingNumber;

$serviceone = $response->CompletedShipmentDetail->OperationalDetail->AstraDescription;

$servicetwo = $response->CompletedShipmentDetail->AssociatedShipments->ShipmentOperationalDetail->AstraDescription;

$trackingNumber = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;//track number of seller

$fedex_overnight_barcode = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->Barcodes->StringBarcodes->Value;

$fedex_cod_return_label_barcode = $response->CompletedShipmentDetail->AssociatedShipments->PackageOperationalDetail->Barcodes->StringBarcodes->Value;





$insert_data =  "INSERT INTO `fedex_cod_craftsvilla`(`shipmentid`, `ursaprefixcode`, `ursaSuffixcode`, `airportid`, `provincecode`, `servicearea`, `postalcode`, `formidone`, `formidtwo`, `trackcodreturn`, `serviceone`, `servicetwo`, `trackcod`, `overnightlabel`, `codreturnlabel`) 

VALUES ('".$shipment."','".$routingone."','".$routingtwo."','".$routingthree."','".$routingfour."','".$routingfive."','".$routingno."','".$formidone."','".$formidtwo."','".$trackCodreturn."','".$serviceone."','".$servicetwo."','".$trackingNumber."',

'".$fedex_overnight_barcode."', '".$fedex_cod_return_label_barcode."') 

ON DUPLICATE KEY UPDATE 

 ursaprefixcode='".$routingone."', ursaSuffixcode= '".$routingtwo."', airportid= '".$routingthree."',  provincecode = '".$routingfour."', servicearea = '".$routingfive."', postalcode = '".$routingno."', formidone=  '".$formidone."', formidtwo= '".$formidtwo."',  trackcodreturn= '".$trackCodreturn."', serviceone = '".$serviceone."', servicetwo = '".$servicetwo."', trackcod = '".$trackingNumber."', overnightlabel = '".$fedex_overnight_barcode."', codreturnlabel = '".$fedex_cod_return_label_barcode."' ";



 $ratingqueryfedex= $statsconnfedex->query($insert_data);

	


	//$trackingNumber = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;//track number of seller
	//$trackcodreturn = $response->CompletedShipmentDetail->AssociatedShipments->TrackingId->TrackingNumber;
	//echo '<pre>';print_r($response->CompletedShipmentDetail);exit;
    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){    	
    	$this->printSuccess($client, $response);
     
		//$fp = fopen(Mage::getBaseDir("media") .'/fedextrack/'.$incrementId.'_codreturn.png', "wb");
		//$fwrite = fwrite($fp, $response->CompletedShipmentDetail->AssociatedShipments->Label->Parts->Image);//Create COD Return PNG or PDF file
		//fclose($fp);
	$this->fedexonepagePackslip($response,$incrementId);

        //echo '<a href="./'.SHIP_CODLABEL.'">'.SHIP_CODLABEL.'</a> was generated.'.Newline;

        // Create PNG or PDF label

        // Set LabelSpecification.ImageType to 'PDF' or 'PNG for generating a PDF or a PNG label       

        //$fp = fopen(SHIP_LABEL, 'wb');   

//	$fp = fopen(Mage::getBaseDir("media") .'/fedextrack/'.$incrementId.'_codlabel.png', "wb");
        //fwrite($fp, $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image); //Create PNG or PDF file
        //fclose($fp);
	return $trackingNumber;
        //echo '<a href="./'.SHIP_LABEL.'">'.SHIP_LABEL.'</a> was generated.';
    }else{
        //$this->printError($client, $response);
    }
    $this->writeToLog($client);    // Write to log file
} catch (SoapFault $exception) {
    printFault($exception, $client);
	}

}

function addSpecialServices($shipment,$grandTotal){

$getDiscAmount = $this->getDiscountamt($shipment);
$amountCOD = $grandTotal - $getDiscAmount;
	$specialServices = array(
		'SpecialServiceTypes' => array('COD'),
		'CodDetail' => array(
			'CodCollectionAmount' => array(
				'Currency' => 'INR', 
				'Amount' => $amountCOD
			),

			'CollectionType' => 'CASH', // ANY, GUARANTEED_FUNDS
		'FinancialInstitutionContactAndAddress' => array('Contact'=>array('PersonName' => 'Manoj Gupta','CompanyName' => 'Craftsvilla Handicrafts Pvt Ltd','PhoneNumber'=>'9920175041'),
		'Address' => array('StreetLines' => '1502 Wing G Lotus Corporate Park  Goregaon (East), Mumbai 400063','City' => 'Mumbai','StateOrProvinceCode'=>'MH','PostalCode' => '400063','CountryCode' => 'IN'
,'CountryName' => 'INDIA','Residential' => 'false')),

		'RemitToName' => 'Craftsvilla Handicrafts Pvt Ltd',
		)
	);
	return $specialServices; 
}
function addShippingChargesPayment($accfed){

	$shippingChargesPayment = array('PaymentType' => 'SENDER',

        'Payor' => array(

			'ResponsibleParty' => array(

				'AccountNumber' => $accfed,

				'Contact' => null,

				'Address' => array(

					'CountryCode' => 'IN'

				)

			)

		)

	);



return $shippingChargesPayment;

}

function printFault($exception, $client) {

    //echo '<h2>Fault</h2>' . "<br>\n";                        

    //echo "<b>Code:</b>{$exception->faultcode}<br>\n";

    //echo "<b>String:</b>{$exception->faultstring}<br>\n";

    //$this->writeToLog($client);

    

    //echo '<h2>Request</h2>' . "\n";

	//echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  

	//echo "\n";

}

function writeToLog($client){  

	/**

	 * __DIR__ refers to the directory path of the library file.

	 * This location is not relative based on Include/Require.

	 */

	if (!$logfile = fopen(Mage::getBaseDir("media").'/fedextrack/fedextransactions.log', "a"))

	{

   		//error_func("Cannot open " .'/home/amit/doejofinal/fedextransactions.log' . " file.\n", 0);

   		exit(1);

	}

	fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\r\n" . $client->__getLastResponse()."\r\n\r\n"));

}

function addShipper($vendorIdfed){
	$dropship = Mage::getModel('udropship/vendor')->load($vendorIdfed);
			//print_r($dropship);exit;
			$vendorStreet = $dropship['street'];
			$vendorCity = $dropship->getCity();
			$vendorName = mysql_escape_string($dropship->getVendorName());
			$vAttn = mysql_escape_string($dropship->getVendorAttn());
			$vendorPostcode = $dropship->getZip();
			$vendorEmail = $dropship->getEmail();
			$vendorTelephone = $dropship->getTelephone();
			$regionId = $dropship->getRegionId();
			$region = Mage::getModel('directory/region')->load($regionId);
			//print_r($region);exit;			
			$reginCode = $region->getCode();
			$countryId = $region->getCountryId();
			$regionName = $region->getName();

		$shipper = array(
			'Contact' => array(
				'PersonName' => $vendorName,
				'CompanyName' => $vAttn,
				'PhoneNumber' => substr($vendorTelephone,0,15)
			),
			'Address' => array(
				'StreetLines' => array(substr($vendorStreet,0,30),substr($vendorStreet,30,60)),
				'City' => $vendorCity,
				'StateOrProvinceCode' => $reginCode,
				'PostalCode' => $vendorPostcode,
				'CountryCode' => $countryId
			)
		);
		return $shipper;
	}
	function addRecipient($orderId){
		$order = Mage::getModel('sales/order')->load($orderId);
		$address = $order->getShippingAddress();
		$postCode = $address->getPostcode();
		$country = $address->getCountry();
		$readId = Mage::getSingleton('core/resource')->getConnection('core_read');
		$getState = "SELECT `state` FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postCode."' AND `carrier` LIKE '%FEDEX%'";
		$resultPin = $readId->query($getState)->fetch();
		$stateGet = rtrim($resultPin['state']);
		$readId->closeConnection();
		$region = Mage::getModel('directory/region')->loadByName($stateGet,'IN');
		//$region = Mage::getModel('directory/region')->load($address->getRegionId());
		$reginCode = $region->getCode();
		$custName = $address->getName();
		$custAddr = $address->getStreetFull();
		$region = $address->getRegion();
		//$country = $address->getCountry();
		$city = $address->getCity();
		$contactN = $address->getTelephone();
		//$postCode = $address->getPostcode();
		$recipient = array(
			'Contact' => array(
				'PersonName' => $custName,
				'CompanyName' => '',
				'PhoneNumber' => substr($contactN,0,15)
			),
			'Address' => array(
				'StreetLines' => array(substr($custAddr,0,35),substr($custAddr,35,70)),
				'City' => $city,
				'StateOrProvinceCode' => $reginCode,
				'PostalCode' => $postCode,
				'CountryCode' => $country,
				'Residential' => true
			)
		);
		return $recipient;	                                    
	}
		function addLabelSpecification(){
		$labelSpecification = array(
			'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
			'ImageType' => 'PNG',  // valid values DPL, EPL2, PDF, ZPLII and PNG
			'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
			'LabelPrintingOrientation' => 'TOP_EDGE_OF_TEXT_FIRST'
		);
		return $labelSpecification;
	}

function addPackageLineItem1($incrementId){
	$packageLineItem = array(
		'SequenceNumber'=>1,
		'GroupPackageCount'=>1,
		'Weight' => array(
			'Value' => 0.5,
			'Units' => 'KG'

		),

		//'Dimensions' => array(
			//'Length' => 6,
			//'Width' => 4,
			//'Height' => 1,
			//'Units' => 'IN'
		//),

		'CustomerReferences' => array(

			'0' => array(

				'CustomerReferenceType' => 'CUSTOMER_REFERENCE',  // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY

				'Value' => 'Bill D/T - Recipient '.$incrementId

			), 

			'1' => array(

				'CustomerReferenceType' => 'INVOICE_NUMBER', 

				'Value' => ''

			),

			'2' => array(

				'CustomerReferenceType' => 'P_O_NUMBER', 

				'Value' => ''

			)

		)

	);

	return $packageLineItem;

}



function addCommodity($items,$shipment,$grandTotal)

{
$getDiscAmount = $this->getDiscountamt($shipment);
$amountCOD = $grandTotal - $getDiscAmount;
$sku ='';
	foreach($items as $_item)
	{
	//print_r($_item->getData());
		$pname = $_item->getName();
		$qty = 	$_item->getQty();
		$qtyOrdered = $_item->getQtyOrdered();
		$skustring = mysql_escape_string($_item->getSku());
		$sku1 = array($skustring.", ");                
		$sku .= implode(" " , $sku1);	
	}
	$commodity = array(
		'Name' => $pname,
		'NumberOfPieces' => $qty,
		'Description' => 'Bought From Craftsvilla'." - ".$sku,
		//'HarmonizedCode' => '170220229',
		'CountryOfManufacture' => 'IN',
		'Weight' => array(
			'Units' => 'KG',
			'Value' => '0.5'
		),
		'Quantity' => $qtyOrdered,
		'QuantityUnits' => 'EA',
		'UnitPrice' => array(
			'Currency' => 'INR',
			'Amount' => $amountCOD
		),

		//'CustomsValue' => array(
			//'Currency' => 'INR',
			//'Amount' => '10.0'
		//),
		//'ExportLicenseNumber' => '123456',
		//'ExportLicenseExpirationDate' => '2014-11-01',
		//'CIMarksAndNumbers' => '124553',
		//'PartNumber' => '1245'
	);
	return $commodity;
}
function getDiscountamt($shipment){
$orderitem = Mage::getModel('sales/order_shipment_item')->getCollection();
			$orderitem->getSelect()->join(array('a'=>'sales_flat_order_item'),'a.item_id=main_table.order_item_id')
			                      ->where('main_table.parent_id='.$shipment)
								  ->columns('SUM(a.base_discount_amount) AS amount');
								  //echo $orderitem->getSelect()->__toString();			  exit;
			$orderitemdata = $orderitem->getData();

			foreach($orderitemdata as $_orderitemdata)

			{

				$discountamount = $_orderitemdata['amount'];

			}	

return $discountamount;
}

function setEndpoint($var){

	if($var == 'changeEndpoint') Return false;

	if($var == 'endpoint') Return 'XXX';

}

function printSuccess($client, $response) {

    $this->printReply($client, $response);

}

function printReply($client, $response){

	$highestSeverity=$response->HighestSeverity;

	//if($highestSeverity=="SUCCESS"){echo '<h2>The transaction was successful.</h2>';}

	//if($highestSeverity=="WARNING"){echo '<h2>The transaction returned a warning.</h2>';}

	//if($highestSeverity=="ERROR"){echo '<h2>The transaction returned an Error.</h2>';}

	//if($highestSeverity=="FAILURE"){echo '<h2>The transaction returned a Failure.</h2>';}

	echo "\n";

	$this->printNotifications($response -> Notifications);

	$this->printRequestResponse($client, $response);

}

function printNotifications($notes){

	foreach($notes as $noteKey => $note){

		if(is_string($note)){    

           // echo $noteKey . ': ' . $note . "\n";

        }

        else{

        	//printNotifications($note);

        }

	}

	echo "\n";

}

function printRequestResponse($client){

//	echo '<h2>Request</h2>' . "\n";

	//echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
	//echo "\n";
	//echo '<h2>Response</h2>'. "\n";
	//echo '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';

//	echo "\n";

}
public function isVendorAllowedProductAdd($vendorId)

{
	$allowedProduct = true;

	$readId = Mage::getSingleton('core/resource')->getConnection('core_read');	

//	$queryOfCountcatalog = "SELECT count(*) AS countcatalog FROM `catalog_product_entity_int` WHERE `value` = '".$vendorId."' AND `attribute_id` = 531";
	$queryOfCountcatalog = "SELECT `num_of_products` AS countcatalog FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorId."'";
	$resultOfCountcatalog = $readId->query($queryOfCountcatalog)->fetch();
	//echo $resultOfCountcatalog['countcatalog'];exit;
	$blackListedSeller = array('8851', '7190', '5946');
	$whiteListedSeller = array('6862','3442','2780','2245','5287','486','295','3942','2998','7317','69','5847','1930','4875','1531','6527','1384','6352','6397','6934','1895','4402','4810','215','1773','28','4035','7623','6107','7621','8473','6218','8061','5583','7013','1552','5275','9687','5214','4050','8005','6984','8108','3857','6289','5225','5508','8904','1656','9525','7679','2591','5265','6102','2796','3384','9067','1017','458','8538','3691','4792','2312','4863','7804','9166','11253','4792','5393','8451','8815','3948','6771','3309','8811','6032','10400','1735','4845','6703','7779','5622','5965','5707','1728','10708','77','4485','11360');
	if(in_array($vendorId,$whiteListedSeller))
	{
		$allowedProduct = true;
	}
	elseif(($resultOfCountcatalog['countcatalog'] > 2500) || (in_array($vendorId,$blackListedSeller)))
		{
			$allowedProduct = false;
		}
	return $allowedProduct;

}

public function fedexonepagePackslip($response,$shipmentId){

 require_once 'app/Mage.php';

 Mage::app();

// check require_once 'app/Mage.php' +  Mage::app() is in file otherwise lib file will not execute  

 include_once(Mage::getBaseDir('lib').'/fedexonepage/barcodefedexscan.php') ;

 //include_once('/var/www/html/doejofinal/app/code/community/Unirgy/Dropship/Helper/barcodefedexscan.php');



$fedex_overnight_barcode = $response->CompletedShipmentDetail->CompletedPackageDetails->OperationalDetail->Barcodes->StringBarcodes->Value;

$fedex_cod_return_label_barcode = $response->CompletedShipmentDetail->AssociatedShipments->PackageOperationalDetail->Barcodes->StringBarcodes->Value;







// BCGcode128.php==========



$className = 'BCGcode128';



// onepagebarcode.php================

// In order to increase or decrease  barcode image or change its width / height  or whatever make changes to this file by muzaffar : 115-05-2015 : START:

$default_value = array();

$default_value['filetype'] = 'PNG';

$default_value['dpi'] = 300;



// $default_valuetwo['scale'] = isset($defaultScale) ? $defaultScale : 1.429

$default_value['scale'] = isset($defaultScale) ? $defaultScale : 2;

$default_value['rotation'] = 0;

$default_value['font_family'] = 'fedexbarcode.ttf';

$default_value['font_size'] = 8;

$default_value['text'] = $fedex_overnight_barcode;

$default_value['thickness'] = 63;

$default_value['start'] = 'C';

$default_value['code'] = 'BCGcode128';



$default_valuetwo = array();

$default_valuetwo['filetype'] = 'PNG';

$default_valuetwo['dpi'] = 300;

$default_valuetwo['scale'] = isset($defaultScale) ? $defaultScale : 2;

$default_valuetwo['rotation'] = 0;

$default_valuetwo['font_family'] = 'fedexbarcode.ttf';

$default_valuetwo['font_size'] = 8;

$default_valuetwo['texttwo'] = $fedex_cod_return_label_barcode;

$default_valuetwo['thickness'] = 63;

$default_valuetwo['start'] = 'C';

$default_valuetwo['code'] = 'BCGcode128';



$code = $default_value['code'];

$code = $default_valuetwo['code'];



 // $codeText = $default_value['text'];

 // $codeTexttwo = $default_valuetwo['texttwo'];





$filetypes = array('PNG' => BCGDrawing::IMG_FORMAT_PNG, 'JPEG' => BCGDrawing::IMG_FORMAT_JPEG, 'GIF' => BCGDrawing::IMG_FORMAT_GIF);

$drawException = null;



// In order to increase or decrease  barcode image or change its width / height  or whatever make changes to this file by muzaffar : 115-05-2015 : END:





// BCGcode128.barcode.php================

define('CODE128_A',    1);            // Table A

define('CODE128_B',    2);            // Table B

define('CODE128_C',    3);            // Table C





// onepagebarcode.php================

// function convertText($text) { 



if(!function_exists('convertText')){

function convertText($text) {

    $text = stripslashes($text);

    if (function_exists('mb_convert_encoding')) {

        $text = mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');

    }



    return $text;

}

 }





if(!function_exists('baseCustomSetup')){

function baseCustomSetup($barcode, $get) {

    // $font_dir = '.';



    if (isset($get['thickness'])) {

        $barcode->setThickness(max(9, min(90, floatval($get['thickness']))));

    }



    $font = 0;

// font family path

    if ($get['font_family'] !== '0' && intval($get['font_size']) >= 1) {

        //$font = new BCGFontFile($font_dir . '/' . $get['font_family'], intval($get['font_size']));



$font = new BCGFontFile(Mage::getBaseDir('lib').'/fedexonepage/'  . $get['font_family'], intval($get['font_size']));



    }



    $barcode->setFont($font);

}



}



if(!function_exists('customSetup')){

function customSetup($barcode, $get) {

    if (isset($get['start'])) {

        $barcode->setStart($get['start'] === 'NULL' ? null : $get['start']);

    }

}

}







// BCGDrawPNG.php================



if (!function_exists('file_put_contents')) {

    function file_put_contents($filename, $data) {

        $f = @fopen($filename, 'w');

        if (!$f) {

            return false;

        } else {

            $bytes = fwrite($f, $data);

            fclose($f);

            return $bytes;

        }

    }

}









// onepagebarcode.php================

// In order to increase or decrease  barcode image or change its width / height  or whatever make changes to this file by muzaffar : 115-05-2015 : END:









try {

    $color_black = new BCGColor(0, 0, 0);

    $color_white = new BCGColor(255, 255, 255);



    $code_generated = new $className();



    if (function_exists('baseCustomSetup')) {

        baseCustomSetup($code_generated, $default_value);

    }



    if (function_exists('customSetup')) {

        customSetup($code_generated, $default_value);

    }



    $code_generated->setScale(max(1, min(4, $default_value['scale'])));

    $code_generated->setBackgroundColor($color_white);

    $code_generated->setForegroundColor($color_black);



    if ($default_value['text'] !== '') {

        $text = convertText($default_value['text']);

        $code_generated->parse($text);

    }



} catch(Exception $exception) {

    $drawException = $exception;

}







// change folder permission so that image save.



 $barcodeimageone=  $shipmentId."_onepage1.png";



 $fileNameBC = Mage::getBaseDir() . '/media/fedextrack' . DS . $barcodeimageone;

 // $fileNameBC = '/var/www/html/doejofinal/media/fedextrack/' . $barcodeimageone;



$drawing = new BCGDrawing($fileNameBC, $color_white);





if($drawException) {

    $drawing->drawException($drawException);

} else {

    $drawing->setBarcode($code_generated);

    $drawing->setRotationAngle($default_value['rotation']);

    $drawing->setDPI($default_value['dpi'] === 'NULL' ? null : max(72, min(500, intval($default_value['dpi']))));

    $drawing->draw();



$drawing->finish();

}



try {

    $color_black = new BCGColor(0, 0, 0);

    $color_white = new BCGColor(255, 255, 255);



    $code_generated = new $className();



    if (function_exists('baseCustomSetup')) {

        baseCustomSetup($code_generated, $default_valuetwo);

    }



    if (function_exists('customSetup')) {

        customSetup($code_generated, $default_valuetwo);

    }



    $code_generated->setScale(max(1, min(4, $default_valuetwo['scale'])));

    $code_generated->setBackgroundColor($color_white);

    $code_generated->setForegroundColor($color_black);



    if ($default_valuetwo['texttwo'] !== '') {

        $text = convertText($default_valuetwo['texttwo']);

        $code_generated->parse($text);

    }



} catch(Exception $exception) {

    $drawException = $exception;

}



$barcodeimagetwo=  $shipmentId."_onepage2.png";



$fileNameBCtwo = Mage::getBaseDir() . '/media/fedextrack' . DS . $barcodeimagetwo;






$drawing = new BCGDrawing($fileNameBCtwo, $color_white);



if($drawException) {

    $drawing->drawException($drawException);

} else {

    $drawing->setBarcode($code_generated);

    $drawing->setRotationAngle($default_valuetwo['rotation']);

    $drawing->setDPI($default_valuetwo['dpi'] === 'NULL' ? null : max(72, min(500, intval($default_valuetwo['dpi']))));

    $drawing->draw();

$drawing->finish();

}





$drawing->finish($filetypes[$default_value['filetype']]);

$drawing->finish($filetypes[$default_valuetwo['filetype']]);



// In order to increase or decrease  barcode image or change its width / height  or whatever make changes to this file by muzaffar : 115-05-2015 : END:

}

public function getServicetaxCv($shipmentId)
{

     $readCon = Mage::getSingleton('core/resource')->getConnection('custom_db'); 
     $queryGet = "SELECT `updated_at` FROM `sales_flat_shipment` WHERE `increment_id` = '".$shipmentId."'";
     $resDate = $readCon->query($queryGet)->fetch();
     $updatedDate = $resDate['updated_at'];
     $readCon->closeConnection();   
        if($updatedDate >= '2015-11-15 23:59:59')
           { 
            $exServicetax = (14.5/100);
        }
         else{

            $exServicetax = (14/100);
         }  
         return  $exServicetax;
}

    public function getVendorCommission($vendorid, $shipment_id)
    {
        $readCon = Mage::getSingleton('core/resource')->getConnection('custom_db'); 
        $sqlGetCreatedDate = "SELECT `created_at` FROM `sales_flat_shipment` WHERE `increment_id` = '".$shipment_id."'";
        $resGetCreatedDate = $readCon->query($sqlGetCreatedDate)->fetch();
        $created_date = date("Y-m-d", strtotime($resGetCreatedDate['created_at']));
        $queryGet = "select `commission_percent` FROM finance_vendor_commission WHERE `vendor_id` = ".$vendorid." AND ((`start_date` <= '".$created_date."' AND `end_date` >= '".$created_date."') OR (`start_date` <= '".$created_date."' AND `end_date` = '0000-00-00'))";
        //echo $queryGet;
        $resCommission = $readCon->query($queryGet)->fetch();
        $readCon->closeConnection();
        $strTest = $vendorid .",". $shipment_id. "," . $created_date . "," . $queryGet;
        $filename = "vendorcommission_".date("Ymd");
        $filePathOfCsv = Mage::getBaseDir('media').DS.'misreport'.DS.$filename.'.txt';
        $fp=fopen($filePathOfCsv,'a');
        
        if($resCommission){
            if(is_numeric($resCommission['commission_percent'])){
                $commission_percent = $resCommission['commission_percent'];        
            } else {
                $commission_percent = 20;
            }
        }else {
            $commission_percent = 20;
        }
        $strTest .= ",".$commission_percent ."\n";
        fputs($fp, $strTest);
        fclose($fp);
        return  $commission_percent;
    }
}
