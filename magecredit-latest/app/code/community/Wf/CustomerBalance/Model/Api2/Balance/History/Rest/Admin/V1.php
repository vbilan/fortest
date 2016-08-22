<?php

class Wf_CustomerBalance_Model_Api2_Balance_History_Rest_Admin_V1 extends Wf_CustomerBalance_Model_Api2_Balance
{
    

    protected function _retrieveCollection()
    {
        $history = $this->_loadCustomerById($this->getRequest()->getParam('id'));
        
        $data = $history->load()->toArray();

        return isset($data['items']) ? $data['items'] : $data;
    }

    protected function _loadCustomerById($id)
    {
        $collection = $this->_getCollectionForRetrieve()
            ->addFieldToFilter('customer_id', $id);

        return $collection;
    }

    protected function _getCollectionForRetrieve()
    {

        $collection = Mage::getModel('wf_customerbalance/balance_history')
            ->getCollection()
            ->addOrder('updated_at', 'DESC')
            ->addOrder('history_id', 'DESC');

        if ($websiteId = $this->getRequest()->getParam('website_id', null)) {
            $collection->addWebsitesFilter($websiteId);
        }

        return $collection;
    }
}
