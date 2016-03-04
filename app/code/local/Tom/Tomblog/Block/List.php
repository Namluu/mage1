<?php
class Tom_Tomblog_Block_List extends Mage_Core_Block_Template
{
    public function getCustomerArticles()
    {
        $collection = null;
        $currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
        if($currentCustomer)
        {
            $collection = Mage::getModel('tomblog/article')->getCollection()
                ->addFieldToFilter('customer_id', $currentCustomer->getId());
        }
        return $collection;
    }
}