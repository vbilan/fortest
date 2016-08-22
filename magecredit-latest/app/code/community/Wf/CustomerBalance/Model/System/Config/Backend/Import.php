<?php


class Wf_CustomerBalance_Model_System_Config_Backend_Import extends Mage_Core_Model_Config_Data
{

    public function _afterSave()
    {
        $tmp_fn = $_FILES["groups"]["tmp_name"]["wf_customerbalance"]["fields"]["import_store_credit"]["value"];
        if (!empty($tmp_fn)) {
            $this->importBalances($tmp_fn);
        }
        return parent::_afterSave();
    }


    protected function importBalances($tmp_fn)
    {
        try {
            Mage::getSingleton('wf_customerbalance/system_importer')->importBalances($tmp_fn);
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('wf_customerbalance')->__('Customer store credit balances were imported successfully.')
            );
        } catch (Exception $e) {
            $messages = explode("\n", $e->getMessage());
            foreach ($messages as $message) {
                if (!empty($message)) {
                    Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('wf_customerbalance')->__($message));
                }
            }
            Mage::logException($e);
        }
        return $this;
    }

}