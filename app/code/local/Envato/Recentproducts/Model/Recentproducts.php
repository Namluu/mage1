<?php
class Envato_Recentproducts_Model_Recentproducts extends Mage_Core_Model_Abstract
{
    /*protected function _construct() {
        $this->_init('weblog/blogpost');
    }*/
    public function getRecentProducts()
    {
    	$products = Mage::getModel('catalog/product')
    				->getCollection()
    				->addAttributeToSelect('*')
    				->setOrder('entity_id', 'DESC')
    				->setPageSize(5);

    	return $products;
    }
}