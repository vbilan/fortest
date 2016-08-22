<?php

/**
 * Customer account Store Credit tab
 *
 */
class Wf_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Set identifier and title
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerbalance');
        $this->setTitle(Mage::helper('wf_customerbalance')->__('Store Credit (Magecredit)'));
    }

    /**
     * Tab label getter
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->getTitle();
    }

    /**
     * Tab title getter
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTitle();
    }

    /**
     * Check whether tab can be showed
     *
     * @return bool
     */
    public function canShowTab()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('customer/storecredit/view')) {
            return false;
        }
        return true;
    }

    /**
     * Check whether tab should be hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        if (!$this->getRequest()->getParam('id')) {
            return true;
        }
        return false;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Check whether content should be generated
     *
     * @return bool
     */
    public function getSkipGenerateContent()
    {
        return true;
    }

    /**
     * Precessor tab ID getter
     *
     * @return string
     */
    public function getAfter()
    {
        return 'tags';
    }

    /**
     * Tab URL getter
     *
     */
    public function getTabUrl()
    {
        return $this->getUrl('*/customerbalance/form', array('_current' => true));
    }
}
