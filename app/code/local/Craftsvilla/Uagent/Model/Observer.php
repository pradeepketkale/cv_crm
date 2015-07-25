<?php
class Craftsvilla_Uagent_Model_Observer
{
    protected $_agentPassword;

    /**
    * Invoke as soon as possible to get correct base_url in frontend
    *
    * @param mixed $observer
    */
    public function controller_front_init_before($observer)
    {
        $this->_getAgent();
    }
	
	protected function _switchSession($area, $id=null)
    {
        session_write_close();
        $GLOBALS['_SESSION'] = null;
        $session = Mage::getSingleton('core/session');
        if ($id) {
            $session->setSessionId($id);
        }
        $session->start($area);
    }

    public function uagent_index_login($observer)
    { 

        $agent = $observer->getEvent()->getAgent();
        $user = Mage::getModel('admin/user')->load($agent->getId(), 'uagent');
        //print_r($user->getData());exit;
		if ($user->getId()) {
            $coreSession = Mage::getSingleton('core/session');
            $oId = $coreSession->getSessionId();
            $sId = !empty($_COOKIE['adminhtml']) ? $_COOKIE['adminhtml'] : $oId;
            $this->_switchSession('adminhtml', $sId);
            $session = Mage::getSingleton('admin/session');
            if (!$session->isLoggedIn()) {
                $user->getResource()->recordLogin($user);
                $session->setIsFirstVisit(true);
                $session->setUser($user);
                $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
            }
            $this->_switchSession('frontend', $oId);
        }
    }

    public function uagent_index_logout($observer)
    {
        $agent = $observer->getEvent()->getAgent();
		
//        $user = Mage::getModel('admin/user')->load($agent->getId(), 'agent_id');
		$user = Mage::getModel('admin/user')->load($agent->getId());

        if ($user->getId() && !empty($_COOKIE['adminhtml'])) {
            $coreSession = Mage::getSingleton('core/session');
            $oId = $coreSession->getSessionId();
            $sId = $_COOKIE['adminhtml'];
            $this->_switchSession('adminhtml', $sId);
            $session = Mage::getSingleton('admin/session');
            if ($session->isLoggedIn() && $session->getUser()->getId()==$user->getId()) {
                Mage::getSingleton('admin/session')->unsetAll();
                Mage::getSingleton('adminhtml/session')->unsetAll();
            }
            $this->_switchSession('frontend', $oId);
        }
    }

    public function admin_session_user_login_success($observer)
    {
        $coreSession = Mage::getSingleton('core/session');
        $oId = $coreSession->getSessionId();
        $sId = !empty($_COOKIE['frontend']) ? $_COOKIE['frontend'] : null;

        $user = $observer->getEvent()->getUser();
        $agentId = $user->getUagentAgent();

        if ($user->getUagentAgent()) {
            $this->_switchSession('frontend', $sId);
            $session = Mage::getSingleton('uagent/session');
            if (!$session->isLoggedIn()) {
                $session->loginById($agentId);
            }
            $this->_switchSession('adminhtml', $oId);
        }
    }

    protected $_agentId;

    public function controller_action_predispatch_adminhtml_index_logout($observer)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($user) {
            $this->_agentId = $user->getUagentAgent();
        }
    }

    public function controller_action_postdispatch_adminhtml_index_logout($observer)
    {
        if (!$this->_agentId) {
            return;
        }
        $coreSession = Mage::getSingleton('core/session');
        $oId = $coreSession->getSessionId();
        $sId = !empty($_COOKIE['frontend']) ? $_COOKIE['frontend'] : null;

        $this->_switchSession('frontend', $sId);
        $session = Mage::getSingleton('uagent/session');
        if ($session->isLoggedIn() && $session->getId()==$this->_agentId) {
            $session->setId(null);
        }

        if (!empty($_SESSION['core']['last_url'])) {
            $url = $_SESSION['core']['last_url'];
        } elseif (!empty($_SESSION['core']['visitor_data']['http_referer'])) {
            $url = $_SESSION['core']['visitor_data']['http_referer'];
        } else {
            $url = Mage::getUrl('uagent', array('_store'=>'default'));
        }
        if (false !== strpos($url, 'ajax')) {
            $url = Mage::getUrl('uagent', array('_store'=>'default'));
        } elseif (false !== strpos($url, 'cms/index/noRoute')) {
            $url = Mage::getUrl('uagent', array('_store'=>'default'));
        }
        $this->_switchSession('adminhtml', $oId);

        header("Location: ".$url);
        exit;
    }

    protected function _getAgent()
    {
        return Mage::helper('uagent')->getCurrentAgent();
    }
}