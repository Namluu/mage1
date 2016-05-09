<?php
class Tom_Tomblog_Block_Adminhtml_Article_Edit_Form extends  Mage_Adminhtml_Block_Widget_Form
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
        $data = null;
        if (Mage::getSingleton('adminhtml/session')->getFormData()){
            $data = Mage::getSingleton('adminhtml/session')->getFormData();
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        }elseif(Mage::registry('article_data'))
            $data = Mage::registry('article_data')->getData();

        $fieldset = $form->addFieldset('article_form', array('legend'=>$this->__('Article information')));

        if ($data && $data['id']) {
            $fieldset->addField('id', 'hidden', array(
                'label'     => $this->__('Id'),
                'class'     => '',
                'required'  => false,
                'name'      => 'id',
            ));
        }

        $fieldset->addField('category_id', 'select', array(
            'label'     => $this->__('Category'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'category_id',
            'values'    => Mage::getSingleton('tomblog/category')->toOptionArray(),
        ));
        $fieldset->addField('title', 'text', array(
            'label'     => $this->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'title',
        ));
        $fieldset->addField('content', 'textarea', array(
            'label'     => $this->__('Content'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'content',
        ));
        $fieldset->addField('status', 'select', array(
            'label'     => $this->__('Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'status',
            'values'    => Mage::getSingleton('tomblog/category')->getStatuses(),
        ));
        $form->setValues($data);
        return parent::_prepareForm();
    }
}