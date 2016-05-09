<?php
class Tom_Tomblog_Model_Category extends Mage_Core_Model_Abstract
{
    const STATUS_ENABLE    = 1;
    const STATUS_DISABLE   = 0;

    protected function _construct() {
        $this->_init('tomblog/category');
    }

    public function getStatuses() {
        return array(
            self::STATUS_ENABLE    => Mage::helper('tomblog')->__('Enable'),
            self::STATUS_DISABLE   => Mage::helper('tomblog')->__('Disable')
        );
    }

    public function toOptionArray() {
        $collection = Mage::getModel('tomblog/category')->getCollection();
        $categories = array(array('value'=>'', 'label'=>'Please Select'));
        foreach ($collection as $item){
            $categories[] = array('value'=>$item->getId(),'label'=>$item->getTitle());
        }

        return $categories;
    }
}