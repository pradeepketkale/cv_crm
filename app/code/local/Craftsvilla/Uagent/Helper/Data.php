<?php

class Craftsvilla_Uagent_Helper_Data extends Mage_Core_Helper_Abstract
{
	protected $_currentAgent;
    protected $_origBaseUrl;
	protected $_agentOrderCollection;
	protected $_agentShipmentCollection;
    protected $_parsedBaseUrl;
    protected $_agentBaseUrl = array();
	
	public function resetCurrentAgent()
    {
        $this->_currentAgent = null;
        return $this;
    }
    protected $_gcvCycleCheck=false;
    public function getCurrentAgent()
    {
        if (is_null($this->_currentAgent) && !$this->_gcvCycleCheck) {
            $this->_gcvCycleCheck = true;
            if (Mage::app()->getStore()->isAdmin()) {
                if (($agent = $this->getAdminhtmlAgent())) {
                    $this->_currentAgent = $agent; // it's adminhtml (from user)
                } else {
                    $this->_currentAgent = false;
                }
            } elseif (($agent = $this->getFrontendAgent())) {
                $this->_currentAgent = $agent; // it's a frontend (from subdomain)
            
            } else {
                // if route known, make it permanent
                if (Mage::app()->getRequest()->getRouteName()) {
                    $this->_currentAgent = false;
                }
            }
            $this->_gcvCycleCheck = false;
        }
        return $this->_currentAgent;
    }
	public function getFrontendAgent()
    {
        return $this->_getFrontendAgent();
    }

	protected function _getFrontendAgent($useUrl=false)
    {
        $url = null;
        if (!$useUrl) {
            $this->_origBaseUrl = Mage::getStoreConfig('web/unsecure/base_link_url');
            $url = parse_url($this->_origBaseUrl);
            $this->_parsedBaseUrl = $url;
            $httpHost = $_SERVER["HTTP_HOST"];
        } else {
            $url = parse_url($useUrl);
            $httpHost =  $url['host'];
        }

        if (empty($httpHost)) {
            return false;
        }
        $level = '';
        if ($level==1) {
            $vUrlKey = $this->_getVendorKeyFromRequest($useUrl);
        } else {
            $host = $httpHost;
            $hostArr = explode('.', trim($host, '.'));
            $i = sizeof($hostArr)-$level;
            $vUrlKey = @$hostArr[$i];
        }

        if (empty($level) || empty($vUrlKey)) {
            return false;
        }

        if (!$useUrl) {
            if ($level>1 && $this->updateStoreBaseUrl()) {
                $baseUrl = $url['scheme'].'://'.$host.(isset($url['path']) ? $url['path'] : '/');
                Mage::app()->getStore()->setConfig('web/unsecure/base_link_url', $baseUrl);
            } else {
                //$this->_removeVendorKeyFromRequest();
            }
        }

        return $agent;
    }
	public function getAgent($id)
    {
        if ($id instanceof Craftsvilla_Uagent_Model_Uagent) {
            if (empty($this->_agents[$id->getId()])) {
                $this->_agents[$id->getId()] = $id;
            }
            return $id;
        }
        
        $agent = Mage::getModel('uagent/uagent');
        if (empty($id)) {
            return $agent;
        }
        if (empty($this->_agents[$id])) {
            if (!is_numeric($id)) {
                $agent->load($id, 'agent_name');
                if ($agent->getId()) {
                    $this->_agents[$agent->getId()] = $agent;
                }
            } else {
                $agent->load($id);
                if ($agent->getId()) {
                    $this->_agents[$agent->getAgentName()] = $agent;
                }
            }
            $this->_agents[$id] = $agent;
        }
        return $this->_agents[$id];
    }
	
	public function getAgentForgotPasswordUrl()
    {
        return Mage::getUrl('uagent/register/password');
    }
	
	public function getAgentRegisterUrl()
    {
        return Mage::getUrl('uagent/register/register');
    }
	 public function isSalesFlat()
    {
        return $this->hasMageFeature('sales_flat');
    }
	 public function compareMageVer($ceVer, $eeVer=null, $op='>=')
    {
        $eeVer = is_null($eeVer) ? $ceVer : $eeVer;
        return $this->isModuleActive('Enterprise_Enterprise')
            ? version_compare(Mage::getVersion(), $eeVer, $op)
            : version_compare(Mage::getVersion(), $ceVer, $op);
    }
	public function isModuleActive($code)
    {
        $module = Mage::getConfig()->getNode("modules/$code");
        $model = Mage::getConfig()->getNode("global/models/$code");
        return $module && $module->is('active') || $model;
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
    public function getAgentOrderCollection()
    {
        if (!$this->_agentOrderCollection) {
            $agentId = Mage::getSingleton('uagent/session')->getAgentId();
            $agent = Mage::helper('uagent')->getAgent($agentId);
            $collection = Mage::getModel('sales/order')->getCollection();
																
			/*echo '<pre>';
			print_r($collection->getData());exit;*/
            $sqlMap = array();
            if (!$this->isSalesFlat()) {
                $collection
                    ->addAttributeToSelect(array('entity_id','incrment_id','total_qty', 'udropship_status', 'udropship_method', 'udropship_method_description'))
                    ->joinAttribute('order_increment_id', 'order/increment_id', 'order_id')
                    ->joinAttribute('order_created_at', 'order/created_at', 'order_id')
                    ->joinAttribute('shipping_method', 'order/shipping_method', 'order_id');
            } else {}

            $collection->addAttributeToFilter('agent_id', $agentId);
			//echo $collection->getSelect()->__ToString();exit;
			//echo '<pre>';print_r($collection->getData());exit;

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

            if (!$r->getParam('apply_filter') && $agent->getData('vendor_po_grid_status_filter')) {
                $filterStatuses = $agent->getData('vendor_po_grid_status_filter');
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
                    $collection->addAttributeToFilter('shipmentpayout.shipmentpayout_status', $v);
            }
            if (!$r->getParam('sort_by') && $agent->getData('vendor_po_grid_sortby')) {
                $r->setParam('sort_by', $agent->getData('vendor_po_grid_sortby'));
                $r->setParam('sort_dir', $agent->getData('vendor_po_grid_sortdir'));
            }

            if (($v = $r->getParam('sort_by'))) {
                $map = array('order_date'=>'order_created_at', 'shipment_date'=>'created_at');
                if (isset($map[$v])) {
                    $v = $map[$v];
                }
                $collection->setOrder($v, $r->getParam('sort_dir'));
            }
            $this->_agentOrderCollection = $collection;
        }
        return $this->_agentOrderCollection;
    }
	  public function getAgentorderStatusName($order)
		{
			$statuses = Mage::getSingleton('uagent/source')->setPath('order_statuses')->toOptionHash();
			$id = $order->getAgentStatus();
			return isset($statuses[$id]) ? $statuses[$id] : 'Unknown';
		}
		public function getAgentOrderStatuses()
			{
			return Mage::getSingleton('uagent/source')->setPath('order_statuses')->toOptionHash();
			}
			
	 public function addAgentorderComment($agentorder, $comment)
    {
        
            $statuses = Mage::getSingleton('uagent/source')->setPath('order_statuses')->toOptionHash();
            $comment = Mage::getModel('sales/order_status_history')
						//->setParentId($_ebsorder['entity_id'])
						->setAgentStatus(@$statuses[$agentorder->getAgentStatus()])
						->setComment($comment)
						->setCreatedAt(NOW())
						->save();
                //->setUdropshipStatus(@$statuses[$shipment->getUdropshipStatus()]);
        
        $agentorder->addComment($comment);
        return $this;
    }		
	
	public function sendAgentWelcomeEmail($agent)
    {
        $store = Mage::app()->getStore();
		//$storeId = Mage::app()->getStore()->getId();
        $templateId = 'uagent_welcome_email_template';
        $sender = Array('name'  => 'Craftsvilla',
					   'email' => 'places@craftsvilla.com');
		Mage::getSingleton('core/translate')->setTranslateInline(true);
        $password = Mage::helper('core')->decrypt($agent->getPasswordEnc());
        Mage::getModel('core/email_template')->sendTransactional($templateId,$sender,$agent->getEmail(),$agent->getAgentName(),
            array(
                'store_name' => $store->getName(),
                'agentName' => $agent->getAgentName(),
                'emailAgent' => $agent->getEmail(),
				'password' => $password,
            )
        );
        return $this;
    }
	public function getAgentShipmentCollection()
    {
        if (!$this->_agentShipmentCollection) {
			
            $agentId = Mage::getSingleton('uagent/session')->getAgentId();
            $agent = Mage::helper('uagent')->getAgent($agentId);
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
                $orderTableQted = $collection->getResource()->getReadConnection()->quoteIdentifier('sales/order');
                $sqlMap['order_increment_id'] = "$orderTableQted.increment_id";
                $sqlMap['order_created_at']   = "$orderTableQted.created_at";
                $collection->join('sales/order', "$orderTableQted.entity_id=main_table.order_id", array(
                    'order_increment_id' => 'increment_id',
                    'order_created_at' => 'created_at',
                    'shipping_method',
                ));
                $shipmentPayoutTable = $collection->getResource()->getReadConnection()->quoteIdentifier('agentpayout/agentpayout');
				$collection->getSelect()->joinLeft('agentpayout', "main_table.increment_id=agentpayout.shipment_id", array(
                    'payout_status' => 'agentpayout_status',
                ));
            }

            $collection->addAttributeToFilter('main_table.agent_id', $agentId);


            $r = Mage::app()->getRequest();
            if (($v = $r->getParam('filter_order_id_from'))) {
			$collection->addAttributeToFilter('main_table.increment_id', array('gteq'=>$v));
            }
            if (($v = $r->getParam('filter_order_id_to'))) {
			$collection->addAttributeToFilter('main_table.increment_id', array('lteq'=>$v));
            }

            if (($v = $r->getParam('filter_order_date_from'))) {
				$datetimestamp = Mage::getModel('core/date')->timestamp(strtotime($v));
				$curdate= date("Y-d-m H:i:s",$datetimestamp);
				
                $_filterDate = Mage::app()->getLocale()->date();
				
                $_filterDate->set($curdate, Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT));
				
                $_filterDate->setTimezone(Mage_Core_Model_Locale::DEFAULT_TIMEZONE);
				

			$collection->addAttributeToFilter('main_table.created_at', array('gteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));
            }
			else
			{
				$_filterDate = Mage::app()->getLocale()->date();
				
				$curdate= date("Y-d-m H:i:s", Mage::getModel('core/date')->timestamp(time()));
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

			$collection->addAttributeToFilter('main_table.created_at', array('lteq'=>$_filterDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)));

            }

            if (($v = $r->getParam('filter_shipment_date_from'))) {
				
                $curdate= date("Y-d-m H:i:s", $v);
				$_filterDate = Mage::app()->getLocale()->date();

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

            if (!$r->getParam('apply_filter') && $agent->getData('vendor_po_grid_status_filter')) {
                $filterStatuses = $agent->getData('vendor_po_grid_status_filter');
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
                    $collection->addAttributeToFilter('agentpayout.agentpayout_status', $v);
            }
            if (!$r->getParam('sort_by') && $agent->getData('vendor_po_grid_sortby')) {
                $r->setParam('sort_by', $agent->getData('vendor_po_grid_sortby'));
                $r->setParam('sort_dir', $agent->getData('vendor_po_grid_sortdir'));
            }

            if (($v = $r->getParam('sort_by'))) {
                $map = array('order_date'=>'order_created_at', 'shipment_date'=>'created_at');
                if (isset($map[$v])) {
                    $v = $map[$v];
                }
                $collection->setOrder($v, $r->getParam('sort_dir'));
            }
            $this->_agentShipmentCollection = $collection;
        }
        return $this->_agentShipmentCollection;
    }
	 public function getAgentShipmentStatuses()
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
	 public function getShipmentStatusNameagent($shipment)
    {
        $statuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
        $id = $shipment->getUdropshipStatus();
        return isset($statuses[$id]) ? $statuses[$id] : 'Unknown';
    }

}