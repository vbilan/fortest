<?php

$installer = $this;

$installer->startSetup();

// $this->addColumns($this->getTable('salesrule'), array(
//     "`store_credit_amount` FLOAT(12,4) DEFAULT 0",
// ));

$table = $installer->getConnection()
    ->newTable($installer->getTable('wf_customerbalance/voucher'))
    ->addColumn('voucher_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity' => true,
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
    ), 'Voucher ID')
    ->addColumn('code', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array('nullable' => false), 'Voucher code')
    ->addColumn('redeemed_by_customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
    ), 'ID of customer that redeemed the voucher.')
    ->addColumn('has_been_redeemed', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'unsigned' => true,
        'nullable' => false,
        'default' => '0',
    ), 'Is Customer Notified')
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable' => false,
        'default' => '0.0000',
    ), 'Voucher credit amount')
    ->addColumn('created_by_admin_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned' => true,
    ), 'Admin user that created this voucher')
    ->addColumn('redeemed_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Date Redeemed')
    ->addColumn('expired_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Date Redeemed')
    ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(), 'Base Currency Code')
    ->addIndex($installer->getIdxName('wf_customerbalance/voucher', array('code')), array('code'))
    ->addForeignKey($installer->getFkName('wf_customerbalance/voucher', 'redeemed_by_customer_id', 'customer/entity', 'entity_id'),
        'redeemed_by_customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_SET_NULL)
    ->addForeignKey(
        $installer->getFkName('wf_customerbalance/voucher', 'created_by_admin_id', 'admin/user', 'user_id'),
        'created_by_admin_id', 
        $installer->getTable('admin/user'), 
        'user_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL, 
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Magecredit store credit vouchers table');

$installer->attemptTableCreation($table);

$installer->endSetup();

// Announce installation success!
$install_version = Mage::getConfig()->getNode('modules/Wf_CustomerBalance/version');
$welcomeUrl = "https://www.magecredit.com/welcome.html";
if ($installer->hasProblems()) {
    $msg_title = "Magecredit {$install_version} encountered errors while trying to update our store.";
    $msg_desc = "Magecredit {$install_version} was unable to install itself on your store for some reason. "
        . "You can configure Magecredit in the Customer section of your Configuration panel. "
        . "The error message was {$installer->getProblemsString()}. "
        . "Please contact our support team at hi@magecredit.com so we can help you solve this issue. ";
    $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
    Mage::helper('wf_customerbalance/debug')->createInstallNotice($msg_title, $msg_desc, $welcomeUrl, $severity);
}

