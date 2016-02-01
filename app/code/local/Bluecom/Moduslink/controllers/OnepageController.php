<?php

require Mage::app()->getConfig()->getModuleDir('controllers', 'Mage_Checkout') . DIRECTORY_SEPARATOR . "OnepageController.php";

class Bluecom_Moduslink_OnepageController  extends Mage_Checkout_OnepageController
{
    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function saveMdPaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }
            $data = $this->getRequest()->getPost('payment', array());

            // Override method for moduslink
            if(isset($data['method']) && strpos($data['method'], 'MD_') === 0) {
                $mdPaymentMethod = $this->getRequest()->getPost("ACCOUNT_BRAND");
                $data['moduslink_data'] = $mdPaymentMethod;
                $data['method'] = 'moduslink';

                Mage::getSingleton("checkout/session")->setData("moduslink_data", $data['moduslink_data']);
            }
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Create order action
     */
    public function saveMdOrderAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        /*$requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
        if ($requiredAgreements) {
            $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
            $diff = array_diff($requiredAgreements, $postedAgreements);
            if ($diff) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return;
            }
        }*/

        $result['success'] = true;
        $result['error']   = false;

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}