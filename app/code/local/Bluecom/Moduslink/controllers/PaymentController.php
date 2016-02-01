<?php
/**
 * @author mserrano
 * @date 23/12/14
 */

class Bluecom_Moduslink_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirect link after user input credit card number
     */
    public function gatewayAction()
    {
        /* Validate get token and checkout/session token */
        if(!$this->_validateSessionTokenValueArr($this->getRequest()->getParam("token"))) {
            $this->_redirect('checkout/onepage');
            return $this;
        }

        if ($this->_expireAjax()) {
            return $this;
        }

        $result = array();
        $hasSendPaymentFailedEmail = false;
        $isRollBackStock = false;
        try {
            $remoteStatusInfo = $this->_getStatusInfoFromModuslinkServer();

            if(
                empty($remoteStatusInfo["result"])
                || empty($remoteStatusInfo["identification.uniqueId"])
                || empty($remoteStatusInfo["identification.transactionid"] )
            )
            {
                throw new Exception("Result, uniqueId, transactionId cannot be empty");
            }

            $data['method'] = 'moduslink';

            if ($data) {
                $data['moduslink_data'] =  Mage::getSingleton("checkout/session")->getData("moduslink_data");
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();
            $order = $this->_getCurrentOrder();

            $arrReturn = array();

            if( $remoteStatusInfo['result'] === 'ACK') {

                $order->sendNewOrderEmail();
                $isReview = true;

                if(Mage::helper("bluecom_moduslink/api")->isReviewStatus(array(
                    "reasonCode" => $remoteStatusInfo["reason.code"], "returnCode" => $remoteStatusInfo["return.code"]
                ) )) {
                    $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, Bluecom_Moduslink_Helper_Data::STATUS_PAYMENT_REVIEW, 'Fraud Order. Need preview.');
                }
                else {
                    /* With default status is Payment Pre Authorization */
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true , "Order successfull");
                    $isReview = false;
                }

                // How get last order and set uniqueid
                $order->setModuslinkUniqueId($remoteStatusInfo["identification.uniqueId"]);
                $order->setModuslinkTransactionId($remoteStatusInfo["identification.transactionid"]);

                /* This tax is not include copy and weee tax */
                $order->setTaxApiAmount(Mage::getSingleton("core/session")->getData("_taxApiInfoAmount"));

                /* Save info of client for send order */
                $remoteStatusInfo["is_review"] = $isReview;
                $order->setRawClientData(serialize($remoteStatusInfo));

                /* Save data for reauthorize */
                $unixTimestamp = time();
                $rawOrderData["last_touch"] = $unixTimestamp;
                $order->setData("raw_order_data", serialize($rawOrderData));

                $order->save();

                if($order->getState() === Mage_Sales_Model_Order::STATE_PROCESSING) {
                    /* Get the price of all virtual product and capture immediately */
                    Mage::helper('bluecom_moduslink/api')->captureVirtualProduct($order);
                }

                Mage::dispatchEvent("payment_redirect_after_save_order", ["order" => &$order]);

                $arrReturn["redirect_url"] = "checkout/onepage/success";
                if(Mage::helper("bluecom_moduslink/api")->isReviewStatus(array(
                    "reasonCode" => $remoteStatusInfo["reason.code"], "returnCode" => $remoteStatusInfo["return.code"]
                ) )) {

                    $arrReturn["warning"] = "Your order is confirmed as Fraud. We will process and contact later.";
                }

                $this->getOnepage()->getQuote()->save();
            }
            elseif( $remoteStatusInfo['result'] === 'NOK') {
                $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::ACTION_FLAG_CANCEL , $remoteStatusInfo["return.message"]);
                $order->setStatus(Bluecom_Moduslink_Helper_Data::ORDER_STATUS_CANCELED, true, $remoteStatusInfo["return.message"]);

                $order->setModuslinkUniqueId($remoteStatusInfo["identification.uniqueId"]);
                $order->cancel()->save();

                // Redirect to error page and show the user the simple error
                $errorMsg = $remoteStatusInfo["return.message"];

                $arrReturn["error"] = $errorMsg;
                Mage::helper('bluecom_moduslink')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $errorMsg);
                $hasSendPaymentFailedEmail = true;

                // Redirect to fail page
                $arrReturn["redirect_url"] = "checkout/onepage/failure";

                $isRollBackStock = true;
            }
        } catch (Exception $e) {
            Mage::logException($e);

            if(!$hasSendPaymentFailedEmail) {
                //Mage::helper('bluecom_moduslink')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            }

            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');


            $arrReturn["error"] = $result["error_messages"];
            $arrReturn["redirect_url"] = "checkout/onepage/failure";
            $isRollBackStock = true;
        }

        if( $isRollBackStock) {
            $quote = $this->getOnepage()->getQuote();
            $items = $this->_getProductsQty($quote->getAllItems());
            Mage::getSingleton('cataloginventory/stock')->revertProductsSale($items);

            // Clear flag, so if order placement retried again with success - it will be processed
            $quote->setInventoryProcessed(false);
        }

        // Clear session
        $this->_cleanUpSession();

        if(isset($arrReturn["warning"])) {
            Mage::getSingleton("core/session")->addWarning($arrReturn["warning"]);
        }

        $checkoutSession = Mage::getSingleton('checkout/session');
        if(isset($arrReturn["error"])) {
            $checkoutSession->setErrorMessage($arrReturn["error"]);
        }

        if(isset($arrReturn["redirect_url"])) {
            $this->_redirect($arrReturn["redirect_url"]);
        }
        return $this;
    }

    private function _getCurrentOrder()  {
        $session = Mage::getSingleton("checkout/session");
        $lastOrderId = $session->getLastOrderId();

        return Mage::getModel("sales/order")->load($lastOrderId);
    }

    private function _getProductsQty($relatedItems)
    {
        $items = array();
        foreach ($relatedItems as $item) {
            $productId  = $item->getProductId();
            if (!$productId) {
                continue;
            }
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $this->_addItemToQtyArray($childItem, $items);
                }
            } else {
                $this->_addItemToQtyArray($item, $items);
            }
        }
        return $items;
    }

    private function _addItemToQtyArray($quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId)
            return;
        if (isset($items[$productId])) {
            $items[$productId]['qty'] += $quoteItem->getTotalQty();
        } else {
            $stockItem = null;
            if ($quoteItem->getProduct()) {
                $stockItem = $quoteItem->getProduct()->getStockItem();
            }
            $items[$productId] = array(
                'item' => $stockItem,
                'qty'  => $quoteItem->getTotalQty()
            );
        }
    }

    /**
     * @return array(
     *      'result',
     *      'reason.code',
     *      'return.code',
     *      'return.message',
     *      'identification.uniqueId'
     * )
     * @throws Exception
     * @throws Zend_Http_Client_Exception
     */
    private function _getStatusInfoFromModuslinkServer()
    {
        $paymentLink = Mage::helper("bluecom_moduslink/api")->getPaymentLink();

        $iClient = $this->_getClient();

        $url = $paymentLink . '/frontend/GetStatus;jsessionid=';
        $url .= Mage::app()->getRequest()->getParam('token');

        Mage::helper("bluecom_moduslink")->moduslinkLog("[ML-GetStatus]", $url);

        $iClient->setUri($url)
            ->setMethod('POST')
            ->setConfig(array(
                'maxredirects'=>0,
                'timeout'=>30,
            ));

        $response = $iClient->request();

        $body = $response->getBody();
        if(!is_string($body)) {
            $body = (string)$body;
        }

        $arr = Mage::helper('core')->jsonDecode($body);
        if(is_array($arr) && isset($arr["errorMessage"])) {
            throw new Exception($arr["errorMessage"]);
        }

        Mage::helper("bluecom_moduslink")->moduslinkLog("response from server", $arr);

        return array(
            "result" => isset($arr["transaction"]["processing"]["result"]) ? $arr["transaction"]["processing"]["result"] : "NOK",
            "reason.code" => isset($arr["transaction"]["processing"]["reason"]["code"]) ? $arr["transaction"]["processing"]["reason"]["code"] : NULL,
            "return.code" => isset($arr["transaction"]["processing"]["return"]["code"]) ? $arr["transaction"]["processing"]["return"]["code"] : NULL,
            "return.message" => isset($arr["transaction"]["processing"]["return"]["message"]) ? $arr["transaction"]["processing"]["return"]["message"] : "There was an error processing your order. Please contact us or try again later.",
            "identification.uniqueId" => isset($arr["transaction"]["identification"]["uniqueId"]) ? $arr["transaction"]["identification"]["uniqueId"]: NULL,
            "identification.transactionid" => isset($arr["transaction"]["identification"]["transactionid"]) ? $arr["transaction"]["identification"]["transactionid"]: NULL,
            "account.bin" => isset($arr["transaction"]["account"]["bin"]) ? $arr["transaction"]["account"]["bin"] : "NULL",
            "account.brand" => isset($arr["transaction"]["account"]["brand"]) ? $arr["transaction"]["account"]["brand"] : "NULL",
            "account.expiry" => isset($arr["transaction"]["account"]["expiry"]) ? $arr["transaction"]["account"]["expiry"] : "NULL",
            "account.holder" => isset($arr["transaction"]["account"]["holder"]) ? $arr["transaction"]["account"]["holder"] : "NULL",
            "account.last4Digits" => isset($arr["transaction"]["account"]["last4Digits"]) ? $arr["transaction"]["account"]["last4Digits"] : "NULL",
        );
    }

    private function _getClient()
    {
        $oClient = new Bluecom_Http_Client();
        $oAdapter = new Bluecom_Http_Adapter_Curl();

        //if( Mage::helper('bluecom_features')->isUseCipher() ) {

            //$arrCiphers = Bluecom_Features_Model_Ciphers::$ciphers;
            $arrCiphers = [
                'ECDHE-RSA-AES128-GCM-SHA256',
                'ECDHE-ECDSA-AES128-GCM-SHA256',
                'ECDHE-RSA-AES256-GCM-SHA384',
                'ECDHE-ECDSA-AES256-GCM-SHA384',
                'DHE-RSA-AES128-GCM-SHA256',
                'DHE-DSS-AES128-GCM-SHA256',
        //        'kEDH+AESGCM',
                'ECDHE-RSA-AES128-SHA256',
                'ECDHE-ECDSA-AES128-SHA256',
                'ECDHE-RSA-AES128-SHA',
                'ECDHE-ECDSA-AES128-SHA',
                'ECDHE-RSA-AES256-SHA384',
                'ECDHE-ECDSA-AES256-SHA384',
                'ECDHE-RSA-AES256-SHA',
                'ECDHE-ECDSA-AES256-SHA',
                'DHE-RSA-AES128-SHA256',
                'DHE-RSA-AES128-SHA',
                'DHE-DSS-AES128-SHA256',
                'DHE-RSA-AES256-SHA256',
                'DHE-DSS-AES256-SHA',
                'DHE-RSA-AES256-SHA'
            ];

            $oAdapter->addOption(CURLOPT_SSL_CIPHER_LIST, implode(",", $arrCiphers));
            $oAdapter->addOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        //}

        $oClient->setAdapter($oAdapter);

        return $oClient;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }


    /**
     * Get one page checkout model
     *
     * @return Mage_Checkout_Model_Type_Onepage
     */
    public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * Send Ajax redirect response
     *
     * @return Mage_Checkout_OnepageController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }


    /**
     * Validate session token in checkout/session and get param
     * @return bool
     */
    private function _validateSessionTokenValueArr($token)
    {
        return Mage::helper("bluecom_moduslink/api")->validateSessionTokenValue($token);
    }

    public function listmethodAction()
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','moduslink', array('template' => 'moduslink/list_methods.phtml'));
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
    }

    public function responseAction()
    {
        if ($this->getRequest()->get("flag") == "1" && $this->getRequest()->get("orderId"))
        {
            $orderId = $this->getRequest()->get("orderId");
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, 'Payment Success.');
            $order->save();

            Mage::getSingleton('checkout/session')->unsQuoteId();
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=> false));
        }
        else
        {
            Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/error', array('_secure'=> false));
        }
    }

    /**
     * Generate token through ajax function
     * Needed: CURRENCY.CODE & IS_CARD
     *
     * @return $this
     */
    public function generateSessionTokenAction()
    {
        $result = array(
            "success" => true,
            "error" => false,
            "error_message" => ""
        );

        if(!Mage::helper("bluecom_moduslink")->validateMdFormKey()) {
            $result["success"] = false;
            $result["error"] = true;
            $result["error_message"] = "Invalid security token check.";

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            return $this;
        }

        $params = $this->getRequest()->getParams();
        $group = $params["group"];

        $countryIso2 = Mage::helper('bluecom_moduslink')->getCountryCodeISO2ByStore(Mage::app()->getStore()->getCode());

        $sessionToken = Mage::helper("bluecom_moduslink/api")->generateNewSessionToken(array(
            "group" => $group,
            "IS_CARD" => $params["IS_CARD"],
            "CURRENCY.CODE" => $params["CURRENCY_CODE"],
            "COUNTRY" => $countryIso2,
        ));

        // save session
        // get old if it is exist
        $arrSessionToken = (array)Mage::getSingleton('checkout/session')->getData("md_session_token");

        array_push($arrSessionToken, $sessionToken);
        Mage::getSingleton('checkout/session')->setData("md_session_token", $arrSessionToken);

        $result["data"]["sessionToken"] = $sessionToken;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return $this;
    }

    /**
     * Clearn up session
     */
    private function _cleanUpSession()
    {
        $sessionH = Mage::getSingleton('checkout/session');
        $sessionH->unsetData("moduslink_data");
        $sessionH->unsetData("md_session_token");

        $coreSessionH = Mage::getSingleton("core/session");
        $coreSessionH->unsetData("_taxApiInfoAmount");
    }
}