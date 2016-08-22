<?php

class Wf_CustomerBalance_Block_Adminhtml_Promo_Voucher_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        // $this->_objectId = 'id';
        $this->_blockGroup = 'wf_customerbalance';
        $this->_controller = 'adminhtml_promo_voucher';

        if (Mage::registry('voucher')->getHasBeenRedeemed()) {
            $this->_removeButton('save');
            $this->_removeButton('delete');
            $this->_removeButton('reset');
        } else {
            $this->_updateButton('save', 'label', $this->__('Save Voucher'));
            $this->_updateButton('delete', 'label', $this->__('Delete Voucher'));
            $this->_addButton('saveandcontinue', array(
                'label'     => $this->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                'class'     => 'save',
            ), -100);
        }

        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'   => true,
            'back'       => 'edit',
        ));
    }

    public function getHeaderText()
    {
        if (Mage::registry('voucher') && Mage::registry('voucher')->getId()) {
            return Mage::helper('wf_customerbalance')->__('Edit Voucher #%s', $this->htmlEscape(Mage::registry('voucher')->getId()));
        } else {
            return Mage::helper('wf_customerbalance')->__('Create New Voucher');
        }
    }
}
