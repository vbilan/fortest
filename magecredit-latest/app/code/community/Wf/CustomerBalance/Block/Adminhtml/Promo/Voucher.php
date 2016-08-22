<?php

class Wf_CustomerBalance_Block_Adminhtml_Promo_Voucher extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_promo_voucher';
        $this->_blockGroup = 'wf_customerbalance';
        $this->_headerText = $this->__("Store Credit Vouchers (Magecredit)");
        $this->_addButtonLabel = $this->__("Create New Voucher");
        parent::__construct();
    }

}
