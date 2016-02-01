<?php
/**
 * Varien HTTP Client
 *
 * @category   Bluecom
 * @package    Bluecom_Http
 * @author     Bluecom group
 */
class Bluecom_Http_Client extends Varien_Http_Client
{

    protected function _trySetCurlAdapter()
    {
        if (extension_loaded('curl')) {
            if( is_null($this->adapter) || ! $this->adapter instanceof Varien_Http_Adapter_Curl ) {
                $this->setAdapter(new Varien_Http_Adapter_Curl());
            }
        }
        else {
            Mage::helper('bluecom_moduslink')->moduslinkLog("[SERVER]", "Curl extension must be enable", Zend_Log::ERR);
        }

        return $this;
    }
}
