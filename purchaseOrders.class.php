<?php
FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/orders.class.php'); 

/**
* model for work with orders of purchase operation
* @author       tatana
*/
class purchaseOrders extends orders
{
    /**
    * @var      string
    */
    public $operation_name = "purchase";
    public $object;  // f.e. products or seourls
    
    /**
    * @param    int $user_id    
    * @param    string $payment_system_name    
    * @param    int $main_account_type_id
    */
    public function initOrder($payment_system_name, $main_account_type_id = 0)
    {
        global $oSecurity;
        
        $user_id = $oSecurity->GetUserID();
        
        switch ($this->object)
        {
            case "products":
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cartProducts.class.php');       
                
                $oCartProducts = new cartProducts();   
                $oCartProducts->changeCartStatusToProcessing();
                
                //debug(FUNC::SESSION("cart_new")); 
                        
                //echo "total".
                $total_amount = $oCartProducts->getCartTotalAmount("processing");
                $order_id = $this->addOrder($user_id, $payment_system_name, $this->operation_name, $total_amount);
                
                $this->initEntries($order_id, $user_id, $payment_system_name);

                return $order_id;
                break;
                
            case "upsells":
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cartUpsells.class.php');       
                
                $oCartUpsells = new cartUpsells();   
                $oCartUpsells->changeCartStatusToProcessing();
                
                //debug(FUNC::SESSION("cart_new")); 
                        
                //echo "total".
                $total_amount = $oCartUpsells->getCartTotalAmount("processing");
                $order_id = $this->addOrder($user_id, $payment_system_name, $this->operation_name, $total_amount);
                
                $this->initEntries($order_id, $user_id, $payment_system_name);

                return $order_id;
                break;
                
            case "seourls":
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cartSeourls.class.php');       
                
                $oCartSeourls = new cartSeourls();   
                $oCartSeourls->changeCartStatusToProcessing();
                
                //debug(FUNC::SESSION("cart_new")); 
                        
                //echo "total".
                $total_amount = $oCartSeourls->getCartTotalAmount("processing");
                $order_id = $this->addOrder($user_id, $payment_system_name, $this->operation_name, $total_amount);
                
                $this->initEntries($order_id, $user_id, $payment_system_name);

                return $order_id;
                break;
        }
    }
    
    /**
    * init creation of order entries and correspondant entries fees
    * @param    int $order_id    order id is been working with
    * @param    int $user_id    user id who inited order
    * @param    string $payment_system_name    name of payment system
    */
    protected function initEntries($order_id, $user_id, $payment_system_name)
    {
        switch ($this->object)
        {
            case "products":
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartProducts.class.php');   
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
                
                $oCartProducts = new cartProducts();               
                $oEntries = new entries();     
                        
                $cart_objects = $oCartProducts->getCartObjectsInfo("processing");
                    
                foreach ($cart_objects as $cart_object_id=>$cart_object)
                {
                    $entry_amount = $cart_object["info"]["price"] * $cart_object["count"];
                    
                    $fees = $this->getFees($user_id, $cart_object_id, $entry_amount);
                    
                    $entry_id = $oEntries->addEntry($order_id, $cart_object["info"]["owner_user_id"], $entry_amount, $cart_object["info"]["price"], $cart_object["count"], $this->object, $cart_object_id, $cart_object["info"]["name"], "", $fees);
                    
                    $this->initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount, $fees);
                }
                
                break;
                
            case "upsells":
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartUpsells.class.php');   
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
                
                $oCartUpsells = new cartUpsells();               
                $oEntries = new entries();     
                        
                $cart_objects = $oCartUpsells->getCartObjectsInfo("processing");
                    
                foreach ($cart_objects as $cart_object_id=>$cart_object)
                {
                    $entry_amount = $cart_object["info"]["price"] * $cart_object["count"];
                    
                    $fees = $this->getFees($user_id, $cart_object_id, $entry_amount);
                    
                    $entry_id = $oEntries->addEntry($order_id, $cart_object["info"]["owner_user_id"], $entry_amount, $cart_object["info"]["price"], $cart_object["count"], $this->object, $cart_object_id, $cart_object["info"]["name"], "", $fees);
                    
                    $this->initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount, $fees);
                }
                
                break;
                
            case "seourls":
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartSeourls.class.php');   
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
                
                $oCartSeourls = new cartSeourls();               
                $oEntries = new entries();     
                        
                $cart_objects = $oCartSeourls->getCartObjectsInfo("processing");
                    
                foreach ($cart_objects as $cart_object_id=>$cart_object)
                {
                    $entry_amount = $cart_object["info"]["price"] * $cart_object["count"];
                    
                    $fees = $this->getFees($user_id, $cart_object_id, $entry_amount);
                    
                    //check if recurring
                    if (isset($cart_object["info"]["recurring_params"]))
                    {
                        //recurring
                        $recurring_params = $cart_object["info"]["recurring_params"];
                        //seourls without trial period. so recurring_total = recurring_cycles. otherwise recurring_total = recurring_cycles + trial_cycles
                        $entry_id = $oEntries->addEntry($order_id, $cart_object["info"]["owner_user_id"], $entry_amount, $cart_object["info"]["price"], $cart_object["count"], $this->object, $cart_object_id, $cart_object["info"]["name"], "store name", $fees, 1, $recurring_params["cycles"]);
                        $oEntries->addRecurringEntry($entry_id, $recurring_params['amount'], $recurring_params['cycles'], $recurring_params['period'], $recurring_params['frequency']);
                    }
                    else
                    {
                        //regular payment
                        $entry_id = $oEntries->addEntry($order_id, $cart_object["info"]["owner_user_id"], $entry_amount, $cart_object["info"]["price"], $cart_object["count"], $this->object, $cart_object_id, $cart_object["info"]["name"], "store name", $fees);
                    }
                    
                    $this->initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount, $fees);
                }
                
                break;
        }
    }
    
    /**
    * init creation of entry transactions
    * @param    int $entry_id    entry id is been working with
    * @param    int $user_id    user id who inited order
    * @param    string $payment_system_name    name of payment system are working with
    * @param    float $entry_amount    total entry amount
    * @param    array $fees    array of entry fees
    */
    protected function initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount, $fees = array())
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
        $oPaymentShared = new payment_shared();
        $payment_system = $oPaymentShared->getPaymentSystem($payment_system_name);   
        $payment_system_local = $oPaymentShared->getPaymentSystem("local");   
        $local_account_type_id = $payment_system_local["account_type_id"];       
        
        Func::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');     
        $oTransactions = new transactions();     
        
        // if payment system is not local 
        if (!$payment_system["is_local"])
        {                                                                            
            $system_user_id = FUNC::getModuleSetting("payment", "systemUserId");
            
            // init transaction for transfering money from user (default account type of current payment system) to system (default account type of current payment system)      
            $main_account_type_id = $payment_system["account_type_id"];
            $oTransactions->addTransaction($entry_id, $user_id, $main_account_type_id, $system_user_id, $main_account_type_id, $entry_amount);
            
            // init transaction for transfering money from system (default local account type) to user (default local account type)      
            $oTransactions->addTransaction($entry_id, $system_user_id, $local_account_type_id, $user_id, $local_account_type_id, $entry_amount);
        } 
        
        // in both cases if payment system is local or not init transactions
        // next transactions are local
        $pending_local_account_type_id = -$local_account_type_id;
        foreach ($fees as $fee)
        {
            $oTransactions->addTransaction($entry_id, $user_id, $local_account_type_id, $fee["credit_user_id"], $pending_local_account_type_id, $fee["fee_amount"]);  
        }
    }
    
    /**
    * get the array of fees
    * describes the logic of money distribution: 
    * how much money will receive users are connected with this entry 
    * (f.e. during the purchase of product not only author should receive money,
    * affiliates and system should receive some percentages too)
    * returns array of fees
    *                each fee has fields:
    *                    credit_user_id  
    *                    relative  
    *                    absolute
    *                    fee_amount
    * @param    int $user_id    user who inited order (buyer)
    * @param    int $object_id    cart object id
    * @param    float $entry_amount    total entry amount
    * @return   array
    */
    public function getFees($user_id, $object_id = 0, $entry_amount = 0)
    {
        switch ($this->object)
        {
            // product fees logic
            case "products":
            case "upsells":
                FUNC::includeFile(PATH_2_GLOBALS.'modules/products/products_shared.class.php');      
                $oProdShared = new products_shared(array(), array());
                $product = $oProdShared->getProductById($object_id, true);
                $product_user_id = FUNC::getUserIdByAuthorId($product["author_id"]);
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/affilate/affilate_shared.class.php');
                $oAffShared = new affilate_shared(array(), array());
                
                $fees = array();
                $fees_amount = 0;
                //echo "aff1".
                $aff1_user_id = $oAffShared->getUserAffilate($user_id, false);     // true!!!
                
                if ($aff1_user_id)
                {
                    // first aff fee
                    $fee = array();
                    $fee["type"] = "aff1";
                    $fee["credit_user_id"] = $aff1_user_id;
                    $aff1_relative = (float)(Func::getValue('earn_rate', $product, FUNC::getModuleSetting("payment", "productsPurchaseAff1RelativeFeeDefault")*100)/100);     
                    $fee["relative"] = $aff1_relative; 
                    $fee["absolute"] = 0;     
                    $fee["fee_amount"] = $aff1_relative*$entry_amount; 
                    $fees_amount += $fee["fee_amount"];
                    $fees[] = $fee;
                    unset($fee);
                    
                    $aff2_user_id = $oAffShared->getUserAffilate($aff1_user_id, false);
                
                    if ($aff2_user_id)
                    {
                        // second aff fee
                        $fee = array();    
                        $fee["type"] = "aff2";
                        $fee["credit_user_id"] = $aff2_user_id;
                        $aff2_relative = (float)FUNC::getModuleSetting("payment", "productsPurchaseAff2RelativeFeeDefault");     
                        $fee["relative"] = $aff2_relative; 
                        $fee["absolute"] = 0;  
                        $fee["fee_amount"] = $aff2_relative*$entry_amount; 
                        $fees_amount += $fee["fee_amount"];  
                        $fees[] = $fee;   
                        unset($fee);      
                    }
                }
                
                $system_user_id = FUNC::getModuleSetting("payment", "systemUserId");        
                
                // system relative fee
                $fee = array();     
                $fee["type"] = "system";
                $fee["credit_user_id"] = $system_user_id;
                $system_relative = (float)FUNC::getModuleSetting("payment", "productsPurchaseSystemRelativeFeeDefault");     
                $fee["relative"] = $system_relative; 
                $fee["absolute"] = 0; 
                $fee["fee_amount"] = $system_relative*$entry_amount; 
                $fees_amount += $fee["fee_amount"];  
                $fees[] = $fee;    
                unset($fee);     
                
                if ($entry_amount >= FUNC::getModuleSetting("payment", "productsPurchaseAmountLimitForAbsoluteFee"))
                {
                    // system absolute fee
                    $system_absolute = (float)FUNC::getModuleSetting("payment", "productsPurchaseSystemAbsoluteFeeDefault");     
                    
                    // add system absolute fee only if products author receives more than 0 after system absolute fee
                    if (($entry_amount - $fees_amount - $system_absolute) > 0)
                    {
                        $fee = array();     
                        $fee["type"] = "system";
                        $fee["credit_user_id"] = $system_user_id;
                        $fee["relative"] = 0; 
                        $fee["absolute"] = $system_absolute; 
                        $fee["fee_amount"] = $fee["absolute"]; 
                        $fees_amount += $fee["fee_amount"];  
                        $fees[] = $fee;   
                        unset($fee);      
                    }
                }  
                
                // product author fee
                $fee = array();     
                $fee["type"] = "author";
                $fee["credit_user_id"] = $product_user_id;
                $fee["relative"] = 0; 
                $fee["absolute"] = $entry_amount - $fees_amount; 
                $fee["fee_amount"] = $fee["absolute"]; 
                $fees[] = $fee;    
                unset($fee);  
                
                return $fees;
                break;
                
            case "seourls":
                $system_user_id = FUNC::getModuleSetting("payment", "systemUserId");        
                
                // system fee
                $fee = array();     
                $fee["type"] = "system";
                $fee["credit_user_id"] = $system_user_id;
                $fee["relative"] = 1; 
                $fee["absolute"] = 0; 
                $fee["fee_amount"] = $entry_amount; 
                $fees[] = $fee;    
                unset($fee);     
                
                return $fees;
                break;
        }
    }
    
    /**
    * process order, that was previously inited
    * @param    int $order_id    id of order need to be processed
    */
    public function processOrder($order_id)
    {
        global $oSecurity;
        
        $order = $this->getOrder($order_id);
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/accounts.class.php');     
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');      
                
        $oAccounts = new accounts();  
        $oPaymentShared = new payment_shared();  
        $oEntries = new entries();  
        $oTransactions = new transactions();     
        
        $payment_system = $oPaymentShared->getPaymentSystem($order["payment_system_name"]);
                
        // if there is not enough money on credit user's account
        if (!$oAccounts->checkAccountAmount($order["user_id"], $payment_system["account_type_id"], $order["amount"]))
        {
            $this->changeOrderStatus($order_id, "error", "not enought money on account to pay the order");
            return false;
        }
        else
        {
            // get entries
            $entries = $oEntries->getOrderEntries($order_id);   
            //debug($entries);
            
            foreach ($entries as $entry)
            {
                // process entry
                if (!$this->processEntry($entry["entry_id"]))
                {
                    return false;
                }
            }
            //all entries are completed successfully so we can change order status to completed
            //$this->changeOrderStatus($entry["entry_id"], "completed"); was ???
            $this->changeOrderStatus($order_id, "completed");
            
            if ($this->object == 'seourls')
            {
                //in the end of successful payment mark seourl as paid for user
                FUNC::includeFile(PATH_2_GLOBALS.'modules/seourls/seourls_shared.class.php');       
                $oSeourl = new seourls_shared();
                //echo "<br/>Mark seourls as paid<br/>";
                foreach ($entries as $entry)
                {
                    $seourls = explode(',',$entry['object_name']);
                    foreach ($seourls as $seourl)
                    {
                        $oSeourl->markSeourlAsPaid($seourl);
                    }
                }
            }
            
            return true;   
        }
    }
    
    /**
    * process exact entry of order
    * @param    int $entry_id    id of order entry need to be processed
    */
    protected function processEntry($entry_id)
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');      
        
        $oEntries = new entries();  
        $oTransactions = new transactions();     
        
        $entry = $oEntries->getEntry($entry_id);       
        $transactions = $oTransactions->getEntryTransactions($entry_id, false, "inited");
        //debug($transactions);

        if ($transactions)
        foreach ($transactions as $transaction)
        {
            $result = $oTransactions->execTransaction($transaction["transaction_id"]);
            if ($result["result"] == "error")
            {
                //change entry and order status to error
                $oEntries->changeEntryStatus($entry_id, $result["result"], $result["result_info"]);    
                $this->changeOrderStatus($entry["order_id"], $result["result"], $result["result_info"]);   
                
                // call cancel order procedure
                $this->cancelOrder($entry["order_id"]); 
                
                return false;
            }
        }
        //all transactions is completed successfully so we can change entry status to pending (money can be refunded in a month)
        $oEntries->changeEntryStatus($entry_id, "pending");  
        return true;   
    }
    
    /**
    * refund previously completed order (wiil refund pending money)
    * @param    int $order_id    id of order need to be refunded
    */
    public function refundOrder($order_id)
    {
        global $oSecurity;
        
        $order = $this->getOrder($order_id);
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
                
        $oEntries = new entries();  

        $entries = $oEntries->getOrderEntries($order_id);   
        
        foreach ($entries as $entry)
        {
            if (!$this->refundEntry($entry["entry_id"]))
            {
                return false;
            }
        }
        return true;   
    } 
    
    /**
    * refund exact order entry
    * @param    int $entry_id    id of order entry need to be refunded
    */
    protected function refundEntry($entry_id)
    {
        global $oSecurity;
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');      
                
        $oEntries = new entries();  
        $oTransactions = new transactions();     
        
        $entry = $oEntries->getEntry($entry_id);       
        $transactions = $oTransactions->getEntryTransactions($entry_id, "pending");
        
        if ($transactions)
        foreach ($transactions as $transaction)
        {
            //add new transaction for refund
            $transaction_id = $oTransactions->addTransaction($entry_id, $transaction["credit_user_id"], $transaction["credit_account_type_id"], $transaction["debit_user_id"], $transaction["debit_account_type_id"], $transaction["transaction_amount"]);
            $result = $oTransactions->execTransaction($transaction["transaction_id"]);
            
            if ($result["result"] == "error")
            {
                $oEntries->changeEntryStatus($entry_id, $result["result"], "refund error:".$result["result_info"]);    
                $this->changeOrderStatus($entry_id, $result["result"], "refund error:".$result["result_info"]);    
                return false;
            }
        }
        //all transactions is refunded successfully so we can change entry status to refunded, and mark order as has refunds
        $oEntries->changeEntryStatus($entry_id, "refunded");                  
        $this->markOrderHasRefunds($entry["order_id"]);  
        
        return true; 
    }
    
    /**
    * cancel order where some error happened. cancel all completed transactions
    * @param    int $order_id    id of order need to be canceled
    */
    public function cancelOrder($order_id)
    {
        global $oSecurity;
        
        $order = $this->getOrder($order_id);
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
                
        $oEntries = new entries();  

        // get entries of order with statuses 
        // "pending" (for entries where transactions are completed successfully) and
        // "error" (for entry where error happened. here some of transactions can be completed successfully and one transaction - error)
        $entries_to_cancel = $oEntries->getOrderEntries($order_id, array("pending", "error"));   
        //debug($entries_to_cancel);
        
        foreach ($entries_to_cancel as $entry_to_cancel)
        {
            if (!$this->cancelEntry($entry_to_cancel["entry_id"]))
            {
                return false;
            }
        }
        return true;   
    }
    
    /**
    * cancel entry transactions that are completed
    * @param    int $entry_id    id of order entry need to be canceled
    */
    protected function cancelEntry($entry_id)
    {
        global $oSecurity;
        
        //echo "<br /><br />cancel entry".$entry_id;
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');      
                
        $oEntries = new entries();  
        $oTransactions = new transactions();     
        
        $entry = $oEntries->getEntry($entry_id);       
        //$transactions_to_cancel = $oTransactions->getOrderTransactions($order_id, "pending", "completed");           
        $transactions_to_cancel = $oTransactions->getEntryTransactions($entry_id, "pending", "completed");           
        
        if ($transactions_to_cancel)
        foreach ($transactions_to_cancel as $transaction_to_cancel)
        {
            if (!$oTransactions->cancelTransaction($transaction_to_cancel["transaction_id"]))
            {
                $error_transactions_ids[] = $transaction_to_cancel["transaction_id"];
            }
        }
        if (!empty($error_transactions_ids))
        {
            $oEntries->appendEntryErrorReason($entry_id, "| Error during transactions cancel (".implode(", ", $error_transactions_ids).")");
            $this->appendOrderErrorReason($entry["order_id"], "| Error during transactions cancel (".implode(", ", $error_transactions_ids).")");
            return false;
        }
            
        // for entry with status pending - change status to canceled, for the entries with status error - remain error status
        if ($entry["status"] == "pending")
        {
            $oEntries->changeEntryStatus($entry_id, "canceled", "error during some order transaction");                  
        }
        return true; 
    }
    
    /**
    * init entry and entry transactions of recurring payment
    * @param    string $payment_system_name    
    * @param    int $initial_entry_id    initial entry id that was done for first recurring payment
    * @param    int $recurring_number    current number of recurring payment
    * @param    float $entry_amount    amount of new recurring entry
    */
    public function initRecurringEntry($payment_system_name, $initial_entry_id, $recurring_number, $entry_amount)
    {
        switch ($this->object)
        {
            case "products":
            case "upsells":
            case "seourls":
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
                
                $oEntries = new entries();     
                $initial_entry = $oEntries->getEntry($initial_entry_id);
                
                // if fees should be the same as in initial entry - get it from initial entry, 
                // otherwise - call :
                // $this->getFees($initial_entry["main_credit_user_id"], $initial_entry["object_id"], $entry_amount);
                $fees = $oEntries->getEntryFees($initial_entry_id);
                
                $entry_id = $oEntries->addEntry($initial_entry["order_id"], $initial_entry["main_credit_user_id"], $entry_amount, $entry_amount, 1, $initial_entry["object"], $initial_entry["object_id"], $initial_entry["object_name"], $initial_entry["object_info"], $fees, $recurring_number, $initial_entry["recurring_total"]);
                $this->initTransactions($entry_id, $initial_entry["main_credit_user_id"], $payment_system_name, $entry_amount, $fees);     
                
                return $entry_id;
                break;
        }
    }
    
    
    /**
    * process recurring entry
    * @param    int $entry_id    recurring entry id
    */
    public function processRecurringEntry($entry_id)
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/transactions.class.php');      
        
        $oEntries = new entries();  
        $oTransactions = new transactions();     
        
        $entry = $oEntries->getEntry($entry_id);       
        $transactions = $oTransactions->getEntryTransactions($entry_id, false, "inited");
        //debug($transactions);

        if ($transactions)
        foreach ($transactions as $transaction)
        {
            $result = $oTransactions->execTransaction($transaction["transaction_id"]);
            if ($result["result"] == "error")
            {
                //change entry and order status to error
                $oEntries->changeEntryStatus($entry_id, $result["result"], $result["result_info"]);    
                $this->changeOrderStatus($entry["order_id"], $result["result"], $result["result_info"]);   
                $this->appendOrderErrorReason($entry["order_id"], "; local error in recurring entry payment id=".$entry_id."; ");
                
                return false;
            }
        }
        //all transactions is completed successfully so we can change entry status to pending (money can be refunded in a month)
        $oEntries->changeEntryStatus($entry_id, "pending");  
        return true;   
    }
}

?>
