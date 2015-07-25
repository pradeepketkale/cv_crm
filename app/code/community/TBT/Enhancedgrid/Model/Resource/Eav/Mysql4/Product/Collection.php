<?php
/**
 * WDCA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   WDCA
 * @package    TBT_Enhancedgrid
 * @copyright  Copyright (c) 2008-2011 WDCA (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Enhanced grid Product collection
 * @nelkaake -a 15/12/10: 
 *
 * @category   TBT
 * @package     Enhancedgrid
 * @author      WDCA
 */
class TBT_Enhancedgrid_Model_Resource_Eav_Mysql4_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
{

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        
        //@nelkaake -a 15/12/10: Reset the group selection. ( for categories grouping)
        $countSelect->reset(Zend_Db_Select::GROUP);

        return $countSelect;
    }
	
//Below function added in dated 14-01-2014 for collection of all product to show in admin panel	
	 protected function _initSelect()
    {
        if ($this->isEnabledFlat()) {
            $this->getSelect()
                ->from(array('e' => $this->getEntity()->getFlatTableName()), null)
                ->columns(array('status' => new Zend_Db_Expr(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)));
            $this->addAttributeToSelect(array('entity_id', 'type_id', 'attribute_set_id'));
            if ($this->getFlatHelper()->isAddChildData()) {
                $this->getSelect()
                    ->where('e.is_child=?', 0);
                $this->addAttributeToSelect(array('child_id', 'is_child'));
            }
        }
        else {
  	    $this->getSelect()->from(array('e'=>$this->getEntity()->getEntityTable()));
			//$this->getSelect()->from(array('e'=>'catalog_product_craftsvilla1'));
        }
        return $this;
    }
}
