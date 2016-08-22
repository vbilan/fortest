<?php


/**
 * Resource Setup Model
 *
 * @category    Wellfounded
 * @package     Wf_CustomerBalance
 * @author      Magecredit Team <hi@magecredit.com>
 */
class Wf_CustomerBalance_Model_Resource_Setup extends Mage_Sales_Model_Mysql4_Setup
{
    protected $ex_stack = array();
    /**
     * alter table for each column update and ignore duplicate column errors
     * This is used since "if column not exists" function does not exist
     * for MYSQL.
     *
     * @param unknown_type $installer
     * @param string       $table_name
     * @param array        $columns
     *
     * @return TBT_Rewards_Helper_Mysql4_Install
     */
    public function addColumns($table_name, $columns)
    {
        foreach ($columns as $column) {
            $sql = "ALTER TABLE {$table_name} ADD COLUMN ( {$column} );";
            // run SQL and ignore any errors including (Duplicate column errors)
            try {
                $this->run($sql);
            } catch (Exception $ex) {
                $this->addInstallProblem($ex);
            }
        }

        return $this;
    }

    /**
     * Returns true if any problems occured after installation.
     *
     * @return bool
     */
    public function hasProblems()
    {
        return sizeof($this->ex_stack) > 0;
    }

    public function attemptTableCreation($table)
    {
        try {
            $this->getConnection()->createTable($table);
        } catch (Exception $ex) {
            $this->addInstallProblem($ex);
        }
    }

    /**
     * Returns a string of problems that occured after any installation scripts were run through this helper.
     *
     * @return string message to display to the user
     */
    public function getProblemsString()
    {
        $msg = Mage::helper('wf_customerbalance')->__('The following errors occured while trying to install the module.');
        $msg .= "\n<br>";
        foreach ($this->ex_stack as $ex_i => $ex) {
            $msg .= "<b>#{$ex_i}: </b>";
            if (Mage::getIsDeveloperMode()) {
                $msg .= nl2br($ex);
            } else {
                $msg .= $ex->getMessage();
            }
            $msg .= "\n<br>";
        }
        $msg .= "\n<br>";
        $msg .= Mage::helper('wf_customerbalance')->__('If any of these problems were unexpected, '
            .'I recommend that you contact our support team.');

        return $msg;
    }

    /**
     * Adds an exception problem to the stack of problems that may
     * have occured during installation.
     * Ignores duplicate column name errors; ignore if the msg starts with "SQLSTATE[42S21]: Column already exists".
     *
     * @param Exception $ex
     */
    public function addInstallProblem(Exception $ex)
    {
        if (strpos($ex->getMessage(), 'SQLSTATE[42S21]: Column already exists') !== false) {
            return $this;
        }
        if (strpos($ex->getMessage(), "SQLSTATE[42000]: Syntax error or access violation: 1091 Can't DROP") !== false
                && strpos($ex->getMessage(), 'check that column/key exists') !== false) {
            return $this;
        }
        $this->ex_stack [] = $ex;

        return $this;
    }
}
