<?php

  class Craftsvilla_PostalCode_Model_PostalCode extends Mage_Core_Model_Abstract
  {
      protected $_model = NULL;
      
      public function _construct()
      {
          $this->_model = 'postalcode/postalcode';
          $this->_init($this->_model);
      }
  }
