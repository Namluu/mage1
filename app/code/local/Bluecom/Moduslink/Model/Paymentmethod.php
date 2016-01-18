<?php
class Bluecom_Moduslink_Model_Paymentmethod extends Mage_Payment_Model_Method_Abstract 
{
    protected $_code  = 'moduslink';
    protected $_formBlockType = 'bluecom_moduslink/form_moduslink';
    protected $_infoBlockType = 'bluecom_moduslink/info_moduslink';
}