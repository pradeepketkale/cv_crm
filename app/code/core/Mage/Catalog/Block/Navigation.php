<?php

class Mage_Catalog_Block_Navigation extends Mage_Core_Block_Template {

    protected $_categoryInstance = null;
    //Current category key
    //@var string

    protected $_currentCategoryKey;
    //Array of level position counters
    //@var array
    protected $_itemLevelPositions = array();

    protected function _construct() {
        $this->addData(array(
            'cache_lifetime' => false,
            'cache_tags' => array(Mage_Catalog_Model_Category::CACHE_TAG, Mage_Core_Model_Store_Group::CACHE_TAG),
        ));
    }

    //Get Key pieces for caching block content
    //@return array

    public function getCacheKeyInfo() {
        $shortCacheId = array(
            'CATALOG_NAVIGATION',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrenCategoryKey()
        );
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrenCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }

    /**
     * Get current category key
     *
     * @return mixed
     */
    public function getCurrenCategoryKey() {
        if (!$this->_currentCategoryKey) {
            $category = Mage::registry('current_category');
            if ($category) {
                $this->_currentCategoryKey = $category->getPath();
            } else {
                $this->_currentCategoryKey = Mage::app()->getStore()->getRootCategoryId();
            }
        }
        return $this->_currentCategoryKey;
    }

    /**
     * Get catagories of current store
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getStoreCategories() {
        $helper = Mage::helper('catalog/category');
        return $helper->getStoreCategories();
    }

    /**
     * Retrieve child categories of current category
     *
     * @return Varien_Data_Tree_Node_Collection
     */
    public function getCurrentChildCategories() {
        $layer = Mage::getSingleton('catalog/layer');
        $category = $layer->getCurrentCategory();
        /* @var $category Mage_Catalog_Model_Category */
        $categories = $category->getChildrenCategories();
        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $layer->prepareProductCollection($productCollection);
        $productCollection->addCountToCategories($categories);
        return $categories;
    }

    /**
     * Checkin activity of category
     *
     * @param   Varien_Object $category
     * @return  bool
     */
    public function isCategoryActive($category) {
        if ($this->getCurrentCategory()) {
            return in_array($category->getId(), $this->getCurrentCategory()->getPathIds());
        }
        return false;
    }

    protected function _getCategoryInstance() {
        if (is_null($this->_categoryInstance)) {
            $this->_categoryInstance = Mage::getModel('catalog/category');
        }
        return $this->_categoryInstance;
    }

    /**
     * Get url for category data
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function getCategoryUrl($category) {
        if ($category instanceof Mage_Catalog_Model_Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->_getCategoryInstance()
                    ->setData($category->getData())
                    ->getUrl();
        }
        return $url;
    }

    /**
     * Return item position representation in menu tree
     *
     * @param int $level
     * @return string
     */
    protected function _getItemPosition($level) {
        if ($level == 0) {
            $zeroLevelPosition = isset($this->_itemLevelPositions[$level]) ? $this->_itemLevelPositions[$level] + 1 : 1;
            $this->_itemLevelPositions = array();
            $this->_itemLevelPositions[$level] = $zeroLevelPosition;
        } elseif (isset($this->_itemLevelPositions[$level])) {
            $this->_itemLevelPositions[$level]++;
        } else {
            $this->_itemLevelPositions[$level] = 1;
        }

        $position = array();
        for ($i = 0; $i <= $level; $i++) {
            if (isset($this->_itemLevelPositions[$i])) {
                $position[] = $this->_itemLevelPositions[$i];
            }
        }
        return implode('-', $position);
    }

    /**
     * Render category to html
     *
     * @param Mage_Catalog_Model_Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @param boolean Whether ot not this item is first, affects list item class
     * @param boolean Whether ot not this item is outermost, affects list item class
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @param boolean Whether ot not to add on* attributes to list item
     * @return string
     */
    protected function _renderCategoryMenuItemHtml($category, $level = 0, $isLast = false, $isFirst = false, $isOutermost = false, $outermostItemClass = '', $childrenWrapClass = '', $noEventAttributes = false) {
        if (!$category->getIsActive()) {
            return '';
        }
        $html = array();

        // get all children
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $children = (array) $category->getChildrenNodes();
            $childrenCount = count($children);
        } else {
            $children = $category->getChildren();
            $childrenCount = $children->count();
        }
        $hasChildren = ($children && $childrenCount);

        // select active children
        $activeChildren = array();
        foreach ($children as $child) {
            if ($child->getIsActive()) {
                $activeChildren[] = $child;
            }
        }
        
        $activeChildrenCount = count($activeChildren);
        $hasActiveChildren = ($activeChildrenCount > 0);

        // prepare list item html classes

        $classes = array();
        $classes[] = 'sale';
        $classes[] = 'nav-' . $this->_getItemPosition($level);
        if ($this->isCategoryActive($category)) {
            $classes[] = 'active';
        }
        $linkClass = '';
        if ($isOutermost && $outermostItemClass) {
            $classes[] = $outermostItemClass;
            $linkClass = ' class="' . $outermostItemClass . '"';
        }
        if ($isFirst) {
            $classes[] = 'first';
        }

        if ($isLast) {
            $classes[] = 'last';
        }
/*
        if ($hasActiveChildren) {
            $classes[] = 'parent';
        }
	
	echo "<pre>";
	print_r($classes);
	echo "</pre>";
	*/	
        // prepare list item attributes
        $attributes = array();
        if (count($classes) > 0) {
            $attributes['class'] = implode(' ', $classes);
        }
        if ($hasActiveChildren && !$noEventAttributes) {
            $attributes['onmouseover'] = 'toggleMenu(this,1)';
            $attributes['onmouseout'] = 'toggleMenu(this,0)';
        }

        // assemble list item with attributes
        //if($moni==1) {
        //sale nav-1-2 last parent

        $classval_moni = "";
        $setval = 0;
        $divval_moni = "";
        $divval = 0;

        foreach ($attributes as $attrName => $attrValue) {
            $classval_moni = ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
            
        }
        //echo "<br/><li><span style='background-color:white;line-height:34px;'>----".$classval_moni."</span>";


        if ($classval_moni == ' class="sale nav-1-1 first"' || $classval_moni == ' class="sale nav-1-2 last"' || $classval_moni == ' class="sale nav-1-2 last parent"' || $classval_moni == ' class="sale nav-1-1 first parent"' || $classval_moni == ' class="sale nav-2-2 last parent"' || $classval_moni == ' class="sale nav-2-1 first parent"' || $classval_moni == ' class="sale nav-3-2 last parent"' || $classval_moni == ' class="sale nav-3-1 first parent"' || $classval_moni == ' class="sale nav-4-2 last parent"' || $classval_moni == ' class="sale nav-4-1 first parent"' || $classval_moni == ' class="sale nav-5-2 last parent"' || $classval_moni == ' class="sale nav-5-1 first parent"' || $classval_moni == ' class="sale nav-6-2 last parent"' || $classval_moni == ' class="sale nav-6-1 first parent"' || $classval_moni == ' class="sale nav-7-2 last parent"' || $classval_moni == ' class="sale nav-7-1 first parent"' || $classval_moni == ' class="sale nav-8-2 last parent"' || $classval_moni == ' class="sale nav-8-1 first parent"' || $classval_moni == ' class="sale nav-9-2 last parent"' || $classval_moni == ' class="sale nav-9-1 first parent"' || $classval_moni == ' class="sale nav-10-2 last parent"' || $classval_moni == ' class="sale nav-10-1 first parent"' || $classval_moni == ' class="sale nav-11-2 last parent"' || $classval_moni == ' class="sale nav-11-1 first parent"' || $classval_moni == ' class="sale nav-12-2 last parent"' || $classval_moni == ' class="sale nav-12-1 first parent"' || $classval_moni == ' class="sale nav-1-1 active first parent"' || $classval_moni == ' class="sale nav-1-2 active last parent"' || $classval_moni == ' class="sale nav-2-1 active first parent"' || $classval_moni == ' class="sale nav-2-2 active last parent"' || $classval_moni == ' class="sale nav-3-1 active first parent"' || $classval_moni == ' class="sale nav-3-2 active last parent"' || $classval_moni == ' class="sale nav-4-1 active first parent"' || $classval_moni == ' class="sale nav-4-2 active last parent"' || $classval_moni == ' class="sale nav-5-1 active first parent"' || $classval_moni == ' class="sale nav-5-2 active last parent"' || $classval_moni == ' class="sale nav-6-1 active first parent"' || $classval_moni == ' class="sale nav-6-2 active last parent"' || $classval_moni == ' class="sale nav-7-1 active first parent"' || $classval_moni == ' class="sale nav-7-2 active last parent"' || $classval_moni == ' class="sale nav-8-1 active first parent"' || $classval_moni == ' class="sale nav-8-2 active last parent"' || $classval_moni == ' class="sale nav-9-1 active first parent"' || $classval_moni == ' class="sale nav-9-2 active last parent"' || $classval_moni == ' class="sale nav-10-1 active first parent"' || $classval_moni == ' class="sale nav-10-2 active last parent"' || $classval_moni == ' class="sale nav-11-1 active first parent"' || $classval_moni == ' class="sale nav-11-2 active last parent"' || $classval_moni == ' class="sale nav-12-1 active first parent"' || $classval_moni == ' class="sale nav-12-2 active last parent"' || $classval_moni == ' class="sale nav-2-1 first"' || $classval_moni == ' class="sale nav-2-2 last"' || $classval_moni == ' class="sale nav-3-1 first"' || $classval_moni == ' class="sale nav-3-2 last"' || $classval_moni == ' class="sale nav-4-1 first"' || $classval_moni == ' class="sale nav-4-2 last"' || $classval_moni == ' class="sale nav-5-1 first"' || $classval_moni == ' class="sale nav-5-2 last"' || $classval_moni == ' class="sale nav-6-1 first"' || $classval_moni == ' class="sale nav-6-2 last"' || $classval_moni == ' class="sale nav-7-1 first"' || $classval_moni == ' class="sale nav-7-2 last"' || $classval_moni == ' class="sale nav-8-1 first"' || $classval_moni == ' class="sale nav-8-2 last"' || $classval_moni == ' class="sale nav-9-1 first"' || $classval_moni == ' class="sale nav-9-2 last"' || $classval_moni == ' class="sale nav-10-1 first"' || $classval_moni == ' class="sale nav-10-2 last"' || $classval_moni == ' class="sale nav-11-1 first"' || $classval_moni == ' class="sale nav-11-2 last"' || $classval_moni == ' class="sale nav-12-1 first"' || $classval_moni == ' class="sale nav-12-2 last"') {
            $setval = 1;
        }

        if ($classval_moni == ' class="sale nav-1-2 last"' || $classval_moni == ' class="sale nav-1-2 last parent"' || $classval_moni == ' class="sale nav-2-2 last parent"' || $classval_moni == ' class="sale nav-3-2 last parent"' || $classval_moni == ' class="sale nav-4-2 last parent"' || $classval_moni == ' class="sale nav-5-2 last parent"' || $classval_moni == ' class="sale nav-6-2 last parent"' || $classval_moni == ' class="sale nav-7-2 last parent"' || $classval_moni == ' class="sale nav-8-2 last parent"' || $classval_moni == ' class="sale nav-9-2 last parent"' || $classval_moni == ' class="sale nav-10-2 last parent"' || $classval_moni == ' class="sale nav-11-2 last parent"' || $classval_moni == ' class="sale nav-12-2 last parent"' || $classval_moni == ' class="sale nav-1-2 active last parent"' || $classval_moni == ' class="sale nav-2-2 active last parent"' || $classval_moni == ' class="sale nav-3-2 active last parent"' || $classval_moni == ' class="sale nav-4-2 active last parent"' || $classval_moni == ' class="sale nav-5-2 active last parent"' || $classval_moni == ' class="sale nav-6-2 active last parent"' || $classval_moni == ' class="sale nav-7-2 active last parent"' || $classval_moni == ' class="sale nav-8-2 active last parent"' || $classval_moni == ' class="sale nav-9-2 active last parent"' || $classval_moni == ' class="sale nav-10-2 active last parent"' || $classval_moni == ' class="sale nav-11-2 active last parent"' || $classval_moni == ' class="sale nav-12-2 active last parent"' || $classval_moni == ' class="sale nav-2-2 last"' || $classval_moni == ' class="sale nav-3-2 last"' || $classval_moni == ' class="sale nav-4-2 last"' || $classval_moni == ' class="sale nav-5-2 last"' || $classval_moni == ' class="sale nav-6-2 last"' || $classval_moni == ' class="sale nav-7-2 last"' || $classval_moni == ' class="sale nav-8-2 last"' || $classval_moni == ' class="sale nav-9-2 last"' || $classval_moni == ' class="sale nav-10-2 last"' || $classval_moni == ' class="sale nav-11-2 last"' || $classval_moni == ' class="sale nav-12-2 last"') {
            $divval = 1;
        }

        // 6 = Bags, 7 = Religious, 8 = Gifts, 9 = Sale

        if ($setval == 1) {

            if ($divval == 0) {
	
                if ($classval_moni == ' class="sale nav-6-2 last parent"' || $classval_moni == ' class="sale nav-6-1 first parent"' || $classval_moni == ' class="sale nav-6-1 active first parent"' || $classval_moni == ' class="sale nav-6-2 active last parent"') {
                    $htmlLi = '  <div class="sub" style="opacity:0; left:-60px;">
						<div class="row"> <ul> <li';
                } else

                if ($classval_moni == ' class="sale nav-7-2 last parent"' || $classval_moni == ' class="sale nav-7-1 first parent"' || $classval_moni == ' class="sale nav-7-1 active first parent"' || $classval_moni == ' class="sale nav-7-2 active last parent"' || $classval_moni == ' class="sale nav-7-2 last"') {
                    $htmlLi = '  <div class="sub" style="opacity:0; left:-150px;">
						<div class="row"> <ul> <li';
                } else

                if ($classval_moni == ' class="sale nav-8-2 last parent"' || $classval_moni == ' class="sale nav-8-1 first parent"' || $classval_moni == ' class="sale nav-8-1 active first parent"' || $classval_moni == ' class="sale nav-8-2 active last parent"') {
                    $htmlLi = '  <div class="sub" style="opacity:0; left:-190px;">
						<div class="row"> <ul> <li';
                } else

                if ($classval_moni == ' class="sale nav-9-2 last parent"' || $classval_moni == ' class="sale nav-9-1 first parent"' || $classval_moni == ' class="sale nav-9-1 active first parent"' || $classval_moni == ' class="sale nav-9-2 active last parent"') {
                    $htmlLi = '  <div class="sub" style="opacity:0; left:-260px;">
						<div class="row"> <ul> <li';
                } else {

                    $htmlLi = '  <div class="sub" style="opacity:0;' . $styletp . '">
							<div class="row"> <ul> <li';
                } 
            } else {
                $htmlLi = '  <ul> <li';
            }

	//$htmlLi = '<div class="sub" style="opacity:0;' . $styletp . '"><div class="row"> <ul><li';
        } else {
            $htmlLi = '<li';
        } 
        foreach ($attributes as $attrName => $attrValue) {

            $htmlLi .= ' ' . $attrName . '="' . str_replace('"', '\"', $attrValue) . '"';
        }

        if ($setval == 1) {
            $htmlLi .= '><h2>';
        } else {
            $htmlLi .= '>';
        }

        $html[] = $htmlLi;

        if ($classval_moni == ' class="sale nav-1 level-top parent"' || $classval_moni == ' class="sale nav-1 level-top first parent"' || $classval_moni == ' class="sale nav-2 level-top parent"' || $classval_moni == ' class="sale nav-3 level-top parent"' || $classval_moni == ' class="sale nav-4 level-top parent"' || $classval_moni == ' class="sale nav-5 level-top parent"' || $classval_moni == ' class="sale nav-6 level-top parent"' || $classval_moni == ' class="sale nav-7 level-top parent"' || $classval_moni == ' class="sale nav-8 level-top parent"' || $classval_moni == ' class="sale nav-9 level-top parent"' || $classval_moni == ' class="sale nav-10 level-top parent"' || $classval_moni == ' class="sale nav-11 level-top parent"' || $classval_moni == ' class="sale nav-12 level-top parent"' || $classval_moni == ' class="sale nav-6 level-top"' || $classval_moni == ' class="sale nav-6 level-top"' || $classval_moni == ' class="sale nav-8 level-top last"') {

            $html[] = '<a href="' . $this->getCategoryUrl($category) . '" class="sale">';
        } else {

            $html[] = '<a href="' . $this->getCategoryUrl($category) . '" >';
        }

        if (($classval_moni != ' class="sale nav-3-2 last"') && ($classval_moni != ' class="sale nav-5-2 last"') && ($classval_moni != ' class="sale nav-6-2 last"') && ($classval_moni != ' class="sale nav-7-2 last"') && ($classval_moni != ' class="sale nav-8-2 last"')) {  // To Not Show Jewellery and Bags
            $html[] = $this->escapeHtml($category->getName());
        }

        $html[] = '</a>';

        if ($setval == 1) {
            $html[] = '</h2></li>';
        }
        // render children
        $htmlChildren = '';
        $j = 0;
        foreach ($activeChildren as $child) {
            $htmlChildren .= $this->_renderCategoryMenuItemHtml(
                    $child, ($level + 1), ($j == $activeChildrenCount - 1), ($j == 0), false, $outermostItemClass, $childrenWrapClass, $noEventAttributes
            );
            $j++;
        }
        if (!empty($htmlChildren)) {



            if ($childrenWrapClass) {
                $html[] = '<div class="' . $childrenWrapClass . '">';
            }

            if ($level != 1 && $level != 0) {
                //$html[] = '<ul class="level' . $level . '">';
                $html[] = '<ul>';
            }
            $html[] = $htmlChildren;

            if ($level != 1 && $level != 0) {
                $html[] = '</ul>';
            }

            if ($childrenWrapClass) {
                $html[] = '</div>';
            }
        }

        if ($setval == 1) {
            $html[] = '</ul>';
            if ($divval == 1) {
                $html[] = '</div></div>';
            }
        } else {
            $html[] = '</li>';
        }

        $html = implode("\n", $html);
  //      echo "<hr>".$html."</hr>";
  
        return $html;
    }

    /**
     * Render category to html
     *
     * @deprecated deprecated after 1.4
     * @param Mage_Catalog_Model_Category $category
     * @param int Nesting level number
     * @param boolean Whether ot not this item is last, affects list item class
     * @return string
     */
    public function drawItem($category, $level = 0, $last = false) {
        return $this->_renderCategoryMenuItemHtml($category, $level, $last);
    }

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
        if (Mage::getSingleton('catalog/layer')) {
            return Mage::getSingleton('catalog/layer')->getCurrentCategory();
        }
        return false;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getCurrentCategoryPath() {
        if ($this->getCurrentCategory()) {
            return explode(',', $this->getCurrentCategory()->getPathInStore());
        }
        return array();
    }

    /**
     * Enter description here...
     *
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
    public function drawOpenCategoryItem($category) {
        $html = '';
        if (!$category->getIsActive()) {
            return $html;
        }

        $html.= '<li';

        if ($this->isCategoryActive($category)) {
            $html.= ' class="active"';
        }

        $html.= '>' . "\n";
        $html.= '<a href="' . $this->getCategoryUrl($category) . '"><span>' . $this->htmlEscape($category->getName()) . '</span></a>' . "\n";

        if (in_array($category->getId(), $this->getCurrentCategoryPath())) {
            $children = $category->getChildren();
            $hasChildren = $children && $children->count();

            if ($hasChildren) {
                $htmlChildren = '';
                foreach ($children as $child) {
                    $htmlChildren.= $this->drawOpenCategoryItem($child);
                }

                if (!empty($htmlChildren)) {
                    $html.= '<ul>' . "\n"
                            . $htmlChildren
                            . '</ul>';
                }
            }
        }
        $html.= '</li>' . "\n";
        return $html;
    }

    /**
     * Render categories menu in HTML
     *
     * @param int Level number for list item class to start from
     * @param string Extra class of outermost list items
     * @param string If specified wraps children list in div with this class
     * @return string
     */
    public function renderCategoriesMenuHtml($level = 0, $outermostItemClass = '', $childrenWrapClass = '') {
        $activeCategories = array();
        foreach ($this->getStoreCategories() as $child) {
            if ($child->getIsActive()) {
                $activeCategories[] = $child;
            }
        }
        $activeCategoriesCount = count($activeCategories);
        $hasActiveCategoriesCount = ($activeCategoriesCount > 0);

        if (!$hasActiveCategoriesCount) {
            return '';
        }

        $html = '';
        $j = 0;
        foreach ($activeCategories as $category) {
            $html .= $this->_renderCategoryMenuItemHtml(
                    $category, $level, ($j == $activeCategoriesCount - 1), ($j == 0), true, $outermostItemClass, $childrenWrapClass, true
            );
            $j++;
        }
        return $html;
    }

}
