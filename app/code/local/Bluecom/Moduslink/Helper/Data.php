<?php

class Bluecom_Moduslink_Helper_Data extends Mage_Core_Helper_Abstract 
{
    const UK_STORE_CODE = 'acer_en';
    const FR_STORE_CODE = 'acer_fr';
    const IT_STORE_CODE = 'acer_it';
    const MD_LOGFILE = 'moduslink.log';

    public function getPaymentGatewayUrl()
    {
        return Mage::getUrl('moduslink/payment/gateway', array('_secure' => true));
    }

    public function getCountryCodeISO2ByStore($storeCode = '')
    {
        $currentStoreCode = $storeCode;
        if(empty($currentStoreCode)) {
            $currentStoreCode = Mage::app()->getStore()->getCode();
        }

        switch($currentStoreCode) {
            case 'default':
                return 'US';
                break;
            case self::UK_STORE_CODE:
                return 'GB';
                break;
            case self::FR_STORE_CODE:
                return 'FR';
                break;
            case self::IT_STORE_CODE:
                return 'IT';
                break;
        }
        Mage::helper("bluecom_moduslink")->moduslinkLog("getLangCode", "Get store Code do not exists");
    }

    public function getLangCodeByStore($storeCode = '')
    {
        $currentStoreCode = $storeCode;
        if(empty($currentStoreCode)) {
            $currentStoreCode = Mage::app()->getStore()->getCode();
        }

        switch($currentStoreCode) {
            case self::UK_STORE_CODE || 'default':
                return 'en';
                break;
            case self::FR_STORE_CODE:
                return 'fr';
                break;
            case self::IT_STORE_CODE:
                return 'it';
                break;
        }

        Mage::helper("bluecom_moduslink")->moduslinkLog("getLangCode", "Get store Code do not exists");
    }

    //    Type parameters: ShipementSync, ReturnSync, ProductSync, StockSync
    //    Level parameters
    //    const EMERG   = 0;  // Emergency: system is unusable
    //    const ALERT   = 1;  // Alert: action must be taken immediately
    //    const CRIT    = 2;  // Critical: critical conditions
    //    const ERR     = 3;  // Error: error conditions
    //    const WARN    = 4;  // Warning: warning conditions
    //    const NOTICE  = 5;  // Notice: normal but significant condition
    //    const INFO    = 6;  // Informational: informational messages
    //    const DEBUG   = 7;  // Debug: debug messages
    public function moduslinkLog($type, $message, $level = 6){

        if(is_array($message)) {
            $message = print_r($message, true);
        }
        if($level === 6) {
            if( $this->getApiHelper()->isTestMode() === "1") {
                Mage::log("[".$type."]:\n " . $message, $level, self::MD_LOGFILE);
                return true;
            }
        }
        //else
        Mage::log("[".$type."]:\n " . $message, $level, self::MD_LOGFILE);
    }

    private function getApiHelper()
    {
        return Mage::helper("bluecom_moduslink/api");
    }
}