<?php
class Tom_Tomblog_Block_Adminhtml_Category_Edit_Form extends  Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm(){
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));
        $form->setUseContainer(true);
        $this->setForm($form);
        if (Mage::getSingleton('adminhtml/session')->getFormData()){
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }elseif(Mage::registry('category_data'))
            $data = Mage::registry('category_data')->getData();

        $fieldset = $form->addFieldset('category_form', array('legend'=>$this->__('Category information')));

        $fieldset->addField('id', 'hidden', array(
            'label'     => $this->__('Id'),
            'class'     => '',
            'required'  => false,
            'name'      => 'id',
        ));
        $fieldset->addField('title', 'text', array(
            'label'     => $this->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
        $fieldset->addField('description', 'text', array(
            'label'     => $this->__('Description'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'description',
        ));
        $form->setValues($data);
        return parent::_prepareForm();
    }
}