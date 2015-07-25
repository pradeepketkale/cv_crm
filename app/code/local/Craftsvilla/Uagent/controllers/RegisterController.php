<?php
class Craftsvilla_Uagent_RegisterController extends Mage_Core_Controller_Front_Action

{
    public function indexAction()
    {
		$this->loadLayout();     
		$this->renderLayout();
    }

	 public function registerAction()
    {
        $this->loadLayout();     
		$this->renderLayout();
		
    }
	
	public function registerPostAction()
    {
		//echo '<pre>';print_r($this->getRequest()->getPost());exit;
        if ($this->getRequest()->getPost() ) {
		     $r = $this->getRequest();
            try {
				$data = $r->getParams();
				$model = Mage::getModel('uagent/uagent');
				$model->addData($data)
                      ->validate()
                      ->save();
                  
                $hlp = Mage::helper('uagent');
                $hlp->sendAgentWelcomeEmail($model);
                //$hlp->sendVendorRegistration($model);
                
                $session = Mage::getSingleton('uagent/session');
                if (!empty($data['email']) && !empty($data['password'])) {
                    if (!$session->login($data['email'], $data['password'])) {
                        $session->addError($this->__('Invalid username or password.'));
                    }
                    $this->_redirect('uagent/index/index/');
                    return;
                } else {
                    $session->addError($this->__('Login and password are required'));
                }
                return;
            } catch (Exception $e) {
                //Mage::getSingleton('uagent/session')->addError($e);
                //$session->addSucess($this->__('Your Registed Succesfully ! Welcome to Craftsvilla-12'));
				//$this->_redirect('*/index/index');
				//$session->addSuccess($this->__('Your Registed Succesfully ! Welcome to Craftsvilla '));
				//$this->_redirect('*/index/index');
                //return;
            }
			Mage::getSingleton('uagent/session')->addSuccess($this->__('Your Registed Succesfully ! Welcome to Craftsvilla '));
			$this->_redirect('*/createorder/createorder');
		}
		
    }

public function passwordAction()
    {
        $this->loadLayout();     
		$this->renderLayout();
    }

    public function passwordPostAction()
    {
        $session = Mage::getSingleton('uagent/session');
        $hlp = Mage::helper('uagent');
        try {
            $r = $this->getRequest();
            if (($confirm = $r->getParam('email'))) {
                //$email =$useremail = $r->getParam('email');
				$password = $r->getParam('password');
                $passwordConfirm = $r->getParam('password_confirm');
                $agent = Mage::getModel('uagent/uagent')->load($confirm,'email');
				if (!$password || !$passwordConfirm || $password!=$passwordConfirm || !$agent->getId()) {
                    $session->addError('Invalid form data');
                    $this->_redirect('*/*/password', array('confirm'=>$confirm));
                    return;
                }
                $agent->setPassword($password)
					  ->setPasswordHash(Mage::helper('core')->getHash($password, 2))
					  ->save();
                //$session->loginById($agent->getId());
                $session->addSuccess('Your password has been reset');
                $this->_redirect('uagent');
            } 
        } catch (Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirect('*/*/password');
        }
    }

	
	
	
}