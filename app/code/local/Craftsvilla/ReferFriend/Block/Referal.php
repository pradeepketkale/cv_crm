<?php 
	class Craftsvilla_ReferFriend_Block_Referal extends Mage_Core_Block_Template
	{
		public $today;
		public $customer_id;
		
		public function _construct(){
			$this->customer_id = Mage::getSingleton('customer/session')->getId();	
			$this->today = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
		}
		
		public function check_refercode_generated(){
			
			$timestamp_model = Mage::getModel('referfriend/timestamp');
			$timestampCollection = $timestamp_model->getCollection()
									-> addFieldToFilter('customer_id', array('eq'=>$this->customer_id))
									-> addFieldToFilter('creation_date', array('lt'=>$this->today))
									-> addFieldToFilter('expiry_date', array('gt'=>$this->today))
									->getdata();
			
			if(empty($timestampCollection)){
				return false;	
			}else{
				return true;	
			}
		}
		
		public function get_refer_code(){
			$model = Mage::getModel('customer/attribute');
			
			$varcharTable = (string)Mage::getConfig()->getTablePrefix() . 'customer_entity_varchar';                    
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('customer_read');
				
			try{
				$select = $read->select()->from($varcharTable, array('value'))
									   ->where("attribute_id = (select attribute_id from eav_attribute where attribute_code like 'refer_code')")
									   ->where("entity_id  = ?",$this->customer_id);
				$row = $read->fetchRow($select);
				return $row['value'];						   
			}catch(Exception $e){
				echo $e->getMessage();
			}
		}
	}
?>