<?php
class Tom_Tomblog_Model_Resource_Article extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct()
    {
        $this->_init('tomblog/article', 'id');
    }

    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $select->joinLeft(
            array('cat' => 'tomblog_category'),
            $this->getMainTable() . '.category_id = cat.id',
            array('cat_title' => 'title'));
        return $select;
    }
}

