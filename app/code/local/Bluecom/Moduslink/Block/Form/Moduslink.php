<?php

class Bluecom_Moduslink_Block_Form_Moduslink extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('moduslink/form/moduslink.phtml');
    }
}