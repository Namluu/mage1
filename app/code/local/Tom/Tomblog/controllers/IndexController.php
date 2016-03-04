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

    public function indexAction() {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function newPostAction()
    {
        try {
            $data = $this->getRequest()->getParams();

            $article = Mage::getModel('tomblog/article');
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if($this->getRequest()->getPost() && !empty($data)) {
                $article->updateData($customer, $data);
                $article->save();
                $successMessage =  Mage::helper('tomblog')->__('Article Successfully Created');
                Mage::getSingleton('core/session')->addSuccess($successMessage);
            }else{
                throw new Exception("Insufficient Data provided");
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->_redirect('*/*/');
        }
        $this->_redirect('*/*/');
    }
}