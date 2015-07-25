<?php
/**
 * Magento
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
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog Search Controller
 */
class Mage_CatalogSearch_ResultController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve catalog session
     *
     * @return Mage_Catalog_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('catalog/session');
    }
    /**
     * Display search result
     */
    public function indexAction()
    {
        $query = Mage::helper('catalogsearch')->getQuery();
        /* @var $query Mage_CatalogSearch_Model_Query */

        $query->setStoreId(Mage::app()->getStore()->getId());

        if ($query->getQueryText()) {
            if (Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->setId(0)
                    ->setIsActive(1)
                    ->setIsProcessed(1);
            }
            else {
                if ($query->getId()) {
                    $query->setPopularity($query->getPopularity()+1);
                }
                else {
                    $query->setPopularity(1);
                }

                if ($query->getRedirect()){
                    $query->save();
                    $this->getResponse()->setRedirect($query->getRedirect());
                    return;
                }
                else {
                    $query->prepare();
                }
            }

            Mage::helper('catalogsearch')->checkNotes();

            $this->loadLayout();
            $this->_initLayoutMessages('catalog/session');
            $this->_initLayoutMessages('checkout/session');
            $this->renderLayout();

            if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
                $query->save();
            }
        }
        else {
            $this->_redirectReferer();
        }
	if (!$query instanceof Mage_CatalogSearch_Model_Query) {
            $query = Mage::helper('catalogsearch')->getQuery();
        }
        $queryText = Mage::helper('catalogsearch')->getQueryText();
        $queryText = preg_replace('/[^a-zA-Z0-9_ -]/s', '', $queryText);

        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $stringHelper = Mage::helper('core/string');
        $words = $stringHelper->splitWords($queryText, true, $query->getMaxQueryWords());

        $bword = "";
        foreach ($words as $word) {
            $word = rtrim($word, "s");
            $bword .= $word . " ";
        }
        $bword = substr($bword, 0, -1);
        //$clike = "select entity_id from `catalog_product_flat_1` AS `s` WHERE `s`.`name` LIKE '%$bword%' OR `s`.`short_description` LIKE '%$bword%'";
        $clike = "SELECT entity_id FROM `catalog_product_entity_varchar` WHERE `entity_type_id` =4 AND `attribute_id` = '56' and value like '%$bword%'";
        $bcond = " value like '%$bword%'";
        //$bcond = "`s`.`name` LIKE '%$bword%' OR `s`.`short_description` LIKE '%$bword%'";
        $results = $conn->fetchAll($clike);
        //exit;    

        if (count($results) >= 1) {
            $found = count($results);
        } else {
            $clike = "";
            foreach ($words as $word) {
                $word = rtrim($word, "s");
                $clike .= "value LIKE '%$word%' OR ";
            }
            $clike = substr($clike, 0, -3);
            $nameSql = "SELECT entity_id FROM `catalog_product_entity_varchar` WHERE `entity_type_id` =4 AND `attribute_id` = '56' and ($clike)";
            $results = $conn->fetchAll($nameSql);
            if (count($results) >= 1) {
                $found = count($results);
            }
        }
       
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        //$write->query("UPDATE catalogsearch_query SET num_results='$found' WHERE query_text='$queryText';");
    }
}
