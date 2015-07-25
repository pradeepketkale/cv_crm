<?php
/**
* @author Amasty Team
* @copyright Amasty
* @package Amasty_Catcopy
*/
class Amasty_Catcopy_Model_Catalog_Resource_Eav_Mysql4_Category extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category
{
	/**
	 * copied here from native magento code, as this function is partialy removed on 1.6
	 */
	public function move($categoryId, $newParentId)
    {
        $category  = Mage::getModel('catalog/category')->load($categoryId);
        $oldParent = $category->getParentCategory();
        $newParent = Mage::getModel('catalog/category')->load($newParentId);

        $childrenCount = $this->getChildrenCount($category->getId()) + 1;

        // update children count of new parents
        $parentIds = explode('/', $newParent->getPath());
        $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` + {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

        // update children count of old parents
          $parentIds = explode('/', $oldParent->getPath());
          $this->_getWriteAdapter()->update(
            $this->getEntityTable(),
            array('children_count' => new Zend_Db_Expr("`children_count` - {$childrenCount}")),
            $this->_getWriteAdapter()->quoteInto('entity_id IN (?)', $parentIds)
        );

        // update parent id
        $this->_getWriteAdapter()->query("UPDATE
            {$this->getEntityTable()} SET parent_id = {$newParent->getId()}
            WHERE entity_id = {$categoryId}");

        return $this;
    }
    
    
    /**
     * Retrieve category tree object
     *
     * @return Varien_Data_Tree_Db
     */
    protected function _getTree()
    {
        /**
        * If we duplicating categories, should reload tree with every method call.
        * Seems to be an error when flat categories enabled.
        */
        if ('amcatcopy' == Mage::app()->getRequest()->getModuleName())
        {
            $this->_tree = Mage::getResourceModel('catalog/category_tree')
                    ->load();
        } else 
        {
            if (!$this->_tree) {
                $this->_tree = Mage::getResourceModel('catalog/category_tree')
                    ->load();
            }
        }
        return $this->_tree;
    }
}