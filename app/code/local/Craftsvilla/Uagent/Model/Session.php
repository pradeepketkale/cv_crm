<?php
class Craftsvilla_Uagent_Model_Session extends Mage_Core_Model_Session_Abstract
{
    protected $_agent;

    public function __construct()
    {
        $namespace = 'uagent';
        $this->init($namespace);
        Mage::dispatchEvent('uagent_session_init', array('session'=>$this));
    }

    public function setAgent($agent)
    {
        $this->_agent = $agent;
        return $this;
    }

    public function getAgent()
    {
		
        if ($this->_agent instanceof Craftsvilla_Uagent_Model_Uagent) {
            return $this->_agent;
        }
	
        if ($this->getId()) {
            $agent = Mage::helper('uagent')->getAgent($this->getId());
        } else {
            $agent = Mage::getModel('uagent/uagent');
        }
        $this->setAgent($agent);

        return $this->_agent;
    }

    public function getAgentId()
    {
        return $this->getId();
    }

    public function isLoggedIn()
    {
        return (bool)$this->getId() && (bool)$this->getAgent()->getId();
    }


    public function setAgentAsLoggedIn($agent)
    {
        $this->setAgent($agent);
        $this->setId($agent->getId());
        Mage::dispatchEvent('uagent_index_login', array('agent'=>$agent));
        return $this;
    }

    public function login($username, $password)
    {
        $agent = Mage::getModel('uagent/uagent');
		if ($agent->authenticate($username, $password)) {
        
			$this->setAgentAsLoggedIn($agent);
            return true;
        }
        return false;
    }

    public function loginById($agentId)
    {
        $agent = Mage::getModel('uagent/uagent')->load($agentId);
        if ($agent->getId()) {
            $this->setAgentAsLoggedIn($agent);
            return true;
        }
        return false;
    }

    public function logout()
    {
        if ($this->isLoggedIn()) {
            $this->setId(null);
            Mage::dispatchEvent('uagent_index_logout', array('agent'=>$this->getAgent()));
        }
        return $this;
    }
}
