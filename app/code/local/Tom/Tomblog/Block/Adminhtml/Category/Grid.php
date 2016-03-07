<?php
class Tom_Tomblog_Block_Adminhtml_Category_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct(){
        parent::__construct();
        $this->setId('categoryGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection(){
        $collection = Mage::getModel('tomblog/category')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'   => $this->__('Id'),
            'width'    => 50,
            'index'    => 'id',
            'sortable' => false,
        ));
        $this->addColumn('title', array(
            'header'   => $this->__('Title'),
            'index'    => 'title',
            'sortable' => false,
        ));
        $this->addColumn('date', array(
            'header'   => $this->__('Created Date'),
            'index'    => 'date',
            'sortable' => false,
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
        return $this;
    }
}