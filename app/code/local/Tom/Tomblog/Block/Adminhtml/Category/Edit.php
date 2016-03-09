<?php
class Tom_Tomblog_Block_Adminhtml_Category_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_category';
        $this->_blockGroup = 'tomblog';
        $this->_mode = 'edit';
        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));

        $this->_addButton('saveandcontinue', array(
            'label'     => $this->__('Save and Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }
    public function getHeaderText()
    {
        if (Mage::registry('category_data') && Mage::registry('category_data')->getId())
            return $this->__("Edit category '%s'", $this->htmlEscape(Mage::registry('category_data')->getTitle()));
        return $this->__('Create a new Category');
    }
}