<?php
/**
* aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdeskultimate
 * @version    2.9.2
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */


class AW_Helpdeakultimate_Test_Model_Mocks_Foreignresetter extends Mage_Core_Model_Abstract {

    public static $counter = 0;

    public static function dropForeignKeys() {

        if (!self::$counter) {

            $resource = Mage::getModel('core/resource');
            $connection = $resource->getConnection('core_write');


            $FKscope = array(
                'sales_flat_quote_item' => array('FK_SALES_QUOTE_ITEM_SALES_QUOTE', 'FK_SALES_FLAT_QUOTE_ITEM_PARENT_ITEM', 'FK_SALES_QUOTE_ITEM_CATALOG_PRODUCT_ENTITY', 'FK_SALES_QUOTE_ITEM_STORE'),
                'cataloginventory_stock_status' => array('FK_CATALOGINVENTORY_STOCK_STATUS_STOCK', 'FK_CATALOGINVENTORY_STOCK_STATUS_WEBSITE'),
                'catalog_product_website' => array('FK_CATALOG_PRODUCT_WEBSITE_WEBSITE'),
                'catalog_product_entity_int' => array('FK_CATALOG_PRODUCT_ENTITY_INT_ATTRIBUTE', 'FK_CATALOG_PRODUCT_ENTITY_INT_STORE', 'FK_CATALOG_PRODUCT_ENTITY_INT_PRODUCT_ENTITY'),
                'aw_collpur_rewrite' => array('FK_DEAL_STORE'),
                'sales_flat_order_item' => array('IDX_ORDER', 'IDX_STORE_ID', 'IDX_PRODUCT_ID', 'FK_SALES_FLAT_ORDER_ITEM_PARENT'),
                'sales_flat_order' => array('IDX_STORE_ID', 'IDX_CUSTOMER_ID', 'FK_SALES_FLAT_ORDER_CUSTOMER')
            );

            foreach ($FKscope as $table => $fks) {
                foreach ($fks as $fk) {
                    try {
                        $connection->exec(new Zend_Db_Expr("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`"));
                        $connection->exec(new Zend_Db_Expr("ALTER TABLE `{$table}` DROP KEY `{$fk}`"));
                    } catch (Exception $e) {
                        
                    }
                }
            }


            self::$counter = 1;
        }
    }

}