<?php

/**
 * Customerbalance history model
 *
 *
 * @category    Wellfounded
 * @package     Wf_CustomerBalance
 * @author      Magecredit Team <hi@magecredit.com>
 */

class Wf_CustomerBalance_Model_Balance_History extends Mage_Core_Model_Abstract
{
    const ACTION_UPDATED = 1;
    const ACTION_CREATED = 2;
    const ACTION_USED = 3;
    const ACTION_REFUNDED = 4;
    const ACTION_REVERTED = 5;
    const ACTION_IMPORTED = 6;
    const ACTION_EXPIRED = 7;

    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('wf_customerbalance/balance_history');
    }

    /**
     * Attempts to parse out an order from the history model's additional_info field.
     * @return Mage_Sales_Model_Order if no order is found, then the ID of the order model will be empty
     */
    public function getOrder()
    {
        $order = Mage::getModel('sales/order');
        $info = $this->getAdditionalInfo();

        preg_match("/(order #)([0-9]+)/i", $info, $results);
        if(!isset($results[2]) || empty($results[2])) {
            return $order; // empty entity - not found.
        }
        $incrementId = trim($results[2]);

        $order->load($incrementId, 'increment_id');


        return $order;
    }


    /**
     * Attempts to parse out a credit memo model from the history model's additional_info field.
     * @return Mage_Sales_Model_Order_Creditmemo if no credit memo is found, then the ID of the credit memo model will be empty
     */
    public function getCreditmemo()
    {
        $creditmemo = Mage::getModel('sales/order_creditmemo');
        $info = $this->getAdditionalInfo();

        preg_match("/(creditmemo #)([0-9]+)/i", $info, $results);
        if(!isset($results[2]) || empty($results[2])) {
            return $creditmemo; // empty entity - not found.
        }
        $incrementId = trim($results[2]);

        $creditmemo->load($incrementId, 'increment_id');

        return $creditmemo;
    }

    /**
     * Available action names getter
     *
     * @return array
     */
    public function getActionNamesArray()
    {
        return array(
            self::ACTION_CREATED => Mage::helper('wf_customerbalance')->__('Created'),
            self::ACTION_UPDATED => Mage::helper('wf_customerbalance')->__('Updated'),
            self::ACTION_USED => Mage::helper('wf_customerbalance')->__('Used'),
            self::ACTION_REFUNDED => Mage::helper('wf_customerbalance')->__('Refunded'),
            self::ACTION_REVERTED => Mage::helper('wf_customerbalance')->__('Reverted'),
            self::ACTION_IMPORTED => Mage::helper('wf_customerbalance')->__('Imported'),
            self::ACTION_EXPIRED => Mage::helper('wf_customerbalance')->__('Expired'),
        );
    }

    /**
     * Validate balance history before saving
     *
     * @return Wf_CustomerBalance_Model_Balance_History
     */
    protected function _beforeSave()
    {
        $balance = $this->getBalanceModel();
        if ((!$balance) || !$balance->getId()) {
            Mage::throwException(Mage::helper('wf_customerbalance')->__('Balance history cannot be saved without existing balance.'));
        }

        $this->addData(array(
            'balance_id' => $balance->getId(),
            'updated_at' => time(),
            'balance_amount' => $balance->getAmount(),
            'balance_delta' => $balance->getAmountDelta(),
        ));

        switch ((int)$balance->getHistoryAction()) {
            case self::ACTION_CREATED:
                // break intentionally omitted
            case self::ACTION_UPDATED:
                if (!$balance->getUpdatedActionAdditionalInfo()) {
                    if ($user = Mage::getSingleton('admin/session')->getUser()) {
                        if ($user->getUsername()) {
                            if (!trim($balance->getComment())) {
                                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('By admin: %s.', $user->getUsername()));
                            } else {
                                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('By admin: %1$s. (%2$s)', $user->getUsername(), $balance->getComment()));
                            }
                        }
                    }
                } else {
                    $this->setAdditionalInfo($balance->getUpdatedActionAdditionalInfo());
                }
                break;
            case self::ACTION_IMPORTED:
                if (!$balance->getUpdatedActionAdditionalInfo()) {
                    if ($user = Mage::getSingleton('admin/session')->getUser()) {
                        if ($user->getUsername()) {
                            if (!trim($balance->getComment())) {
                                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('Improted by admin: %s.', $user->getUsername()));
                            } else {
                                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('Improted by admin: %1$s. (%2$s)', $user->getUsername(), $balance->getComment()));
                            }
                        }
                    }
                } else {
                    $this->setAdditionalInfo($balance->getUpdatedActionAdditionalInfo());
                }
                break;
            case self::ACTION_USED:
                $this->_checkBalanceModelOrder($balance);
                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('Order #%s', $balance->getOrder()->getIncrementId()));
                break;
            case self::ACTION_REFUNDED:
                $this->_checkBalanceModelOrder($balance);
                if ((!$balance->getCreditMemo()) || !$balance->getCreditMemo()->getIncrementId()) {
                    Mage::throwException(Mage::helper('wf_customerbalance')->__('There is no creditmemo set to balance model.'));
                }
                $this->setAdditionalInfo(
                    Mage::helper('wf_customerbalance')->__('Order #%s, creditmemo #%s', $balance->getOrder()->getIncrementId(), $balance->getCreditMemo()->getIncrementId())
                );
                break;
            case self::ACTION_REVERTED:
                $this->_checkBalanceModelOrder($balance);
                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('Order #%s', $balance->getOrder()->getIncrementId()));
                break;
            case self::ACTION_EXPIRED:
                $this->_checkBalanceModelOrder($balance);
                $this->setAdditionalInfo(Mage::helper('wf_customerbalance')->__('Remaining balance expired after order #%s', $balance->getOrder()->getIncrementId()));
                break;
            default:
                Mage::throwException(Mage::helper('wf_customerbalance')->__('Unknown balance history action code'));
            // break intentionally omitted
        }
        $this->setAction((int)$balance->getHistoryAction());

        return parent::_beforeSave();
    }

    /**
     * Send balance update if required
     *
     * @return Wf_CustomerBalance_Model_Balance_History
     */
    protected function _afterSave()
    {
        parent::_afterSave();

        // attempt to send email
        $this->setIsCustomerNotified(false);
        if ($this->getBalanceModel()->getNotifyByEmail()) {
            $storeId = $this->getBalanceModel()->getStoreId();
            $email = Mage::getModel('core/email_template')->setDesignConfig(array('store' => $storeId));
            $customer = $this->getBalanceModel()->getCustomer();
            $email->sendTransactional(
                Mage::getStoreConfig('customer/wf_customerbalance/email_template', $storeId),
                Mage::getStoreConfig('customer/wf_customerbalance/email_identity', $storeId),
                $customer->getEmail(), $customer->getName(),
                array(
                    'balance' => Mage::app()->getWebsite($this->getBalanceModel()->getWebsiteId())
                        ->getBaseCurrency()->format($this->getBalanceModel()->getAmount(), array(), false),
                    'name' => $customer->getName(),
                ));
            if ($email->getSentSuccess()) {
                $this->getResource()->markAsSent($this->getId());
                $this->setIsCustomerNotified(true);
            }
        }

        return $this;
    }

    /**
     * Validate order model for balance update
     *
     * @param Mage_Sales_Model_Order $model
     */
    protected function _checkBalanceModelOrder($model)
    {
        if ((!$model->getOrder()) || !$model->getOrder()->getIncrementId()) {
            Mage::throwException(Mage::helper('wf_customerbalance')->__('There is no order set to balance model.'));
        }
    }

    /**
     * Retrieve history data items as array
     *
     * @param  string $customerId
     * @param string|null $websiteId
     * @return array
     */
    public function getHistoryData($customerId, $websiteId = null)
    {
        $result = array();
        /** @var $collection Wf_CustomerBalance_Model_Resource_Balance_History_Collection */
        $collection = $this->getCollection()->loadHistoryData($customerId, $websiteId);
        foreach ($collection as $historyItem) {
            $result[] = $historyItem->getData();
        }
        return $result;
    }

}
