<?php
class Tom_Tomblog_Block_Adminhtml_Article extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct(){
        $this->_controller = 'adminhtml_article';
        $this->_blockGroup = 'tomblog';
        $this->_headerText = $this->__('Blog Article Manager');
        parent::__construct();
    }
}