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

class Unirgy_Dropship_VendorController extends Unirgy_Dropship_Controller_VendorAbstract
{

    public function indexAction()
    {
        $_hlp = Mage::helper('udropship');
        if ($_hlp->isUdpoActive() && !$this instanceof Unirgy_DropshipPo_VendorController) {
            $this->_forward('index', 'vendor', 'udpo');
            return;
        }
        switch ($this->getRequest()->getParam('submit_action')) {
        case 'labelBatch':
        case $_hlp->__('Create and Download Labels Batch'):
            $this->_forward('labelBatch');
            return;

        case 'existingLabelBatch':
            $this->_forward('existingLabelBatch');
            return;

        case 'packingSlips':
        case $_hlp->__('Download Packing Slips'):
            $this->_forward('packingSlips');
            return;

        case 'updateShipmentsStatus':
            $this->_forward('updateShipmentsStatus');
            return;
			
		 }
        
        $this->_renderPage(null, 'dashboard');
    }
	
	public function loginAction()
    {
        $ajax = $this->getRequest()->getParam('ajax');
        if ($ajax) {
            Mage::getSingleton('udropship/session')->addError($this->__('Your session has been expired. Please log in again.'));
        }
        $this->_renderPage($ajax ? 'udropship_vendor_login_ajax' : null);
    }

    public function logoutAction()
    {
        $this->_getSession()->logout();
        $this->_redirect('udropship/vendor');
    }

    public function passwordAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $confirm = $this->getRequest()->getParam('confirm');
        if ($confirm) {
            $vendor = Mage::getModel('udropship/vendor')->load($confirm, 'random_hash');
            if ($vendor->getId()) {
                Mage::register('reset_vendor', $vendor);
            } else {
                $session->addError($hlp->__('Invalid confirmation link'));
            }
        }
        $this->_renderPage();
    }

    public function passwordPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        try {
            $r = $this->getRequest();
            if (($confirm = $r->getParam('confirm'))) {
                $password = $r->getParam('password');
                $passwordConfirm = $r->getParam('password_confirm');
                $vendor = Mage::getModel('udropship/vendor')->load($confirm, 'random_hash');
                if (!$password || !$passwordConfirm || $password!=$passwordConfirm || !$vendor->getId()) {
                    $session->addError('Invalid form data');
                    $this->_redirect('*/*/password', array('confirm'=>$confirm));
                    return;
                }
                $vendor->setPassword($password)->unsRandomHash()->save();
                $session->loginById($vendor->getId());
                $session->addSuccess($hlp->__('Your password has been reset.'));
                $this->_redirect('*/*');
            } elseif (($email = $r->getParam('email'))) {
                $hlp->sendPasswordResetEmail($email);
                $session->addSuccess($hlp->__('Thank you, password reset instructions have been sent to the email you have provided, if a vendor with such email exists.'));
                $this->_redirect('*/*/login');
            } else {
                $session->addError($hlp->__('Invalid form data'));
                $this->_redirect('*/*/password');
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/password');
        }
    }

    public function accountAction()
    {
        $this->_renderPage();
    }

    public function preferencesAction()
    {
		// Added By Amit Pitre On (3 Apr 2012) as to pass current session vendor to new local module payment filter under craftsvilla.
		Mage::register('craftsvilla_paymentfilter_vendor', Mage::getSingleton('udropship/session')->getVendor());
        if (Mage::helper('udropship')->isWysiwygAllowed()) {
            $this->_renderPage(array('default', 'uwysiwyg_editor', 'uwysiwyg_editor_js'), 'preferences');
        } else {
            $this->_renderPage(null, 'preferences');
        }
    }
    
   
    
    public function preferencesPostAction()
    {
        $defaultAllowedTags = Mage::getStoreConfig('udropship/vendor/preferences_allowed_tags');
        $session = Mage::getSingleton('udropship/session');
       $hlp = Mage::helper('udropship');
       $vendorid = $session->getVendorId();
        $r = $this->getRequest();
       	if ($r->isPost()) {
            $p = $r->getPost();
			$hlp->processPostMultiselects($p);
            try {
                $v = $session->getVendor();
                foreach (array('vendor_name', 'vendor_attn', 'email', 'password', 'telephone') as $f) {
                    $v->setData($f, $p[$f]);
                }
                foreach (Mage::getConfig()->getNode('global/udropship/vendor/fields')->children() as $code=>$node) {
                    if (!isset($p[$code])) {
                        continue;
                    }

					$param = $p[$code];
                    if (is_array($param)) {
                        foreach ($param as $key=>$val) {
                            $param[$key] = strip_tags($val, $defaultAllowedTags);
                        }
                    }
                    else {
                        $allowedTags = $defaultAllowedTags;
                        if ($node->filter_input && ($stripTags = $node->filter_input->strip_tags) && isset($stripTags->allowed)) {
                            $allowedTags = (string)$node->strip_tags->allowed;
                        }
                        if ($allowedTags && $node->type != 'wysiwyg') {
                            $param = strip_tags($param, $allowedTags);
                        }

                        if ($node->filter_input && ($replace = $node->filter_input->preg_replace) && isset($replace->from) && isset($replace->to)) {
                            $param = preg_replace((string)$replace->from, (string)$replace->to, $param);
                        }
                    } // end code injection protection
                    $v->setData($code, $param);
                }
			    Mage::dispatchEvent('udropship_vendor_preferences_save_before', array('vendor'=>$v));
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess('Settings has been saved');
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udropship/vendor/preferences');
    }

    public function productAction()
    {
	$this->_renderPage(null, 'stockprice');
    }

    public function productSaveAction()
    {
		$postData=Mage::app()->getRequest()->getPost();
		$hlp = Mage::helper('udropship');
	   $session = Mage::getSingleton('udropship/session');

	$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
         $getCatalogPrivilleges = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorId,'vendor_id'); 
	 $catalog_privileges = $getCatalogPrivilleges->getCatalogPrivileges();
         
        if($catalog_privileges == 0 && $catalog_privileges != '')
		{
		$session->addError($hlp->__('You are not allowed to update products. Please contact places@craftsvilla.com'));
		}
        elseif($catalog_privileges == 1 || $catalog_privileges == '')
	{
        try {
						
			$cnt = $hlp->saveVendorProducts($this->getRequest()->getParam('vp'));
			$v = $this->getRequest()->getParam('vp');
			//print_r($v);exit;
			foreach($v as $_v)
			{
				$vsku = $_v['sku'];
				$vqty = $_v['stock_qty'];
				$read = Mage::getSingleton('core/resource')->getConnection('core_read');
				$productquery = "select `entity_id` from `catalog_product_entity` WHERE `sku`= '$vsku'";
				$resultVendor = $read->query($productquery)->fetch();
				$entity = $resultVendor['entity_id'];
				$write = Mage::getSingleton('core/resource')->getConnection('core_write');
				if($_v['stock_status']==0)
				{
				   	
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');
					$productquery = "update `cataloginventory_stock_item` set `is_in_stock` = 0,`qty` = 0 WHERE `product_id`= '".$entity."'";
					$writequery = $write->query($productquery);	
					}
				else{
					$productquery = "update `cataloginventory_stock_item` set `is_in_stock` = 1,`qty` = '".$vqty."' WHERE `product_id`= '".$entity."'";
					$writequery = $write->query($productquery);	
					}	
							
			}
			
            if (($multi = Mage::getConfig()->getNode('modules/Unirgy_DropshipMulti')) && $multi->is('active')) {
                $cnt += Mage::helper('udmulti')->saveVendorProductsPidKeys($this->getRequest()->getParam('vp'));
            }
            if (!$cnt) {
            	$session->addNotice($hlp->__('No updates were made'));
                
            } else {
                $session->addSuccess($hlp->__('Products were updated Succesfully'));
            }
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
}
         else
		{
		$session->addError($hlp->__('You are not allowed to update products. Please contact places@craftsvilla.com'));
		}
    	if (is_callable(array(Mage::helper('core/http', 'getHttpReferer')))) {
            $this->getResponse()->setRedirect(Mage::helper('core/http')->getHttpReferer());
        } else {
            $this->getResponse()->setRedirect(@$_SERVER['HTTP_REFERER']);
        }
		
    }

    public function batchesAction()
    {
        $this->_renderPage(null, 'batches');
    }

    public function shipmentInfoAction()
    {
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }
	
	public function codordersInfoAction()
    {
		
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }
    
public function disputeAction()
    {
		
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }
	
	
    public function shipmentPostAction()
    {
		$hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        $id = $r->getParam('id');
		
		$shipment = Mage::getModel('sales/order_shipment')->load($id);
		//get the shipment id for sms Added By Dileswar as dated 08-11-2012
		$_shipmentId = $shipment->getIncrementId();
		$baseTotalValue = $shipment->getBaseTotalValue();
		$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
        $session = $this->_getSession();
		// Craftsvilla Comment Added By Amit Pitre On 25-06-2012 for international shipping changing status without tracking number /////
		$_order = $shipment->getOrder();
		$_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();
		
		// for SMS Added By Dileswar on Dated 08-11-2012 
		$customerTelephone = $_order->getBillingAddress()->getTelephone();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		//
		$shipmentcodOrder = Mage::getModel('sales/order')->load($shipment->getOrderId());
			$testcodPayment = $shipmentcodOrder->getPayment();
			
			//if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery'):
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (!$shipment->getId()) {
            return;
        }

        try {
            $store = $shipment->getOrder()->getStore();

            $track = null;
            $highlight = array();

            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');

            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');
            $courier_name = $r->getParam('courier_name');

            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
            $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
			$statusAccepted = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_ACCEPTED;
			//added By dileswar 13-10-2012
			$statusOutofstock = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_OUTOFSTOCK_CRAFTSVILLA ;
			
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
           // if label was printed
            if ($printLabel) {
                $status = $r->getParam('is_shipped') ? $statusShipped : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } 
			else { // if status was set manually
                $status = $r->getParam('status');
                
				$isShipped = $status == $statusShipped || $status == $statusAccepted || $status==$statusDelivered || $autoComplete && ($status==='' || is_null($status));
				
			}

            // if label to be printed
            if ($printLabel) {
                $data = array(
                    'weight'    => $r->getParam('weight'),
                    'value'     => $r->getParam('value'),
                    'length'    => $r->getParam('length'),
                    'width'     => $r->getParam('width'),
                    'height'    => $r->getParam('height'),
                    'reference' => $r->getParam('reference'),
                    'package_count' => $r->getParam('package_count'),
                );

                $extraLblInfo = $r->getParam('extra_label_info');
                $extraLblInfo = is_array($extraLblInfo) ? $extraLblInfo : array();
                $data = array_merge($data, $extraLblInfo);

                $oldUdropshipMethod = $shipment->getUdropshipMethod();
                $oldUdropshipMethodDesc = $shipment->getUdropshipMethodDescription();
                if ($r->getParam('use_method_code')) {
                    list($useCarrier, $useMethod) = explode('_', $r->getParam('use_method_code'));
                    if (!empty($useCarrier) && !empty($useMethod)) {
                        $shipment->setUdropshipMethod($r->getParam('use_method_code'));
                        $carrierMethods = Mage::helper('udropship')->getCarrierMethods($useCarrier);
                        $shipment->setUdropshipMethodDescription(
                            Mage::getStoreConfig('carriers/'.$useCarrier.'/title', $shipment->getOrder()->getStoreId())
                            .' - '.$carrierMethods[$useMethod]
                        );
                    }
                }

                // generate label
                $batch = Mage::getModel('udropship/label_batch')
                    ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                    ->processShipments(array($shipment), $data, array('mark_shipped'=>$isShipped));

                // if batch of 1 label is successfull
                if ($batch->getShipmentCnt()) {
                    $url = Mage::getUrl('udropship/vendor/reprintLabelBatch', array('batch_id'=>$batch->getId()));
                    Mage::register('udropship_download_url', $url);

                    if (($track = $batch->getLastTrack())) {
                        $session->addSuccess('Label was succesfully created');
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $this->__('%s printed label ID %s', $vendor->getVendorName(), $track->getNumber())
                        );
                        $shipment->save();
                        $highlight['tracking'] = true;
                    }
                if ($r->getParam('use_method_code')) {
                        $shipment->setUdropshipMethod($oldUdropshipMethod);
                        $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                        $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                    }
                } else {
                    if ($batch->getErrors()) {
                        foreach ($batch->getErrors() as $error=>$cnt) {
                            $session->addError($hlp->__($error, $cnt));
                        }
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
                    } else {
                        $session->addError('No items are available for shipment');
                        if ($r->getParam('use_method_code')) {
                            $shipment->setUdropshipMethod($oldUdropshipMethod);
                            $shipment->setUdropshipMethodDescription($oldUdropshipMethodDesc);
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method');
                            $shipment->getResource()->saveAttribute($shipment, 'udropship_method_description');
                        }
                    }
                }

            } elseif ($number && $courier_name) { // if tracking id was added manually
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($method[0])
                	->setCourierName($courier_name)
                    ->setTitle($title);
                

                $shipment->addTrack($track);

                Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
                );
                $shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            } 
			
				//condition for penalaty charge for 2% on out of stock case added on dated 09-11-2013
				if($status == $statusOutofstock)
					{
					$templateId = 'refund_in_outofstock_email_template';
					$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
					$_email = Mage::getModel('core/email_template');
	
					$penaltyAmount = ($baseTotalValue*0.02);
					
					$oldAdjustAmount = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection()->addFieldToFilter('shipment_id',$_shipmentId);
					
					foreach($oldAdjustAmount as $_oldAdjustAmount){ $amntAdjust = $_oldAdjustAmount['adjustment']; }
					
					$amntAdjust = $amntAdjust-$penaltyAmount;
					
					$read = Mage::getSingleton('core/resource')->getConnection('udropship_read');	
					$getClosingblncQuery = "SELECT `vendor_name`,`closing_balance` FROM `udropship_vendor` where `vendor_id` = '".$vendor->getId()."'";
					$getClosingblncResult = $read->query($getClosingblncQuery)->fetch();
					$closingBalance = $getClosingblncResult['closing_balance'];
					$vendorName = $getClosingblncResult['vendor_name'];
					$closingBalance = $closingBalance-$penaltyAmount; 
					
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
					
					$queryUpdateForAdjustment = "update shipmentpayout set adjustment='".$amntAdjust."' , `comment` = 'Adjustment Against Out Of Stock'  WHERE shipment_id = '".$_shipmentId."'";
					$write->query($queryUpdateForAdjustment);
					
					$queryUpdateForClosingbalance = "update `udropship_vendor` set `closing_balance`='".$closingBalance."'  WHERE `vendor_id`= '".$vendor->getId()."'";
					$write->query($queryUpdateForClosingbalance);
					$vars = array('penaltyprice'=>$penaltyAmount,
								  'shipmentid'=>$_shipmentId,
								  'vendorShopName'=>$vendorName
								);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->sendTransactional($templateId, $sender,$vendor->getEmail(), '', $vars, $storeId);
					$session->addSuccess($this->__('Shipment status has been changed to out of stock and charged penalty of Rs.'.$penaltyAmount));							
					}
			

            // if track was generated - for both label and manual tracking id
            /*
            if ($track) {
                // if poll tracking is enabled for the vendor
                if ($pollTracking && $vendor->getTrackApi()) {
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
                    $isShipped = false;
                } else { // otherwise process track
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_READY);
                    Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
                }
            */
            // if tracking id added manually and new status is not current status
            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {
            	$check = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
				// Craftsvilla Comment Added By Amit Pitre On 25-06-2012 for international shipping changing status without tracking number /////
            	// Extra condition for Out of stock product added By Dileswar on 13-10-2012
				
				//if($check[0]['number'] == ''){
				
				//Cash on delivery condition and cancelled = 6 condition added by dileswar to avoid error in cod panel ....once a while errorr comes to add track id on dated 28-11-2013 //
				
				if(($check[0]['number'] == '') && ($_address->getCountryId() == 'IN' && $status != '18' && $status != '6' && $testcodPayment->getMethodInstance()->getTitle() != 'Cash On Delivery')){
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            		if($r->getParam('tracking_id') == '' && $r->getParam('courier_name') == ''){
            			$session->addError($this->__('Please enter Tracking id and Courier name'));
            			$this->_forward('shipmentInfo');
            		}


            	} 
				
            	else{
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered)
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = $this->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                $triedToChangeComment = $this->__('%s tried to change the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                if ($status==$statusShipped || $status==$statusDelivered) {
                	$hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    $hlp->completeOrderIfShipped($shipment, true);
                    $hlp->completeUdpoIfShipped($shipment, true);
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                } elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $changedComment
                        );
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, $vendor);
                    } else {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $triedToChangeComment
                        );
                    } 
                } else { 
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }
                $shipment->getCommentsCollection()->save();
                $session->addSuccess($this->__('Shipment status has been changed'));
               
              }
			  // Condition for cod
			$checkTrack12 = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
			if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']==''))
				{
					
				$awbNumber = $hlp->fetchawbgenerate('Delhivery');
				//commented the below line by dileswar on dated 19-11-2013 for some time...
				//$awbOrdergenerate = $hlp->fetchawbcreateorder('Delhivery',$shipment); 
			    $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
                
				$track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($awbNumber)
                    ->setCarrierCode($method[0])
                	->setCourierName('Delhivery')
                    ->setTitle($title);
                

                $shipment->addTrack($track);
				//Below commented by Gayatri on dated 4-12-2013 as when status is accepted mail should not go to customer
            //  Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $awbNumber)
                );
                
				$shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            
				}
            }

            $comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($shipment->getAllItems() as $item) {
                    	if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('udropship')->sendVendorComment($shipment, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {
                                    
                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess($this->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess($this->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) {
                        // doesn't support voiding
                    }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $this->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    #$save = true;
                    $highlight['tracking'] = true;
                    $session->addSuccess($this->__('Track %s was deleted', $track->getNumber()));
                } else {
                    $session->addError($this->__('Track %s was not found', $track->getNumber()));
                }
            }
            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
		/* Craftsvilla Comment - By Amit Pitre On 12 Jun-2012 To set feedback reminder entry and shippment payout when shippment status changed to 'Shipped To Customer' */
		Mage::dispatchEvent(
                'craftsvilla_shipment_status_save_after',
                array('shipment'=>$shipment)
            );
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' )
		{
			if(!empty($checkTrack12[0]['number']))
			{
				$session->addError($this->__('Tracking Id Already Exists'));
			}
			$this->_forward('codordersinfo');
		
		}
		else
		{
			$this->_forward('shipmentInfo');
		}
    /*//shipment email to sellerrrr
	
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'udropship_customer_tracking_email_template';
			$sender = Array('name'  => 'Craftsvilla places',
			'email' => 'places@craftsvilla.com');
			$_vendorName = $vendor->getVendorName();
			$_vendorEmail = $vendor->getEmail();
			$_vendorShopName = $vendor->getShopName();
			$varsVendorEmail = Array('shipmentid' => $shipment_id_value,
						'vendorName' => $_vendorName,
						'vendorShopName' => $_vendorShopName,
						);
			$_email = Mage::getModel('core/email_template');
			$_email->sendTransactional($templateId, $sender, $_vendorEmail, $varsVendorEmail,$storeId);	*/
	/* For SMS to customer Added by Dileswar on dated 08-11-2012 */
	if(!$statusOutofstock){
	$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		if($_orderBillingCountry == 'IN')
			{
			$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$number.'Courier Name :'.$courier_name.' - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
			$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
			$parse_url = file($_customerSmsUrl);			
			}
		
	}
	
	}
	
	public function feedbackPostAction()
	{
		$Feedback = '';
		$id = $this->getRequest()->getParam('id');
		$_feedback = $this->getRequest()->getParam('feedback');
		$Feedback = Mage::getModel('feedback/vendor_feedback');
		$shipment = Mage::getModel('sales/order_shipment')->load($id);
		$_feedbackAt =  Mage::getModel('core/date')->date('Y-m-d H:i:s');
		$Feedback->setFeedbackAt($_feedbackAt)
					->setShipmentId($id)
					->setVendorId($shipment->getUdropshipVendor())
					->setCustId($shipment->getCustomerId())
					->setFeedback($_feedback)
					->setRating(2)
					->setFeedbackType(2)
					->save();
		$this->_forward('shipmentInfo');
	}
	
/* Below Function Added By dileswar on dated 12-10-2013  For action to send email to customer about vendor queries */	
	public function custnotePostAction()
	{
		$id = $this->getRequest()->getParam('id');
		$session = $this->_getSession();
		$_custnote = $this->getRequest()->getParam('custnote');
		if($_custnote == '')
			{
            $session->addError($this->__('You have not entered any note below. Please enter text in the note to customer'));
            $this->_redirect('udropship/vendor/');
			}
		else{
		$_custnote1 = '<table border="0" width="750"><tr><td style="font-size: 17px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">'.$_custnote.'</td></table>';
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment')->load($id);
		$storeId = Mage::app()->getStore()->getId();
		$path = Mage::getBaseDir('media') . DS . 'vendorreplyemail' . DS;
		
		//echo $_FILES["file"]["type"];exit;
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		 $extension = end(explode(".", $_FILES["file"]["name"]));
		 //if($_FILES["file"]["type"]=="image/jpeg")
		 if ((($_FILES["file"]["type"] == "image/gif")
		 || ($_FILES["file"]["type"] == "image/jpeg")
		 || ($_FILES["file"]["type"] == "image/jpg")
		 || ($_FILES["file"]["type"] == "image/pjpeg")
		 || ($_FILES["file"]["type"] == "image/x-png")
		 || ($_FILES["file"]["type"] == "image/png"))
			//&& ($_FILES["file"]["size"] < 50000)
			&& in_array($extension, $allowedExts))
		 {
			//echo 'I m entered';exit;
				 if ($_FILES["file"]["error"] > 0)
					{
						
					echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
					}
				 else
					{
				   echo "Upload: " . $_FILES["file"]["name"] . "<br>";
				   echo "Type: " . $_FILES["file"]["type"] . "<br>";
					//echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
					//echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";exit;
				
				if (file_exists($path . $_FILES["file"]["name"]))
				  {
				  echo $_FILES["file"]["name"] . " already exists. ";
				  }
				else
				  {
				  move_uploaded_file($_FILES["file"]["tmp_name"],$path . $_FILES["file"]["name"]);
				 // echo "Stored in: " . "upload/" . $_FILES["file"]["name"];
				  $tmp = $path . $_FILES["file"]["name"];
				   //echo $tmp;				  
				   }
				  }
				}
		//$dirpath = Mage::getBaseUrl('media') . DS . 'vendorreplyemail' . DS;
		$dirpath = Mage::getBaseUrl('media') . 'vendorreplyemail/';
		$_path = '<img src="'.$dirpath.$_FILES["file"]["name"].'">';
		$templateId = 'custnote_reminder_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$customer = Mage::getModel('customer/customer');	
		
		$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU/Vendorsku</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		$shipmentData = '';
		$shipmentData = $shipment->load($id);
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		$orderEmail = $customerData->getCustomerEmail();
		$incmntId = $shipmentData->getIncrementId();
		$currencysym = Mage::app()->getLocale()->currency($customerData->getBaseCurrencyCode())->getSymbol();
		$_items = $shipment->getAllItems();
		$customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('email',$orderEmail);
		foreach($customer as $_customer)
		{
		  $custid = $_customer['entity_id'];
		}
		//echo '<pre>';print_r($_items);exit;
		
		foreach ($_items as $_item)
				{
				//below line commented and added load function by dileswar on dated 29-01-2014
				//$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());exit;
				$product = Mage::getModel('catalog/product')->load($_item->getProductId());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				$vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incmntId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getSku()." / ".$product->getVendorsku()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";		
		$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'custNote' => $_custnote1,
					'imagecust' => $_path,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
				);
			//print_r($vars);exit;	
		$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				->setReplyTo($vendorEmail)
				->sendTransactional($templateId, $sender, $orderEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, $vendorEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, 'places@craftsvilla.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		$this->_redirect('udropship/vendor/');
		$session->addSuccess('Sent Email To Customer Sucessfully for your shipment :'.$shipmentData->getIncrementId());
		        $this->_redirect('udropship/vendor/prepaidorders');
		}
	}
	
   public function requestPickupAction()
	 	{
		$id = $this->getRequest()->getParam('id');
		$session = $this->_getSession();
		$selectDate = $this->getRequest()->getParam('select_date');
		$cnv1 =  strtotime($selectDate);
		$cnvdate = date('jS F Y',$cnv1);
		$pickupdateAramex = date('m/d/Y',$cnv1);
		$delhivery = Mage::getStoreConfig('courier/general/delhivery');
		
		if($selectDate == '')
			{
            $session->addError($this->__('You have not selected date. Please select date for pick up'));
            $this->_redirect('udropship/vendor/codorders');
			}
		else{
			$shipmentDet = Mage::getModel('sales/order_shipment')->load($id);
			$shipmentCount = count($shipmentDet);
			$totalQtyOrdered = $shipmentDet->getTotalQty();
			$totalQtyWeight = 0.5;
			$incmntId = $shipmentDet->getIncrementId();
			$orderid = $shipmentDet->getOrderId();
			//get the vendor address details
			$dropship = Mage::getModel('udropship/vendor')->load($shipmentDet->getUdropshipVendor());
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
			//get the customer details
		$order = Mage::getModel('sales/order')->load($shipmentDet->getOrderId());
		
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		//print_r($address);exit;
		$street = $address['street'];
		$city = $address['city'];
		$name = $address['firstname'].' '.$address['lastname'];
		$postcode = $address['postcode'];
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$track = "select `number` from `sales_flat_shipment_track` where `order_id` = '".$orderid."'";
		$trackQueryres = $read->query($track)->fetch();
		$awbnumber = $trackQueryres['number'];
			if($delhivery == '1')
			{
			$templateId = "pick_up_date_email_courier";
			$sender = Array('name'  => 'Craftsvilla',
						    'email' => 'places@craftsvilla.com');
			$_email = Mage::getModel('core/email_template');
			$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
			
    	$vars = array('shipmentId'=>$incmntId,
						  'vendorName'=>$vendorName,
						  'vendorCity'=>$vendorCity,
						  'vendorStreet'=>$vendorStreet,
						  'vendorDate' =>$cnvdate,
						  'vendorpostcode'=>$vendorPostcode,
						  'vendorTelephone'=>$vendorTelephone);
			
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				  ->setReplyTo($vendorEmail)
				  ->sendTransactional($templateId, $sender, 'vendordesk@delhivery.com', '', $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, $vendorEmail, '', $vars, $storeId);
		$_email->sendTransactional($templateId, $sender, 'places@craftsvilla.com', '', $vars, $storeId);
		
		$session->addSuccess('Pick up request has been succesfully sent to Logistics service provider for this order:'.$incmntId);
			 $storeId1 = Mage::app()->getStore()->getId();
		$templateId1 = "pick_up_date_email_seller";
			$sender1 = Array('name'  => 'Craftsvilla',
						    'email' => 'places@craftsvilla.com');
			$_email1 = Mage::getModel('core/email_template');
			$translate  = Mage::getSingleton('core/translate');
						$translate->setTranslateInline(false);
			$vars1 = array('shipmentId'=>$incmntId, 'vendorName'=>$vendorName, 'awbnumber'=>$awbnumber);
			$_email1->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId1))
				   ->sendTransactional($templateId1, $sender1, $vendorEmail, $recname, $vars1, $storeId1);
				   $translate->setTranslateInline(true);
           echo "Email has been sent successfully";
		$this->_redirect('udropship/vendor/codorders');
			}
		//Condition for aramex	
	else{
		$account=Mage::getStoreConfig('courier/general/account_number');		
		//$country_code=Mage::getStoreConfig('courier/general/account_country_code');
		$country_code= 'IN';
		$post = '';
		$country = Mage::getModel('directory/country')->loadByCode($country_code);		
		$response=array();
		$clientInfo = Mage::helper('courier')->getClientInfo();		
		
		try {
				if (empty($selectDate)) {
					$response['type']='error';
					$response['error']=$this->__('Invalid form data.');
					print json_encode($response);		
					die();
				}		
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
								'Reference1'			=>html_entity_decode($incmntId),
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
										'ShipmentWeight'=>array('Value'=>0.5,'Unit'=>'KG'),
										
									),
								),
								'Status'				=>'Ready'

							)
	);
	
	$baseUrl = Mage::helper('courier')->getWsdlPath();
	$soapClient = new SoapClient($baseUrl . 'shipping-services-api-wsdl.wsdl');
	try{
	$results = $soapClient->CreatePickup($params);		
	//echo '<pre>';print_r($results);exit;
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
			
			$notify = false;
        	$visible = false;
			$comment="Pickup reference number ( <strong>".$results->ProcessedPickup->ID."</strong> ) created by Vendor ".$vendorName.".";
			//$_order->addStatusHistoryComment($comment, $_order->getStatus())
			//->setIsVisibleOnFront($visible);
			//->setIsCustomerNotified($notify);
			//$_order->save();	
			//$shipmentId=null;
			//$shipment = Mage::getModel('sales/order_shipment')->getCollection()
			//->addFieldToFilter("order_id",$_order->getId())->load();
			//if($shipment->count()>0){
				//foreach($shipment as $_shipment){
					//$shipmentId=$_shipment->getId();
					//break;
				//}
			//}
			if($id!=null){
					
					$shipmentDet->addComment(
                	$comment,
                	false,
                	false
            	);
				$shipmentDet->save();
			}
			$response['type']='success';
			$amount="<p class='amount'>Pickup reference number ( ".$results->ProcessedPickup->ID.").</p>";		
			$response['html']=$amount;
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
		print json_encode($response);		
		//die();
		$session->addSuccess('Pick up request has been succesfully generated for this order:'.$incmntId.'. Your Pickup reference number is '.$results->ProcessedPickup->ID);
	 	$this->_redirect('udropship/vendor/codorders');
			}
		}
		
		}
    /**
    * Download one packing slip
    *
    */
    public function pdfAction()
    {
        try {
        	$id = $this->getRequest()->getParam('shipment_id');
            if (!$id) {
                Mage::throwException('Invalid shipment ID is supplied');
            }

            $shipments = Mage::getResourceModel('sales/order_shipment_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', $id)
                ->load();
            if (!$shipments->getSize()) {
                Mage::throwException('No shipments found with supplied IDs');
            }

            return $this->_preparePackingSlips($shipments);

        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
        }
        $this->_redirect('udropship/vendor/');
    }
    
    public function printAction()
    {
		$this->loadLayout(false);
		$block = $this->getLayout()->createBlock('udropship/vendor_shipment_print');
		$this->getResponse()->setBody($block->toHtml());
    }

    /**
    * Download multiple packing slips
    *
    */
    public function packingSlipsAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();
            if (!$shipments->getSize()) {
                Mage::throwException('No shipments found for these criteria');
            }

            return $this->_preparePackingSlips($shipments);

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError($this->__($e->getMessage()));
        	}
        }
    	if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    /**
    * Generate and print labels batch
    *
    */
    public function labelBatchAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();
            if (!$shipments->getSize()) {
                Mage::throwException('No shipments found for these criteria');
            }

            Mage::getModel('udropship/label_batch')
                ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                ->processShipments($shipments, array(), array('mark_shipped'=>true))
                ->prepareLabelsDownloadResponse();

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError($this->__($e->getMessage()));
        	}
        }
        if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    public function existingLabelBatchAction()
    {
    	$result = array();
        try {
            $shipments = $this->getVendorShipmentCollection();


            if (!$shipments->getSize()) {
                Mage::throwException('No shipments found for these criteria');
            }

            Mage::getModel('udropship/label_batch')
                ->setVendor(Mage::getSingleton('udropship/session')->getVendor())
                ->renderShipments($shipments)
                ->prepareLabelsDownloadResponse();

        } catch (Exception $e) {
        	if ($this->getRequest()->getParam('use_json_response')) {
        		$result = array(
        			'error'=>true,
        			'message'=>$e->getMessage()
        		);
        	} else {
            	$this->_getSession()->addError($this->__($e->getMessage()));
        	}
        }
    	if ($this->getRequest()->getParam('use_json_response')) {
        	$this->getResponse()->setBody(
        		Mage::helper('core')->jsonEncode($result)
        	);
        } else {
        	$this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
        }
    }

    public function updateShipmentsStatusAction()
    {
        $hlp = Mage::helper('udropship');
        try {
            $shipments = $this->getVendorShipmentCollection();
            $status = $this->getRequest()->getParam('update_status');

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;

            if (!$shipments->getSize()) {
                Mage::throwException($this->__('No shipments found for these criteria'));
            }
            if (is_null($status) || $status==='') {
                Mage::throwException($this->__('No status selected'));
            }

        	$shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            foreach ($shipments as $shipment) {
                if (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses))) {
                    if ($status==$statusShipped || $status==$statusDelivered) {
                        $tracks = $shipment->getAllTracks();
                        if (count($tracks)) {
                            foreach ($tracks as $track) {
                                $hlp->processTrackStatus($track, true, true);
                            }
                        } else {
                            $hlp->completeShipment($shipment, true, $status==$statusDelivered);
                            $hlp->completeOrderIfShipped($shipment, true);
                            $hlp->completeUdpoIfShipped($shipment, true);
                        }
                    }
                    $shipment->setUdropshipStatus($status)->save();
                }
            }
            $this->_getSession()->addSuccess($this->__('Shipment status has been updated for the selected shipments'));
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__($e->getMessage()));
        }
        $this->_redirect('udropship/vendor/', array('_current'=>true, '_query'=>array('submit_action'=>'')));
    }

    public function reprintLabelBatchAction()
    {
        $hlp = Mage::helper('udropship');

        if (($trackId = $this->getRequest()->getParam('track_id'))) {
            $track = Mage::getModel('sales/order_shipment_track')->load($trackId);
            if (!$track->getId()) {
                return;
            }
            $labelModel = $hlp->getLabelTypeInstance($track->getLabelFormat());
            $labelModel->printTrack($track);
        }

        if (($batchId = $this->getRequest()->getParam('batch_id'))) {
            $batch = Mage::getModel('udropship/label_batch')->load($batchId);
            if (!$batch->getId()) {
                return;
            }
            $labelModel = Mage::helper('udropship')->getLabelTypeInstance($batch->getLabelType());
            $labelModel->printBatch($batch);
        }
    }

    protected function _preparePackingSlips($shipments)
    {
        $vendorId = $this->_getSession()->getId();
        $vendor = Mage::helper('udropship')->getVendor($vendorId);

        foreach ($shipments as $shipment) {
            if ($shipment->getUdropshipVendor()!=$vendorId) {
                Mage::throwException('You are not authorized to print this shipment');
            }
        }

        if (Mage::getStoreConfig('udropship/vendor/ready_on_packingslip')) {
            foreach ($shipments as $shipment) {
                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s printed packing slip', $vendor->getVendorName())
                );
                if ($shipment->getUdropshipStatus()==Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PENDING) {
                    $shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_READY);
                }
                $shipment->save();
            }
        }

        foreach ($shipments as $shipment) {
            $order = $shipment->getOrder();
            $order->setData('__orig_shipping_amount', $order->getShippingAmount());
            $order->setData('__orig_base_shipping_amount', $order->getBaseShippingAmount());
            $order->setShippingAmount($shipment->getShippingAmount());
            $order->setBaseShippingAmount($shipment->getBaseShippingAmount());
        }

        $theme = explode('/', Mage::getStoreConfig('udropship/admin/interface_theme', 0));
        Mage::getDesign()->setArea('adminhtml')
            ->setPackageName(!empty($theme[0]) ? $theme[0] : 'default')
            ->setTheme(!empty($theme[1]) ? $theme[1] : 'default');

        $pdf = Mage::helper('udropship')->getVendorShipmentsPdf($shipments);
        $filename = 'packingslip'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf';

        foreach ($shipments as $shipment) {
            $order = $shipment->getOrder();
            $order->setShippingAmount($order->getData('__orig_shipping_amount'));
            $order->setBaseShippingAmount($order->getData('__orig_base_shipping_amount'));
        }

        Mage::helper('udropship')->sendDownload($filename, $pdf->render(), 'application/x-pdf');
    }

    public function wysiwygAction()
    {
        $elementId = $this->getRequest()->getParam('element_id', md5(microtime()));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $storeMediaUrl = Mage::app()->getStore($storeId)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);

        $content = $this->getLayout()->createBlock('udropship/vendor_wysiwyg_form_content', '', array(
            'editor_element_id' => $elementId,
            'store_id'          => $storeId,
            'store_media_url'   => $storeMediaUrl,
        ));

        $this->getResponse()->setBody($content->toHtml());
    }

    public function getVendorShipmentCollection()
    {
        return Mage::helper('udropship')->getVendorShipmentCollection();
    }
    
    public function manageproductAction()
    {  
	   //$this->_renderPage(null, 'manageproduct'); 
    } 
    
    public function addproductAction()
    {   
       Mage::getSingleton("udropship/session")->setProductImagePath('');
        Mage::getSingleton("udropship/session")->setProductSecImagePath('');
        Mage::getSingleton("udropship/session")->setProductThiImagePath('');
        Mage::getSingleton("udropship/session")->setProductFourImagePath('');
        Mage::getSingleton("udropship/session")->setProductFifImagePath('');
		$this->_renderPage(null, 'addproduct'); 
    } 
    
    public function editproductAction()
    {
    	$this->_renderPage(null, 'editproduct');
    }
    
    public function shopstatsAction()
    {
    	$this->_renderPage(null, 'stats');
    }
    
    public function statementAction()
    {
    	$this->_renderPage(null, 'statement');
    }
    
    public function getChildCategoriesAction()
    { 
        $childrenCat = Mage::getModel('catalog/category')->getCategories($this->getRequest()->getParam('id'));
	if($childrenCat){
        foreach($childrenCat as $cat) { 
                $catDetails = Mage::getModel('catalog/category')->load($cat->getId());
                $count = $catDetails->getProductCount();
                if($count == 0)
                  continue;
		$array .= "<option value='".$cat->getId()."#".$cat->getName()."'>".$cat->getName();
		/*if($category->hasChildren()) {
		 $children = Mage::getModel('catalog/category')->getCategories($category->getId());
		$array .=  get_categories($children);
		}*/
		$array .= '</option>';
            }
            echo $array;
	}
    }
    
    public function getSecondChildCategoriesAction()
    { 
        $childrenCat = Mage::getModel('catalog/category')->getCategories($this->getRequest()->getParam('id'));
	if($childrenCat){
        foreach($childrenCat as $cat) { 
		$count = $cat->getProductCount();
		$array .= "<option value='".$cat->getId()."#".$cat->getName()."'>".$cat->getName();
		$array .= '</option>';
		 }
            echo $array;
	}
    }
    
    public function getUserDefineAttributesAction()
    { 
        $attributes  = Mage::getModel('catalog/product_attribute_api')->itemsUserDefined($this->getRequest()->getParam('attributesetid'));
        $productData = Mage::getModel('catalog/product')->load($this->getRequest()->getParam('productid'));
	/*echo '<pre>';
	print_r($attributes);exit;*/
	foreach($attributes as $_attribute){
		if($_attribute['code'] == 'cost' || $_attribute['code'] == 'sap_sync' || $_attribute['code'] == 'udropship_vendor' || $_attribute['code'] == 'product_payment_methods'):
			continue;
		endif;
		if($_attribute['type'] == 'text' || $_attribute['type'] == 'textarea'){
                    if($_attribute['required'] == 1):
                        $array .= "<li class='lablelist'><label>".$_attribute['label']."</label> <div class='floatl'><input type='text' name='".$_attribute['code']."' class='required-entry' value='".$productData->getData($_attribute['code'])."' /></div></li>";
                    else:
                        $array .= "<li class='lablelist'><label>".$_attribute['label']."</label> <input type='text' name='".$_attribute['code']."' value='".$productData->getData($_attribute['code'])."' /></li>";
                    endif;
                }else if($_attribute['type'] == 'select' || $_attribute['type'] == 'multiselect'){
                    $attributesSelect = Mage::getModel('catalog/product_attribute_api')->options($_attribute['attribute_id'], Mage::app()->getStore()->getId());    
                    $array .= "<li class='lablelist'><label>".$_attribute['label']."</label>";
                    if($_attribute['type'] == 'multiselect'){
                        if($_attribute['required'] == 1):
                            $array .= "<div class='floatl'><select multiple='multiple' name='".$_attribute['code']."[]' class='validate-select'>";
                        else:
                            $array .= "<div class='floatl'><select multiple='multiple' name='".$_attribute['code']."[]'>";
                        endif;
                    }else{
                        if($_attribute['required'] == 1):
                            $array .= "<div class='floatl'><select name='".$_attribute['code']."' class='validate-select'>";
                        else:
                            $array .= "<div class='floatl'><select name='".$_attribute['code']."'>";
                        endif;
                    }
                    if($_attribute['type'] == 'multiselect'){
                        $val = explode(",",$productData->getData($_attribute['code']));
                    }
                                    foreach($attributesSelect as $_attributeselect){
                                        if($_attribute['type'] == 'multiselect'){
                                            if(in_array($_attributeselect['value'],$val)){
                                                $array .= "<option value='".$_attributeselect['value']."' selected='selected'>".$_attributeselect['label']."</option>";    
                                            }else{
                                                $array .= "<option value='".$_attributeselect['value']."'>".$_attributeselect['label']."</option>";    
                                            }
                                        }else{
                                            if($productData->getData($_attribute['code']) == $_attributeselect['value']){
                                                $array .= "<option value='".$_attributeselect['value']."' selected='selected'>".$_attributeselect['label']."</option>";
                                            }else{
                                                $array .= "<option value='".$_attributeselect['value']."'>".$_attributeselect['label']."</option>";
                                            }
                                        }
                                    }
                    $array .= "</select></div>
				</li>";
                }
	}
	echo $array;
    }
    
    public function createproductAction()
    {  
       $msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
        $_session = Mage::getSingleton('udropship/session');
		$veCatalog = $_session->getVendorId();
		$allowedPrdFlag = Mage::helper('udropship')->isVendorAllowedProductAdd($veCatalog);

		$getCatalogPrivilleges = Mage::getModel('vendorneftcode/vendorneftcode')->load($veCatalog,'vendor_id'); 
	     $catalog_privileges = $getCatalogPrivilleges->getCatalogPrivileges();		

	if($catalog_privileges == 0 && $catalog_privileges != '')
	{
	$session->addError($this->__('You are not allowed to add products. Please contact places@craftsvilla.com'));
            $this->_redirect('*/vendor/addproduct');
	
}
elseif($catalog_privileges == 1 || $catalog_privileges == '')
{


		if(!$allowedPrdFlag)
		{
			$session->addError($this->__('You cannnot add  any more products. Please contact places@craftsvilla.com'));
            $this->_redirect('*/vendor/addproduct');

		}
		else{
        $postData=Mage::app()->getRequest()->getPost();
	//echo '<pre>';print_r($postData);
		
        $firstName = $_session->getVendor()->getVendorName();
	    $shopdesc = $_session->getVendor()->getShopDescription();
		$sku = "M".strtoupper(substr($firstName,0,4)).rand(1111111111,9999999999)."0";
		$attributeSetName = Mage::getModel('eav/entity_attribute_set')->load(($postData['attributeset']))->getAttributeSetName();  
        $product = Mage::getModel('catalog/product');
        
		$product->setSku($sku);
		
        if($postData['productname'] == ''):
            $session->addError($this->__('Product name is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
		//$productnameext = ' - Online Shopping for '.$attributeSetName.' by '.$firstName;
        //$productnamenew = $postData['productname'].$productnameext;
		$productnamenew = $postData['productname'];
		$product->setName($productnamenew);
        //unset($productnameext);
		unset($productnamenew);
		
        if($postData['description'] == ''):
            $session->addError($this->__('Description is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
		//$productdescext = '<br><br>'.$shopdesc.'<br><br>SKU: '.$sku.'<br><br>Online Shopping for '.$attributeSetName.' by '.$firstName.' on Craftsvilla.com';
		//$productdescriptionnew = $postData['description'].$productdescext;
		$productdescriptionnew = $postData['description'];
		
			if(strstr($productdescriptionnew,"http")|| strstr($productdescriptionnew,"https") || strstr($productdescriptionnew,"www") || preg_match("/\bscript\b/i", $productdescriptionnew)):
		 $session->addError($this->__('You cannot use http, https, www, script in the description.'));
            $this->_redirect('*/vendor/addproduct');
            return;
       endif;	
      
        $product->setDescription($productdescriptionnew);
		//unset($productdescext);
		unset($productdescriptionnew);

        if($postData['shortdesc'] == ''):
            $session->addError($this->__('Short description is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
		
		//$productshortdescext = 'Online Shopping for ';
		$productshortdescriptionnew = $productshortdescext.$postData['shortdesc'];
        $product->setShortDescription($productshortdescriptionnew);
		//unset($productshortdescext);
		unset($productshortdescriptionnew);
		
        if($postData['price'] == '' && ctype_digit($postData['price']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
	if($postData['price'] <= 10) 
          {
            $session->addError($this->__('Please enter a number greater than 10 in Price field.'));
            $this->_redirect('*/vendor/addproduct');
            return;
          }
        $product->setPrice($postData['price']);
	if($postData['special_price'] != '' && $postData['special_price'] <= 10) 
          {
            $session->addError($this->__('Please enter a number greater than 10 in Price After Discount field.'));
            $this->_redirect('*/vendor/addproduct');
            return;
          }
        if($postData['special_price'] != ''):
            $product->setSpecialPrice($postData['special_price']);
        else:
		$product->setSpecialPrice(null);
	 	endif;
        if($postData['special_from_date'] != ''):
            $product->setSpecialFromDate($postData['special_from_date']);
		else:
		$product->setSpecialFromDate(null);
        endif;
        if($postData['special_to_date'] != ''):
            $product->setSpecialToDate($postData['special_to_date']);
        
		else:
				 $product->setSpecialToDate(null);
			endif;
        if($postData['shippingcost'] == '' && ctype_digit($postData['shippingcost']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
	
	if(($postData['price'] != '' && $postData['price'] <= $postData['shippingcost']) || ($postData['special_price'] != '' && $postData['special_price'] <= $postData['shippingcost'])):
           $session->addError($this->__('Please enter Domestic Shipping Cost less than Price,Price After Discount field.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;

        $product->setShippingcost($postData['shippingcost']);
        $product->setShippingTablerate($postData['shippingcost']);
        $product->setAddedFrom(1);
     
        $product->setTypeId('simple');
        $product->setUdropshipVendor($postData['vendorid']);
        
		if($postData['attributeset'] == ''):
            $session->addError($this->__('This Attribute set is not available'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        
        $product->setAttributeSetId($postData['attributeset']); // need to look this up
        if(!empty($postData['intershippingcost'])) {
        $product->setIntershippingcost($postData['intershippingcost']);
        $product->setInterShippingTablerate($postData['intershippingcost']);
        
		} else { 
		
		if($postData['parentcategories']){
            $data = explode('#', $postData['parentcategories']);
            $catIds = $data[0];
            if(strtolower($data[1]) == 'jewellery'):
                $international_shipping = 500;
                $international_shipping_morethan_one = 500;
            elseif(strtolower($data[1]) == 'sarees'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'bags'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'home decor'):
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            elseif(strtolower($data[1]) == 'clothing'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'accessories'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'home furnishing'):
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            elseif(strtolower($data[1]) == 'bath & beauty'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'food & health'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'books'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            elseif(strtolower($data[1]) == 'footwear'):
                $international_shipping = 1000;
                $international_shipping_morethan_one = 1000;
            else:
                $international_shipping = 1500;
                $international_shipping_morethan_one = 1500;
            endif;
            
            $product->setIntershippingcost($international_shipping);
            $product->setInterShippingTablerate($international_shipping_morethan_one);
        }
        }

         if($postData['parentcategories']){
            $data = explode('#', $postData['parentcategories']);
            //print_r($postData['parentcategories']);
          //echo "<br>";
          //exit;
           if(($data[0] == 1056) && $postData['price'] <= 100)
              {
                $session->addError($this->__('For sarees category price not allowed less than 100.'));
                $this->_redirect('*/vendor/addproduct');
                return;
              }
            }



		if($postData['parentchildrencategories']){
            $data = explode('#', $postData['parentchildrencategories']);

            //print_r($data[0]);
          //echo "<br>";
          //exit;
           if(($data[0] == 37 || $data[0] == 74 || $data[0] == 224 || $data[0] == 388 || $data[0] == 731 || $data[0] == 732) && $postData['price'] <= 100)
           {
              
           $session->addError($this->__('For clothing category price not allowed less than 100.'));
           $this->_redirect('*/vendor/addproduct');
            return;
             }

            $catIds .= ",".$data[0];
        }
        if($postData['childrencat']){
            $data = explode('#', $postData['childrencat']);
            $catIds .= ",".$data[0];
        }
			
		$productmetaext = 'Online Shopping for ';
		$productmetatitlenew = $productmetaext.substr($postData['productname'],0,35).' | '.$attributeSetName.' | Unique Indian Products by '.$firstName.' - '.$sku;
		$product->setMetaTitle($productmetatitlenew);
		unset($productmetaext);
		unset($productmetatitlenew);
		
        //$catIds .= ",".'2';
		
        $product->setCategoryIds($catIds); // need to look these up
        $product->setWeight(0);
        $product->setTaxClassId(2); // taxable goods
        $product->setVisibility(4); // catalog, search
        $product->setStatus(1); // For Enabled
		$ship_handling_time = $postData['ship_handling_times'];//added by dileswar on date 20-06-2014
if($ship_handling_time)
		$product->setShipHandlingTime($ship_handling_time);

        //$product->setStatus($postData['status']); // enabled
     // changes done only comment a below line on dated 29-06-2013   
     // print_r($product->getData());exit;
        $attributes = Mage::getModel('catalog/product_attribute_api')->itemsUserDefined($postData['attributeset']);
		//$attributes ='';
	foreach($attributes as $_attribute){
            if($_attribute['code'] == 'cost' || $_attribute['code'] == 'sap_sync' || $_attribute['code'] == 'udropship_vendor'):
                continue;
            endif;
            
                if($_attribute['type'] == 'text' || $_attribute['type'] == 'textarea'){ 
                    if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                        $session->addError($this->__($_attribute['code'].' is required.'));
                        $this->_redirect('*/vendor/addproduct');
                        return;   
                    endif;    
                    $code = ucfirst($_attribute['code']); 
                    $realVal = $_attribute['code'];
                    $product->{'set'.$code}($postData[$realVal]);
                }else if($_attribute['type'] == 'select'){
                    if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                        $session->addError($this->__($_attribute['code'].' is required.'));
                        $this->_redirect('*/vendor/addproduct');
                        return;   
                    endif;    
                    $code = ucfirst($_attribute['code']);
                    $realVal = $_attribute['code'];
                    $product->{'set'.$code}($postData[$realVal]);
                }else if($_attribute['type'] == 'multiselect'){ 
                    if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                        $session->addError($this->__($_attribute['code'].' is required.'));
                        $this->_redirect('*/vendor/addproduct');
                        return;   
                    endif;    
                    $realVal = $_attribute['code'];
                    $data[$_attribute['code']] = $postData[$realVal]; 
                    $product->addData($data); 
                }
      }
	     //$product->setManufacturer($firstName);
        //$data['test'] = '17861'; 
        //$product->addData($data);
        if($postData['inventory'] == '' && ctype_digit($postData['inventory']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setStockData(array(
                        'is_in_stock' => 1,
                        'qty' => $postData['inventory'],
                        'manage_stock' => 0,
                    ));
       $category = Mage::getModel('catalog/category')->load($catIds);
		$categoryName = $category->getName();
			
		 $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product' . DS;
	   $vendorpath = Mage::getBaseDir('media') . DS . 'vendorimages' .DS;
        if(Mage::getSingleton("udropship/session")->getProductPreviewImagePath() == ''){
            $session->addError($this->__('Please provide the product image'));
            $this->_redirect('*/vendor/addproduct');
            return;
        }else{
			$image = $path.Mage::getSingleton("udropship/session")->getProductPreviewImagePath();
			/*Added By Gayatri to save images with extension in directory*/
			$ext = pathinfo($image, PATHINFO_EXTENSION);
			$newimagename = $vendorpath.'CV-'.$sku.'-'.$categoryName.'-'.$firstName.'-Craftsvilla_1.'.$ext;
			/*$str = $newimagename;
			$sp_chr = array("[,]","[']","[=]","[;]","*",":","<",">","/","\"","(",")");
			$str = preg_replace($sp_chr,"-",$str);
			$newimagename = $str;*/
			file_put_contents($newimagename, file_get_contents($image));
			/**************************************************************/
            //echo $image;exit;
            if(file_exists($image)){

                $product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
                $product->addImageToMediaGallery ($newimagename, array ('image','small_image','thumbnail'), false, false);
            }
        }    
            
        if(Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath() != '' && file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath())){
            $image2 = $path.Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath();
			/*Added By Gayatri to save images with extension in directory*/
			$ext2 = pathinfo($image2, PATHINFO_EXTENSION);
			$newimagename2 = $vendorpath.'CV-'.$sku.'-'.$categoryName.'-'.$firstName.'-Craftsvilla_2.'.$ext2;
			/*$str2 = $newimagename2;
			$sp_chr2 = array("[,]","[']","[=]","[;]","*",":","<",">","/","\"","(",")");
			$str2 = preg_replace($sp_chr2,"-",$str2);
			$newimagename2 = $str2;*/
			file_put_contents($newimagename2, file_get_contents($image2));
			/********************************************************************/
            $product->addImageToMediaGallery ($newimagename2, array (''), false, false);
        }
        
        if(Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath() != '' && file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath())){
            $image3 = $path.Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath();
			/*Added By Gayatri to save images with extension in directory*/
			$ext3 = pathinfo($image3, PATHINFO_EXTENSION);
			$newimagename3 = $vendorpath.'CV-'.$sku.'-'.$categoryName.'-'.$firstName.'-Craftsvilla_3.'.$ext3;
			/*$str3 = $newimagename3;
			$sp_chr3 = array("[,]","[']","[=]","[;]","*",":","<",">","/","\"","(",")");
			$str3 = preg_replace($sp_chr3,"-",$str3);
			$newimagename3 = $str3;*/
			file_put_contents($newimagename3, file_get_contents($image3));
			/*******************************************************************/
            $product->addImageToMediaGallery ($newimagename3, array (''), false, false);
        }
        
        if(Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath() != '' && file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath())){
            $image4 = $path.Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath();
			/*Added By Gayatri to save images with extension in directory*/
			$ext4 = pathinfo($image4, PATHINFO_EXTENSION);
			$newimagename4 = $vendorpath.'CV-'.$sku.'-'.$categoryName.'-'.$firstName.'-Craftsvilla_4.'.$ext4;
			/*$str4 = $newimagename4;
			$sp_chr4 = array("[,]","[']","[=]","[;]","*",":","<",">","/","\"","(",")");
			$str4 = preg_replace($sp_chr4,"-",$str4);
			$newimagename4 = $str4;*/
			file_put_contents($newimagename4, file_get_contents($image4));
			/*******************************************************************/
            $product->addImageToMediaGallery ($newimagename4, array (''), false, false);
        }
        if(Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath() != '' && file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath())){
            $image5 = $path.Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath();
			/*Added By Gayatri to save images with extension in directory*/
			$ext5 = pathinfo($image5, PATHINFO_EXTENSION);
			$newimagename5 = $vendorpath.'CV-'.$sku.'-'.$categoryName.'-'.$firstName.'-Craftsvilla_5.'.$ext5;
			/*$str5 = $newimagename5;
			$sp_chr5 = array("[,]","[']","[=]","[;]","*",":","<",">","/","\"","(",")");
			$str5 = preg_replace($sp_chr5,"-",$str5);
			$newimagename5 = $str5;*/
			file_put_contents($newimagename5, file_get_contents($image5));
			/*******************************************************************/
            $product->addImageToMediaGallery ($newimagename5, array (''), false, false);
        }
     	
        
        // assign product to the default website
        $product->setStoreIDs(array(0));
        $vendorData = Mage::getModel('udropship/vendor')->load($postData['vendorid']);
       $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
				// Below lines are comment for block stagingin site on dated 19-12-2012
		
		/*if($vendorData->getAutoUpload() == 0){
            $product->setWebsiteIds(array(Mage::getStoreConfig('udropship/microsite/staging_website')));
        }else{
            $product->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
        }*/
     //$vendor=Mage::getSingleton("udropship/session")->getVendorSku();
	 
        try{
			$product->save();		
            Mage::getSingleton("udropship/session")->setProductPreviewImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductName('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewSecImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewThiImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewFourImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewFifImagePath('');
            Mage::getSingleton("udropship/session")->setProductImagePath('');
            Mage::getSingleton("udropship/session")->setProductSecImagePath('');
            Mage::getSingleton("udropship/session")->setProductThiImagePath('');
            Mage::getSingleton("udropship/session")->setProductFourImagePath('');
            Mage::getSingleton("udropship/session")->setProductFifImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductName('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductPrice('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductInventory('');
            $session->addSuccess($this->__('Product Added Successfully.'));
            $this->_redirect('*/vendor/addproduct');
	} catch(Exception $e){
	    echo $e->getMessage();
	    //handle your error
	}
        

       } 
} 
else
{
$session->addError($this->__('You are not allowed to add products. Please contact places@craftsvilla.com'));
$this->_redirect('*/vendor/addproduct');
} 

}
	
	
    public function updateproductAction()
    {  
	
        $msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
        
        $postData=Mage::app()->getRequest()->getPost();
       //echo '<pre>';
		//print_r($postData)."<br/>"; exit;
        $product = Mage::getModel('catalog/product')->load($postData['productid']);
        $productid = $product->getId();
$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
         $getCatalogPrivilleges = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorId,'vendor_id'); 
	 $catalog_privileges = $getCatalogPrivilleges->getCatalogPrivileges();		

		if($catalog_privileges == 0 && $catalog_privileges != '')
		{
		$session->addError($this->__('You are not allowed to edit products. Please contact places@craftsvilla.com'));
			     $this->_redirect('*/vendor/addproduct');
		}
     elseif($catalog_privileges == 1 || $catalog_privileges == '')
	{

        if($postData['productname'] == ''):
            $session->addError($this->__('Product name is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setName($postData['productname']);
        if($postData['description'] == ''):
            $session->addError($this->__('Description is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setDescription($postData['description']);
        if($postData['shortdesc'] == ''):
            $session->addError($this->__('Short description is compulsory.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setShortDescription($postData['shortdesc']);
        if($postData['price'] == '' && ctype_digit($postData['price']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
if($postData['price'] <= 10) 
          {
            $session->addError($this->__('Please enter a number greater than 10 in Price field.'));
            $this->_redirect('*/vendor/addproduct');
            return;
          }
        $product->setPrice($postData['price']);
	if($postData['special_price'] != '' && $postData['special_price'] <= 10) 
          {
            $session->addError($this->__('Please enter a number greater than 10 in Price After Discount field.'));
            $this->_redirect('*/vendor/addproduct');
            return;
          }	   
		if($postData['special_price'] != ''):
            $product->setSpecialPrice($postData['special_price']);
		else:
			$product->setSpecialPrice(null);
        endif;
        if($postData['special_from_date'] != ''):
            $product->setSpecialFromDate($postData['special_from_date']);
		else:
			$product->setSpecialFromDate(null);	
        endif;
        if($postData['special_to_date'] != ''):
            $product->setSpecialToDate($postData['special_to_date']);
		else:
			$product->setSpecialToDate(null);	
        endif;
        if($postData['shippingcost'] == '' && ctype_digit($postData['shippingcost']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        if(ctype_digit($postData['intershippingcost']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setIntershippingcost($postData['intershippingcost']);
        
// added by dileswar on dated 20-06-2014
	if($postData['ship_handling_times'])
		$product->setShipHandlingTime($postData['ship_handling_times']);
        $product->setShippingcost($postData['shippingcost']);
        if($postData['attributeset'] != ''){
                $attributes = Mage::getModel('catalog/product_attribute_api')->itemsUserDefined($postData['attributeset']);
                foreach($attributes as $_attribute){
                    if($_attribute['code'] == 'cost' || $_attribute['code'] == 'sap_sync' || $_attribute['code'] == 'udropship_vendor'):
                        continue;
                    endif;

                        if($_attribute['type'] == 'text' || $_attribute['type'] == 'textarea'){ 
                            if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                                $session->addError($this->__($_attribute['code'].' is required.'));
                                $this->_redirect('*/vendor/addproduct');
                                return;   
                            endif;    
                            $code = ucfirst($_attribute['code']); 
                            $realVal = $_attribute['code'];
                            $product->{'set'.$code}($postData[$realVal]);
                        }else if($_attribute['type'] == 'select'){
                            if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                                $session->addError($this->__($_attribute['code'].' is required.'));
                                $this->_redirect('*/vendor/addproduct');
                                return;   
                            endif;    
                            $code = ucfirst($_attribute['code']);
                            $realVal = $_attribute['code'];
                            $product->{'set'.$code}($postData[$realVal]);
                        }else if($_attribute['type'] == 'multiselect'){ 
                            if($_attribute['required'] == 1 && $postData[$_attribute['code']] == ''):
                                $session->addError($this->__($_attribute['code'].' is required.'));
                                $this->_redirect('*/vendor/addproduct');
                                return;   
                            endif;    
                            $realVal = $_attribute['code'];
                            $data[$_attribute['code']] = $postData[$realVal]; 
                            $product->addData($data); 
                        }
              }
        }
        if($postData['inventory'] == '' && ctype_digit($postData['inventory']) != 1):
            $session->addError($this->__('Please use numbers only.'));
            $this->_redirect('*/vendor/addproduct');
            return;
        endif;
        $product->setStockData(array(
                        'is_in_stock' => 1,
                        'qty' => $postData['inventory'],
                        'manage_stock' => 0,
                    ));


		//print_r ($postData['rm']);exit;

		if(count($postData['rm']) > 0):
			
			$mediaGalleryAttribute = Mage::getModel('catalog/resource_eav_attribute')->loadByCode(4, 'media_gallery');
           
			foreach($postData['rm'] as $rm){
                $mediaGalleryAttribute->getBackend()->removeImage($product, $rm);
            }
        endif;
		
        $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
		//$vendorpath = Mage::getBaseDir('media') . DS . 'vendorimages' .DS;
        if(Mage::getSingleton("udropship/session")->getProductImagePath() != Mage::getSingleton("udropship/session")->getProductPreviewImagePath()){
            if(file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewImagePath())){
                $image = $path.Mage::getSingleton("udropship/session")->getProductPreviewImagePath();
				//$newimagename = $vendorpath.$sku.'-'.preg_replace('/[,]/', '-', $catIds).'-Craftsvilla'.'.jpg';
			//file_put_contents($newimagename, file_get_contents($image));
               $product->setMediaGallery (array('images'=>array (), 'values'=>array ()));
                $product->addImageToMediaGallery ($image, array ('image','small_image','thumbnail'), false, false);
            }
        }else if(Mage::getSingleton("udropship/session")->getProductImagePath() == '' && Mage::getSingleton("udropship/session")->getProductPreviewImagePath() == ''){
            $session->addError($this->__('Please provide the product image'));
            $this->_redirect('*/vendor/addproduct');
            return;
        }    
            
        if(Mage::getSingleton("udropship/session")->getProductSecImagePath() != Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath()){
            if(file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath())){
                $image2 = $path.Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath();
				//$newimagename2 = $vendorpath.$sku.'-'.preg_replace('/[,]/', '-', $catIds).'-Craftsvilla'.'.jpg';
			//file_put_contents($newimagename2, file_get_contents($image2));
                $product->addImageToMediaGallery ($image2, array (''), false, false);
            }
        }
        
        if(Mage::getSingleton("udropship/session")->getProductThiImagePath() != Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath()){
            if(file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath())){
                $image3 = $path.Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath();
				//$newimagename3 = $vendorpath.$sku.'-'.preg_replace('/[,]/', '-', $catIds).'-Craftsvilla'.'.jpg';
			//file_put_contents($newimagename3, file_get_contents($image3));
                $product->addImageToMediaGallery ($image3, array (''), false, false);
            }
        }
        
        if(Mage::getSingleton("udropship/session")->getProductFourImagePath() != Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath()){
            if(file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath())){
                $image4 = $path.Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath();
				//$newimagename4 = $vendorpath.$sku.'-'.preg_replace('/[,]/', '-', $catIds).'-Craftsvilla'.'.jpg';
			//file_put_contents($newimagename4, file_get_contents($image4));
                $product->addImageToMediaGallery ($image4, array (''), false, false);
            }
        }
        
        if(Mage::getSingleton("udropship/session")->getProductFifImagePath() != Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath()){
            if(file_exists($path.Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath())){
                $image5 = $path.Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath();
				//$newimagename5 = $vendorpath.$sku.'-'.preg_replace('/[,]/', '-', $catIds).'-Craftsvilla'.'.jpg';
			//file_put_contents($newimagename5, file_get_contents($image5));
                $product->addImageToMediaGallery ($image5, array (''), false, false);
            }
        }
        
        try{
	    $product->save();
		$this->clearproductcache($productid);
            Mage::getSingleton("udropship/session")->setProductPreviewImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductName('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewSecImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewThiImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewFourImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewFifImagePath('');
            Mage::getSingleton("udropship/session")->setProductImagePath('');
            Mage::getSingleton("udropship/session")->setProductSecImagePath('');
            Mage::getSingleton("udropship/session")->setProductThiImagePath('');
            Mage::getSingleton("udropship/session")->setProductFourImagePath('');
            Mage::getSingleton("udropship/session")->setProductFifImagePath('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductName('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductDesc('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductPrice('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost('');
            Mage::getSingleton("udropship/session")->setProductPreviewProductInventory('');
            $session->addSuccess($this->__('Product Updated Successfully.'));
            $this->_redirect('*/vendor/product');
	} catch(Exception $e){
	    echo $e->getMessage();
	    //handle your error
	}
}
        else
	{
	$session->addError($this->__('You are not allowed to edit products. Please contact places@craftsvilla.com'));
        $this->_redirect('*/vendor/addproduct');
	}
        
    }
	
	public function clearproductcache($productId)
	{
$session = Mage::getSingleton('udropship/session');
      	//$memcache_host = '175.41.147.59';
 		//$memcache_host1 = '175.41.141.42';
 		//$memcache_host2 = '46.137.208.109';
 		$memcache_host = 'memcache5.beuas5.cfg.apse1.cache.amazonaws.com';
  		//$memcache_host = '10.144.160.18';
          //$memcache_host1 = '10.144.64.88';
  		//$memcache_host2 = '10.144.73.34';	
 		//$memcache_host = 'localhost';
  		$id=strtoupper('mage_fullproductcache_'.$productId.'_currency_INR_0');
  		$idm=strtoupper('mage_fullproductcache_'.$productId.'_currency_INR_1');
 $id1=strtoupper('mage_fullproductcache_'.$productId.'_currency_USD');
  	   $memcache_obj = memcache_connect($memcache_host, 11211);
        if(!$memcache_obj)
		
	   {
		   $session->addError($this->__('Cannot connect to server: '.$memcache_host));
	   }
	   else
	   {
		 memcache_delete($memcache_obj, $id);
		 memcache_delete($memcache_obj, $id1);
		 memcache_delete($memcache_obj, $idm);
		 $memcache_obj->close();
	   }
	   /*$memcache_obj1 = memcache_connect($memcache_host1, 11211);
	   if(!$memcache_obj1)
	   {
		   $session->addError($this->__('Cannot connect to server: '.$memcache_host1));
	   }
	   else
	   {
		   memcache_delete($memcache_obj1, $id);
		memcache_delete($memcache_obj1, $id1);
		$memcache_obj1->close();
	   }
	   $memcache_obj2 = memcache_connect($memcache_host2, 11211);
	   if(!$memcache_obj2)
	   {
		   $session->addError($this->__('Cannot connect to server: '.$memcache_host2));
	   }
	   else
	   {
						
		memcache_delete($memcache_obj2, $id);
		memcache_delete($memcache_obj2, $id1);
		$memcache_obj2->close();
	   }*/
		
	}

	
    public function getAttributeIdByNameAction(){
        $name = $this->getRequest()->getParam('attributesetname');
        $attributeSetId = Mage::getModel('eav/entity_attribute_set')
                            ->load($name, 'attribute_set_name')
                            ->getAttributeSetId();
        if($attributeSetId):
            echo $attributeSetId;
        else:
            echo 0;
        endif;
    }
    
    public function showproductpreviewAction(){
		
        if($this->getRequest()->getParam('name') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductName($this->getRequest()->getParam('name'));
			//Mage::getSingleton("udropship/session")->addError(Mage::getSingleton("udropship/session")->getProductPreviewProductName());
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductName('');
		}
		
        if($this->getRequest()->getParam('description') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductDesc($this->getRequest()->getParam('description'));
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductDesc('');
		}
		
        if($this->getRequest()->getParam('shortdesc') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc($this->getRequest()->getParam('shortdesc'));
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc('');
		}
		
        if($this->getRequest()->getParam('price') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductPrice($this->getRequest()->getParam('price'));
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductPrice('');
		}
		
        if($this->getRequest()->getParam('shippingcost') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost($this->getRequest()->getParam('shippingcost'));
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost('');
		}
		
        if($this->getRequest()->getParam('inventory') != ''){
            Mage::getSingleton("udropship/session")->setProductPreviewProductInventory($this->getRequest()->getParam('inventory'));
        }
		else 
		{
			Mage::getSingleton("udropship/session")->setProductPreviewProductInventory('');
		}
		
        if($_FILES){
			
            if($_FILES['productpic']['name'] != ''){
                if($_FILES['productpic']['error'] == 1){
                    echo "2";
               		 exit;
                }    
                $uploader = new Varien_File_Uploader('productpic');
                $exts = explode(".", $_FILES['productpic']['name']) ;
                $n = count($exts)-1;
                $exts = strtolower($exts[$n]);                        
                if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;
				}
                
               $file_size = $_FILES['productpic']['size'];
               if($file_size > 2097152) {
               		echo "2";
               		exit;
                }

                $uploader->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
                $uploader->save($path, $_FILES['productpic']['name']);
				$image = $uploader->getUploadedFileName();
				
				//Mage::getSingleton("udropship/session")->addError($_FILES['productpic']['name']);
				Mage::getSingleton("udropship/session")->setProductPreviewImagePath($image);
                echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tmp/catalog/product".Mage::getSingleton("udropship/session")->getProductPreviewImagePath();
                exit;
            }
            
            if($_FILES['productpic2']['name'] != ''){
                if($_FILES['productpic2']['error'] == 1){
                    echo "2";
                    exit;
                }
                $uploader2 = new Varien_File_Uploader('productpic2');
                $exts = explode(".", $_FILES['productpic2']['name']) ;
                $n = count($exts)-1;
                $exts = strtolower($exts[$n]);                        
                if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;

                }

                $file_size = $_FILES['productpic2']['size'];
                if($file_size > 2097152) {
                        echo "2";
                        exit;
                }

                $uploader2->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                $uploader2->setAllowRenameFiles(true);
                $uploader2->setFilesDispersion(true);
                $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
                $uploader2->save($path, $_FILES['productpic2']['name']);
                $image = $uploader2->getUploadedFileName();
                Mage::getSingleton("udropship/session")->setProductPreviewSecImagePath($image);
                echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tmp/catalog/product".Mage::getSingleton("udropship/session")->getProductPreviewSecImagePath();
                exit;
            }
            
            if($_FILES['productpic3']['name'] != ''){
                if($_FILES['productpic3']['error'] == 1){
                    echo "2";
                    exit;
                }
                $uploader3 = new Varien_File_Uploader('productpic3');
                $exts = explode(".", $_FILES['productpic3']['name']) ;
                $n = count($exts)-1;
                $exts = strtolower($exts[$n]);                        
                if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;

                }

                $file_size = $_FILES['productpic3']['size'];
                if($file_size > 2097152) {
                        echo "2";
                        exit;
                }

                $uploader3->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                $uploader3->setAllowRenameFiles(true);
                $uploader3->setFilesDispersion(true);
                $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
                $uploader3->save($path, $_FILES['productpic3']['name']);
                $image = $uploader3->getUploadedFileName();
                Mage::getSingleton("udropship/session")->setProductPreviewThiImagePath($image);
                echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tmp/catalog/product".Mage::getSingleton("udropship/session")->getProductPreviewThiImagePath();
                exit;
            }
            
            if($_FILES['productpic4']['name'] != ''){
                if($_FILES['productpic4']['error'] == 1){
                    echo "2";
                    exit;
                }
                $uploader4 = new Varien_File_Uploader('productpic4');
                $exts = explode(".", $_FILES['productpic4']['name']) ;
                $n = count($exts)-1;
                $exts = strtolower($exts[$n]);                        
                if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;

                }

                $file_size = $_FILES['productpic4']['size'];
                if($file_size > 2097152) {
                        echo "2";
                        exit;
                }

                $uploader4->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                $uploader4->setAllowRenameFiles(true);
                $uploader4->setFilesDispersion(true);
                $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
                $uploader4->save($path, $_FILES['productpic4']['name']);
                $image = $uploader4->getUploadedFileName();
                Mage::getSingleton("udropship/session")->setProductPreviewFourImagePath($image);
                echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tmp/catalog/product".Mage::getSingleton("udropship/session")->getProductPreviewFourImagePath();
                exit;
            }
            
            if($_FILES['productpic5']['name'] != ''){
                if($_FILES['productpic5']['error'] == 1){
                    echo "2";
                    exit;
                }
                $uploader5 = new Varien_File_Uploader('productpic5');
                $exts = explode(".", $_FILES['productpic5']['name']) ;
                $n = count($exts)-1;
                $exts = strtolower($exts[$n]);                        
                if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
                {

                } else {
                            echo "1";
                            exit;

                }

                $file_size = $_FILES['productpic5']['size'];
                if($file_size > 2097152) {
                        echo "2";
                        exit;
                }

                $uploader5->setAllowedExtensions(array('jpg','jpeg','png','gif'));
                $uploader5->setAllowRenameFiles(true);
                $uploader5->setFilesDispersion(true);
                $path = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'catalog' . DS . 'product';
                $uploader5->save($path, $_FILES['productpic5']['name']);
				$image = $uploader5->getUploadedFileName();
                Mage::getSingleton("udropship/session")->setProductPreviewFifImagePath($image);
                echo Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)."tmp/catalog/product".Mage::getSingleton("udropship/session")->getProductPreviewFifImagePath();
                exit;
            }
            
            
        }
    }
    
    public function showbyskuAction(){
      	$pSku = $this->getRequest()->getParam('productsku');
	   $read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$productquery = "select `entity_id` from `catalog_product_entity` WHERE `sku`= '".$pSku."'";
		$resultVendor = $read->query($productquery)->fetch();
		$entity = $resultVendor['entity_id'];
		
	   //$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$this->getRequest()->getParam('productsku'));
	   $product = Mage::getModel('catalog/product')->load($entity);
       $str = $product->getName()."#".$product->getDescription()."#".$product->getShortDescription()."#".preg_replace('/[,]/', '',(number_format($product->getPrice(),0)))."#".number_format($product->getShippingcost(),0);
       Mage::getSingleton("udropship/session")->setProductPreviewProductName($product->getName());
       Mage::getSingleton("udropship/session")->setProductPreviewProductDesc($product->getDescription());
       Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc($product->getShortDescription());
       Mage::getSingleton("udropship/session")->setProductPreviewProductPrice(number_format($product->getPrice(),0));
       Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost(number_format($product->getShippingcost(),0));
       echo $str;
    }
    
    public function showbylastAction(){
		
       $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('udropship_vendor', $this->getRequest()->getParam('vendorid'))
                   ->addAttributeToSort('created_at', 'desc')->setPageSize('1');
	
		$fromPart = $collection->getSelect()->getPart(Zend_Db_Select::FROM);
		$fromPart['e']['tableName'] ='catalog_product_entity';
		$collection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
		$prdouctView = $collection->getData();
		
		if($prdouctView[0]['sku'] != ''){
			
		   //$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$collection[0]['sku']);
		   $product = Mage::getModel('catalog/product')->load($prdouctView[0]['entity_id']);

		   $str = $product->getName()."#".$product->getDescription()."#".$product->getShortDescription()."#".preg_replace('/[,]/', '',(number_format($product->getPrice(),0)))."#".number_format($product->getShippingcost(),0);
           Mage::getSingleton("udropship/session")->setProductPreviewProductName($product->getName());
           Mage::getSingleton("udropship/session")->setProductPreviewProductDesc($product->getDescription());
           Mage::getSingleton("udropship/session")->setProductPreviewProductShortDesc($product->getShortDescription());
           Mage::getSingleton("udropship/session")->setProductPreviewProductPrice(number_format($product->getPrice(),0));
           Mage::getSingleton("udropship/session")->setProductPreviewProductShippingCost(number_format($product->getShippingcost(),0));
           echo $str;
       }else{
           echo 0;
       }
    }
    
    public function productpreviewAction(){
        $this->_renderPage(null, 'productpreview'); 
    }
/*	Added By Gayatri on dated 03-07-2013*/
    /*public function blogpreviewAction(){
        $this->_renderPage(null, 'blogpreview'); 
    }*/
	/*	Added By Gayatri on dated 03-07-2013*/
	public function blogdashboardAction(){
		
	$this->_renderPage(null, 'blogdashboard');
	}	
	/*	Added By Gayatri on dated 23-08-2013*/
	public function vacationmodeAction(){
		
	$this->_renderPage(null, 'vacationmode');
	}	
	
	//Added by Gayatri to add bulk upload action on dated 06-09-2013
	public function bulkuploadcsvAction(){
		
	$this->_renderPage(null, 'bulkuploadcsv');
	}	
	
	public function productdownloadreqAction(){
		
	$this->_renderPage(null, 'productdownloadreq');
	}	
	
	//Added by Gayatri to add bulk inventory update action on dated 06-09-2013
	public function bulkinventoryupdateAction(){
		
	$this->_renderPage(null, 'bulkinventoryupdate');
	}	
	
	public function noticeboardAction(){
		
	$this->_renderPage(null, 'noticeboard');
	}	
	
	public function disputeraisedAction(){
		
	$this->_renderPage(null, 'disputeraised');
	}	
    public function vendorinboxAction()
    {
        $this->_renderPage(null, 'vendorinbox');
    }
    
    public function vendorinboxreadAction()
    {
        $this->_renderPage(null, 'vendorinboxread');
    }
    
    public function refundpolicyAction(){
		
	$this->_renderPage(null, 'refundpolicy');
	}	
    public function vendorreplyAction()
    {
        $storeId = Mage::app()->getStore()->getId();
        $translate  = Mage::getSingleton('core/translate');
		$translate->setTranslateInline(false);
		$vars = array();
        
        if(Mage::getSingleton('core/session')->getAttachImageVendor()!=''){
           $image="<img src='".Mage::getSingleton('core/session')->getAttachImageVendor()."' alt='' width='154' border='0' style='float:left; border:2px solid #7599AB; margin:0 20px 20px;' />";
        }
        else{
            $image='';
        }
    /*Some columns Like url image ,product name added By dileswar On dated 22-07-2013  for showing in venodr page as well as on email*/    
	
        $values=$this->getRequest()->getParams();
        $content=$values['reply_text'];
        $recepientEmail=$values['recepmail'];
        $recepientName=$values['recepname'];
        $messageId=$values['msgid'];
        $customerId=$values['custid'];
        $vendorId=$values['vendorid'];
        $subject = str_replace('%','',$values['subject']);
        $productid = $values['productid'];
		$_productImagephoto=$values['imagephoto'];
		$_productImagename=$values['productname'];
        $_productUrl = $values['producturl'];
		//Commented By dileswar on dated 06-12-2012
		
       	//$cc='messages@craftsvilla.com';
        $ccName='kribhasanvi';
        $sendEmail=$values['sendemail'];
        $sendName=$values['sendname'];
        //$sendccName='messages@craftsvilla.com';
        
        $templateId='sellerbuyer_comm_template';
        $sender = Array('name'  => 'Craftsvilla',
		'email' => 'customercare@craftsvilla.com');
       // $senderCc = Array('name'  => $sendName,
		//'email' => $sendccName);
        
        
        
        $url=Mage::getBaseUrl().'craftsvillacustomer/index/customerinboxread/msgid/'.$messageId.'/sub/'.$subject.'/custid/'.$customerId.'/vendid/'.$vendorId;
        $infoText='<strong> Seller has responded to your query! </strong> Please respond to this email by going to your inbox in your customer account after you log in. DO NOT RESPOND to the sender if this message requests that you complete the transaction outside of Craftsvilla. This type of communication is against Craftsvilla policy, is not covered by the Craftsvilla Buyer Protection Program and can result in termination of your account with us.';
        $vars['content']=$content;
        $vars['image']=$image;
        $vars['replyurl']=$url;
        $vars['infotext']=$infoText;
		$vars['imagephoto'] = $_productImagephoto;
		$vars['productname'] = $_productImagename;
        $vars['prod_url'] = $_productUrl;
        
        date_default_timezone_set('Asia/Kolkata');
        $emailCommunication=Mage::getModel('craftsvillacustomer/emailcommunication');
        $emailCommunication->setVendorId($vendorId)
                           ->setCustomerId($customerId)
                           ->setMessageId($messageId)
                           ->setRecepientEmail($recepientEmail)
                           ->setRecepientName($recepientName)
                           ->setImage(Mage::getSingleton('core/session')->getAttachImageVendor())
                           ->setSubject($subject)
                           ->setProductid($productid)
						   ->setContent($content)
                           ->setType(1)
                           ->setCreatedAt(now())
                           ->save();
        
        $mailTemplate=Mage::getModel('core/email_template');
        /* Send mail To vendor*/
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$sender,$recepientEmail,$recepientName,$vars,$storeId);
        /* Send mail To Customer*/
        $mailTemplate->setTemplateSubject($subject)->setReplyTo($recepientEmail)->sendTransactional($templateId,$sender,$values['sendemail'],$values['sendname'],$vars,$storeId);
        /* Send mail To Saanvi for cc*/
        
        $_vendor=Mage::helper('udropship')->getVendor($vendorId);
        $_vendorName = $_vendor['vendor_name'];
        $_customerName=Mage::getModel('customer/customer')->load($values['custid'])->getFirstname();
        $vars['custtovendor']= 'Vendor Name - '.$_vendorName. ' Vendor Id - ' .$vendorId. ' => Customer Name - ' .$_customerName. ' Customer Id - ' .$values['custid'] .' ';
        $mailTemplate->setTemplateSubject($subject)->sendTransactional($templateId,$senderCc,$cc,$ccName,$vars,$storeId);
        
        $translate->setTranslateInline(true);
        //$redirectUrl='marketplace/vendor/vendorinboxread/msgid/'.$messageId.'/sub/'.$subject.'/custid/'.$values['custid'].'/vendid/'.$vendorId;
        echo 'message sent!';
        //$this->_redirect($redirectUrl);
    }
    
    public function previewimageAction()
    {
        if($_FILES['imgfile']['error'] == 1){
             echo "filetype";
             exit;
        }  
        $file_size = $_FILES['imgfile']['size'];
        if($file_size > 2097152) {
            echo "image size!";
            exit;
        }
        
        $exts = explode(".", $_FILES['imgfile']['name']) ;
        $n = count($exts)-1;
        $exts = strtolower($exts[$n]);                        
        if($exts == 'jpg' || $exts =='jpeg' || $exts =='png' || $exts =='gif')
        {

        } else {
                    echo "filetype";
                    exit;

        }
        $uploader = new Varien_File_Uploader('imgfile');
        $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(false);
        $uploader->setFilesDispersion(false);
        $media_path  = Mage::getBaseDir('media').'/vendorreplyemail'. DS;                
        $uploader->save($media_path, $_FILES['imgfile']['name']);
        $data['imgfile'] = Mage::getBaseUrl('media').'vendorreplyemail/'. $_FILES['imgfile']['name'];
        
        echo $data['imgfile'];
        Mage::getSingleton('core/session')->setAttachImageVendor($data['imgfile']);
        
        exit;
    }
    
    public function createstatementAction()
    {
        $data = $this->getRequest()->getParams();
         
		$vendorId   = $data['vendor_id'];
        $session    = Mage::getSingleton('udropship/session');
        $fromDate   = date('Y-m-d', strtotime($data['special_from_date']));
        $toDate     = date('Y-m-d', strtotime($data['special_to_date']));
        $checkDate  = date('Y-m-d', strtotime('05/16/12'));
        if($data['special_from_date'] == ''):
            $session->addError($this->__('Please select From Date for Shipment sale report.'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($data['special_to_date'] == ''):
            $session->addError($this->__('Please select To Date for Shipment sale report.'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($fromDate < $checkDate):
            $session->addError($this->__('From date should be greater than 15th May 2012'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($toDate < $checkDate):
            $session->addError($this->__('To date should be greater than 15th May 2012'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;

        $shipmentData = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentData->getSelect()->join('shipmentpayout','main_table.increment_id=shipmentpayout.shipment_id');
           $shipmentData->addAttributeToFilter('udropship_vendor', $vendorId)
                        ->addAttributeToFilter('udropship_status',array('in' => array('1','7','17')))
                        ->addAttributeToFilter('created_at',array('gteq' => $fromDate))
                        ->addAttributeToFilter('created_at',array('lteq' => $toDate));
						
						//echo $shipmentData->getSelect()->__toString();exit;
          //echo "<pre>"; print_r($shipmentData->getData());
        $file = Mage::getModel('udropship/vendorstatement')->exportshipmentspdf($shipmentData);
        $this->_prepareDownloadResponse('Statement'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $file->render(), 'application/pdf');
        
    }
    
    public function exportcsvAction()
    {
        $data       = $this->getRequest()->getParams();
        $vendorId   = $data['vendor_id'];
        $session    = Mage::getSingleton('udropship/session');
        $fromDate   = date('Y-m-d', strtotime($data['from_date']));
        $toDate     = date('Y-m-d', strtotime($data['to_date']));
        echo 'cdate'.$checkDate  = date('Y-m-d', strtotime('05/16/12'));
        if($data['from_date'] == ''):
            $session->addError($this->__('Please select From Date for Itemised sale report.'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($data['to_date'] == ''):
            $session->addError($this->__('Please select To Date for Itemised sale report.'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($fromDate < $checkDate):
            $session->addError($this->__('From date should be greater than 15th May 2012'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        if($toDate < $checkDate):
            $session->addError($this->__('To date should be greater than 15th May 2012'));
            $this->_redirect('*/vendor/statement');
            return;
        endif;
        
        $commission     = $data['commission'];
        $manageShipping = $data['manage_shipping'];
        $shipmentData   = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentData->getSelect()->join('sales_flat_shipment_item','main_table.entity_id=sales_flat_shipment_item.parent_id');
        $shipmentData->getSelect()->join('shipmentpayout','main_table.increment_id=shipmentpayout.shipment_id');
                        $shipmentData->addAttributeToFilter('udropship_vendor', $vendorId)
                        ->addAttributeToFilter('udropship_status',array('eq' => 1))
                        ->addAttributeToFilter('created_at',array('gteq' => $fromDate))
                        ->addAttributeToFilter('created_at',array('lteq' => $toDate));
                        //echo $shipmentData->getSelect()->__toString();exit;
        
        $fileContent = Mage::getModel('udropship/vendorstatement')->exportCsvShipment($shipmentData,$commission,$manageShipping);                
        $this->_prepareDownloadResponse($fileContent, file_get_contents(Mage::getBaseDir('export').'/'.$fileContent));
    }
	
	public function wholesaleAction(){
		$this->_renderPage(null, 'wholesale');
	}
	// Added By dileswar on dated 25-04-2013 for Module MKt Vendors
	public function mktvendorsAction(){
		$updateStatus = $this->getRequest()->getParam('cancel');
		$managemktid = $this->getRequest()->getParam('managemkt');
		$managemktvendorwrite = Mage::getSingleton('core/resource')->getConnection('managemkt_write');
	
		if ($updateStatus == "Cancel")
			{
			$resultState = 6;
			$vendormktStatuspkgQuery = "update `managemkt` set `status` = '".$resultState."' WHERE `managemkt_id` = '".$managemktid."'";
			$resultStatus =$managemktvendorwrite->query($vendormktStatuspkgQuery);
			}

		$this->_renderPage(null, 'mktvendors');
	}
	public function mktvendorsurlAction(){
		
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');

		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
	
		$activity = $this->getRequest()->getParam('activity');
		$startDate = $this->getRequest()->getParam('start_date');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('end_date');
		$ed = date('Y-m-d',strtotime($endDate));
		$productUrl = $this->getRequest()->getParam('product_url');
		if ($activity == 'Facebook Post'){$act = 1;}if ($activity == 'Emailer'){$act = 2;}if ($activity == 'Homepage Banner'){$act = 3;} if ($activity == 'Homepage Products'){$act = 4;}if ($activity == 'Featured Seller'){$act = 5;}if ($activity == 'Guaranteed Sale'){$act = 6;}
		$modelMng = Mage::getModel('managemkt/managemkt');
		   $modelMng->setActivity($act)
					->setVendorname($vendorId)
					->setStatus(1)
					->setStartDate($sd)
					->setEndDate($ed)
					->setCommentUrl($productUrl)
					->save();
					
					
	$session->addSuccess($hlp->__('Your Request Has been Sent to Our Marketing Team'));
	$this->_redirect('*/vendor/mktvendors');		
	//$this->_renderPage(null, 'mktvendors');
	}
	
	/*Added by Gayatri for download request from vendor*/
	public function prdownlaodreqAction(){
		
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');

		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
	
		$activity = $this->getRequest()->getParam('activity');
		if ($activity == 'Full Product Download')
		{echo $act = 1;
		$modelMng = Mage::getModel('productdownloadreq/productdownloadreq');
		   $modelMng->setActivity($act)
		            ->setVendorname($vendorId)
					->setStatus(1)
					->save();
					
		
		}
		if($activity == 'Inventory Download')
		{echo $act = 2;
		$modelMng = Mage::getModel('productdownloadreq/productdownloadreq');
		   $modelMng->setActivity($act)
		            ->setVendorname($vendorId)
					->setStatus(1)
					->save();	
					
				}
					
				//$modelMng = Mage::getModel('productdownloadreq/productdownloadreq');
								
					
	$session->addSuccess($hlp->__('Your Request Has been Submitted'));
	$this->_redirect('*/vendor/productdownloadreq');		
	
	}
	
	public function claimproductAction(){
		
		$this->_renderPage(null, 'claimproduct');
	}
	public function shipmentoutofstockAction()
    	{
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    	}
	
	
	public function outofstockPostAction()
		{
		$msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();	
		$hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        $id = $r->getParam('id');
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
		//get the shipment id for sms Added By Dileswar as dated 08-11-2012
		$_shipmentorderId = $shipment->getOrderId();
		$_shipmentId = $shipment->getIncrementId();
		$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();//get Cuurent vendor
		$vendor = $shipment->getUdropshipVendor();//[real vendor of product]getVendor($shipment->getUdropshipVendor());
		$vendorread = Mage::getSingleton('core/resource')->getConnection('core_read');
		$vendorQuery = "select `udropship_vendor` from `sales_flat_shipment` WHERE `order_id` = '".$_shipmentorderId."' AND `udropship_vendor` = '".$vendorId."'"  ;
		$resultVendor = $vendorread->query($vendorQuery)->fetchAll();
		
 //for checking vendor has shipment or not of last 90 days  added By dileswar on dated 23-10-2013
  $vendoroldshipmentQuery = "select count(sfs.`udropship_vendor`) as count from `sales_flat_shipment` as sfs,`udropship_vendor` as uv where uv.`vendor_id` = sfs.`udropship_vendor` and sfs.`udropship_vendor` = '".$vendorId."'"  ;		
  $resultvendoroldshipment = $vendorread->query($vendoroldshipmentQuery)->fetch();
	if($resultvendoroldshipment['count'])
	{
		$vendordetails = Mage::getSingleton('udropship/source')->setPath('vendors')->toOptionHash();
		

		if($resultVendor == NULL)
			{
			
			$outofstockwrite = Mage::getSingleton('core/resource')->getConnection('core_write');
			$outofstockQuery = "update `sales_flat_shipment` set `udropship_status` = '11',`udropship_vendor` = '".$vendorId."' WHERE `increment_id` = '".$_shipmentId."'";
			$resultoutofstock = $outofstockwrite->query($outofstockQuery);
			$vendorUpdateQuery = "update `sales_flat_order_item` set `udropship_vendor` = '".$vendorId."' WHERE `order_id` = '".$_shipmentorderId."' AND `udropship_vendor` = '".$vendor."'"  ;
			$resultvendorupdate = $outofstockwrite->query($vendorUpdateQuery);
			if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PROCESSING) {
				
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PROCESSING);
					Mage::helper('udropship')->addShipmentComment($shipment,
							  ($vendordetails[$vendorId].' has claimed From '.$vendordetails[$vendor]));
						
							}
			$shipment->setUdropshipVendor($vendorId)			
			          ->save();
			$vendormodel = Mage::getModel('udropship/vendor')->load($vendorId);
		    $vendorcurrentEmail = $vendormodel->getEmail();
			$primarycategory = $vendormodel->getPrimaryCategory();
			$_categories = Mage::getModel('catalog/category')->load($primarycategory);
			$catname = $_categories->getName();
			$activeshipment = Mage::getModel('activeshipment/activeshipment')->getCollection()
									->addFieldToFilter('shipment_id', $_shipmentId);
			foreach($activeshipment as $_shippmentExpCollection){
				 $activeid = $_shippmentExpCollection->getActiveshipmentId();
				}
			if($activeshipment->count() == 0)
				{
					
					$shippmentload = Mage::getModel('activeshipment/activeshipment')->setActiveshipmentId($activeid)
											     									->setShipmentId($_shipmentId)
												     							    ->setPrimaryCategory($catname)
																				    ->setVendorClaimedfrom($vendordetails[$vendor])
																				    ->setVendorClaimedto($vendordetails[$vendorId])
																				    ->setClaimedDate(now())
																				    ->save();
				}
			else
				{
					
				$shippmentload = Mage::getModel('activeshipment/activeshipment')->load($activeid)
				                            ->setPrimaryCategory($catname)
				                            ->setVendorClaimedfrom($vendordetails[$vendor])
											->setVendorClaimedto($vendordetails[$vendorId])
											->setClaimedDate(now())
											->save();
					   
									  
				}
			//Email to customercare  
			$storeId = Mage::app()->getStore()->getId();
			$templateId = 'udropship_customercare_email_template';
			$mailSubject = 'For the Shipment No.'.$_shipmentId.'-'.$vendordetails[$vendorId].'  has claimed From '.$vendordetails[$vendor].'';
			$sender = Array('name' => 'Craftsvilla Places',
							'email' => 'places@craftsvilla.com');
			//$translate  = Mage::getSingleton('core/translate');
			//$translate->setTranslateInline(false);
			$_email = Mage::getModel('core/email_template');
			$vars = Array('shipmentid' =>$_shipmentId,
						  
						 );
							//echo '<pre>';print_r($vars);exit;
	//commented By dileswar on dated 10-10-2014 to block cc emails to customercare		
			$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId));
				//	->setTemplateSubject($mailSubject)
					//->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', $vars, $storeId);
			$_email->sendTransactional($templateId, $sender, $vendorcurrentEmail, $vars, $storeId);		
			//$translate->setTranslateInline(true);
			$session->addSuccess('This Shipment '.$_shipmentId.' Has Been Claimed by You Successfully.Please Process it ASAP');
			$this->_redirect('*/vendor/claimproduct');		
			}
		else
			{
			$session->addError('Sorry, This Shipment Cannot be Claimed by You!');
			$this->_redirect('*/vendor/claimproduct');		
			}
		}
	else
		{
		$session->addError('Sorry, This Shipment Cannot be Claimed by You!');
		$this->_redirect('*/vendor/claimproduct');		
		}
		
	}
	

//***********Below function has added By Dileswar On dated 03-07-2013****************//
	public function generatecouponAction(){
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');
		$updateStatus = $this->getRequest()->getParam('inactive');
		$ruleId = $this->getRequest()->getParam('generatecoupon');
		$salesRulewrite = Mage::getSingleton('core/resource')->getConnection('core_write');
	
		if ($updateStatus == "DeActivate")
			{
			$iaActivate = 0;
			$saleruleactiveQuery = "update `salesrule` set `is_active` = '".$iaActivate."' WHERE `rule_id` = '".$ruleId."'";
			$resultActivateStatus =$salesRulewrite->query($saleruleactiveQuery);
			$session->addSuccess($hlp->__('Your Rule Has Succesfully Deactivated '));
			}
	$this->_renderPage(null, 'generatecoupon');
	}
	
/*Action Of first row*/	
	public function generatecouponblockAction(){
			function generateUniqueId($length = null)
			{
				$rndId = crypt(uniqid(rand(),1));
				$rndId = strip_tags(stripslashes($rndId));
				$rndId = str_replace(array(".", "$"),"",$rndId);
				$rndId = strrev(str_replace("/","",$rndId));
					if (!is_null($rndId)){
					return strtoupper(substr($rndId, 0, $length));
					}
				
				return strtoupper($rndId);
			}
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');	
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
		$_vendorName = Mage::getModel('udropship/vendor')->load($vendorId);
		$vendorName = $_vendorName->getVendorName();
		$couponCode='CVSE'.generateUniqueId(4).$vendorId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('percentage');
		$startDate = $this->getRequest()->getParam('from_date');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date');
		$ed = date('Y-m-d',strtotime($endDate));
		 if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
		$vId = $vendorId;
		$rule = Mage::getModel('salesrule/rule');
    	$customer_groups = array(0, 1, 2, 3);
    $rule->setName($discount.'% Off -'.$vendorName.' Tll '.$ed)
      ->setDescription('Flat:-'.$discount.'% Off Over All Products')
      ->setFromDate($sd)
	  ->setToDate($ed)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCustomer(100)
      ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
      ->setIsActive(1)
      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('by_percent')
      ->setDiscountAmount($discount)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('0')
      ->setIsRss(0)
      ->setVendorid($vId)
	  ->setWebsiteIds(array(1));
 
    $item_found = Mage::getModel('salesrule/rule_condition_product_found')
      ->setType('salesrule/rule_condition_product_found')
	  ->setValue(1) // 1 == FOUND
      ->setAggregator('all'); // match ALL conditions
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $item_found->addCondition($conditions);
 
    $actions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $rule->getActions()->addCondition($actions);
    $rule->save();
	
	$session->addSuccess($hlp->__('Your Rule1 Has Succesfully created & applied '));
	$this->_redirect('*/vendor/generatecoupon');		
	}
	/*Action Of Second row*/
	public function generatecouponfixedcartAction(){
			function generateUniqueId($length = null)
			{
				$rndId = crypt(uniqid(rand(),1));
				$rndId = strip_tags(stripslashes($rndId));
				$rndId = str_replace(array(".", "$"),"",$rndId);
				$rndId = strrev(str_replace("/","",$rndId));
					if (!is_null($rndId)){
					return strtoupper(substr($rndId, 0, $length));
					}
				
				return strtoupper($rndId);
			}
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');	
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
		$_vendorName = Mage::getModel('udropship/vendor')->load($vendorId);
		$vendorName = $_vendorName->getVendorName();
		$couponCode='CVSE'.generateUniqueId(4).$vendorId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('rupees');
		$discountOff = $this->getRequest()->getParam('rupees1');
		$startDate = $this->getRequest()->getParam('from_date1');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date1');
		$ed = date('Y-m-d',strtotime($endDate));
		if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
		$vId = $vendorId;
		$rule = Mage::getModel('salesrule/rule');
    	$customer_groups = array(0, 1, 2, 3);
    $rule->setName($discount.'/- Off -'.$vendorName.' Tll '.$ed)
      ->setDescription($discount.'/- Off If Order is greater than Rs.'.$discountOff)
      ->setFromDate($sd)
	  ->setToDate($ed)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCustomer(100)
      ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
      ->setIsActive(1)
      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('cart_fixed')
      ->setDiscountAmount($discount)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('0')
      ->setIsRss(0)
      ->setVendorid($vId)
	  ->setWebsiteIds(array(1));

    $item_found = Mage::getModel('salesrule/rule_condition_product_subselect')
      ->setType('salesrule/rule_condition_product_subselect')
	  ->setAttribute('base_row_total')
	  ->setOperator('>=')
	  ->setValue($discountOff)
      ->setAggregator('all'); // match ALL conditions
	  
	  
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $item_found->addCondition($conditions);
 
    $actions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $rule->getActions()->addCondition($actions);
    $rule->save();
	
	$session->addSuccess($hlp->__('Your Rule2 Has Succesfully created & applied '));
	$this->_redirect('*/vendor/generatecoupon');		
	}
/*Action Of Third row*/	
	public function generatecouponpercentageAction(){
			function generateUniqueId($length = null)
			{
				$rndId = crypt(uniqid(rand(),1));
				$rndId = strip_tags(stripslashes($rndId));
				$rndId = str_replace(array(".", "$"),"",$rndId);
				$rndId = strrev(str_replace("/","",$rndId));
					if (!is_null($rndId)){
					return strtoupper(substr($rndId, 0, $length));
					}
				
				return strtoupper($rndId);
			}
		$hlp = Mage::helper('udropship');
        $session = Mage::getSingleton('udropship/session');	
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
		$_vendorName = Mage::getModel('udropship/vendor')->load($vendorId);
		$vendorName = $_vendorName->getVendorName();
		$couponCode='CVSE'.generateUniqueId(4).$vendorId;
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$ruleQuery = "SELECT * FROM `salesrule` ORDER BY `salesrule`.`rule_id`  DESC";
		$resultrule = $read->query($ruleQuery)->fetch();
		
		$ruleIdlast = $resultrule['rule_id'];
		
		$discount = $this->getRequest()->getParam('rupeepercent');
		$discountOff1 = $this->getRequest()->getParam('rupeepercent1');
		$startDate = $this->getRequest()->getParam('from_date2');
		$sd = date('Y-m-d',strtotime($startDate));
		$endDate = $this->getRequest()->getParam('to_date2');
		$ed = date('Y-m-d',strtotime($endDate));
				if($startDate == ''):
            $session->addError($this->__('Please select From Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
        if($endDate == ''):
            $session->addError($this->__('Please select To Date.'));
            $this->_redirect('*/vendor/generatecoupon');
            return;
        endif;
		$vId = $vendorId;
		$rule = Mage::getModel('salesrule/rule');
		//echo '<pre>';print_r($rule->getCollection()->getData());exit;
    	$customer_groups = array(0, 1, 2, 3);
    $rule->setName($discount.'% Off -'.$vendorName.' Tll '.$ed)
      ->setDescription($discount.'% Off If order is greater than rupees Rs.'.$discountOff1)
      ->setFromDate($sd)
	  ->setToDate($ed)
      ->setCouponType(2)
      ->setCouponCode($couponCode)
      ->setUsesPerCustomer(100)
      ->setCustomerGroupIds($customer_groups) //an array of customer grou pids
      ->setIsActive(1)


      ->setConditionsSerialized('')
      ->setActionsSerialized('')
      ->setStopRulesProcessing(0)
      ->setIsAdvanced(1)
      ->setProductIds('')
      ->setSortOrder(0)
      ->setSimpleAction('by_percent')
      ->setDiscountAmount($discount)
      ->setDiscountQty(null)
      ->setDiscountStep(0)
      ->setSimpleFreeShipping('0')
      ->setApplyToShipping('0')
      ->setIsRss(0)
      ->setVendorid($vId)
	  ->setWebsiteIds(array(1))
	  ;
 
    $item_found = Mage::getModel('salesrule/rule_condition_product_subselect')
      ->setType('salesrule/rule_condition_product_subselect')
	  ->setAttribute('base_row_total')
	  ->setOperator('>=')
	  ->setValue($discountOff1)
      ->setAggregator('all'); // match ALL conditions
    $rule->getConditions()->addCondition($item_found);
    $conditions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $item_found->addCondition($conditions);
 
    $actions = Mage::getModel('salesrule/rule_condition_product')
      ->setType('salesrule/rule_condition_product')
      ->setAttribute('udropship_vendor')
      ->setOperator('==')
      ->setValue($vendorId);
    $rule->getActions()->addCondition($actions);
    $rule->save();
	
	$session->addSuccess($hlp->__('Your Rule3 Has Succesfully created & applied '));
	$this->_redirect('*/vendor/generatecoupon');		
	}
		
		public function wholesalesaveAction()
	{
		$vendorquote = $this->getRequest()->getParam('vendorquote');
		$wholesaleId=$this->getRequest()->getParam('wholesale');
		$day = $this->getRequest()->getParam('day');
		$month = $this->getRequest()->getParam('month');
		$year = $this->getRequest()->getParam('year');
		$deliveryAt = $day.'-'.$month.'-'.$year;
		$deliveryDate=date('Y-m-d',strtotime($deliveryAt));
		$model = Mage::getModel('wholesale/wholesale')->load($wholesaleId);
		$model->setVendorquote($vendorquote)
		        ->setDeliverydate($deliveryDate)
				->save();
		//For reply of vendor email post
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'udropship_vendor_wholesale_post_email_template';
		$translate  = Mage::getSingleton('core/translate');
		$_email = Mage::getModel('core/email_template');
		$mailSubject = 'You Have Received a Reply of Seller of requested Wholesale Request From!';
		$cc = 'wholesale@craftsvilla.com';
		$ccname = 'Wholesale';
		$sender = Array('name' => 'Craftsvilla Wholesale',
		'email' => 'wholesale@craftsvilla.com');
			
		$vars = array('wholesale' => $wholesaleId ,'vendorquote' => $vendorquote,'day' => $day,'month' => $month,'year' => $year);
		$_email->setTemplateSubject($mailSubject)->sendTransactional($templateId,$sender,$cc,$ccname,$vars,$storeId);
    	$translate->setTranslateInline(true);
		Mage::getSingleton('core/session')->addSuccess('Thank You.');
		//$this->_redirect('marketplace/vendor/wholesalesave/');
		$this->_renderPage(null, 'wholesale');
	}
//blog create added by gayatri on dated 03-07-2013
	public function blogcreateAction()
	{
			
		$msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
        
       print_r($postData=Mage::app()->getRequest()->getPost());
        
       $_session = Mage::getSingleton('udropship/session');
		
		 if($postData['blog'] == ''){
		   
            $_session->addError($this->__('blog is compulsory.'));
			
            $this->_redirect('*/vendor/blogdashboard');
          return;
		 }
        else{
	
		  $response = $this->wpPostXMLRPC($postData['blogtitle'],$postData['blog'],'Sarees');
	     echo $response;
		 $_session->addSuccess($this->__('blog added successfully.'));
		 $this->_redirect('*/vendor/blogdashboard');
			}
	}
	
   public function wpPostXMLRPC($title,$body,$category,$keywords='',$encoding='UTF-8') {
       $title = htmlentities($title,ENT_NOQUOTES,$encoding);
	    $keywords = htmlentities($keywords,ENT_NOQUOTES,$encoding);
      $rpcurl = 'http://blog.craftsvilla.com/xmlrpc.php';
	  
    $content = array(	    
        'title'=>$title,
        'description'=>$body,
        'mt_allow_comments'=>0,  // 1 to allow comments
        'mt_allow_pings'=>0,  // 1 to allow trackbacks
        'post_type'=>'post',
        'mt_keywords'=>$keywords,
        'categories'=>array($category)
		
    );
	
     
    $params = array(0,'craftsvilla','#5yt&3QW',$content,true);
    $request = xmlrpc_encode_request('metaWeblog.newPost',$params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_URL, $rpcurl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   // curl_setopt($ch, CURLOPT_TIMEOUT, 1);
	$results = curl_exec($ch);
    	if(curl_errno($ch))
			{		
				print curl_error($ch);
			}
			curl_close($ch);
    return $results;
}


	/*Added by Gayatri to set vacation mode*/
	public function vacationmodesetblockAction(){
		$msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();		
        $getCatalogPrivilleges = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorId,'vendor_id'); 
	 $catalog_privileges = $getCatalogPrivilleges->getCatalogPrivileges();	
         if($catalog_privileges == 0 && $catalog_privileges != '')
		{
		$session->addError($this->__('You are not allowed to Updated Shop on Vacation Mode. Please contact places@craftsvilla.com'));
            $this->_redirect('*/vendor/vacationmode');
	    return;
		} 
              
            elseif($catalog_privileges == 1 || $catalog_privileges == '')
	{
        $product = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
			->addAttributeToFilter('udropship_vendor',$vendorId)			
			->joinField('inventory_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id','is_in_stock',array('qty'=>'0','eq' => "0"));
		//echo '<pre>';print_r($product->getSelect()->__toString());exit;
			
			$write = Mage::getSingleton('core/resource')->getConnection('core_write');
       foreach($product as $_productoutofstock)
		 {
			
			$productquery = "update `cataloginventory_stock_item` set `is_in_stock`= 0 WHERE `product_id`=".$_productoutofstock['entity_id'];
			$write->query($productquery);	
			 
			
		 }
		 
		 $seller = "UPDATE `udropship_vendor` SET `fax` = 'vacation' WHERE `vendor_id`=".$vendorId;
		 $write->query($seller);
			try{
				$session->addSuccess($this->__('Updated Shop on Vacation Mode Successfully.'));
            $this->_redirect('*/vendor/vacationmode');
	} catch(Exception $e){
	    echo $e->getMessage();
	    
	}		 
	}
         else
		{
		$session->addError($this->__('You are not allowed to Updated Shop on Vacation Mode. Please contact places@craftsvilla.com'));
            $this->_redirect('*/vendor/vacationmode');
			    return;
		}	 
	}
	
	public function vacationmodeunsetblockAction(){
		$msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
$getCatalogPrivilleges = Mage::getModel('vendorneftcode/vendorneftcode')->load($vendorId,'vendor_id'); 
	 $catalog_privileges = $getCatalogPrivilleges->getCatalogPrivileges();	
         if($catalog_privileges == 0 && $catalog_privileges != '')
		{
		$session->addError($this->__('You are not allowed to Updated Shop. Please contact places@craftsvilla.com'));
            $this->_redirect('*/vendor/vacationmode');
	    return;
		} 
              
            elseif($catalog_privileges == 1 || $catalog_privileges == '')
	{
       $product = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('*')
		    ->addAttributeToFilter('udropship_vendor',$vendorId)
			->joinField('inventory_in_stock', 'cataloginventory_stock_item', 'is_in_stock', 'product_id=entity_id','is_in_stock = 0' );
		
			$fromPart = $product->getSelect()->getPart(Zend_Db_Select::FROM);
			$fromPart['e']['tableName'] ='catalog_product_entity';
			$product->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);

		
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');
       foreach($product as $_productinstock)
		 {
			 
			 
			$productquery = "update `cataloginventory_stock_item` set `is_in_stock`= '1' WHERE `product_id`= ".$_productinstock['entity_id']." AND qty>=1";
			
			$write->query($productquery);
			 	 
			
		 }
		 $seller = "UPDATE `udropship_vendor` SET `fax` = 'unsetvacation' WHERE `vendor_id`=".$vendorId;
		 $write->query($seller);
			try{
				$session->addSuccess($this->__('Updated Shop Successfully.'));
            $this->_redirect('*/vendor/vacationmode');
	} catch(Exception $e){
	    echo $e->getMessage();
	    
	}		 
	}
         else
		{
		$session->addError($this->__('You are not allowed to Updated Shop. Please contact places@craftsvilla.com'));
			     $this->_redirect('*/vendor/vacationmode');
			    return;
		}	 
	}
	 
	//Added by Gayatri to download the shipments from vendor panel on dated 02-09-2013
	 public function downloadShipmentAction()
	 {
		 $data       = $this->getRequest()->getParams();
		 $session    = Mage::getSingleton('udropship/session');
		 $vendorId   = $session->getVendor()->getVendorId();
		// $ii=$this->getRequest()->getParam('uploadTracking');
		 
		 $shipmentData   = Mage::getModel('sales/order_shipment')->getCollection();
		//print_r($shipmentData);		
        //$shipmentData->getSelect()->join(array('a'=>'sales_flat_shipment_item'),'main_table.entity_id=a.parent_id')
	//	                          ->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
	//							  ->join(array('c'=>'sales_flat_order'),'c.entity_id=main_table.order_id','c.customer_email')
	//							  ->where('main_table.udropship_vendor = '.$vendorId.' AND main_table.udropship_status = 11 AND b.address_type = "shipping"');
          $shipmentData->getSelect()->join(array('b'=>'sales_flat_order_address'), 'b.parent_id=main_table.order_id')
								  ->join(array('c'=>'sales_flat_order'),'c.entity_id=main_table.order_id','c.customer_email')
								  ->join(array('d'=>'sales_flat_order_payment'),'main_table.order_id=d.parent_id','d.method')
					  ->where('main_table.udropship_vendor = '.$vendorId.' AND main_table.udropship_status IN (24,30,31) AND b.address_type = "shipping" AND d.method = "cashondelivery"');
						//echo	$shipmentData->getSelect()->__toString();	  exit;
		$shipmentreport = $shipmentData->getData();
		
    	$filename = "ShipmentReport"."_".date("Y-m-d");
		$outputreport = "";
	    //$list = array("Date of Shipment","Shipment No","SKU Id","Vendor_Sku","Name", "Quantity","Selling Price","Customer Name","Customer Address","Phone Number","Email Id");
		$list = array("Date of Shipment","Shipment No","Customer Name","Customer Address");
    	$numfields = sizeof($list);
		for($k =0; $k < $numfields;  $k++) { 
			$outputreport .= $list[$k];
			if ($k < ($numfields-1)) $outputreport .= ", ";
		}
		$outputreport .= "\n";
		foreach($shipmentreport as $_shipmentreport)
	    {  
		
			$countryModel = Mage::getModel('directory/country')->loadByCode($_shipmentreport['country_id']);
        $countryName = $countryModel->getName();
		$vendors = Mage::getModel('udropship/vendor')->getCollection();
		     	
		    	$vendors_arr = $vendors->getData();
				for($m =0; $m < sizeof($list); $m++) {
	              	$fieldvalue = $list[$m];
		    		if($fieldvalue == "Date of Shipment")
		    		{
		    			$outputreport .= date("d-m-Y", strtotime($_shipmentreport['created_at']));
		    		}
		    			
		    		if($fieldvalue == "Shipment No")
		    		{
		    			$outputreport .= $_shipmentreport['increment_id'];
		    		}
		    		
		    	//	if($fieldvalue == "SKU Id")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['sku'];
		    	//	}
		    			
		    	//	if($fieldvalue == "Vendor_Sku")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['vendor_sku'];
		    	//	}
		    		
		    	//	if($fieldvalue == "Name")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['name'];
		    	//	}
		    		
			//		if($fieldvalue == "Quantity")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['qty_ordered'];
		    	//	}
			//		if($fieldvalue == "Selling Price")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['base_original_price'];
		    	//	}
					if($fieldvalue == "Customer Name")
		    		{
		    			$outputreport .= $_shipmentreport['firstname'].' '.$_shipmentreport['lastname'];
		    		}
					if($fieldvalue == "Customer Address")
		    		{
		    			$outputreport .= '"'.$_shipmentreport['street'].", ".$_shipmentreport['city'].", ".$_shipmentreport['region'].", ".$countryName.", ".$_shipmentreport['postcode'].'"';
		    		}
			//		if($fieldvalue == "Phone Number")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['telephone'];
		    	//	}
			//		if($fieldvalue == "Email Id")
		    	//	{
		    	//		$outputreport .= $_shipmentreport['customer_email'];
		    	//	}
		    		if ($m < ($numfields-1))
		    		{
		    			$outputreport .= ",";
		    		}
		    		
		    	}
		    	$outputreport .= "\n";
    		
		}
					//export to csv
				header("Content-type: text/x-csv");
				header("Content-Disposition: attachment; filename=$filename.csv");
				header("Pragma: no-cache");
				header("Expires: 0");
				echo $outputreport;
					exit;
					try{
				    $this->_redirect('*/vendor/dashboard');
	} catch(Exception $e){
	    echo $e->getMessage();
	    
	}		 
    }
		 
	/*Added by Gayatri to import shipment tracking numbers from vendor panel on dated 03-09-2013*/
	public function importAction()
    {
		 $session    = Mage::getSingleton('udropship/session');
		 $vendorId   = $session->getVendor()->getVendorId();
		//$info = pathinfo($_FILES['import_shipmenttracking_file']['tmp_name']);
		/*Modified by Gayatri to show the error when the file format is not csv*/
		   if($_FILES['import_shipmenttracking_file']['type'] != "application/vnd.ms-excel"){
	                  $this->_getSession()->addError($this->__("This is not correct format. Please upload in CSV format."));
					  
			}
         else{
		 if ($this->getRequest()->isPost() && !empty($_FILES['import_shipmenttracking_file']['tmp_name'])) {
            try {
				//$trackingTitle = $_POST['import_shipmenttracking_tracking_title'];
                $this->_importShipmenttrackingFile($_FILES['import_shipmenttracking_file']['tmp_name']);
						
            }
		       catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Invalid file upload attempt'));
            }           
		}
		else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        }
		 }
           $this->_redirect('*/vendor/index');
	}

    /**
     * Importation logic
     * @param string $fileName
     * @param string $trackingTitle
	      */
		  
	protected function _processTrackStatusSave($save, $object)
    {
        if ($save===true) {
            $object->save();
        } elseif ($save instanceof Mage_Core_Model_Resource_Transaction) {
            $save->addObject($object);
        }
    }
	
    protected function _importShipmenttrackingFile($fileName)
    {
		$session    = Mage::getSingleton('udropship/session');
		$vendorId   = $session->getVendor()->getVendorId();
		ini_set('auto_detect_line_endings', true);
		$csvObject = new Varien_File_Csv();
		$csvData = $csvObject->getData($fileName);
		
		//print_r($csvData);exit;
		/**
		 * File expected fields
		 */
		$expectedCsvFields  = array(
			0   => $this->__('Order Id'),
			1   => $this->__('Courier Name'),
			2   => $this->__('Tracking Number')
		);
		/* $k is line number
		 * $v is line content array
		 */
		 $numfailed = 0;
		 $numsuccess = 0;
			foreach ($csvData as $k => $v) 
			{
				   /**
				 * End of file has more than one empty lines
				 */
				if (count($v) <= 1 && !strlen($v[0])) {
					continue;
				}
				 /**
				 * Check that the number of fields is not lower than expected
				 */
				if (count($v) < count($expectedCsvFields)) {
					$this->_getSession()->addError($this->__('Line %s format is invalid and has been ignored', $k));
					continue;
				 }
				/**
				 * Get fields content
				 */
			   $orderId = $v[0];
			   $courierName=$v[1];
			   $trackingNumber = $v[2];				
			  $shipment=Mage::getModel('sales/order_shipment')->loadByIncrementId($orderId);
			  
			   if(!$shipment->getUdropshipVendor()){
				  $this->_getSession()->addError($this->__('You have entered wrong shipment Id %s. So can not update tracking number', $orderId));
				  $numfailed++;
				  continue;
					 }
			    if($shipment->getUdropshipVendor() != $vendorId){
				  $this->_getSession()->addError($this->__('This shipment does not belong to you hence tracking not updated: %s', $orderId));
				  $numfailed++;
				  continue;
					 }
					 if($shipment->getUdropshipStatus()=='23' || $shipment->getUdropshipStatus()== '12'){
				  $this->_getSession()->addError($this->__('This status of the shipment can not be changed as it is refunded : %s', $orderId));
				  $numfailed++;
				  continue;
					 }
			  
				$track = Mage::getModel('sales/order_shipment_track')
							->setNumber($trackingNumber)
							->setCourierName($courierName)
							 ->setTitle('Domestic Shipping');
				$shipment->addTrack($track);
				if ($shipment->getUdropshipStatus()!= Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED) {
				
					$shipment->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
				}
				else
				{
					$this->_getSession()->addSuccess($this->__('%s Shipment Status already shipped but tracking number updated!!!',$orderId));
				}
				
				
					Mage::helper('udropship')->addShipmentComment($shipment,
							   $this->__('Shipment has been complete by System')
							  );	
				$numsuccess++;	
				$shipment->save(); 
				   /**
				 * Comment handling
				 */ 
				$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
				$_shipmentId = $shipment->getIncrementId();
				$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
				 $session = $this->_getSession();
				$_order = $shipment->getOrder();
				$customerTelephone = $_order->getBillingAddress()->getTelephone();
				$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
				$storeId = $_order->getStoreId();
				/*$newStatus = $delivered
					? Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED
					: Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
	          $notifyOnOld = Mage::getStoreConfig('udropship/customer/notify_on', $storeId);
				$notifyOn = Mage::getStoreConfig('udropship/customer/notify_on_shipment', $storeId);
			if (($notifyOn || $notifyOnOld==Unirgy_Dropship_Model_Source::NOTIFYON_SHIPMENT) && !$delivered) 
			   {*/
				$shipment->sendEmail();
				$shipment->setEmailSent(true);
				$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
				$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
				$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
				$_smsSource = Mage::getStoreConfig('sms/general/source');
				if($_orderBillingCountry == 'IN')
				{
						$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$trackingNumber.'Courier Name :'.$courierName.' - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
						$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
						$parse_url = file($_customerSmsUrl);			
				}
				
				$this->_processTrackStatusSave($save, $shipment);
				
				
			}
			 try{
				 	if($numsuccess>0)
					{
						$this->_getSession()->addSuccess($this->__('%s Shipments Successfully Saved!!!',$numsuccess));
					}
					if($numfailed>0)
					{
						$this->_getSession()->addError($this->__('%s Shipments Update Failed!!!', $numfailed));
					}
					
					if($numsuccess==0 && $numfailed==0)
					{
						$this->_getSession()->addError($this->__('No Shipments Were Updated. Please check your csv file!!!'));
					}
					
				$this->_redirect('*/vendor/index');
				 } 
			 catch(Exception $e){
				echo $e->getMessage();
				
				}		  
			
	  }
		
			
	/*Added by Gayatri to download the csv format for uploading the shipment tracking numbers on dated 04-09-2013*/
	public function downloadformatAction()
	 {
		 header("Content-type: text/csv");  
         header("Cache-Control: no-store, no-cache");  
         header('Content-Disposition: attachment; filename="TrackingnumberuploadFormat.csv"');  
				$outstream = fopen("php://output",'w');  
  
        $test_data = array(  
			array( '100008220', 'Aramex', '12345678'),  
			array( '100008225', 'DHL', '63254169' )  
		);  
  
		foreach( $test_data as $row )  
		{  
			fputcsv($outstream, $row, ',', '"');  
		}  
  
       fclose($outstream);  
			
		 
	 }

    /*Added by Gayatri to add the bulk upload of products option for vendor*/
	public function bulkuploadcsvblockAction()
	{
		$msg = $this->_getSession()->getMessages(true); 
        $this->getLayout()->getMessagesBlock()->addMessages($msg);
        $this->_initLayoutMessages('core/session');   
        $session = $this->_getSession();
		$vendorId = Mage::getSingleton('udropship/session')->getVendorId();
		$product = Mage::getModel('catalog/product')->getCollection();
	}
   
	 /*Added by Gayatri to import products from vendor panel on dated 03-09-2013*/
	public function productimportAction()
    {
		$session    = Mage::getSingleton('udropship/session');
		$vendorId   = $session->getVendor()->getVendorId();
		$firstName   = $session->getVendor()->getVendorName();
		$_target = Mage::getBaseDir('media') . DS . 'vendorcsv' . DS;
		$targetcsv = Mage::getBaseUrl('media').'vendorcsv/';
		$name = $_FILES['import_product_file']['name'];
		$source_file_path = basename($name);
		$newfilename = mt_rand(10000000, 99999999).'_upload.csv';
		$path = $_target.$newfilename;
		$path1 = $targetcsv.$newfilename;
		$pathhtml = '<a href="'.$path1.'">'.$newfilename.'</a>';
		file_put_contents($path, file_get_contents($_FILES['import_product_file']['tmp_name']));
		$csvObject = new Varien_File_Csv();
		$csvData = $csvObject->getData($path);
		$count = sizeof($csvData);
		$info = pathinfo($_FILES['import_product_file']['name']);
          
        /*   $csv_mimetypes = array(
									'text/csv',
									'text/plain',
									'application/csv',
									'application/excel',
									'application/vnd.ms-excel',
									'application/vnd.msexcel',
									'text/anytext',
									'application/octet-stream',
									
								);*/
		  if($this->getRequest()->isPost() && empty($_FILES['import_product_file']['tmp_name']))
		  {
			  $this->_getSession()->addError($this->__("Please select csv file"));
		  }
		  if($info['extension'] != 'csv'){
	                 $this->_getSession()->addError($this->__("This is not correct format. Please upload in CSV format."));
				}
			
         else{
		 if ($this->getRequest()->isPost() && !empty($_FILES['import_product_file']['tmp_name'])) {
            try {
				
				$bulk = Mage::getModel('bulkuploadcsv/bulkuploadcsv');
												$bulk->setVendor($vendorId)
												->setFilename($newfilename)
												->setStatus(3)
												->setProductsactiveted('0')
												->setProductsrejected('0')
												->setTotalproducts($count)
												->setUploaded(now())
												->setFilepath($pathhtml)
												->save();
				$successnotice = 'Your File '.$source_file_path.' Containing '.$count.' Products Has Been Successfully Submitted. Your Upload Can Take Upto 7 Days. You Will Be Notified Once Upload Is Completed. For Any Question Email Us At places@craftsvilla.com';
				$this->_getSession()->addSuccess($successnotice);
		
		 }
		 
		       catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Invalid file upload attempt'));
            }           
		}
		else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        	}
		 }
         $this->_redirect('*/vendor/bulkuploadcsv');
	}
	
	 /*Added by Gayatri to import inventory from vendor panel on dated 03-09-2013*/
	public function inventoryimportAction()
    {
		$session    = Mage::getSingleton('udropship/session');
		$vendorId   = $session->getVendor()->getVendorId();
		$firstName   = $session->getVendor()->getVendorName();
		$_target = Mage::getBaseDir('media') . DS . 'inventorycsv'. DS;
		$targetcsv = Mage::getBaseUrl('media').'inventorycsv/';
		$name = $_FILES['import']['name'];
		$source_file_path = basename($name);
		   $newfilename = mt_rand(10000000, 99999999).'_upload.csv';
			$path = $_target.$newfilename;
			$path1 = $targetcsv.$newfilename;
			$pathhtml = '<a href="'.$path1.'">'.$newfilename.'</a>';
		
		file_put_contents($path, file_get_contents($_FILES['import']['tmp_name']));
		$csvObject = new Varien_File_Csv();
		$csvData = $csvObject->getData($path);
		$count = sizeof($csvData);
		$info = pathinfo($_FILES['import']['name']);
		 if($this->getRequest()->isPost() && empty($_FILES['import']['tmp_name']))
		  {
			  $this->_getSession()->addError($this->__("Please select csv file"));
		  }
		if($info['extension'] != 'csv'){
	                 $this->_getSession()->addError($this->__("This is not correct format. Please upload in CSV format."));
				}
			
         else{
		 if ($this->getRequest()->isPost() && !empty($_FILES['import']['tmp_name'])) {
            try {
				
				$bulk = Mage::getModel('bulkinventoryupdate/bulkinventoryupdate');
												$bulk->setVendor($vendorId)
												->setFilename($newfilename)
												->setStatus(3)
												->setTotalproducts($count)
												->setUploaded(now())
												->setFilenameurl($pathhtml)
												->save();
				$successnotice = 'Your File '.$source_file_path.' Containing '.$count.' Products Has Been Successfully Submitted. For Any Question Email Us At places@craftsvilla.com';
				$this->_getSession()->addSuccess($successnotice);
		
		 }
		 
		       catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $this->_getSession()->addError($this->__('Invalid file upload attempt'));
            }           
		}
		/*else {
            $this->_getSession()->addError($this->__('Invalid file upload attempt'));
        	}*/
		 }
         $this->_redirect('*/vendor/bulkinventoryupdate');
	}

/*Added on dated 29-10-2013 */
	public function codordersAction(){
		$this->_renderPage(null, 'codorders');
	}
	public function codenabledisableAction(){

		$defaultAllowedTags = Mage::getStoreConfig('udropship/vendor/preferences_allowed_tags');
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
		$id = $session->getVendorId();
				$vendor = Mage::getModel('udropship/vendor')->load($id);
		$pincode = $vendor['zip'];
		$read = Mage::getSingleton('core/resource')->getConnection('core_read');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery'); 
		if($delhivery=='1')
		{
			
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Delhivery%'";
		
		}
		else
		{
			$pincodeQuery = "SELECT * FROM `checkout_cod_craftsvilla` where `pincode` = '".$pincode."' AND `carrier` like '%Aramex%'";
		
		}
					
		$rquery = $read->query($pincodeQuery)->fetch();
		$cod = $rquery['is_cod'];
		
		//echo $q = $r->getPost('vendor_payment_methods');
		if ($r->isPost()) {
            $p = $r->getPost();
			//echo $p['vendor_payment_methods']; 
			$hlp->processPostMultiselects($p);
            try {
				
				if((is_null($cod) || ($cod == '') || ($cod != '0')) && empty($p['vendoradmin_payment_methods'][0]))
					{
						$session->addError($hlp->__('Sorry we cannot enable COD for you because your pickup pincode is not servicable by our logistic service provider!'));
					}
					else
					{
                $v = $session->getVendor();
				 //$v->setData('vendor_payment_methods', $p['vendor_payment_methods']);
					 $v->setData('vendoradmin_payment_methods', $p['vendoradmin_payment_methods']);
			
				Mage::dispatchEvent('udropship_vendor_preferences_save_before', array('vendor'=>$v));
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
				if($p['vendoradmin_payment_methods'] == 'cashondelivery')
				{
               	 $session->addSuccess($hlp->__('COD as Payment Method Is Now Disabled For You!'));
				}
				else{
					  	$session->addSuccess($hlp->__('COD as Payment Method Is Now Enabled For You!'));
					}
					}
				
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
			
					
       $this->_redirect('udropship/vendor/codorders');
   	
	}

//added by dileswar on dated 15-01-2014  for change the direction of dashboard url to prepaid ordres in prepaid orders action...	
	public function prepaidordersAction()
    {
		$this->_renderPage(null, 'prepaidorders');
    }
	/*public function checkCourierAction(){
		$session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
		$courierName = $this->getRequest()->getParam('couriername');
		$shipmentId = $this->getRequest()->getParam('idshipment');
		$shipmentDet = Mage::getModel('sales/order_shipment')->load($shipmentId);
		$orderid = $shipmentDet->getOrderId();
		$order = Mage::getModel('sales/order')->load($shipmentDet->getOrderId());
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		$postcode = $address['postcode'];
		$readQp = Mage::getSingleton('core/resource')->getConnection('core_read');
	//Check pincode is availabale for respected courier	
	
	if($courierName == 'Delhivery')
		{
		$readQp = Mage::getSingleton('core/resource')->getConnection('core_read');
		$chckPin = "SELECT * FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postcode."' and `carrier` LIKE '%".$courierName."%'";
		$resultCheck = $readQp->query($chckPin)->fetch();
		if($resultCheck['pincode'])
			{
			$writeQp = 	Mage::getSingleton('core/resource')->getConnection('core_write');
			$trackUpdate = "UPDATE `sales_flat_shipment` SET `udropship_shipcheck` = '".$courierName."' WHERE `entity_id` = '".$shipmentId."'";
			$resultTrack = $writeQp->query($trackUpdate);
			$session->addError($this->__('Courier Updated existsss'));
			$this->_redirect('udropship/vendor/codorders');
			}
			else
			{
				$session->addError($this->__('Cod I not available for thhis pincode'));
				$this->_redirect('udropship/vendor/codorders');
			}
		}
	elseif($courierName == 'Aramex')	
		{
		$readQp = Mage::getSingleton('core/resource')->getConnection('core_read');
		$chckPin = "SELECT * FROM `checkout_cod_craftsvilla` WHERE `pincode` = '".$postcode."' and `carrier` LIKE '%".$courierName."%'";
		$resultCheck = $readQp->query($chckPin)->fetch();
		if($resultCheck['pincode'])
			{
			$writeQp = 	Mage::getSingleton('core/resource')->getConnection('core_write');
			$trackUpdate = "UPDATE `sales_flat_shipment` SET `udropship_shipcheck` = '".$courierName."' WHERE `entity_id` = '".$shipmentId."'";
			$resultTrack = $writeQp->query($trackUpdate);
			$session->addError($this->__('Courier Updated existsss'));
			$this->_redirect('udropship/vendor/codorders');
			}
			else
			{
				$session->addError($this->__('Cod I not available for thhis pincode'));
				$this->_redirect('udropship/vendor/codorders');
			}
		}
		else
		{
			echo 'please select on courier';
			$this->_redirect('udropship/vendor/codorders');
		}
		$this->_redirect('udropship/vendor/codorders');
		}
*/		
public function shipmentPostCourierAction()
		{
		$hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        $id = $r->getParam('id');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery');
		$storeId = Mage::app()->getStore()->getId();
		$shipment1 = Mage::getModel('sales/order_shipment');		
		$shipment = $shipment1->load($id);
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
		//$courierPartner = $shipment->getUdropshipShipcheck();
		
		//get the shipment id for sms Added By Dileswar as dated 08-11-2012
		$_shipmentId = $shipment->getIncrementId();
		$baseTotalValue = $shipment->getBaseTotalValue();
		$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
		
        $session = $this->_getSession();
		// Craftsvilla Comment Added By Amit Pitre On 25-06-2012 for international shipping changing status without tracking number /////
		$_order = $shipment->getOrder();
		$_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();
//Get the customer details For aramex pick up request
		$order = Mage::getModel('sales/order')->load($shipment->getOrderId());
		
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		//print_r($address);exit;
		$street = $address['street'];
		$city = $address['city'];
		$name = $address['firstname'].' '.$address['lastname'];
		$postcode = $address['postcode'];
		$selectCourier = $hlp->getCurrentCourier($vendorPostcode,$postcode);		
// for SMS Added By Dileswar on Dated 08-11-2012 
		/* For SMS to customer Added by Dileswar on dated 08-11-2012 */
	
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		$customerTelephone = $_order->getBillingAddress()->getTelephone();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
		$testcodPayment = $order->getPayment();
			
		if (!$shipment->getId()) {
            return;
        }

        try {
            $store = $shipment->getOrder()->getStore();
		    $track = null;
            $highlight = array();
		    $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');
		    $printLabel = $r->getParam('print_label');
            //$number = $r->getParam('tracking_id');
            //$courier_name = $r->getParam('courier_name');
		    $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusDelivered = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED;
            $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
			$statusAccepted = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_ACCEPTED;
			//added By dileswar 13-10-2012
			$statusOutofstock = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_OUTOFSTOCK_CRAFTSVILLA ;
			
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
           // if label was printed
            if ($printLabel) {
                $status = $r->getParam('is_shipped') ? $statusShipped : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } 
			else { // if status was set manually
                $status = $r->getParam('status');
                
				$isShipped = $status == $statusShipped || $status == $statusAccepted || $status==$statusDelivered || $autoComplete && ($status==='' || is_null($status));
				
			}
//email to customer in case seller cacncelled the order
			if($status == $statusCanceled){
			$templateId = 'cancel_email_to_customer_email_template';
				$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
				$emailCanceled = Mage::getModel('core/email_template');
				$customerShipmentItemHtml = '';		
				$currencysym = Mage::app()->getLocale()->currency($order->getBaseCurrencyCode())->getSymbol();
				$_items = $shipment1->getAllItems();
				//echo '<pre>';print_r($_items);exit;
				$customerShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>ShipmentId</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>SKU</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
				foreach ($_items as $_item)
				{
				//echo $_item['product_id'];exit;
				$product = Mage::helper('catalog/product')->loadnew($_item['product_id']);
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />					";				
				 $customerShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_shipmentId."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item['sku']."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$customerShipmentItemHtml .= "</table>";
				$vars = array('shipmentid'=>$_shipmentId,
							'vendorShopName'=>$vendorName,
							'selleremail' => $vendorEmail,
							'sellerTelephone' => $vendorTelephone,					
							'customershipmentitemdetail' =>	$customerShipmentItemHtml,
							'custfirstname' => $name		
							);
				//print_r($vars);exit;
				$emailCanceled->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							  ->sendTransactional($templateId, $sender,$_orderBillingEmail, '', $vars, $storeId);
				$session->addSuccess($this->__('Shipment status has been changed to Cancelled &  Emailed To customer'));
				}
//condition for penalaty charge for 2% on out of stock case added on dated 09-11-2013
				/*if($status == $statusOutofstock)
					{
					$templateId = 'refund_in_outofstock_email_template';
					$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
					$_email = Mage::getModel('core/email_template');
	
					$penaltyAmount = ($baseTotalValue*0.02);
					
					$oldAdjustAmount = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection()->addFieldToFilter('shipment_id',$_shipmentId);
					
					foreach($oldAdjustAmount as $_oldAdjustAmount){ $amntAdjust = $_oldAdjustAmount['adjustment']; }
					
					$amntAdjust = $amntAdjust-$penaltyAmount;
					
					$read = Mage::getSingleton('core/resource')->getConnection('udropship_read');	
					$getClosingblncQuery = "SELECT `vendor_name`,`closing_balance` FROM `udropship_vendor` where `vendor_id` = '".$vendor->getId()."'";
					$getClosingblncResult = $read->query($getClosingblncQuery)->fetch();
					$closingBalance = $getClosingblncResult['closing_balance'];
					$vendorName = $getClosingblncResult['vendor_name'];
					$closingBalance = $closingBalance-$penaltyAmount; 
					
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
					
					$queryUpdateForAdjustment = "update shipmentpayout set adjustment='".$amntAdjust."' , `comment` = 'Adjustment Against Out Of Stock'  WHERE shipment_id = '".$_shipmentId."'";
					$write->query($queryUpdateForAdjustment);
					
					$queryUpdateForClosingbalance = "update `udropship_vendor` set `closing_balance`='".$closingBalance."'  WHERE `vendor_id`= '".$vendor->getId()."'";
					$write->query($queryUpdateForClosingbalance);
					$vars = array('penaltyprice'=>$penaltyAmount,
								  'shipmentid'=>$_shipmentId,
								  'vendorShopName'=>$vendorName
								);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->sendTransactional($templateId, $sender,$vendor->getEmail(), '', $vars, $storeId);
					$session->addSuccess($this->__('Shipment status has been changed to out of stock and charged penalty of Rs.'.$penaltyAmount));							
					}*/
			

            // if track was generated - for both label and manual tracking id
            /*
            if ($track) {
                // if poll tracking is enabled for the vendor
                if ($pollTracking && $vendor->getTrackApi()) {
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
                    $isShipped = false;
                } else { // otherwise process track
                    $track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_READY);
                    Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
                }
            */
            // if tracking id added manually and new status is not current status
            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {
            	$check = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
				
				if(($check[0]['number'] == '') && ($_address->getCountryId() == 'IN' && $status != '18' && $status != '6' && $testcodPayment->getMethodInstance()->getTitle() != 'Cash On Delivery')){/*
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            		if($r->getParam('tracking_id') == '' && $r->getParam('courier_name') == ''){
            			$session->addError($this->__('Please enter Tracking id and Courier name'));
            			$this->_forward('shipmentInfo');
            		}
            	*/} 
				
            	else{
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered)
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = $this->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                $triedToChangeComment = $this->__('%s tried to change the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
		if($status != $statusAccepted)
		{
                	if ($status==$statusShipped || $status==$statusDelivered) {
                		$hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    		$hlp->completeOrderIfShipped($shipment, true);
		               $hlp->completeUdpoIfShipped($shipment, true);
                    		Mage::helper('udropship')->addShipmentComment(
                        	$shipment,
                        	$changedComment
                    		);
                		} elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $changedComment
                        );
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, $vendor);
                    } else {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $triedToChangeComment
                        );
                    } 
                } else { 
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }
                $shipment->getCommentsCollection()->save();
                $session->addSuccess($this->__('Shipment status has been changed'));
               }
              }
			  // Condition for cod
			$checkTrack12 = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
			$trckentId = $checkTrack12[0]['entity_id'];
			
			
		//	if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']=='') && $delhivery == '1')
if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Delhivery')	
				{
				$awbNumber = $hlp->fetchawbgenerate('Delhivery');
				//commented the below line by dileswar on dated 19-11-2013 for some time...
				//$awbOrdergenerate = $hlp->fetchawbcreateorder('Delhivery',$shipment); 
			    $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            				
					$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumber)
                    	->setCarrierCode($method[0])
                		->setCourierName('Delhivery')
                    	->setTitle($title);
                
					$shipment->addTrack($track);
				
				//Below commented by Gayatri on dated 4-12-2013 as when status is accepted mail should not go to customer
            //  Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $awbNumber)
                );
                
				$shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            
				}
	//	if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']=='') && $delhivery == '0')

//Code For Aramex Start
	if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Aramex')
	  {
		 
		$awbNumber = $hlp->aramaxawbgenerate('Aramex',$id);
		if(!empty($awbNumber))
		{
		$shipment->setUdropshipStatus($status);
		 $method = explode('_', $shipment->getUdropshipMethod(), 2);
                 $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            		$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumber)
                    	->setCarrierCode($method[0])
                		->setCourierName('Aramex')
                    	->setTitle('Aramex');
               //$track = Mage::getModel('sales/order_shipment_api')->addTrack($shipmentid, 'aramex', 'Aramex', $awbNumber);
					$shipment->addTrack($track);
				
				//Below commented by Gayatri on dated 4-12-2013 as when status is accepted mail should not go to customer
            //  Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID VD11 %s ', $vendor->getVendorName(), $awbNumber)
                );
       //Generating pick up request when seller accept for cod
	   //$account=Mage::getStoreConfig('courier/general/account_number');		
		//$country_code=Mage::getStoreConfig('courier/general/account_country_code');
	//	$country_code= 'IN';
	//	$post = '';
	//	$country = Mage::getModel('directory/country')->loadByCode($country_code);		
	//	$response=array();
	//	$clientInfo = Mage::helper('courier')->getClientInfo();		
	//	try {
	//					
//	//	echo $pickupDate = $pickupdateAramex;		
	//	$pickupDate = time() + (1 * 24 * 60 * 60);		
	//	$readyTimeH=10;
	//	$readyTimeM=10;			
	//	$readyTime=mktime(($readyTimeH-2),$readyTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));	
	//	$closingTimeH=18;
	//	$closingTimeM=59;
	//	$closingTime=mktime(($closingTimeH-2),$closingTimeM,0,date("m",$pickupDate),date("d",$pickupDate),date("Y",$pickupDate));
	//	$params = array(
	//	'ClientInfo'  	=> $clientInfo,
	//							
	//	'Transaction' 	=> array(
	//							'Reference1'			=> $incmntId 
	//							),
	//							
	//	'Pickup'		=>array(
	//							'PickupContact'			=>array(
	//								'PersonName'		=>html_entity_decode(substr($vAttn.','.$vendorName,0,45)),
	//								'CompanyName'		=>html_entity_decode($vendorName),
	//								'PhoneNumber1'		=>html_entity_decode($vendorTelephone),
	//								'PhoneNumber1Ext'	=>html_entity_decode(''),
	//								'CellPhone'			=>html_entity_decode($vendorTelephone),
	//								'EmailAddress'		=>html_entity_decode($vendorEmail)
	//							),
	//							'PickupAddress'			=>array(
	//								'Line1'				=>html_entity_decode($vendorStreet),
	//								'City'				=>'',//html_entity_decode($vendorCity),
	//								'StateOrProvinceCode'=>'',//html_entity_decode($regionName),
	//								'PostCode'			=>html_entity_decode($vendorPostcode),
	//								'CountryCode'		=>'IN'
	//							),
	//							
	//							'PickupLocation'		=>html_entity_decode('Reception'),
	//							'PickupDate'			=>$readyTime,
	//							'ReadyTime'				=>$readyTime,
	//							'LastPickupTime'		=>$closingTime,
	//							'ClosingTime'			=>$closingTime,
	//							'Comments'				=>html_entity_decode('Please Pick up'),
	//							'Reference1'			=>html_entity_decode($_shipmentId),
	//							'Reference2'			=>'',
	//							'Vehicle'				=>'',
	//							'Shipments'				=>array(
	//								'Shipment'					=>array()
	//							),
	//							'PickupItems'			=>array(
	//								'PickupItemDetail'=>array(
	//									'ProductGroup'	=>'DOM',
	//									'ProductType'	=>'CDA',
	//									'Payment'		=>'3',										
	//									'NumberOfShipments'=>$shipmentCount,
	//									'NumberOfPieces'=>$totalQtyOrdered,										
	//									'ShipmentWeight'=>array('Value'=>'0.5','Unit'=>'KG'),
	//									
	//								),
	//							),
	//							'Status' =>'Ready'
	//						)
	//);
	
	//$baseUrl = Mage::helper('courier')->getWsdlPath();
	//$soapClient = new SoapClient($baseUrl . 'shipping.wsdl');
	//try{
	//$results = $soapClient->CreatePickup($params);		
	//echo '<pre>';print_r($results);exit;
	//if($results->HasErrors){
	//	if(count($results->Notifications->Notification) > 1){
	//		$error="";
	//		foreach($results->Notifications->Notification as $notify_error){
	//			$error.=$this->__('Aramex: ' . $notify_error->Code .' - '. $notify_error->Message)."<br>";				
	//			}
	//			$response['error']=$error;
	//		}else{
	//			$response['error']=$this->__('Aramex: ' . $results->Notifications->Notification->Code . ' - '. $results->Notifications->Notification->Message);
	//		}
	//		$response['type']='error';
	//	}else{
	//		
	//		$notify = false;
        //	$visible = false;
	//		$commentAramex="Pickup reference number (".$results->ProcessedPickup->ID." ) created by Vendor ".$vendorName.".";
	//		//$_order->addStatusHistoryComment($comment, $_order->getStatus())
			//->setIsVisibleOnFront($visible);
			//->setIsCustomerNotified($notify);
			//$_order->save();	
			//$shipmentId=null;
			//$shipment = Mage::getModel('sales/order_shipment')->getCollection()
			//->addFieldToFilter("order_id",$_order->getId())->load();
			//if($shipment->count()>0){
				//foreach($shipment as $_shipment){
					//$shipmentId=$_shipment->getId();
					//break;
				//}
			//}
	//		if($id!=null){
	//				
	//				$shipment->addComment(
          //      	$commentAramex,
            //    	false,
              //  	false
            //	);
				//$shipment->save();
	//		}
	//		$response['type']='success';
	//		$amount="<p class='amount'>Pickup reference number ( ".$results->ProcessedPickup->ID.").</p>";		
	//		$response['html']=$amount;
	//	}
	//	} catch (Exception $e) {
	//		$response['type']='error';
	//		$response['error']=$e->getMessage();			
	//		}
	//	}
	//	catch (Exception $e) {
	//		$response['type']='error';
	//		$response['error']=$e->getMessage();			
	//	}
	//	json_encode($response);	
	//Email to seller
	//	$storeId1 = Mage::app()->getStore()->getId();
	//	$templateId1 = "aramex_pick_up_date_email_seller";
	//		$sender1 = Array('name'  => 'Craftsvilla',
	//					    'email' => 'places@craftsvilla.com');
	//		$_email1 = Mage::getModel('core/email_template');
	//		$translate  = Mage::getSingleton('core/translate');
	//					$translate->setTranslateInline(false);
	//		$vars1 = array('shipmentId'=>$_shipmentId, 'vendorName'=>$vendorName, 'awbnumber'=>$awbNumber,'pickupId'=>$results->ProcessedPickup->ID);
	//		//print_r($vars1);exit;
	//		$_email1->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId1))
	//			   ->sendTransactional($templateId1, $sender1, $vendorEmail, '', $vars1, $storeId1);
	//			   $translate->setTranslateInline(true);
          // echo "Email has been sent successfully";
		
		
		//echo 'entered'.$awbNumber;exit;
						
		


			
		//die();
	//	$session->addSuccess($this->__('Pick up request has been succesfully generated for this order:'.$_shipmentId.'. Your Pickup reference number is '.$results->ProcessedPickup->ID));
	 	$shipment->save();
        $session->addSuccess($this->__('Tracking ID has been added. Please take two printouts of the manifest and keep one copy signed by courier boy with you as proof of pick up. '));
        $highlight['tracking'] = true;
		$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$awbNumber.'Courier Partner : www.aramex.com - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
			$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
			$parse_url = file($_customerSmsUrl);
		}
		else{
		$session->addError($this->__('Tracking ID Not Generated'));
		}
		}
//Aramex code Ends

//Fedex code start
if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' && $status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Fedex'){

	$awbNumberFedex = $hlp->fedexawbgenerate('Fedex',$id);
	if(!empty($awbNumberFedex))
		{
			$shipment->setUdropshipStatus($status);
		$title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            		$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumberFedex)
                    	->setCarrierCode($method[0])
                	->setCourierName('Fedex')
                    	->setTitle('Fedex');
         $shipment->addTrack($track);
		 Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s addeddd tracking ID VD11 %s ', $vendor->getVendorName(), $awbNumberFedex)
                );
		$session->addSuccess($this->__('Tracking ID has been added. Please take three printouts of each manifest and keep one copy of airway bill signed by courier boy with you as proof of pick up.  '));
        $highlight['tracking'] = true;
		$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$awbNumberFedex.'Courier Partner : www.fedex.com - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
	 	$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
		$shipment->save();
	}
	else{
	$session->addError($this->__('Tracking ID Not Generated'));
	}
	}
// Fedex Code Ends
				
            }
			$comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($shipment->getAllItems() as $item) {
                    	if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('udropship')->sendVendorComment($shipment, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {
                                    
                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess($this->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess($this->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) {
                        // doesn't support voiding
                    }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $this->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    #$save = true;
                    $highlight['tracking'] = true;
                    $session->addSuccess($this->__('Track %s was deleted', $track->getNumber()));
                } else {
                    $session->addError($this->__('Track %s was not found', $track->getNumber()));
                }
            }
            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
		/* Craftsvilla Comment - By Amit Pitre On 12 Jun-2012 To set feedback reminder entry and shippment payout when shippment status changed to 'Shipped To Customer' */
		Mage::dispatchEvent(
                'craftsvilla_shipment_status_save_after',
                array('shipment'=>$shipment)
            );
		////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if($testcodPayment->getMethodInstance()->getTitle() == 'Cash On Delivery' )
		{
			if(!empty($checkTrack12[0]['number']))
			{
				$session->addError($this->__('Tracking Id Already Exists'));
			}
			$this->_forward('codordersinfo');
		
		}
		else
		{
			$this->_forward('shipmentInfo');
		}
	
		}
//for aramax packing slip		
		public function printLabelAction(){
			$shipmentId = $this->getRequest()->getParam('shipment_id');
			$_shipmentOrder = Mage::getModel('sales/order_shipment')->load($shipmentId);
			$_shipmentOrder->getIncrementId();
			$orderIda = $_shipmentOrder->getOrderId();
			/*echo'<pre>';
			print_r($_shipmentOrder);
			exit;*/
			$_order = Mage::getModel('sales/order')->load($orderIda);
			//$previuosUrl=Mage::getSingleton('core/session')->getPreviousUrl();exit;
			
			if($_order->getId()){
				$baseUrl = Mage::helper('courier')->getWsdlPath();
				$soapClient = new SoapClient($baseUrl . 'shipping-services-api-wsdl.wsdl');
				$clientInfo = Mage::helper('courier')->getClientInfo();	
				$commentTable= Mage::getSingleton('core/resource')->getTableName('sales/shipment_comment');
				/*$shipments = Mage::getResourceModel('sales/order_shipment_collection')
				->addAttributeToSelect('*')	
				//->addFieldToFilter("order_id",$_order->getId())->join("sales/shipment_comment",'main_table.entity_id=parent_id','comment')->load();
				->addFieldToFilter("order_id",$_order->getId())->join("sales/shipment_comment",'main_table.entity_id=parent_id','comment')->addFieldToFilter('comment', array('like'=>"%{added}%"))->load();
				echo $shipments->getSelect()->__toString();exit;
				echo '<pre>';print_r($shipments->getData());exit;
				if($shipments->count()){
				foreach($shipments as $key=>$comment){
					echo $awbno=strstr($comment->getComment(),"- Order No",true);exit;
					$awbno=trim($awbno,"AWB No.");
					break;
				}exit;*/
				$shipments = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipmentId)->getData();
				$awbno = $shipments[0]['number'];
				
				if($shipments){
				$params = array(		
			
				'ClientInfo'  			=> $clientInfo,

				'Transaction' 			=> array(
											'Reference1'			=> $_shipmentOrder->getIncrementId(),
											'Reference2'			=> '', 
											'Reference3'			=> '', 
											'Reference4'			=> '', 
											'Reference5'			=> '',									
										),
				'LabelInfo'				=> array(
											'ReportID' 				=> 9729,
											'ReportType'			=> 'URL',
				),
				);
				$params['ShipmentNumber']=$awbno;
				//print_r($params);	
				try {
					$auth_call = $soapClient->PrintLabel($params);
					$filepath=$auth_call->ShipmentLabel->LabelURL;
					$name="{$_shipmentOrder->getIncrementId()}-shipment-label.pdf";
					header('Content-type: application/pdf');
					header('Content-Disposition: attachment; filename="'.$name.'"');
					readfile($filepath);
					exit();					
				} catch (SoapFault $fault) {					
					Mage::getSingleton('adminhtml/session')->addError('Error : ' . $fault->faultstring);
					$this->_redirect('udropship/vendor/codorders');
				}
				}else{
					Mage::getSingleton('adminhtml/session')->addError($this->__('Shipment is empty or not created yet.'));
					$this->_redirect('udropship/vendor/codorders');
				}
			}else{
				Mage::getSingleton('adminhtml/session')->addError($this->__('This order no longer exists.'));
				$this->_redirect('udropship/vendor/codorders');
			}
		}
		
		
		public function generatecontent1Action()
		{

			$sellercontent = $this->getRequest()->getParam('sellercontent');
			$session = Mage::getSingleton('udropship/session');
			$vendorid = $session->getVendorId();
			$_target = Mage::getBaseDir('media') . DS . 'noticeboardimages' . DS;
			$targetimg = Mage::getBaseUrl('media').'noticeboardimages/';
			$name = $_FILES['import_image']['name'];
			$source_file_path = basename($name);
			$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
			$path = $_target.$newfilename;
			$path1 = $targetimg.$newfilename;
			$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
			file_put_contents($path, file_get_contents($_FILES['import_image']['tmp_name']));
			
			$csvObject = new Varien_File_Csv();
			$csvData = $csvObject->getData($path);
			$count = sizeof($csvData);
			$info = pathinfo($_FILES['import_image']['name']);
			$notice = Mage::getModel('noticeboard/noticeboard');
			$notice->setContent($sellercontent)
			       ->setType(4)
				   ->setStatus(2)
				   ->setVendor($vendorid)
				   ->setCreated(NOW())
				   ->setImage($pathhtml)
				   ->save();
				   
	    	$session->addSuccess($this->__('Your post has been successfully submitted and will be live after approval.'));  
			
			$this->_redirect('udropship/vendor/noticeboard');
			
			}
			
        public function downloadinvoiceAction()
		{
			$session = Mage::getSingleton('udropship/session');
			
			$vendorid = $session->getVendorId();
			
			$data = $this->getRequest()->getParams();
			$statement = Mage::getModel('udropship/vendor_statement')->load($vendorid);
			//echo '<pre>'; print_r($statement); exit;
			$selectedMonth = $this->getRequest()->getParam('selectmonth');
			$date = explode(" ",$selectedMonth);
			$month = $date[0];
			$yr = $date[1].'-';
			$year = $date[1];
			$month_number = date("n",strtotime($month));
	         $_month = substr($month,0,3);
			$statementQuery =  Mage::getSingleton('core/resource')->getConnection('core_read');	
			

$selectedMonthData =$statementQuery->fetchAll("SELECT `statement_filename`,`order_date_from`, `order_date_to`, `vendor_id` FROM `udropship_vendor_statement` where MONTH(`order_date_from`) = '".$month_number."' AND YEAR(`order_date_from`) = '".$year."' AND  vendor_id= '".$vendorid."' ORDER BY `statement_id` DESC");

//print_R($selectedMonthData); exit;
			  
			foreach($selectedMonthData as $_selectedMonthData)       
			{
		
				$filename = $_selectedMonthData['statement_filename'];
				$targetimg = Mage::getBaseUrl('media').'statementreport/'.$vendorid .'/';
				$file=$targetimg.$filename;
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.basename($file).'"');
				//header('Content-Length: ' . filesize($file));
				if(!readfile($file)):
				 $session->addError($this->__('Sorry we could not find data for this month. Please contact craftsvilla finance team at finance@craftsvilla.com'));
				 $this->_redirect('*/vendor/statement');
				endif;
				exit();

		   }
		$session->addError($this->__('Sorry we could not find invoice for this month. Please contact craftsvilla finance team at finance@craftsvilla.com'));
		  $this->_redirect('*/vendor/statement');
		}
		
public function disputecustmessageAction()
	{
		$id = $this->getRequest()->getParam('id');
		$session = Mage::getSingleton('udropship/session');
		$_custnote = $this->getRequest()->getParam('custnote');
		if($_custnote == '')
			{
			 Mage::getSingleton('core/session')->addError($this->__('You have not entered any note below. Please enter text in the note to customer'));
             $this->_redirect('udropship/vendor/disputeraised');
			}
		else
			{
		
		$_custnote1 = '<table border="0" width="750"><tr><td style="font-size: 17px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;">'.$_custnote.'</td></table>';
		
		$vendor = Mage::getModel('udropship/vendor');
		$shipment = Mage::getModel('sales/order_shipment');
		$shipmentid = $shipment->getIncrementId();
		$storeId = Mage::app()->getStore()->getId();
		$shipmentData = $shipment->load($id);
		$vendorDataCust = $vendor->load($shipmentData->getUdropshipVendor());
		$vendorname = $vendorDataCust->getVendorName();
		$vendorEmail = $vendorDataCust->getEmail();
		$vendorName = $vendorDataCust->getVendorName();
		$_target = Mage::getBaseDir('media') . DS . 'emailcommunication' . DS;
			$targetimg = Mage::getBaseUrl('media').'emailcommunication/';
			$name = $_FILES['file']['name'];
			$source_file_path = basename($name);
			$newfilename = mt_rand(10000000, 99999999).'_image.jpg';
			$path = $_target.$newfilename;
			$path1 = $targetimg.$newfilename;
			$pathhtml = '<a href="'.$path1.'" target="_blank"><img src="'.$path1.'" width="90px" height="80px"/></a>';
			file_put_contents($path, file_get_contents($_FILES['file']['tmp_name']));
		$customerData = Mage::getModel('sales/order')->load($shipmentData->getOrderId());
		
		$custname = $customerData->getCustomerFirstname().' '.$customerData->getCustomerLastname();
		$orderEmail = $customerData->getCustomerEmail();
		$customer = Mage::getModel('customer/customer')->getCollection()->addFieldToFilter('email',$orderEmail);
		foreach($customer as $_customer)
		{
		  $custid = $_customer['entity_id'];
		}
              
		
		  $model = Mage::getModel('disputeraised/disputeraised');
		  $model->setIncrementId($shipmentData->getIncrementId())
		        ->setCustomerId($custid)
		        ->setVendorId($shipmentData->getUdropshipVendor())
		        ->setImage($pathhtml)
		        ->setContent($_custnote)
		        ->setAddedby($vendorname)
		        ->setStatus(4)
		        ->setCreatedAt(NOW())
		        ->setUpdatedAt(NOW())
		        ->save();

		        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
		        $disputestatus = "update `disputeraised` set `status` = 4 where `increment_id` = '".$shipmentData->getIncrementId()."'";
				$writequery = $write->query($disputestatus);	
		 
	$incmntId = $shipmentData->getIncrementId();
		$currencysym = Mage::app()->getLocale()->currency($customerData->getBaseCurrencyCode())->getSymbol();
		$_items = $shipment->getAllItems();
		$vendorShipmentItemHtml .= "<table border='0' width='750px'><tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Image</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>Shipment Id</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Product Name</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>Price</td></tr>";
		foreach ($_items as $_item)
				{
					$product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
				$image="<img src='".Mage::helper('catalog/image')->init($product, 'image')->resize(154, 154)."' alt='' width='154' border='0' style='float:left; border:2px solid #ccc; margin:0 20px 20px;' />";				
				 $vendorShipmentItemHtml .= "<tr><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$image."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'><a href='www.craftsvilla.com/marketplace' style='color:#CE3D49;'>".$incmntId."</a></td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$_item->getName()."</td><td style='font-size: 13px;height: 26px;padding: 11px;vertical-align:top;background:#F2F2F2;color:#CE3D49;'>".$currencysym .$_item->getPrice()."</td></tr>";
				}
		$vendorShipmentItemHtml .= "</table>";	
		 $authnumber = mt_rand(100000,999999);
		 $read = Mage::getSingleton('core/resource')->getConnection('core_read');
		 $readquery = "select * from `craftsvilla_auth` where `reference_number`=".$shipmentData->getIncrementId();
		 $readqueryresult = $read->query($readquery)->fetch();
		 	
			$insertorderquery = "INSERT INTO `craftsvilla_auth` (`log_id`, `authid`, `reference_number`) VALUES ('', '" . $authnumber . "', '" . $shipmentData->getIncrementId() . "')";
			$write->query($insertorderquery);
		 
		 
			$readlog = "select `log_id` from `craftsvilla_auth` where `reference_number`=".$shipmentData->getIncrementId();
			$readlogresult = $read->query($readlog)->fetch(); 
			$logid = $readlogresult['log_id'];
	$urlaction1 = Mage::getBaseUrl().'umicrosite/vendor/closedispute?q='.$shipmentData->getIncrementId().'&authid='.$authnumber.'&log_id='.$logid;
	$disputeHtml = '';
   $disputeHtml .= '<a href ="'.$urlaction1.'"><button style ="text-decoration:none; text-align:center; padding:5px 10px; border:solid 1px #004F72;  -webkit-border-radius:4px; -moz-border-radius:4px;  border-radius: 4px;  font:18px Arial, Helvetica, sans-serif;  font-weight:bold;  color:#E5FFFF;  background-color:#3BA4C7;  background-image: -moz-linear-gradient(top, #3BA4C7 0%, #1982A5 100%);  background-image: -webkit-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -o-linear-gradient(top, #3BA4C7 0%, #1982A5 100%); background-image: -ms-linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);  background-image: linear-gradient(top, #3BA4C7 0% ,#1982A5 100%);-webkit-box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff; -moz-box-shadow: 0px 0px 2px #bababa,  inset 0px 0px 1px #ffffff;box-shadow:0px 0px 2px #bababa, inset 0px 0px 1px #ffffff;"  type="button" name="accept" value="Accept">Close Dispute</button></a>';	
		$storeId = Mage::app()->getStore()->getId();
		$templateId = 'customerdisputenote_email_template';
		$sender = Array('name'  => 'Craftsvilla',
						'email' => 'customercare@craftsvilla.com');
		$_email = Mage::getModel('core/email_template');
		$model1  = Mage::getModel('disputeraised/disputeraised')->getCollection()->addFieldToFilter('increment_id',$shipmentData->getIncrementId())->setOrder('id', 'DESC');;
		$html = '';
		$html .= '<div><table style="border-collapse: collapse;border: 1px solid black;"><tr><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Image</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Content</font></th><th style="background:#7599AB;border-collapse: collapse;border: 1px solid black;"><font color="#FFFFFF">Added By</font></th></tr>';
		foreach ($model1 as $_model)
		{
            $incrementid = $_model->getIncrementId();
           
            $html .= '<tr><td style="border-collapse: collapse;border: 1px solid black;">'.$_model['image'].'</td><td width="600px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['content'].'</td><td width="250px" style="border-collapse: collapse;border: 1px solid black;">'.$_model['addedby'].'</td></tr>';
		}
		 $html .= '</table></div>';
		
		$vars = Array('shipmentId' => $shipmentData->getIncrementId(),
					'shipmentDate' => date('jS F Y',strtotime($shipmentData->getCreatedAt())),
					'customerName' =>$customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(),
					'vendorNote' => $_custnote1,
					'imagecust' => $pathhtml,
					'vendorName' =>$vendorName,
					'vendorItemHTML' =>$vendorShipmentItemHtml,
		            'disputeHtml' => $disputeHtml,  
						'html' => $html, 
				);
			//print_r($vars);exit;	
		//$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
				//->setReplyTo($vendorEmail)
			
		//$_email->sendTransactional($templateId, $sender, 'gsonar8@gmail.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, $orderEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, $vendorEmail, $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		//$_email->sendTransactional($templateId, $sender, 'customercare@craftsvilla.com', $customerData->getCustomerFirstname()." ".$customerData->getCustomerLastname(), $vars, $storeId);
		
		$session->addSuccess($this->__('Email Sent To Customer Sucessfully for your shipment :'.$shipmentData->getIncrementId()));
		        $this->_redirect('udropship/vendor/disputeraised');
		 }
	}
	
public function autoacceptcodAction(){
		
		$session = Mage::getSingleton('udropship/session');
		$vendorid = $session->getVendorId();
		$getValue = $this->getRequest()->getParam('auto_accept_option');
		$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
		if($getValue == 0){
			$queryOautoacceptid = "update `udropship_vendor` set `auto_accept`='".$getValue."'  WHERE `vendor_id`= '".$vendorid."'";
			$write->query($queryOautoacceptid);
			$session->addSuccess($this->__('Successfuly Disabled'));
			}
		else{
			$queryOautoacceptid = "update `udropship_vendor` set `auto_accept`='".$getValue."'  WHERE `vendor_id`= '".$vendorid."'";
			$write->query($queryOautoacceptid);
			$session->addSuccess($this->__('Successfuly Enabled '));
			}

		$this->_redirect('udropship/vendor/codorders');
		}

    public function bulkuploaddeletecsvAction(){
		
			$session = Mage::getSingleton('udropship/session');
		    $vendorid = $session->getVendorId();
		    $bulkuploadid = $this->getRequest()->getParam('deleteid');
		    $write = Mage::getSingleton('core/resource')->getConnection('bulkuploadcsv_write');	
		    $bulkuploaddeletequery = "delete from `bulkuploadcsv`  WHERE `bulkuploadid`= '".$bulkuploadid."'";
			$write->query($bulkuploaddeletequery);
			$session->addSuccess($this->__('Your bulkupload id: '.$bulkuploadid.' has been successfully deleted'));
			$this->_redirect('udropship/vendor/bulkuploadcsv');
		}
		
	public function bulkinventoryupdatedeletecsvAction(){
		
			$session = Mage::getSingleton('udropship/session');
		    $vendorid = $session->getVendorId();
		    $bulkinventoryid = $this->getRequest()->getParam('inventoryupdatedeleteid');
		    $write = Mage::getSingleton('core/resource')->getConnection('bulkinventoryupdate_write');	
		    $bulkinventorydeletequery = "delete from `bulkinventoryupdate`  WHERE `bulkinventoryupdateid`= '".$bulkinventoryid."'";
			$write->query($bulkinventorydeletequery);
			$session->addSuccess($this->__('Your bulkinventory update id: '.$bulkinventoryid.' has been successfully deleted'));
			$this->_redirect('udropship/vendor/bulkinventoryupdate');
		}
		
	public function uploadkycAction()
    {
     	$session = Mage::getSingleton('udropship/session');
       $hlp = Mage::helper('udropship');
       $vendorid = $session->getVendorId();
       $_target = Mage::getBaseDir('media') . DS . 'vendorKYC' . DS . $vendorid .DS;
	   $panpath = $_target.'PAN.jpg';
	   $image_sourcepan = $_FILES["panfile"]["name"];
		//file_put_contents($panproofpath, file_get_contents($panproof));
		$uploader = new Varien_File_Uploader('panfile');
	   $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
        $uploader->setAllowRenameFiles(true);
        $uploader->save($panpath,$image_sourcepan);
       
      
		$image_sourceaddress = $_FILES["addressfile"]["name"];
		$_targetpath = Mage::getBaseDir('media') . DS . 'vendorKYC' . DS . $vendorid . DS;
		$addressproof = $_targetpath.'Addressproof.jpg';
		
		//imagejpeg($image_sourceaddress,$addressproof,100) ;
	  //file_put_contents($addressproof, file_get_contents($image_sourceaddress));
			$uploader1 = new Varien_File_Uploader('addressfile');
			$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader1->setAllowRenameFiles(true);
         $uploader1->save($addressproof, $image_sourceaddress);
     
        
      if(($_FILES['panfile']['name']=='') || (empty($_FILES['panfile']['name'])))
       	{
       		$session->addError('Please upload PAN Card');
       	}
       if(($_FILES['addressfile']['name']=='')|| (empty($_FILES['addressfile']['name'])))
       	{
       		$session->addError('Please upload Address Proof');
       	}
       if(($_FILES['panfile']['size']> 2097152) && ($_FILES['addressfile']['size']>2097152))
       	{
       	
       	  $session->addError('Image size should be less than 2MB');
       	}
       	else
       	{
       	$session->addSuccess('KYC Form Successfully Submitted');
       	}
    
     $this->_redirect('udropship/vendor/preferences');
    }
	
public function penaltychargesAction(){
		$this->_renderPage(null, 'penaltycharges');
	}
	public function internationalordersAction(){
		$this->_renderPage(null,'internationalorders');
	}
public function internationShipmentAction()
	{
		$hlp = Mage::helper('udropship');
		$r = $this->getRequest();
        $id = $r->getParam('id');
        $session = $this->_getSession();
        $storeId = Mage::app()->getStore()->getId();
        $shipment = Mage::getModel('sales/order_shipment')->load($id);
        $_shipmentId = $shipment->getIncrementId();
        $baseTotalValue = $shipment->getBaseTotalValue();
       $_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
		$vendor = $hlp->getVendor($shipment->getUdropshipVendor());
		$_order = $shipment->getOrder();
		$_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();
		$customerTelephone = $_order->getBillingAddress()->getTelephone();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		if (!$shipment->getId()) {
            return;
        }
		try {
			$store = $shipment->getOrder()->getStore();	
			$track = null;
            $highlight = array();
            
            $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');
 
            $printLabel = $r->getParam('print_label');
            $number = $r->getParam('tracking_id');
            $courier_name = $r->getParam('courier_name');
            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusShipped = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED;
            $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
			$statusAccepted = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_ACCEPTED;
			$statusOutofstock = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_OUTOFSTOCK_CRAFTSVILLA ;
		$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
		
      	if($number && $courier_name) {
             // if tracking id was added manually
                $method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setNumber($number)
                    ->setCarrierCode($method[0])
                	->setCourierName($courier_name)
                    ->setTitle($title);
            	$shipment->addTrack($track);
				Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);
				Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $number)
                );
                $shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));
				$highlight['tracking'] = true;
				$this->_forward('internationalordersinfo');
            }
            			
				//condition for penalaty charge for 2%[4%] on out of stock case 
				if($status == $statusOutofstock)
					{
					$templateId = 'refund_in_outofstock_email_template';
					$sender = Array('name'  => 'Craftsvilla',
						'email' => 'places@craftsvilla.com');
					$_email = Mage::getModel('core/email_template');
	
					$penaltyAmount = ($baseTotalValue*0.04);
					
					$oldAdjustAmount = Mage::getModel('shipmentpayout/shipmentpayout')->getCollection()->addFieldToFilter('shipment_id',$_shipmentId);
					
					foreach($oldAdjustAmount as $_oldAdjustAmount){ $amntAdjust = $_oldAdjustAmount['adjustment']; }
					
					$amntAdjust = $amntAdjust-$penaltyAmount;
					$read = Mage::getSingleton('core/resource')->getConnection('udropship_read');	
					$getClosingblncQuery = "SELECT `vendor_name`,`closing_balance` FROM `udropship_vendor` where `vendor_id` = '".$vendor->getId()."'";
					$getClosingblncResult = $read->query($getClosingblncQuery)->fetch();
					$closingBalance = $getClosingblncResult['closing_balance'];
					$vendorName = $getClosingblncResult['vendor_name'];
					$closingBalance = $closingBalance-$penaltyAmount; 
					
					$write = Mage::getSingleton('core/resource')->getConnection('core_write');	
					
					$queryUpdateForAdjustment = "update shipmentpayout set adjustment='".$amntAdjust."' , `comment` = 'Adjustment Against Out Of Stock'  WHERE shipment_id = '".$_shipmentId."'";
					$write->query($queryUpdateForAdjustment);
					
					$queryUpdateForClosingbalance = "update `udropship_vendor` set `closing_balance`='".$closingBalance."'  WHERE `vendor_id`= '".$vendor->getId()."'";
					$write->query($queryUpdateForClosingbalance);
					$vars = array('penaltyprice'=>$penaltyAmount,
								  'shipmentid'=>$_shipmentId,
								  'vendorShopName'=>$vendorName
								);
					$_email->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
							->sendTransactional($templateId, $sender,$vendor->getEmail(), '', $vars, $storeId);
					$session->addSuccess($this->__('Shipment status has been changed to out of stock and charged penalty of Rs.'.$penaltyAmount));							
					}
			
			$status = $r->getParam('status');
			$isShipped = $status == $statusShipped || $status == $statusAccepted || $status==$statusDelivered || $autoComplete && ($status==='' || is_null($status));
			if($status == '')
			{
			$this->_forward('internationalordersinfo');
			}
			
			// if tracking id added manually and new status is not current status
            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {	
            	$check = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
			
				if(($check[0]['number'] == '') && ($_address->getCountryId() == 'IN' && $status != '18' && $status != '6')){
					if($r->getParam('tracking_id') == '' && $r->getParam('courier_name') == ''){
            			$session->addError($this->__('Please enter Tracking id and Courier name'));
            			$this->_forward('shipmentInfo');
            		}
				} 
				
            	else{
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusShipped || $oldStatus==$statusDelivered)
                    && $status!=$statusShipped && $status!=$statusDelivered && $hlp->isUdpoActive()
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                } elseif ($oldStatus==$statusCanceled && $hlp->isUdpoActive()) {
                    Mage::throwException(Mage::helper('udpo')->__('Canceled shipment cannot be reverted'));
                }
                $changedComment = $this->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                $triedToChangeComment = $this->__('%s tried to change the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                if ($status==$statusShipped || $status==$statusDelivered) {
                	$hlp->completeShipment($shipment, true, $status==$statusDelivered);
                    $hlp->completeOrderIfShipped($shipment, true);
                    $hlp->completeUdpoIfShipped($shipment, true);
                    
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }elseif ($status == $statusShippedCraftsvilla) { 
                	$hlp->completeShipment($shipment, true, $status == $statusShippedCraftsvilla);
                	Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }
                elseif ($status == $statusCanceled && $hlp->isUdpoActive()) {
                    if (Mage::helper('udpo')->cancelShipment($shipment, true)) {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $changedComment
                        );
                        Mage::helper('udpo')->processPoStatusSave(Mage::helper('udpo')->getShipmentPo($shipment), Unirgy_DropshipPo_Model_Source::UDPO_STATUS_PARTIAL, true, $vendor);
                    } else {
                        Mage::helper('udropship')->addShipmentComment(
                            $shipment,
                            $triedToChangeComment
                        );
                    } 
                } 
                else { 
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
                }
                $this->_forward('internationalordersinfo');
                $shipment->getCommentsCollection()->save();
                $session->addSuccess($this->__('Shipment status has been changed'));
               
              }
			  
            }
            
            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {
                                    
                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess($this->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess($this->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) { }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $this->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    $highlight['tracking'] = true;
                    $session->addSuccess($this->__('Track %s was deleted', $track->getNumber()));
                    $this->_forward('internationalordersinfo');
                } else {
                    $session->addError($this->__('Track %s was not found', $track->getNumber()));
                }
            }
            $session->setHighlight($highlight);
        }
        catch (Exception $e) {
            $session->addError($e->getMessage());
        }
            
        if(!$statusOutofstock){
	$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		if($_orderBillingCountry !== 'IN')
			{
			$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$number.'Courier Name :'.$courier_name.' - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
			$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
			$parse_url = file($_customerSmsUrl);			
			}
		
	}       
			
        	
}
public function internationalordersenabledisableAction()
	{
	
		$session = Mage::getSingleton('udropship/session');
		$hlp = Mage::helper('udropship');
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $vendor = $session->getVendor();
        $vendorid = $session->getVendorId(); 
        $shipmentdet = Mage::getSingleton('udropship/vendor')->load($vendorid);
        
        $countryid = $shipmentdet->getCountryId();
        $ioQuery = "SELECT * FROM `vendor_info_craftsvilla` WHERE `vendor_id` = '".$vendorid."'";
		$rquery = $read->query($ioQuery)->fetch();
		$iostatus = $rquery['international_order'];
		try {
		if($iostatus == 0) {
		$iostatus = 1;
			$enableiostatus ="UPDATE `vendor_info_craftsvilla` SET `international_order` = '".$iostatus."' WHERE vendor_id = '".$vendorid."'";
			$read->query($enableiostatus);
			
		} else{
		$iostatus = 0;
			$disableiostatus ="UPDATE `vendor_info_craftsvilla` SET `international_order` = '".$iostatus."' WHERE vendor_id = '".$vendorid."'";
			$read->query($disableiostatus);
		
		}
		
		if($iostatus == 1)
        {
        $session->addSuccess($hlp->__('International Orders Is Now Enabled For You!'));
        
		}	
		else {
		$session->addSuccess($hlp->__('International Orders  Is Now Disabled For You!'));
			
			 }
		
		} catch (Exception $e) {
			$session->addError($e->getMessage());
		}
		$this->_redirect('udropship/vendor/internationalorders');	
		
	}

public function internationalordersInfoAction(){
    
    $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }

public function pickupreferenceAction(){
		
	$this->_renderPage(null, 'pickupreference');
	}

public function sellerfaqAction(){
		$this->_renderPage(null, 'sellerfaq');
	}

//added on dated 15-07-2015 

public function canceledshipmentsAction(){
		$this->_renderPage(null, 'canceledshipments');
	}
	
	public function canceledshipmentsInfoAction()
    {
		
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }
    
    
     public function canceledVendorShipmentsAction() {
 
 		$hlp = Mage::helper('udropship');

        $r = $this->getRequest();
        $id = $r->getParam('id');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery');
		$storeId = Mage::app()->getStore()->getId();
		$shipment1 = Mage::getModel('sales/order_shipment');		
		$shipment = $shipment1->load($id);
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
		
		//get the shipment id for sms
		$_shipmentId = $shipment->getIncrementId();
		$baseTotalValue = $shipment->getBaseTotalValue();
		$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
		
        $session = $this->_getSession();
		
		$_order = $shipment->getOrder();
		$_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();
//Get the customer details For aramex pick up request
		$order = Mage::getModel('sales/order')->load($shipment->getOrderId());
		
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		//print_r($address);exit;
		$street = $address['street'];
		$city = $address['city'];
		$name = $address['firstname'].' '.$address['lastname'];
		$postcode = $address['postcode'];
		$selectCourier = $hlp->getCurrentCourier($vendorPostcode,$postcode);

		//echo 'e1';echo $selectCourier; 
		
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		$customerTelephone = $_order->getBillingAddress()->getTelephone();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
		$testcodPayment = $order->getPayment();
			
		if (!$shipment->getId()) {
            return;
        }
		try {
			$store = $shipment->getOrder()->getStore();
		    $track = null;
            $highlight = array();
		    $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');
		    $printLabel = $r->getParam('print_label');
            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusCanceled = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_CANCELED;
			$statusAccepted = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_ACCEPTED;
			
			$statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
           // if label was printed
            if ($printLabel) {
                $status = $r->getParam('is_shipped') ? $statusShipped : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } 
			else { // if status was set manually
                $status = $r->getParam('status');
                
				$isShipped = $status == $statusAccepted || $autoComplete && ($status==='' || is_null($status));
				
			}
            
		$shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {
            	$check = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
				
                $oldStatus = $shipment->getUdropshipStatus();
                if ($oldStatus==$statusAccepted)
               	{
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                }
                $changedComment = $this->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
               
                $shipment->getCommentsCollection()->save();
                $session->addSuccess($this->__('Shipment status has been changed To Accepted'));
                $this->_forward('canceledshipmentsinfo');
               
            // Tracking ID generation
			$checkTrack12 = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
			$trckentId = $checkTrack12[0]['entity_id'];
			
			
	if($status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Delhivery')	
				{
				$awbNumber = $hlp->fetchawbgenerate('Delhivery');
				$method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            				
					$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumber)
                    	->setCarrierCode($method[0])
                		->setCourierName('Delhivery')
                    	->setTitle($title);
                
					$shipment->addTrack($track);
				
			//  Mage::helper('udropship')->processTrackStatus($track, true, $isShipped);

                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $awbNumber)
                );
                
				$shipment->save();
                $session->addSuccess($this->__('Tracking ID has been added'));

                $highlight['tracking'] = true;
            
				}
	
if($status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Aramex')
				{
			 $awbNumber = $hlp->aramaxawbgenerate('Aramex',$id);
			 
			 
				$method = explode('_', $shipment->getUdropshipMethod(), 2);
                $title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            		$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumber)
                    	->setCarrierCode($method[0])
                		->setCourierName('Aramex')
                    	->setTitle('Aramex');
   
					$shipment->addTrack($track);
				
	
                Mage::helper('udropship')->addShipmentComment(
                    $shipment,
                    $this->__('%s added tracking ID %s', $vendor->getVendorName(), $awbNumber)
                );
     
	 	$shipment->save();
        $session->addSuccess($this->__('Tracking ID has been added. Please take two printouts of the manifest and keep one copy signed by courier boy with you as proof of pick up. '));
        $highlight['tracking'] = true;
		$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$awbNumber.'Courier Partner : www.aramex.com - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
			$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
			$parse_url = file($_customerSmsUrl);
		}
if($status == $statusAccepted && ($checkTrack12[0]['number']=='') && $selectCourier == 'Fedex'){
 
		$awbNumberFedex = $hlp->fedexawbgenerate('Fedex',$id);
		 //echo '<pre>';print_r($awbNumberFedex);exit;
		$title = Mage::getStoreConfig('carriers/'.$method[0].'/title', $store);
            		$track = Mage::getModel('sales/order_shipment_track')
                    	->setNumber($awbNumberFedex)
                    	->setCarrierCode($method[0])
                	->setCourierName('Fedex')
                    	->setTitle('Fedex');
		$shipment->addTrack($track);
		 Mage::helper('udropship')->addShipmentComment(
                    $shipment,

$this->__('%s added tracking ID %s', $vendor->getVendorName(), $awbNumberFedex)
                );
		

		$session->addSuccess($this->__('Tracking ID has been added. Please take three printouts of each manifest and keep one copy of airway bill signed by courier boy with you as proof of pick up.  '));
        $highlight['tracking'] = true;
		$customerMessage = 'Your order has been shipped. Tracking Details.Shipment#: '.$_shipmentId.' , Track Number: '.$awbNumberFedex.'Courier Partner : www.fedex.com - Craftsvilla.com (Customercare email: customercare@craftsvilla.com)';
	 	$_customerSmsUrl = $_smsServerUrl."username=".$_smsUserName."&password=".$_smsPassowrd."&type=0&dlr=0&destination=".$customerTelephone."&source=".$_smsSource."&message=".urlencode($customerMessage);
		$parse_url = file($_customerSmsUrl);
		$shipment->save();


	}
		    }
			$comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($shipment->getAllItems() as $item) {
                    	if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('udropship')->sendVendorComment($shipment, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }

            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {
                                    
                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess($this->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess($this->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) {
                        // doesn't support voiding
                    }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $this->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    #$save = true;
                    $highlight['tracking'] = true;
                    $session->addSuccess($this->__('Track %s was deleted', $track->getNumber()));
                    $this->_forward('canceledshipmentsinfo');
                } else {
                    $session->addError($this->__('Track %s was not found', $track->getNumber()));
                }
            }
            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
		
		Mage::dispatchEvent(
                'craftsvilla_shipment_status_save_after',
                array('shipment'=>$shipment)
            );
		
			/*if(!empty($checkTrack12[0]['number']))
			{
				$session->addError($this->__('Tracking Id  Already Exists'));
			}
			$this->_forward('canceledordersinfo');*/
		
		    
 } 
 
 
 
 //------------------------------------------Returned Functions--------------------------------
 
  public function returnedshipmentsAction(){
		$this->_renderPage(null, 'returnedshipments');
	}
	
public function returnedshipmentsInfoAction()
    {
		
        $this->_setTheme();
        $this->loadLayout(false);

        $block = $this->getLayout()->getBlock('info');
        Mage::helper('udropship')->applyItemRenderers('sales_order_shipment', $block, '/checkout/', false);
        if (($url = Mage::registry('udropship_download_url'))) {
            $block->setDownloadUrl($url);
        }
        $this->_initLayoutMessages('udropship/session');

        $this->getResponse()->setBody($block->toHtml());
    }
public function returnedVendorShipmentsAction() {
		$hlp = Mage::helper('udropship');

        $r = $this->getRequest();
        $id = $r->getParam('id');
		$delhivery = Mage::getStoreConfig('courier/general/delhivery');
		$storeId = Mage::app()->getStore()->getId();
		$shipment1 = Mage::getModel('sales/order_shipment');		
		$shipment = $shipment1->load($id);
		//echo '<pre>';print_r($shipment);exit;
		
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
		
		//get the shipment id for sms
		$_shipmentId = $shipment->getIncrementId();
		$baseTotalValue = $shipment->getBaseTotalValue();
		$_shipmentUdropshipstatus = $shipment->getUdropshipStatus();
        $vendor = $hlp->getVendor($shipment->getUdropshipVendor());
		
        $session = $this->_getSession();
		
		$_order = $shipment->getOrder();
		$_address = $_order->getShippingAddress() ? $_order->getShippingAddress() : $_order->getBillingAddress();
//Get the customer details For aramex pick up request
		$order = Mage::getModel('sales/order')->load($shipment->getOrderId());
		
		$shippingId = $order->getShippingAddress()->getId();
		$address = Mage::getModel('sales/order_address')->load($shippingId);
		//print_r($address);exit;
		$street = $address['street'];
		$city = $address['city'];
		$name = $address['firstname'].' '.$address['lastname'];
		$postcode = $address['postcode'];
		$selectCourier = $hlp->getCurrentCourier($vendorPostcode,$postcode);

		//echo 'e1';echo $selectCourier; 
		
		$_smsServerUrl = Mage::getStoreConfig('sms/general/server_url');
		$_smsUserName = Mage::getStoreConfig('sms/general/user_name');
		$_smsPassowrd = Mage::getStoreConfig('sms/general/password');
		$_smsSource = Mage::getStoreConfig('sms/general/source');
		
		$customerTelephone = $_order->getBillingAddress()->getTelephone();
		$_orderBillingCountry = $_order->getBillingAddress()->getCountryId();
		$_orderBillingEmail = $_order->getBillingAddress()->getEmail();
		$testcodPayment = $order->getPayment();
			
		if (!$shipment->getId()) {
            return;
        }
		try {
			$store = $shipment->getOrder()->getStore();
		    $track = null;
            $highlight = array();
		    $partial = $r->getParam('partial_availability');
            $partialQty = $r->getParam('partial_qty');
		    $printLabel = $r->getParam('print_label');
            $notifyOn = Mage::getStoreConfig('udropship/customer/notify_on', $store);
            $pollTracking = Mage::getStoreConfig('udropship/customer/poll_tracking', $store);
            $autoComplete = Mage::getStoreConfig('udropship/vendor/auto_shipment_complete', $store);

            $statusReturnReceivedCustomer = Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURN_RECIEVED_FROM_CUSTOMER;
			//added By dileswar 13-10-2012
			
            $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
           // print_r($statuses); exit;
           // if label was printed
            if ($printLabel) {
                $status = $r->getParam('is_shipped') ? $statusShipped : Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_PARTIAL;
                $isShipped = $r->getParam('is_shipped') ? true : false;
            } 
			else { // if status was set manually
                $status = $r->getParam('status');
                
				$isShipped = $status==$statusReturnReceivedCustomer || $autoComplete && ($status==='' || is_null($status));
				
			}
           
            $shipmentStatuses = false;
            if (Mage::getStoreConfig('udropship/vendor/is_restrict_shipment_status')) {
                $shipmentStatuses = Mage::getStoreConfig('udropship/vendor/restrict_shipment_status');
                if (!is_array($shipmentStatuses)) {
                    $shipmentStatuses = explode(',', $shipmentStatuses);
                }
            }
            if (!$printLabel && !is_null($status) && $status!=='' && $status!=$shipment->getUdropshipStatus()
                && (!$shipmentStatuses || (in_array($shipment->getUdropshipStatus(), $shipmentStatuses) && in_array($status, $shipmentStatuses)))
            ) {
            	$check = Mage::getModel('sales/order_shipment_track')->getcollection()->addAttributeToFilter('parent_id',$shipment->getId())->getData();
				
                $oldStatus = $shipment->getUdropshipStatus();
                if (($oldStatus==$statusReturnReceivedCustomer)
                ) {
                    Mage::helper('udpo')->revertCompleteShipment($shipment, true);
                }
                $changedComment = $this->__('%s has changed the shipment status to %s', $vendor->getVendorName(), $statuses[$status]);
                
                    $shipment->setUdropshipStatus($status)->save();
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $changedComment
                    );
               
                $shipment->getCommentsCollection()->save();
                $session->addSuccess($this->__('Shipment status has been changed to Return Received from Customer.'));
                $this->_forward('returnedshipmentsinfo');
            }
			
			$comment = $r->getParam('comment');
            if ($comment || $partial=='inform' && $partialQty) {
                if ($partialQty) {
                    $comment .= "\n\nPartial Availability:\n";
                    foreach ($shipment->getAllItems() as $item) {
                    	if (!array_key_exists($item->getId(), $partialQty) || '' === $partialQty[$item->getId()]) {
                            continue;
                        }
                        $comment .= $this->__('%s x [%s] %s', $partialQty[$item->getId()], $item->getName(), $item->getSku())."\n";
                    }
                }

                Mage::helper('udropship')->sendVendorComment($shipment, $comment);
                $session->addSuccess($this->__('Your comment has been sent to store administrator'));

                $highlight['comment'] = true;
            }
            
            $deleteTrack = $r->getParam('delete_track');
            if ($deleteTrack) {
                $track = Mage::getModel('sales/order_shipment_track')->load($deleteTrack);
                if ($track->getId()) {
                                    
                    try {
                        $labelModel = Mage::helper('udropship')->getLabelCarrierInstance($track->getCarrierCode())->setVendor($vendor);
                        try {
                            $labelModel->voidLabel($track);
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% voided tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                            );
                            $session->addSuccess($this->__('Track %s was voided', $track->getNumber()));
                        } catch (Exception $e) {
                            Mage::helper('udropship')->addShipmentComment(
                                $shipment,
                                $this->__('% attempted to void tracking ID %s: %s', $vendor->getVendorName(), $track->getNumber(), $e->getMessage())
                            );
                            $session->addSuccess($this->__('Problem voiding track %s: %s', $track->getNumber(), $e->getMessage()));
                        }
                    } catch (Exception $e) {
                        // doesn't support voiding
                    }

                    $track->delete();
                    if ($track->getPackageCount()>1) {
                        foreach (Mage::getResourceModel('sales/order_shipment_track_collection')
                            ->addAttributeToFilter('master_tracking_id', $track->getMasterTrackingId())
                            as $_track
                        ) {
                            $_track->delete();
                        }
                    }
                    Mage::helper('udropship')->addShipmentComment(
                        $shipment,
                        $this->__('%s deleted tracking ID %s', $vendor->getVendorName(), $track->getNumber())
                    );
                    $shipment->getCommentsCollection()->save();
                    #$save = true;
                    $highlight['tracking'] = true;
                    $session->addSuccess($this->__('Track %s was deleted', $track->getNumber()));
                    $this->_forward('returnedshipmentsinfo');
                } else {
                    $session->addError($this->__('Track %s was not found', $track->getNumber()));
                }
            }
            $session->setHighlight($highlight);


            
            $session->setHighlight($highlight);
        } catch (Exception $e) {
            $session->addError($e->getMessage());
        }
		
		Mage::dispatchEvent(
                'craftsvilla_shipment_status_save_after',
                array('shipment'=>$shipment)
            );
		

	}

}
