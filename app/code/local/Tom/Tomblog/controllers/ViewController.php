<?php
class Tom_Tomblog_ViewController extends Mage_Core_Controller_Front_Action
{
    public function viewAction()
    {
        $id = $this->getRequest()->getParam('id');
        if($id){
            $article = Mage::getModel('tomblog/article');
            if($article->load($id))
            {
                Mage::register('loaded_article', $article);
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();
                return $this;
            } else {
                $this->_forward('noroute');
                return $this;
            }
        }
        $this->_redirect('*/*/');
    }
}