<?php

class Wf_CustomerBalance_Adminhtml_DebugstorecreditController extends Mage_Adminhtml_Controller_Action
{

    public function repairdesignsAction()
    {
        $verbose = $this->getRequest()->getParam('verbose', false);

        if (Mage::helper('wf_customerbalance/debug')->checkDesigns($verbose)) {
            $msg = Mage::helper('wf_customerbalance')->__("There were no design issues detected.");
            Mage::getSingleton('core/session')->addSuccess($msg);
        } else {
            $msg = Mage::helper('wf_customerbalance')->__("Design issues were detected and possibly fixed. Please see notifications for more information.");
            Mage::getSingleton('core/session')->addNotice($msg);
        }
        if (!$verbose) {
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'customer'));
        }

        return $this;
    }

    public function currentRoleAction()
    {
        $view = Mage::getSingleton('admin/session')->isAllowed('customer/storecredit/view');
        echo ("Is allowed to view? ". ($view ? 'yes' : 'no') . '<br>');
        $add_or_deduct = Mage::getSingleton('admin/session')->isAllowed('customer/storecredit/add_or_deduct');
        echo ("Is allowed to add/remove? ". ($add_or_deduct ? 'yes' : 'no') . '<br>');
        exit;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }

}
