<?php
class Tom_Tomblog_Helper_Data extends Mage_Core_Helper_Abstract {
    public function getCategories()
    {
        $collection = Mage::getModel('tomblog/category')->getCollection()
            ->addFieldToSelect(array('id', 'title'));
        return $collection;
    }
}