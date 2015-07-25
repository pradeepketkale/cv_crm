<?php 
	class Craftsvilla_ReferFriend_LinkController extends Mage_Core_Controller_Front_Action
	{
		protected $refer_code;
		protected $refer_code_change;
		protected $row;
		
		protected function _getSession()
		{
			return Mage::getSingleton('customer/session');
		}
		
		public function generateAction(){
			
			$session = $this->_getSession();
			if(!$session->isLoggedIn()){
				$session->addError('Please Login.');
				$this->_redirectUrl(Mage::getUrl().'referfriend');
				return;
			}
			
			$customer_id = $session->getId();
			$data = $this->getRequest()->getPost();
			
			$this->refer_code = $data['refer_code'];
			$this->refer_code_change = $data['refer_code_change'];
			
			
			if($this->refer_code_change != '' && $customer_id != null && $customer_id!=''){
				if($this->refer_code_change == '1') $redirect_url = Mage::getUrl().'referfriend'; else $redirect_url = Mage::getUrl().'referfriend/index/invite/';
				$timestamp_model = Mage::getModel('referfriend/timestamp');
				
				$creation_date = date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time()));
				$expiry_date = date('Y-m-d H:i:s',strtotime($creation_date) + 604800);
				
				$varcharTable = (string)Mage::getConfig()->getTablePrefix() . 'customer_entity_varchar';                    
				$resource = Mage::getSingleton('core/resource');
				$read = $resource->getConnection('customer_read');
				$write= $resource->getConnection('customer_write');
				
				try{
					
					$attr_id = $read->select()
							 ->from('eav_attribute', array('attribute_id'))
							 ->where("attribute_code like 'refer_code'");
					$attr_id = $read->fetchRow($attr_id);				
					
				}catch(Exception $e){
					$session->addError('Something Wrong!!!');
					$this->_redirectUrl(Mage::getUrl().'referfriend');
					return;
				}
				
				// data to insert customer_entity_varchar
				$data = array(
							'entity_type_id'=>'1',
							'attribute_id'=>$attr_id['attribute_id'],
							'entity_id'=> $customer_id,
							'value'=>$this->refer_code
							);
				
				try{
					
					// check if the code already exist
					$code = $read->select()
						 ->from($varcharTable)
						 ->where("attribute_id = ".$attr_id['attribute_id'])
						 ->where("value like '".$this->refer_code."'");		 
					$v = $read->fetchRow($code);
					
					
					if(empty($v)){	
						$write->insert($varcharTable, $data);	
					}else{
							$session->addError('Code Already in use. Please Try Diffrent Code.');
							$this->_redirectUrl($redirect_url);
							return;
					}
				}catch(Exception $e){
					if($e->getCode() == 23000){
						try {
							$data_new = array('value'=>$this->refer_code);
							$write->update($varcharTable, $data_new, 'attribute_id='.$attr_id['attribute_id'].' and entity_id ='.$customer_id);
							$this->_redirect('referfriend');
						}
						catch(Exception $e){
							$session->addError('Something Went Wrong..!!!');
							$this->_redirectUrl($redirect_url);
							return;
						}
					}
				}
				
				try{
					$timestamp_model->setCustomer_id($customer_id)
									->setTime_stamp_value(time())
									->setCreation_date($creation_date)
									->setExpiry_date($expiry_date)
									->save();
				}
				catch(Exception $e){
					$session->addError('Failed To save Timestamp');
					$this->_redirectUrl($redirect_url);
					return;
				}	
				
			}else{
				$session->addError('Cannot Change Value');
				$this->_redirectUrl($redirect_url);
				return;
			}
			
				$session->addSuccess('Code Generated Successfully');
				$this->_redirectUrl($redirect_url);
				return;
		}
		
		public function checkAction(){
			
			$customer_model = Mage::getModel('customer/check')
				->setCustomer_id('1')
				->save();	
		}
	}