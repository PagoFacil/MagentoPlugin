<?php

/** 
 *  Cash Form 
 *
 *  
 */

class Pagofacil_Pagofacildirect_Block_Redirect extends Mage_Core_Block_Template{
    
    protected function _construct(){

        parent::_construct();
        
        $this->setTemplate('pagofacildirect/redirect.phtml');
        
    }
}

?>
