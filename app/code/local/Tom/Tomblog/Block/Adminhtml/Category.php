<?php
class Tom_Tomblog_Block_Adminhtml_Category extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'tomblog';
        $this->_headerText = $this->__('Blog Category Manager');
        parent::__construct();
    }
}