<?php
class Tom_Tomblog_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $id     = $this->getRequest()->getParam('id', null);
        $category  = Mage::getModel('tomblog/category');
        if ($id) {
            $category->load((int) $id);
            if ($category->getId()) {
                $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
                if ($data) {
                    $category->setData($data)->setId($id);
                }
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('The Blog category does not exist'));
                $this->_redirect('*/*/');
            }
        }
        Mage::register('category_data', $category);
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost())
        {
            try {
                $data = $this->getRequest()->getPost();
                $id = $this->getRequest()->getParam('id');
                $category = Mage::getModel('tomblog/category');
                if ($data && $id) {
                    $category = $category->load($id);
                } 

                $data['date'] = Mage::getModel('core/date')->gmtDate();
                $category->setData($data);
                $category->save();

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $category->getId()));
                    return;
                }

            } catch (Exception $e) {
                $this->_getSession()->addError(
                    $this->__('An error occurred while saving the category data. Please review the log and try again.')
                );
                Mage::logException($e);
            }
            $this->_redirect('*/*/index');
            //$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
        }
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if(!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select one or more category.'));
        } else {
            try {
                $category = Mage::getModel('tomblog/category');
                foreach ($ids as $id) {
                    $category->load($id)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->__('Total of %d record(s) were deleted.', count($ids))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        $status = $this->getRequest()->getParam('status') == '1' ? '1' : '0';

        if (!is_array($ids)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($ids as $id) {
                    $category = Mage::getModel('tomblog/category')
                        ->load($id)
                        ->setStatus($status)
                        ->setIsMassUpdate(true)
                        ->save();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__('Total of %d record(s) were successfully updated', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}