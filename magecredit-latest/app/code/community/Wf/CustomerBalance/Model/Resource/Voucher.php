<?php

class Wf_CustomerBalance_Model_Resource_Voucher extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('wf_customerbalance/voucher', 'voucher_id');
    }
}
