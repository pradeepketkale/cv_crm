<?php

class Craftsvilla_Uagent_Controller_AgentAbstract extends Mage_Core_Controller_Front_Action
{
    protected $_loginFormChecked = false;

    protected function _getSession()
    {
		return Mage::getSingleton('uagent/session');
    }

    protected function _renderPage($handles=null, $active=null)
    {
      // $this->_setTheme();
        $this->loadLayout($handles);
        $root = $this->getLayout()->getBlock('root');

        if ($root) {
            $root->addBodyClass('uagent-agent');
        }
        if ($active && ($head = $this->getLayout()->getBlock('header'))) {
            $head->setActivePage($active);
        }
        $this->_initLayoutMessages('uagent/session');
        $this->renderLayout();
    }

    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice
        parent::preDispatch();

        $r = $this->getRequest();
		
        $action = $r->getActionName();
        
		$session = Mage::getSingleton('uagent/session');
		
        if (!$session->isLoggedIn() && !Mage::registry('uagent_login_checked')) {

			Mage::register('uagent_login_checked', true);
		    if ($r->getPost('login')) {
                $login = $this->getRequest()->getPost('login');
				if (!empty($login['username']) && !empty($login['password'])) {
                	try {
						if (!$session->login($login['username'], $login['password'])) {
                           
							$session->addError($this->__('Invalid username or password.'));
                        }
                        $session->setUsername($login['username']);
                    }
                    catch (Exception $e) {
                        $session->addError($e->getMessage());
                    }
                } else {
                    $session->addError($this->__('Login and password are required'));
                }

                if ($session->isLoggedIn()) {
					
                    if ($this->getRequest()->getActionName()=='noRoute') {
			            $this->_redirect('*/*');
                    } else {
						$this->_redirect('*/*/*', array('_current'=>true));
                    }
                }
            }
			 if (!preg_match('#^(login|logout|password)#i', $action)) {
                $this->_forward('login','index','uagent');
				//$this->_forward('login', 'vendor', 'udropship');
            }
        }

    }

    protected function _forward($action, $controller = null, $module = null, array $params = null)
    {
        if (!is_null($module)) {
            $module = Mage::app()->getFrontController()->getRouterByRoute($module)->getFrontNameByRoute($module);
        	
		}
        return parent::_forward($action, $controller, $module, $params);
    }

}