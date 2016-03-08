<?php
class Tom_Tomblog_Block_Adminhtml_Customer_Edit_Tab_Tomblog_List extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('blogList');
        $this->setUseAjax(true);
        $this->setDefaultSort('date');
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
    }
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tomblog/article')
            ->getCollection()
            ->addFieldToFilter('main_table.customer_id', $this->getRequest()->getParam('id'))
            ->join(array('cat'=>'category'), 'cat.id=main_table.category_id', array('cat_title'=> 'title'));
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
        $this->addColumn('category', array(
            'header'   => $this->__('Category'),
            'index'    => 'cat_title',
            'sortable' => false,
        ));
        return parent::_prepareColumns();
    }
}