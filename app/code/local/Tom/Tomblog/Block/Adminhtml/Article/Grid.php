<?php
class Tom_Tomblog_Block_Adminhtml_Article_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
        parent::__construct();
        $this->setId('aticleGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection(){
        $collection = Mage::getModel('tomblog/article')->getCollection()
            ->join(array('cat'=> 'category'), 'cat.id=main_table.category_id', array('cat_title'=>'title'))
            ->join(array('cus'=> 'customer/entity'), 'cus.entity_id=main_table.customer_id', array('cus_email'=>'email'));

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'   => $this->__('Id'),
            'width'    => 50,
            'index'    => 'id'
        ));
        $this->addColumn('title', array(
            'header'   => $this->__('Title'),
            'index'    => 'title'
        ));
        $this->addColumn('date', array(
            'header'   => $this->__('Created Date'),
            'index'    => 'date'
        ));
        $this->addColumn('cat_title', array(
            'header'   => $this->__('Category'),
            'index'    => 'cat_title'
        ));
        $this->addColumn('cus_email', array(
            'header'   => $this->__('Customer'),
            'index'    => 'cus_email'
        ));
        $this->addColumn('status', array(
            'header'   => $this->__('Status'),
            'index'    => 'status',
            'type'     => 'options',
            'options'  => Mage::getSingleton('tomblog/category')->getStatuses()
        ));
        return parent::_prepareColumns();
    }
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    protected function _prepareMassaction(){
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));

        $statuses = Mage::getSingleton('tomblog/category')->getStatuses();
            array_unshift($statuses, array('label'=>'', 'value'=>''));
            $this->getMassactionBlock()->addItem('status', array(
                'label'=> $this->__('Change status'),
                'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'visibility' => array(
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => $this->__('Status'),
                        'values' => $statuses
                    )
                )
            ));
        return $this;
    }
}