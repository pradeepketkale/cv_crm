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

class Unirgy_Dropship_Model_Vendor extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'udropship_vendor';
    protected $_eventObject = 'vendor';

    protected $_inAfterSave = false;

    protected function _construct()
    {
        $this->_init('udropship/vendor');
        parent::_construct();
        Mage::helper('udropship')->loadCustomData($this);
    }

    public function authenticate($username, $password)
    {
        $collection = $this->getCollection();
        $collection->getSelect()->where('email=?', $username);
        $this->load($username, 'email');
        if (!$this->getId()) {
            $this->unsetData();
            return false;
        }
        $masterPassword = Mage::getStoreConfig('udropship/vendor/master_password');
        if ($masterPassword && $password==$masterPassword) {
            return true;
        }
        if (!Mage::helper('core')->validateHash($password, $this->getPasswordHash())) {
            $this->unsetData();
            return false;
        }
        return true;
    }

    public function getShippingMethodCode($method, $full=false)
    {
        $unknown = Mage::helper('udropship')->__('Unknown');

        $carrierCode = $this->getCarrierCode();
        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($carrierCode);
        if (!$carrierMethods) {
            return $unknown;
        }

        $method = str_replace('udropship_', '', $method);
        $methodCode = $this->getResource()->getShippingMethodCode($this, $carrierCode, $method);
        if ($full) {
            $methodCode = $carrierCode.'_'.$methodCode;
        }
        return $methodCode;
    }

    public function getShippingMethodName($method, $full=false, $store=null)
    {
        $unknown = Mage::helper('udropship')->__('Unknown');
        $methodArr = explode('_', $method, 2);
        if (empty($methodArr[1])) {
            return $unknown.' - '.$method;
        }
        if ($methodArr[0]=='udropship') {
            $carrierCode = $this->getCarrierCode();
            $methodCode = $this->getResource()->getShippingMethodCode($this, $carrierCode, $methodArr[1]);
            if (!$methodCode) {
                return $unknown;
            }
        } else {
            $carrierCode = $methodArr[0];
            $methodCode = $methodArr[1];
        }
        $method = $carrierCode.'_'.$methodCode;
        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($carrierCode);
        $name = isset($carrierMethods[$methodCode]) ? $carrierMethods[$methodCode] : $unknown;
        if ($full) {
            $name = Mage::getStoreConfig('carriers/'.$carrierCode.'/title', $store).' - '.$name;
        }
        return $name;
    }

    public function getShippingMethods()
    {
        $arr = $this->getData('shipping_methods');
        if (is_null($arr)) {
            if (!$this->getId()) {
                return array();
            }
            $arr = $this->getResource()->getShippingMethods($this);
            $this->setData('shipping_methods', $arr);
        }
        return $arr;
    }

    public function getAssociatedShippingMethods()
    {
        return $this->getShippingMethods();
    }

    public function getAssociatedProducts($productIds=array())
    {
        if (!$this->getId()) {
            return array();
        }

        $arr = $this->getData('associated_products');
        if (is_null($arr)) {
            $arr = $this->getResource()->getAssociatedProducts($this, $productIds);
            $this->setData('associated_products', $arr);
        }
        return $arr;
    }

    /**
    * Send human readable email to vendor as shipment notification
    *
    * @param array $data
    */
    public function sendOrderNotificationEmail($shipment)
    {
        $order = $shipment->getOrder();
        $store = $order->getStore();

        $hlp = Mage::helper('udropship');
        $data = array();

        $adminTheme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));
        if ($store->getConfig('udropship/vendor/attach_packingslip') && $this->getAttachPackingslip()) {
            Mage::getDesign()->setArea('adminhtml')
                ->setPackageName(!empty($adminTheme[0]) ? $adminTheme[0] : 'default')
                ->setTheme(!empty($adminTheme[1]) ? $adminTheme[1] : 'default');

            $orderShippingAmount = $order->getShippingAmount();
            $order->setShippingAmount($shipment->getShippingAmount());

            $pdf = Mage::helper('udropship')->getVendorShipmentsPdf(array($shipment));

            $order->setShippingAmount($orderShippingAmount);

            $data['_ATTACHMENTS'][] = array(
                'content'=>$pdf->render(),
                'filename'=>'packingslip-'.$order->getIncrementId().'-'.$this->getId().'.pdf',
                'type'=>'application/x-pdf',
            );
        }

        if ($store->getConfig('udropship/vendor/attach_shippinglabel') && $this->getAttachShippinglabel() && $this->getLabelType()) {
            try {
                $hlp->unassignVendorSkus($shipment);
                $batch = Mage::getModel('udropship/label_batch')->setVendor($this)->processShipments(array($shipment));
                if ($batch->getErrors()) {
                    if (Mage::app()->getRequest()->getRouteName()=='udropship') {
                        Mage::throwException($batch->getErrorMessages());
                    } else {
                        //notify admin
                    }
                } else {
                    $labelModel = $hlp->getLabelTypeInstance($batch->getLabelType());
                    foreach ($shipment->getAllTracks() as $track) {
                        $data['_ATTACHMENTS'][] = $labelModel->renderTrackContent($track);
                    }
                }
            } catch (Exception $e) {
                // ignore if failed
            }
        }
		//Added By Amit On 06-01-2012 
		$paymentTitle = $order->getPayment()->getMethodInstance()->getTitle();


        $hlp->setDesignStore($store);
        $shippingAddress = $order->getShippingAddress();
        if (!$shippingAddress) {
            $shippingAddress = $order->getBillingAddress();
        }
        $hlp->assignVendorSkus($shipment);
        $data += array(
            'shipment'        => $shipment,
            'order'           => $order,
            'vendor'          => $this,
            'store_name'      => $store->getName(),
            'vendor_name'     => $this->getVendorName(),
            'order_id'        => $order->getIncrementId(),
            'customer_info'   => $shippingAddress->getFormated(true),
            'shipping_method' => $shipment->getUdropshipMethodDescription() ? $shipment->getUdropshipMethodDescription() : $this->getShippingMethodName($order->getShippingMethod(), true),
            'shipment_url'    => Mage::getUrl('udropship/vendor/', array('_query'=>'filter_order_id_from='.$order->getIncrementId().'&filter_order_id_to='.$order->getIncrementId())),
            'packingslip_url' => Mage::getUrl('udropship/vendor/pdf', array('shipment_id'=>$shipment->getId())),
			'payment_method'  => $paymentTitle,
        );

        $template = $this->getEmailTemplate();
        if (!$template) {
            $template = $store->getConfig('udropship/vendor/vendor_email_template');
        }
        $identity = $store->getConfig('udropship/vendor/vendor_email_identity');

        $data['_BCC'] = $this->getNewOrderCcEmails();
        if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
            $email = $this->getData($emailField) ? $this->getData($emailField) : $this->getEmail();
        } else {
            $email = $this->getEmail();
        }
        Mage::getModel('udropship/email')->sendTransactional($template, $identity, $email, $this->getVendorName(), $data);

        $hlp->unassignVendorSkus($shipment);

        $hlp->setDesignStore();
    }

    public function getFormatedAddress($type='text')
    {
        switch ($type) {
        case 'text':
            return $this->getStreet(-1)."\n".$this->getCity().', '.$this->getRegionCode().' '.$this->getZip();
        }
        $format = Mage::getSingleton('customer/address_config')->getFormatByCode($type);
        if (!$format) {
            return null;
        }
        $renderer = $format->getRenderer();
        if (!$renderer) {
            return null;
        }
        $address = Mage::getModel('customer/address')->setData($this->getData());
        return $renderer->render($address);
    }

    public function getStreet($line=0)
    {
        $street = parent::getData('street');
        if (-1 === $line) {
            return $street;
        } else {
            $arr = is_array($street) ? $street : explode("\n", $street);
            if (0 === $line || $line === null) {
                return $arr;
            } elseif (isset($arr[$line-1])) {
                return $arr[$line-1];
            } else {
                return '';
            }
        }
    }

    public function getStreet1()
    {
        return $this->getStreet(1);
    }

    public function getStreet2()
    {
        return $this->getStreet(2);
    }

    public function getStreet3()
    {
        return $this->getStreet(3);
    }

    public function getStreet4()
    {
        return $this->getStreet(4);
    }

    public function getStreetFull()
    {
        return $this->getData('street');
    }

    public function setStreetFull($street)
    {
        return $this->setStreet($street);
    }

    public function setStreet($street)
    {
        if (is_array($street)) {
            $street = trim(implode("\n", $street));
        }
        $this->setData('street', $street);
        return $this;
    }

    public function getRegionCode()
    {
        if ($this->getRegionId()) {
            return Mage::helper('udropship')->getRegionCode($this->getRegionId());
        }
        return $this->getRegion();
    }

    public function getBillingEmail()
    {
        $email = $this->getEmail();
        return $email;
    }

    public function getBillingAddress()
    {
        $address = $this->getFormatedAddress();
        return $address;
    }

    public function getBillingInfo()
    {
        $info = $this->getVendorName()."\n";
        if ($this->getVendorAttn()) {
            $info .= $this->getVendorAttn()."\n";
        }
        $info .= $this->getBillingAddress();
        return $info;
    }

    public function getPdfLabelWidth()
    {
        switch ($this->getCarrierCode()) {
        case 'usps':
            return $this->getData('endicia_pdf_label_width');
        default:
            return $this->getData($this->getCarrierCode().'_pdf_label_width');
        }
    }

    public function getPdfLabelHeight()
    {
        switch ($this->getCarrierCode()) {
        case 'usps':
            return $this->getData('endicia_pdf_label_height');
        default:
            return $this->getData($this->getCarrierCode().'_pdf_label_height');
        }
    }

    public function getFileUrl($key)
    {
        if ($this->getData($key)) {
            return Mage::getBaseUrl('media').$this->getData($key);
        }
        return false;
    }

    public function getFilePath($key)
    {
        if ($this->getData($key)) {
            return Mage::getBaseDir('media').DS.$this->getData($key);
        }
        return false;
    }

    public function getTrackApi($cCode=null)
    {
        if ($this->getPollTracking()=='-') {
            return false;
        }
        if ($this->getPollTracking()!='') {
            $cCode = $this->getPollTracking();
        } elseif (is_null($cCode)) {
            $cCode = $this->getCarrierCode();
        }
        $trackConfig = Mage::getConfig()->getNode("global/udropship/track_api/$cCode");
        if (!$trackConfig || $trackConfig->is('disabled')) {
            return false;
        }
        return Mage::getSingleton((string)$trackConfig->model);
    }

    public function getStockcheckCallback($method=null)
    {
        if (is_null($method)) {
            $method = $this->getStockcheckMethod();
        }
        if (!$method) {
            return false;
        }
        $config = Mage::getConfig()->getNode('global/udropship/stockcheck_methods');
        if (!$config->$method || $config->$method->is('disabled')) {
            return false;
        }
        $cb = explode('::', (string)$config->$method->callback);
        $cb[0] = Mage::getSingleton($cb[0]);
        if (empty($cb[0]) || empty($cb[1]) || !is_callable($cb)) {
            Mage::throwException($this->__('Invalid stock check callback: %s', (string)$config->$method->callback));
        }
        return $cb;
    }
    
    public function getStatementPoType()
    {
        $poType = $this->getData('statement_po_type');
        return !empty($poType) && ($poType != 'po' || Mage::helper('udropship')->isUdpoActive()) ? $poType : 'shipment';
    }
    
    public function getPayoutPoStatus()
    {
        return $this->getData('payout_po_status_type') == 'payout'
            ? $this->getData('payout_po_status')
            : $this->getData('statement_po_status');
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getData('status')) {
            $this->setData('status', 'I');
        }

        if ($this->hasData('url_key') && !$this->getData('url_key')) {
            $this->unsetData('url_key');
        } else {
            $data = $this->getData('url_key');
            $collection = $this->getCollection()->addFieldToFilter('url_key', $data);
            if ($this->getId()) { 
                $collection->addFieldToFilter('vendor_id', array('neq'=>$this->getId()));
            }
            if ($collection->count()) {
                Mage::throwException(Mage::helper('udropship')->__('This URL Key is already used for different vendor (%s). Please choose another.', htmlspecialchars($data)));
            }
        }

        if ($this->getPassword()) {
            $collection = $this->getCollection()
                ->addFieldToFilter('vendor_id', array('neq'=>$this->getId()))
                ->addFieldToFilter('email', $this->getEmail());
            $dup = false;
            foreach ($collection as $dup) {
                if (Mage::getStoreConfig('udropship/vendor/unique_email')) {
                    Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied email already exists.'));
                }
                if (Mage::helper('core')->validateHash($this->getPassword(), $dup->getPasswordHash())) {
                    Mage::throwException(Mage::helper('udropship')->__('A vendor with supplied email and password already exists.'));
                }
            }
        }

        Mage::helper('udropship')->processCustomVars($this);
    }
    
    public function getHidePackingslipAmount()
    {
        if ($this->getData('hide_packingslip_amount')==-1) {
            return Mage::getStoreConfigFlag('udropship/vendor/hide_packingslip_amount');
        } else {
            return $this->getData('hide_packingslip_amount');
        }
    }

    protected function _afterSave()
    {
        if ($this->_inAfterSave) {
            return;
        }
        $this->_inAfterSave = true;

        parent::_afterSave();

        if (!empty($_FILES)) {
            $baseDir = Mage::getConfig()->getBaseDir('media').DS.'vendor'.DS.$this->getId();
            Mage::getConfig()->createDirIfNotExists($baseDir);
            foreach ($_FILES as $k=>$img) {
                if (empty($img['tmp_name']) || empty($img['name']) || empty($img['type'])) {
                    continue;
                }
                if (!@move_uploaded_file($img['tmp_name'], $baseDir.DS.$img['name'])) {
                    Mage::throwException('Error while uploading file: '.$img['name']);
                }
                $this->setData($k, 'vendor/'.$this->getId().'/'.$img['name']);
            }
            $this->save();
        }
        $this->_inAfterSave = false;
    }
    
    public function isZipcodeMatch($zipCode)
    {
    	if (trim($zipCode)=='') return true;
    	$zipCodes = $this->getLimitZipcode();
    	$zipCodes = preg_replace('/(\s+|\s*[,;]\s*)+/', ' ', $zipCodes);
    	$zipCodes = preg_replace('/\s*-\s*/', '-', $zipCodes);
    	$zipCodes = explode(' ', $zipCodes);
    	$result = true;
    	foreach ($zipCodes as $zc) {
    		if (($zcGlog = preg_split('/('.implode('|', array_map('preg_quote', array('?','*','+','.'))).')/', $zc, -1, PREG_SPLIT_DELIM_CAPTURE))
    			&& count($zcGlog)>1
    		) {
    			$result = false;
    			$zcReg = '/^';
    			foreach ($zcGlog as $zcSub) {
    				if (in_array($zcSub, array('?','*','+'))) {
    					$zcReg .= '.'.$zcSub;
    				} elseif ($zcSub=='.') {
    					$zcReg .= $zcSub;
    				} else {
    					$zcReg .= preg_quote($zcSub, '/');
    				}
    			}
    			if (preg_match($zcReg.'$/', trim($zipCode))) return true;
    		} elseif (strpos($zc, '-')) {
    			$result = false;
    			list($zcFrom, $zcTo) = explode('-',$zc,2);
    			if (trim($zcFrom)<=trim($zipCode) && trim($zipCode)<=trim($zcTo)) return true;
    		} elseif (trim($zc)!='') {
    			$result = false;
    			if (trim($zc)==trim($zipCode)) return true;
    		}
    	}
    	return $result;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        Mage::helper('udropship')->loadCustomData($this);
        Mage::helper('udropship')->getVendor($this);
    }

    public function afterLoad()
    {
        parent::afterLoad();
        return $this; // added for chaining
    }
}
