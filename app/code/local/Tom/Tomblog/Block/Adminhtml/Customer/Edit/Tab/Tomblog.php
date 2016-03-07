<?php
class Tom_Tomblog_Block_Adminhtml_Customer_Edit_Tab_Tomblog 
extends Mage_Adminhtml_Block_Template implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        $this->setTemplate('tomblog/customer/main.phtml');
        parent::_construct();
    }

    public function getCustomerId()
    {
        return Mage::registry('current_customer')->getId();
    }
    public function getTabLabel()
    {
        return $this->__('Blog List');
    }
    public function getTabTitle()
    {
        return $this->__('Click to view the customer Blogs');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}