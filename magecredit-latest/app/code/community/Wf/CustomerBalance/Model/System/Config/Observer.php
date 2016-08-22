<?php

class Wf_CustomerBalance_Model_System_Config_Observer extends Varien_Object
{

    public function checkDesigns(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('wf_customerbalance')->isEnabled()) {
            return $this;
        }

        $this->_checkDesigns();

        return $this;

    }

    public function checkConfigDesigns(Varien_Event_Observer $observer)
    {
        if (!Mage::helper('wf_customerbalance')->isEnabled()) {
            return $this;
        }

        $event = $observer->getEvent();
        if (!$event) {
            return $this;
        }

        $action = $event->getControllerAction();
        if (!$action) {
            return $this;
        }

        $request = $action->getRequest();
        if (!$request) {
            return $this;
        }

        if ($request->getParam('section') != 'design') {
            return $this;
        }

        $this->_checkDesigns();

        return $this;
    }

    protected function _checkDesigns()
    {
        try {
            Mage::helper('wf_customerbalance/debug')->checkDesigns();
        } catch (Exception $e) {
            Mage::log("Magecredit encountered an error while trying to automatically integrate into designs: " . $e->getMessage());
            Mage::logException($e);
        }

        return $this;
    }

}