<?php

class Wf_CustomerBalance_Model_Api2_Balance_Rest_Admin_V1 extends Wf_CustomerBalance_Model_Api2_Balance
{

    protected function _retrieve()
    {
        $balance = $this->_loadCustomerById($this->getRequest()->getParam('id'));

        return $balance->getData();
    }

    /**
     * Get customers store credit list.
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        $data = $this->_getCollectionForRetrieve()->load()->toArray();

        return isset($data['items']) ? $data['items'] : $data;
    }

    /**
     * Update customer store credit balance.
     *
     * @param array $data
     *
     * @throws Mage_Api2_Exception
     */
    protected function _update(array $data)
    {
        try {
            $customerId = $this->getRequest()->getParam('id');
            $balance = $this->_loadCustomerById($customerId);

            unset($data['balance_id']);
            unset($data['customer_id']);

            if (!isset($data['action'])) {
                $data['action'] = 'update';
            }

            $oldBalance = $balance->getAmount();
            $requestAmount = $data['amount'];


            if ($data['action'] == 'add') {
                $data['amount'] = $oldBalance + $requestAmount;
            } elseif ($data['action'] == 'subtract') {
                $data['amount'] = $oldBalance - $requestAmount;
            } elseif ($data['action'] == 'update') {
                $data['amount'] = $requestAmount;
            } else {
                throw new Mage_Core_Exception('Invalid action specified. Only add/subtract/update are allowed.');
            }

            $balance->addData($data);

            $balance->save();

            $newBalanceModel = $this->_loadCustomerById($customerId);

            if ($newBalanceModel->getAmount() == $balance->getAmount() && $balance->getId()) {
                $msg = Mage::helper('wf_customerbalance')->__("The customer balance could not be updated, most likely because the request to modify the store credit balance was bad (such as trying to decrease balance below '0', or add a non-valid number, or add/subtract '0', etc...");
                $this->_error($msg, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }

        } catch (Mage_Core_Exception $e) {
            $this->_error($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    protected function _loadCustomerById($id)
    {
        $balance = Mage::getModel('wf_customerbalance/balance');
        if ($websiteId = $this->getRequest()->getParam('website_id', null)) {
            $balance->setWebsiteId($websiteId);
        }
        $balance->setCustomerId($id)->loadByCustomer();

        return $balance;
    }

    protected function _getCollectionForRetrieve()
    {
        $collection = Mage::getModel('wf_customerbalance/balance')->getCollection();

        return $collection;
    }
}
