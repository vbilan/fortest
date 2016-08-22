<?php

class Wf_CustomerBalance_Model_System_Importer extends Varien_Object
{

    public function importBalances($filename)
    {

        /* Local Variables */
        $hasError = false;
        $errorMsg = "";
        $line = 0;

        /* Store indices of titles on first line of csv file */
        $CUSTOMER_BAL_COLUMN_INDEX = -1;
        $CUSTOMER_ID_COLUMN_INDEX = -1;
        $CUSTOMER_EMAIL_COLUMN_INDEX = -1;
        $WEBSITE_ID_INDEX = -1;

        /* Open file handle and read csv file line by line separating comma delaminated values */
        $handle = fopen($filename, "r");

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if ($line == 0) {
                // This is the first line of the csv file. It usually contains titles of columns
                // Next iteration will propagate to "else" statement and increment to line 2 immediately
                $line = 1;

                /* Read in column headers and save indices if they appear */
                $num = count($data);
                for ($index = 0; $index < $num; $index++) {
                    $columnTitle = trim(strtolower($data [$index]));
                    if ($columnTitle === "customer_id" || $columnTitle === "id") {
                        $CUSTOMER_ID_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "balance" || $columnTitle === "customer_balance" || $columnTitle === "store_credit" || $columnTitle === "store_credit_balance" || $columnTitle === "amount") {
                        $CUSTOMER_BAL_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "customer_email" || $columnTitle === "email") {
                        $CUSTOMER_EMAIL_COLUMN_INDEX = $index;
                    }
                    if ($columnTitle === "website_id") {
                        $WEBSITE_ID_INDEX = $index;
                    }
                }

                /* Terminate if no customer identifier column found */
                if ($CUSTOMER_EMAIL_COLUMN_INDEX == -1 && $CUSTOMER_ID_COLUMN_INDEX == -1) {
                    Mage::throwException(Mage::helper('wf_customerbalance')->__("Error on line") . " " . $line . ": " . Mage::helper('wf_customerbalance')->__("No customer identifier in CSV file. Please check the contents of the file."));
                }

                /* Terminate if no store credit column found */
                if ($CUSTOMER_BAL_COLUMN_INDEX == -1) {
                    Mage::throwException(Mage::helper('wf_customerbalance')->__("Error on line") . " " . $line . ": " . Mage::helper('wf_customerbalance')->__("No identifier for \"balance\" in CSV file. Please check the contents of the file."));
                }
            } else {
                try {
                    $line++;
                    // This handles the rest of the lines of the csv file


                    /* Prepare line data based on values provided */
                    $num = count($data);
                    $cust_bal = $data [$CUSTOMER_BAL_COLUMN_INDEX];
                    $custId = null;
                    $cusEmail = null;
                    $websiteId = null;

                    if ($WEBSITE_ID_INDEX != -1) {
                        $websiteId = array_key_exists($WEBSITE_ID_INDEX, $data) ? $data [$WEBSITE_ID_INDEX] : null;
                    }
                    if ($CUSTOMER_EMAIL_COLUMN_INDEX != -1) {
                        $cusEmail = array_key_exists($CUSTOMER_EMAIL_COLUMN_INDEX, $data) ? $data [$CUSTOMER_EMAIL_COLUMN_INDEX] : null; // customer email.
                    }
                    if ($CUSTOMER_ID_COLUMN_INDEX != -1) {
                        $custId = array_key_exists($CUSTOMER_ID_COLUMN_INDEX, $data) ? $data [$CUSTOMER_ID_COLUMN_INDEX] : null; // customer id.
                    } else {
                        // If no customer_id provided, try finding the id by their email
                        // Customer email is website dependent. Either load deafult website or look at website ID provided in file
                        if ($websiteId == null) {
                            $websiteId = Mage::app()->getDefaultStoreView()->getWebsiteId();
                        } else {
                            $websiteId = Mage::app()->getWebsite($websiteId)->getId();
                        }
                        $custId = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail(trim($cusEmail))->getId();
                        if (empty($custId)) {
                            $hasError = true;
                            $errorMsg .= "- " . Mage::helper('wf_customerbalance')->__('Error on line %1$s: Customer with email %2$s was not found in website with id #%3$s', $line, $cusEmail, $websiteId) . "\n";
                            continue;
                        }
                    }
                    /* Start Import */
                    //Load in transfer model
                    $balance = Mage::getModel('wf_customerbalance/balance')
                        ->setCustomerId($custId)
                        ->setWebsiteId($websiteId)
                        ->loadByCustomer();
                    $balance->setHistoryAction(Wf_CustomerBalance_Model_Balance_History::ACTION_IMPORTED);
                    $balance->setAmountDelta((float)$cust_bal);
                    $balance->save();


                    // Keep a record in system log
                    Mage::log("Successfully imported store credit data on line {$line} for following customer: \tcustId: " . $custId . "\n\tcusEmail: " . $cusEmail . "\n\twebsiteId: " . $websiteId . "\n\tstore_credit: " . $cust_bal . "\n", null, "magecredit_import.log");
                } catch (Exception $e) {
                    // Any other errors which happen on each line should be saved and reported at the very end
                    Mage::logException($e);
                    $hasError = true;
                    $errorMsg .= "- " . Mage::helper('wf_customerbalance')->__('Error on line %1$s: %2$s', $line, $e->getMessage()) . "\n";
                }
            }
        }

        fclose($handle);
        if ($hasError) {
            // If there were any errors saved, now's the time to report them
            Mage::throwException(Mage::helper('wf_customerbalance')->__("Store credit balances were imported with the following errors:") . "\n" . $errorMsg);
        }
        return $this;
    }
}
