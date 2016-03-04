<?php
class Tom_Tomblog_Block_New extends Mage_Core_Block_Template
{
    public function getCategories()
    {
        $collection = Mage::getModel('tomblog/category')->getCollection()
            ->addFieldToSelect(array('id', 'title'));
        return $collection;
    }
}