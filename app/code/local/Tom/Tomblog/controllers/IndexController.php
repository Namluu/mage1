<?php
class Tom_Tomblog_IndexController extends Mage_Core_Controller_Front_Action {

    // require login customer to access this URL
    public function preDispatch() {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->getResponse()->setRedirect(Mage::helper('customer')->getLoginUrl());
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
        }
    }

    public function _initModel($param = 'id')
    {
        $model = Mage::getModel('tomblog/article');
        if( $modelId = $this->getRequest()->getParam($param))
        {
            $model->load($modelId);
            if(!$model->getId())
            {
                Mage::throwException($this->__('There was a problem initializing the article registry.'));
            }
        }
        Mage::register('current_article', $model);
        return $model;
    }

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_initModel();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_initModel();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function deleteAction()
    {
        try {
            $id = $this->getRequest()->getParam('id');
            if($id){
                if($article = Mage::getModel('tomblog/article')->load($id))
                {
                    $article->delete();
                    Mage::getSingleton('core/session')->addSuccess($this->__('Article has been succesfully deleted.'));
                    $this->_redirect('*/*/');

                }else{
                    throw new Exception("There was a problem deleting the article");
                }
            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = $this->_initModel();
                if($model === false)
                {
                    throw new Exception("There was a problem saving the article");
                }
                
                $customer = Mage::getSingleton('customer/session')->getCustomer();

                $model->updateData($customer, $data);
                $model->save();

                Mage::getSingleton('core/session')->addSuccess($this->__('Article Successfully Saved'));
                
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($this->__('There was an error trying to save the gift registry.'));
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/');
    }


}