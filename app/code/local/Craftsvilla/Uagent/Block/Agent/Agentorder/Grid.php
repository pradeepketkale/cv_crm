<?php
class Craftsvilla_Uagent_Block_Agent_Agentorder_Grid extends Mage_Core_Block_Template
{
    protected $_collection;

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($toolbar = $this->getLayout()->getBlock('agentorder.grid.toolbar')) {
            $toolbar->setCollection(Mage::helper('uagent')->getAgentOrderCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }
}