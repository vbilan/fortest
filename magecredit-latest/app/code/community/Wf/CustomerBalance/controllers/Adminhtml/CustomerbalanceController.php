<?php

/**
 * Controller for Customer account -> Store Credit ajax tab and all its contents
 *
 */
class Wf_CustomerBalance_Adminhtml_CustomerbalanceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check is enabled module in config
     *
     * @return Wf_CatalogEvent_Adminhtml_Catalog_EventController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('wf_customerbalance')->isEnabled()) {
            if ($this->getRequest()->getActionName() != 'noroute') {
                $this->_forward('noroute');
            }
        }
        return $this;
    }

    /**
     * Customer balance form
     *
     */
    public function formAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Redirects the request to an entity's page based on the history ID. This is used because 
     * the history model does not natively store the order ID/credit memo, but we may still want to
     * make it easy for an admin to access the order/credit memo model.
     */
    public function viewStoreCreditHistoryEntityAction()
    {
        $type = $this->getRequest()->getParam('type', 'order');
        $history_id = $this->getRequest()->getParam('history_id', null);

        $history = Mage::getModel('wf_customerbalance/balance_history')->load($history_id);

        if($type == 'creditmemo') {
            $entity = $history->getCreditmemo();
            $path = "adminhtml/sales_creditmemo/view";
            $this->_redirect($path, array('creditmemo_id' => $entity->getId()));
        } else {
            $entity = $history->getOrder();
            $path = "adminhtml/sales_order/view";
            $this->_redirect($path, array('order_id' => $entity->getId()));
        }

        if(empty($entity) || !$entity->getId()) {
            Mage::throwException("No {$type} entity found for history ID of #{$history_id}.");
        }

        return $this;


    }

    /**
     * Customer balance grid
     *
     */
    public function gridHistoryAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('wf_customerbalance/adminhtml_customer_edit_tab_customerbalance_balance_history_grid')->toHtml()
        );
    }

    /**
     * Delete orphan balances
     *
     */
    public function deleteOrphanBalancesAction()
    {
        $balance = Mage::getSingleton('wf_customerbalance/balance')->deleteBalancesByCustomerId(
            (int)$this->getRequest()->getParam('id')
        );
        $this->_redirect('*/customer/edit/', array('_current' => true));
    }

    /**
     * Instantiate customer model
     *
     * @param string $idFieldName
     */
    protected function _initCustomer($idFieldName = 'id')
    {
        $customer = Mage::getModel('customer/customer')->load((int)$this->getRequest()->getParam($idFieldName));
        if (!$customer->getId()) {
            Mage::throwException(Mage::helper('wf_customerbalance')->__('Failed to initialize customer'));
        }
        Mage::register('current_customer', $customer);
    }

    /**
     * Check is allowed customer management
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
