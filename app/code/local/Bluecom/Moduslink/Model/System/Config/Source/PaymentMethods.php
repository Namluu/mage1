<?php
class Bluecom_Moduslink_Model_System_Config_Source_PaymentMethods
{
    protected $_options;

    public function toOptionArray($isMultiselect=false)
    {
        if (!$this->_options) {
            $this->_options = array(
                "AMEX" => array(
                    "label" => "American Express",
                    "value" => "AMEX",
                ),

                "MASTER" => array(
                    "label" => "MasterCard",
                    "value" => "MASTER"
                ),

                "VISA" => array(
                    "label" => "Visa Credit card",
                    "value" => "VISA",
                ),

                "PAYPAL" => array(
                    "label" => "PayPal Wallet",
                    "value" => "PAYPAL"
                ),
            );
        }

        $options = $this->_options;
        if(!$isMultiselect){
            array_unshift($options, array('value'=>'', 'label'=> Mage::helper('bluecom_moduslink')->__('--Please Select--')));
        }

        return $options;
    }
}