<?php
//
// +------------------------------------------------------------------------+
// | PHP Version 5                                                          |
// +------------------------------------------------------------------------+
// | Copyright (c) All rights reserved.                                     |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
// | Author:                                                                |
// +------------------------------------------------------------------------+
//
// $Id$
//


/**
* model for work with transaction entity
* @author       tatana
*/
class transactions
{

    /**
    * add new transaction in db
    * @param    int $entry_id    entry id of new transaction
    * @param    int $debit_user_id    user_id - debit side of transaction
    * @param    int $debit_account_type_id    account_type_id of debit side
    * @param    int $credit_user_id    user_id - the credit side of transaction
    * @param    int $credit_account_type_id    account_type_id of credit side
    * @param    float $transaction_amount    transaction amount
    * @param    int $entry_fee_id    id of entry fee according to which the transaction is created 
    * @return   int transaction id
    */
    public function addTransaction($entry_id, $debit_user_id, $debit_account_type_id, $credit_user_id, $credit_account_type_id, $transaction_amount, $entry_fee_id = 0)
    {
        global $oDb;       
        
        $query = '
            INSERT INTO
                '.DB_PREFIX.'transactions
            SET
                sid = "'.SID.'",
                entry_id = "'.$entry_id.'",
                debit_user_id = "'.$debit_user_id.'",
                debit_account_type_id = "'.$debit_account_type_id.'",
                credit_user_id = "'.$credit_user_id.'",
                credit_account_type_id = "'.$credit_account_type_id.'",
                entry_fee_id = "'.$entry_fee_id.'",
                transaction_amount = "'.$transaction_amount.'"
            ';
        $oDb->execute($query);    
        
        return $oDb->insert_id();
    }
    
    /**
    * get array of order transactions
    * @param    int $order_id    order id
    * @param    string $type    credit_account_type - 
                              false - any type
                              "pending" 
                              "real"
    * @param    mixed $status    current status of transactions, false - any status
    */
    public function getOrderTransactions($order_id, $type = false, $status = false)
    {
        global $oDb;      
        
        $where = '';
        if ($type == "pending")
        {
            $where .= '
                credit_account_type_id < 0 AND 
            ';
        }
        elseif ($type == "real")
        {
            $where .= '
                credit_account_type_id > 0 AND 
            ';       
        }
        if ($status)
        {
            $where .= '
                t.status = "'.$status.'" AND
            ';
        }
        
        //echo "<br />".
        $query = '
            SELECT
                t.*
            FROM
                '.DB_PREFIX.'transactions as t,
                '.DB_PREFIX.'entries as e
            WHERE
                '.$where.'
                e.entry_id = t.entry_id AND
                e.order_id = "'.$order_id.'" AND
                e.sid = "'.SID.'" AND
                t.sid = "'.SID.'"
            ';
        $transactions = $oDb->select_assoc($query);   
        
        //debug($transactions);
        
        return $transactions; 
    }
    
    /**
    * get array of transactions by entry id
    * @param    int $entry_id    entry id  
    * @param    string $type    credit_account_type - 
                              false - any type
                              "pending" 
                              "real"
    * @param    mixed $status    current status of transactions, false - any status
    */
    public function getEntryTransactions($entry_id, $type = false, $status = false)
    {
        global $oDb;       
        
        $where = '';
        if ($type == "pending")
        {
            $where .= '
                credit_account_type_id < 0 AND 
            ';
        }
        elseif ($type == "real")
        {
            $where .= '
                credit_account_type_id > 0 AND 
            ';       
        }
        if ($status)
        {
            $where .= '
                status = "'.$status.'" AND
            ';
        }
        
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'transactions
            WHERE
                '.$where.'
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        $transactions = $oDb->select_assoc($query);   
        
        return $transactions; 
    }
    
    /**
    * get transaction info by id
    * @param    int $transaction_id    transaction id
    */
    public function getTransaction($transaction_id)
    {
        global $oDb;       
        
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'transactions
            WHERE
                sid = "'.SID.'" AND
                transaction_id = "'.$transaction_id.'"
            ';
        $transaction = $oDb->one_row_assoc($query);   
        
        return $transaction; 
    }
    
    /**
    * change transaction status
    * @param    int $transaction_id    transaction id, which status need to be changed
    * @param    string $result    result of operation - "inited","completed","error"
    * @param    string $result_info    result description - it can be some error reason
    */
    public function changeTransactionStatus($transaction_id, $result, $result_info = "")
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            UPDATE
                '.DB_PREFIX.'transactions
            SET
                status = "'.$result.'",
                error_reason = "'.$result_info.'"
            WHERE
                sid = "'.SID.'" AND
                transaction_id = "'.$transaction_id.'"
            ';
        return $oDb->execute($query);
    }
    
    
    /**
    * exec the transaction procedure: transfering money from debit to credit
    * @param    int $transaction_id    id of transaction need to be executed
    */
    function execTransaction($transaction_id)
    {
        //echo "<br />-------------------exec trans".$transaction_id;
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/accounts.class.php');     
        $oAccounts = new accounts(); 
        
        $transaction = $this->getTransaction($transaction_id);
        
        // if there is not enough money on credit user's account
        if (!$oAccounts->checkAccountAmount($transaction["debit_user_id"], $transaction["debit_account_type_id"], $transaction["transaction_amount"]))
        {
            // change transaction status to error
            $result["result"] = "error";
            $result["result_info"] = "not enought money on account to pay the transaction";
            $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);
        }
        else
        {
            // exec transaction
            if ($oAccounts->changeAccountAmount($transaction["debit_user_id"], $transaction["debit_account_type_id"], -$transaction["transaction_amount"]))
            {
                if ($oAccounts->changeAccountAmount($transaction["credit_user_id"], $transaction["credit_account_type_id"], $transaction["transaction_amount"]))    
                {
                    if ($transaction["credit_account_type_id"] < 0)
                    {
                        $result["result"] = "pending";
                        $result["result_info"] = "";                                                 
                    }
                    elseif ($transaction["debit_account_type_id"] < 0)
                    {
                        $result["result"] = "refunded";
                        $result["result_info"] = "";    
                    }
                    else
                    {
                        $result["result"] = "completed";
                        $result["result_info"] = "";    
                    }  
                    $this->changeTransactionStatus($transaction["transaction_id"], "completed");
                }
                else
                {
                    $result["result"] = "error";
                    $result["result_info"] = "can not put money to credit_user_id";    
                    $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);      
                }
            }
            else
            {
                $result["result"] = "error";
                $result["result_info"] = "can not take money from debit_user_id";    
                $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);           
            }
        }
        return $result;    
    }
    
    /**
    * cancel the transaction: return money from credit to debit
    * @param    int $transaction_id    id of transaction need to be canceled
    */
    function cancelTransaction($transaction_id)
    {
        //echo "<br />cancel".$transaction_id;
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/accounts.class.php');     
        $oAccounts = new accounts(); 
        
        $transaction = $this->getTransaction($transaction_id);
        
        // if there is not enough money on credit user's account
        if (!$oAccounts->checkAccountAmount($transaction["credit_user_id"], $transaction["credit_account_type_id"], $transaction["transaction_amount"]))
        {
            // change transaction status to error
            $result["result"] = "error";
            $result["result_info"] = "can not cancel transaction, not enought money on credit account to return it debit";
            $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);
        }
        else
        {
            // exec transaction
            if ($oAccounts->changeAccountAmount($transaction["credit_user_id"], $transaction["credit_account_type_id"], -$transaction["transaction_amount"]))
            {
                if ($oAccounts->changeAccountAmount($transaction["debit_user_id"], $transaction["debit_account_type_id"], $transaction["transaction_amount"]))    
                {
                    $result["result"] = "canceled";
                    $result["result_info"] = "tansaction amount is returned from credit to debit successfully";    
                    $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);      
                }
                else
                {
                    $result["result"] = "error";
                    $result["result_info"] = "can not cancel transaction. can not take money from credit_user_id to retun it debit";    
                    $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);      
                }
            }
            else
            {
                $result["result"] = "error";
                $result["result_info"] = "can not cancel transaction. can not return money to debit_user_id";    
                $this->changeTransactionStatus($transaction["transaction_id"], $result["result"], $result["result_info"]);           
            }
            return $result;
        }
    }
}

?>
