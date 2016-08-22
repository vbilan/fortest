<?php

$installer = $this;
/* @var $installer Wf_CustomerBalance_Model_Resource_Setup */
$installer->startSetup();

try {
    /**
     * Create table 'wf_customerbalance/balance'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('wf_customerbalance/balance'))
        ->addColumn('balance_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'Balance Id')
        ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Customer Id')
        ->addColumn('website_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
        ), 'Website Id')
        ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => false,
            'default' => '0.0000',
        ), 'Balance Amount')
        ->addColumn('base_currency_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(), 'Base Currency Code')
        ->addIndex($installer->getIdxName('wf_customerbalance/balance', array('customer_id', 'website_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
            array('customer_id', 'website_id'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
        ->addIndex($installer->getIdxName('wf_customerbalance/balance', array('website_id')),
            array('website_id'))
        ->addForeignKey($installer->getFkName('wf_customerbalance/balance', 'website_id', 'core/website', 'website_id'),
            'website_id', $installer->getTable('core/website'), 'website_id',
            Varien_Db_Ddl_Table::ACTION_SET_NULL, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->addForeignKey($installer->getFkName('wf_customerbalance/balance', 'customer_id', 'customer/entity', 'entity_id'),
            'customer_id', $installer->getTable('customer/entity'), 'entity_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Magecredit Customerbalance');

    try {
        $installer->getConnection()->createTable($table);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Base table or view already exists") === false) {
            throw $e;
        }
    }


    /**
     * Create table 'wf_customerbalance/balance_history'
     */
    $table = $installer->getConnection()
        ->newTable($installer->getTable('wf_customerbalance/balance_history'))
        ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary' => true,
        ), 'History Id')
        ->addColumn('balance_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Balance Id')
        ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(), 'Updated At')
        ->addColumn('action', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Action')
        ->addColumn('balance_amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => false,
            'default' => '0.0000',
        ), 'Balance Amount')
        ->addColumn('balance_delta', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable' => false,
            'default' => '0.0000',
        ), 'Balance Delta')
        ->addColumn('additional_info', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(), 'Additional Info')
        ->addColumn('is_customer_notified', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
        ), 'Is Customer Notified')
        ->addIndex($installer->getIdxName('wf_customerbalance/balance_history', array('balance_id')),
            array('balance_id'))
        ->addForeignKey($installer->getFkName('wf_customerbalance/balance_history', 'balance_id', 'wf_customerbalance/balance', 'balance_id'),
            'balance_id', $installer->getTable('wf_customerbalance/balance'), 'balance_id',
            Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
        ->setComment('Magecredit Customerbalance History');

    try {
        $installer->getConnection()->createTable($table);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), "Base table or view already exists") === false) {
            throw $e;
        }
    }


    // Modify Sales Entities
    //  0.0.5 => 0.0.6
    // Renamed: base_customer_balance_amount_used => base_customer_bal_amount_used
    $installer->addAttribute('quote', 'customer_balance_amount_used', array('type' => 'decimal'));
    $installer->addAttribute('quote', 'base_customer_bal_amount_used', array('type' => 'decimal'));


    $installer->addAttribute('quote_address', 'base_customer_balance_amount', array('type' => 'decimal'));
    $installer->addAttribute('quote_address', 'customer_balance_amount', array('type' => 'decimal'));

    $installer->addAttribute('order', 'base_customer_balance_amount', array('type' => 'decimal'));
    $installer->addAttribute('order', 'customer_balance_amount', array('type' => 'decimal'));

    $installer->addAttribute('order', 'base_customer_balance_invoiced', array('type' => 'decimal'));
    $installer->addAttribute('order', 'customer_balance_invoiced', array('type' => 'decimal'));

    $installer->addAttribute('order', 'base_customer_balance_refunded', array('type' => 'decimal'));
    $installer->addAttribute('order', 'customer_balance_refunded', array('type' => 'decimal'));

    $installer->addAttribute('invoice', 'base_customer_balance_amount', array('type' => 'decimal'));
    $installer->addAttribute('invoice', 'customer_balance_amount', array('type' => 'decimal'));

    $installer->addAttribute('creditmemo', 'base_customer_balance_amount', array('type' => 'decimal'));
    $installer->addAttribute('creditmemo', 'customer_balance_amount', array('type' => 'decimal'));

    // 0.0.6 => 0.0.7
    $installer->addAttribute('quote', 'use_customer_balance', array('type' => 'integer'));

    // 0.0.9 => 0.0.10
    // Renamed: base_customer_balance_total_refunded    => bs_customer_bal_total_refunded
    // Renamed: length: customer_balance_total_refunded => customer_bal_total_refunded
    $installer->addAttribute('creditmemo', 'bs_customer_bal_total_refunded', array('type' => 'decimal'));
    $installer->addAttribute('creditmemo', 'customer_bal_total_refunded', array('type' => 'decimal'));

    $installer->addAttribute('order', 'bs_customer_bal_total_refunded', array('type' => 'decimal'));
    $installer->addAttribute('order', 'customer_bal_total_refunded', array('type' => 'decimal'));

    $installer->endSetup();


    // Check for issues
    try {
        Mage::helper('wf_customerbalance/debug')->checkInstall();
    } catch (Exception $e) {
        // Ignore any checker errors.
    }

    $error = null;

} catch (Exception $e) {
    Mage::logException($e);
    $error = $e->getMessage();
}


// Announce installation success!
$install_version = Mage::getConfig()->getNode('modules/Wf_CustomerBalance/version');
$welcomeUrl = "https://www.magecredit.com/welcome.html";
if ($error) {
    $msg_title = "Magecredit {$install_version} was unable to install itself on your store for some reason.";
    $msg_desc = "Magecredit {$install_version} was unable to install itself on your store for some reason. "
        . "You can configure Magecredit in the Customer section of your Configuration panel. "
        . "The error message was {$error}. "
        . "Please contact our support team at hi@magecredit.com so we can help you solve this issue. ";
    $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
} else {
    $msg_title = "Magecredit {$install_version} was successfully installed! Remember to flush all cache, recompile and log-out and log back in.";
    $msg_desc = "Magecredit {$install_version} was successfully installed on your store. "
        . "Remember to flush all cache, recompile and log-out and log back in. "
        . "You can now go into any customer's account an add/remove store credits."
        . "You can configure Magecredit in the Customer Configuration section of your Magento configuration screen.";
    $severity = Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE;
}
Mage::helper('wf_customerbalance/debug')->createInstallNotice($msg_title, $msg_desc, $welcomeUrl, $severity);
