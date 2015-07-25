<?php

class Craftsvilla_Uagent_Block_Agent_Agentorder_Info extends Mage_Sales_Block_Items_Abstract
{
    protected function _construct()
    {
        Mage_Core_Block_Template::_construct();
        $this->addItemRender('default', 'sales/order_item_renderer_default', 'sales/order/shipment/items/renderer/default.phtml');
    }

    public function getAgentorder()
    {
		
        if (!$this->hasData('agentorder')) {
            $id = (int)$this->getRequest()->getParam('id');
            $agentorder = Mage::getModel('sales/order')->load($id);
            $this->setData('agentorder', $agentorder);
        }
        return $this->getData('agentorder');
    }

}
