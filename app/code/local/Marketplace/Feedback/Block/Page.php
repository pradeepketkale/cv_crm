<?php
class Marketplace_Feedback_Block_Page extends Mage_Page_Block_Html_Pager
{
   /**
     * List of available view types
     *
     * @var string
     */
    protected $_availableMode       = array();
        
    protected function _construct()
    {
        parent::_construct();

        $this->_availableMode = array('list' => $this->__('List'));
        $this->setTemplate('page/html/pager.phtml');
    }
    
    public function setCollection($collection)
    {
        parent::setCollection($collection);
        return $this;
    }
    
    public function getModes()
    {
        return $this->_availableMode;
    }

    /**
     * Set available view modes list
     *
     * @param array $modes
     * @return Mage_Catalog_Block_Product_List_Toolbar
     */
    public function setModes($modes)
    {
        if(!isset($this->_availableMode)){
            $this->_availableMode = $modes;
        }
        return $this;
    }
}
?>
