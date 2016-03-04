<?php
class Tom_Tomblog_Model_Article extends Mage_Core_Model_Abstract
{
    protected function _construct() {
        $this->_init('tomblog/article');
    }

    public function updateData(Mage_Customer_Model_Customer $customer, $data)
    {
        try{
            if(!empty($data))
            {
                $this->setCustomerId($customer->getId());
                //$this->setWebsiteId($customer->getWebsiteId());
                $this->setTitle($data['title']);
                $this->setContent($data['content']);
                $this->setCategoryId($data['category_id']);
                $this->setDate(Mage::getModel('core/date')->gmtDate());
            }else{
                throw new Exception("Error Processing Request: Insufficient Data Provided");
            }
        } catch (Exception $e){
            Mage::logException($e);
        }
        return $this;
    }
}