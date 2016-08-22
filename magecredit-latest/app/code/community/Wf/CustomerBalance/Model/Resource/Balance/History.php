<?php


/**
 * Customerbalance history resource model
 *
 * @category    Wellfounded
 * @package     Wf_CustomerBalance
 * @author      Magecredit Team <hi@magecredit.com>
 */
class Wf_CustomerBalance_Model_Resource_Balance_History extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource
     *
     */
    protected function _construct()
    {
        $this->_init('wf_customerbalance/balance_history', 'history_id');
    }

    /**
     * Set updated_at automatically before saving
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Wf_CustomerBalance_Model_Resource_Balance_History
     */
    public function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setUpdatedAt($this->formatDate(time()));
        return parent::_beforeSave($object);
    }

    /**
     * Mark specified balance history record as sent to customer
     *
     * @param int $id
     */
    public function markAsSent($id)
    {
        $this->_getWriteAdapter()->update($this->getMainTable(),
            array('is_customer_notified' => 1),
            array('history_id = ?' => $id)
        );
    }
}
