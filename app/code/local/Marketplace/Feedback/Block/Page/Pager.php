<?php

class Marketplace_Feedback_Block_Page_Pager extends Mage_Core_Block_Template
{
    protected $_collection = null;
    protected $_pageVarName     = 'page';
    protected $_limitVarName    = 'limit';
    protected $_availableLimit  = array(1 => 'test 1', 2 => 'test 2', 5 =>5 , 10=>10 ,20=>20);
    protected $_dispersion      = 3;
    protected $_displayPages    = 5;
    protected $_showPerPage	= true;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('marketplace/feedback/vendor/pager.phtml');
    }
    
    public function getCurrentPage()
    {
        if ($page = (int) $this->getRequest()->getParam($this->getPageVarName())) {
            return $page;
        }
        return 1;
    }   
    
    public function getLimit()
    {
        $limits = $this->getAvailableLimit();
        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }
        $limits = array_keys($limits);
        return $limits[0];
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection
            ->setCurPage($this->getCurrentPage());
        // If not int - then not limit
        if ((int) $this->getLimit()) {
            $this->_collection->setPageSize($this->getLimit());
        }
        return $this;
    }

    /**
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function getCollection()
    {
        return $this->_collection;
    }

    public function setPageVarName($varName)
    {
        $this->_pageVarName = $varName;
        return $this;
    }

    public function getPageVarName()
    {
        return $this->_pageVarName;
    }

    public function setShowPerPage($varName)
    {
    	$this->_showPerPage=$varName;
    }

    public function getShowPerPage()
    {
        if(sizeof($this->getAvailableLimit())<=1) {
            return false;
        }
    	return $this->_showPerPage;
    }

    public function setLimitVarName($varName)
    {
        $this->_limitVarName = $varName;
        return $this;
    }

    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }

    public function setAvailableLimit(array $limits)
    {
        $this->_availableLimit = $limits;
    }

    public function getAvailableLimit()
    {
        return $this->_availableLimit;
    }

    public function getFirstNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+1;
    }

    public function getLastNum()
    {
        $collection = $this->getCollection();
        return $collection->getPageSize()*($collection->getCurPage()-1)+$collection->count();
    }

    public function getTotalNum()
    {
        return $this->getCollection()->getSize();
    }

    public function isFirstPage()
    {
        return $this->getCollection()->getCurPage() == 1;
    }

    public function getLastPageNum()
    {
        return $this->getCollection()->getLastPageNumber();
    }

    public function isLastPage()
    {
        return $this->getCollection()->getCurPage() >= $this->getLastPageNum();
    }

    public function isLimitCurrent($limit)
    {
        return $limit == $this->getLimit();
    }

    public function isPageCurrent($page)
    {
        return $page == $this->getCurrentPage();
    }

    public function getPages()
    {
        $collection = $this->getCollection();

        $pages = array();
        if ($collection->getLastPageNumber() <= $this->_displayPages) {
            $pages = range(1, $collection->getLastPageNumber());
        }
        else {
            $half = ceil($this->_displayPages / 2);
            if ($collection->getCurPage() >= $half && $collection->getCurPage() <= $collection->getLastPageNumber() - $half) {
                $start  = ($collection->getCurPage() - $half) + 1;
                $finish = ($start + $this->_displayPages) - 1;
            }
            elseif ($collection->getCurPage() < $half) {
                $start  = 1;
                $finish = $this->_displayPages;
            }
            elseif ($collection->getCurPage() > ($collection->getLastPageNumber() - $half)) {
                $finish = $collection->getLastPageNumber();
                $start  = $finish - $this->_displayPages + 1;
            }

            $pages = range($start, $finish);
        }
        return $pages;

//        $pages = array();
//        for ($i=$this->getCollection()->getCurPage(-$this->_dispersion); $i <= $this->getCollection()->getCurPage(+($this->_dispersion-1)); $i++)
//        {
//
//            $pages[] = $i;
//        }
//
//        return $pages;
    }

    public function getFirstPageUrl()
    {
        return $this->getPageUrl(1);
    }

    public function getPreviousPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(-1));
    }

    public function getNextPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getCurPage(+1));
    }

    public function getLastPageUrl()
    {
        return $this->getPageUrl($this->getCollection()->getLastPageNumber());
    }

    public function getPageUrl($page)
    {
        return $this->getPagerUrl(array($this->getPageVarName()=>$page));
    }

    public function getLimitUrl($limit)
    {
        return $this->getPagerUrl(array($this->getLimitVarName()=>$limit));
    }

    public function getPagerUrl($params=array())
    {
        $urlParams = array();
        $urlParams['_current']  = true;
        $urlParams['_escape']   = true;
        $urlParams['_use_rewrite']   = true;
        $urlParams['_query']    = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
}

