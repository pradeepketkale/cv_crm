<?php 
	class Craftsvilla_ReferFriend_IndexController extends Mage_Core_Controller_Front_Action
	{
		protected $error;

		const XML_PATH_REFERFRIEND_EMAIL_TEMPLATE  = 'referfriend/testemail/email_template';
		
		protected function _getSession()
		{
			return Mage::getSingleton('customer/session');
		}
		
		public function indexAction(){
		
			$model = Mage::getModel('referfriend/check');
			$data = $model->getCollection();
			
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->renderLayout();			
		}	
		
		public function inviteAction()
		{
			$session = $this->_getSession();
			
			if(!$session->isLoggedIn()){
				$current_url = Mage::getUrl().'referfriend/index/invite';
				$session->setData("after_auth_url", $current_url);
				$this->_redirectUrl(Mage::getUrl().'customer/account/login');				
				return;
			}
			
			$model = Mage::getModel('referfriend/check');
			$data = $model->getCollection();
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->renderLayout();			
		}
		
		public function acceptAction()
		{
			$model = Mage::getModel('referfriend/check');
			$data = $model->getCollection();
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->renderLayout();			
		}	
		
		public function acceptedAction()
		{
			$model = Mage::getModel('referfriend/check');
			$data = $model->getCollection();
			$this->loadLayout();
			$this->_initLayoutMessages('customer/session');
			$this->renderLayout();			
		}

		public function get_emails($str)
		{
			$emails = array();
			$pattern="/([\s]*)([_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*([ ]+|)@([ ]+|)([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,}))([\s]*)/i"; 
			//preg_match_all($pattern, $html_page, $matches); 
			//preg_match_all("/\b\w+\@\w+[\.\w+]+\b/", $str, $output);
			preg_match_all($pattern, $str, $output);
			foreach($output[0] as $email) array_push ($emails, strtolower($email));
			if (count ($emails) >= 1) return $emails;
			else return false;
		}

		public function sendAction(){
			$emails = $this->get_emails($this->getRequest()->getParam('invite_friends'));
			$session = $this->_getSession();
			
			if(!$session->isLoggedIn()){
				$session->addError('Please Login.');
				$this->_redirectUrl(Mage::getUrl().'referfriend/index/invite/');
				return;
			}
			
			$customer_id = $session->getId();
			$model = Mage::getModel('customer/attribute');
			
			$varcharTable = (string)Mage::getConfig()->getTablePrefix().'customer_entity_varchar';                    
			$resource = Mage::getSingleton('core/resource');
			$read = $resource->getConnection('customer_read');
				
			try{
				$select = $read->select()->from($varcharTable, array('value'))
									   ->where("attribute_id = (select attribute_id from eav_attribute where attribute_code like 'refer_code')")
									   ->where("entity_id  = ?",$customer_id);
				$row = $read->fetchRow($select);
				$refercode = $row['value'];						   
				
			}catch(Exception $e){
				echo $e->getMessage();
			}
			
			foreach($emails as $mail){
				$mail = trim($mail);
				$data = array();
				if(!Zend_Validate::is($mail, 'EmailAddress')){
					$session->addError($mail.'is In-Valid. Please Enter Correct Mail.');
					$this->error = true;
				}
			}
			
			if(!$this->error){
				try{
				$count = 0;
				foreach($emails as $mail){
					if($count >= 20)
						break;
					$mail = trim($mail);
					$data = array();
					$name = explode('@',$mail);
					$name = $name[0];
					$model = Mage::getModel('referfriend/referral');
					try {
						$model->setReferral_parent_id($customer_id)
						->setReferral_name($name)
						->setReferral_code($refercode)
						->setReferral_email($mail)
						->save();
						$count++;
						$session->addSuccess($this->__('Invited Successfully'));
						$this->_redirectUrl(Mage::getUrl().'referfriend/index/invite/');
					} catch (Exception $e) {
						//$session->addSuccess($this->__(''));	
						$session->addError($this->__('You have already invited some of your some of them.'));
						$this->_redirectUrl(Mage::getUrl().'referfriend/index/invite/');
					}
					$customer = Mage::getModel('customer/customer')->load($customer_id);
					$model->sendReferMail($name,$mail,$refercode,$customer);
				}
				} catch (Exception $e) {
					//$session->addSuccess($this->__(''));	
					$session->addError($this->__('There is some Error.'));
					$this->_redirectUrl(Mage::getUrl().'referfriend/index/invite/');

				}
						
			}else{
				$this->loadLayout();
				$this->_initLayoutMessages('customer/session');
				$this->renderLayout();
			}
		}

		public function reminderAction(){
			$emails = $this->get_emails($this->getRequest()->getParam('invite_friends'));
			$session = $this->_getSession();
			if(!$session->isLoggedIn())
			{
				$session->addError('Please Login.');
				$this->_redirectUrl(Mage::getUrl().'referfriend/index/invite/');
				return;
			}
			
			$customer_id = $session->getId();
			$model = Mage::getModel('customer/attribute');
			
			$varcharTable = (string)Mage::getConfig()->getTablePrefix() . 'customer_entity_varchar';                    
				$resource = Mage::getSingleton('core/resource');
				$read = $resource->getConnection('customer_read');
			try{
				$select = $read->select()->from($varcharTable, array('value'))
									   ->where("attribute_id = (select attribute_id from eav_attribute where attribute_code like 'refer_code')")
									   ->where("entity_id  = ?",$customer_id);
				$row = $read->fetchRow($select);
				$refercode = $row['value'];						   
			}catch(Exception $e){
				echo $e->getMessage();
			}

			foreach($emails as $mail)
			{
					$mail = trim($mail);
					$name = explode('@',$mail);
					$name = $name[0];
			}
			// increament reminder flag-ashu
			$referral_model = Mage::getModel('referfriend/referral');
			$row = array_shift($referral_model->getCollection()
									   ->addFieldToFilter('referral_email', array('eq', $mail))
									   ->addFieldToFilter('referral_parent_id', array('eq', $customer_id))->getdata());				
			if(($row != NULL) && ($row != ''))
			{
				$count_reminder = $row['referral_reminder']+1;
				$referral_model->load($row['referfriend_referral_id'])->setReferral_reminder($count_reminder)->save();													
			} 
			$customer = Mage::getModel('customer/customer')->load($customer_id);
			$res = $referral_model->sendReferMail($name,$mail,$refercode,$customer);
		}
	}
?>
