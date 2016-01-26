<?php

class Bluecom_Moduslink_Block_Info_Moduslink extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation)
        {
            return $this->_paymentSpecificInformation;
        }

        $data = array();
        if ($this->getInfo()->getModuslinkInfo())
        {
            $data[Mage::helper('payment')->__('Moduslink info')] = $this->getInfo()->getModuslinkInfo();
        }

        $transport = parent::_prepareSpecificInformation($transport);

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}