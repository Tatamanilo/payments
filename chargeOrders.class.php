<?php
FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/orders.class.php');       

/**
* model for work with orders of charge operation
* @author       tatana
*/
class chargeOrders extends orders
{
    
    /**
    * @var      string
    */
    public $operation_name = "charge";
    
    /**
    * init creation of new order, calls init entries
    * @param    string $payment_system_name    payment system name
    * @param    int $main_account_type_id    the main account type id for the transactions
    */
    public function initOrder($payment_system_name, $main_account_type_id = 0)
    {
        global $oSecurity;
        
        $user_id = $oSecurity->GetUserID();
        
        //echo "ca".
        $total_amount = FUNC::SESSION("charge_amount");
        $order_id = $this->addOrder($user_id, $payment_system_name, $this->operation_name, $total_amount);
        
        $this->initEntries($order_id, $user_id, $payment_system_name);

        return $order_id;
    }
    
    /**
    * init creation of order entries and correspondant entries fees
    * @param    int $order_id    order id is been working with
    * @param    int $user_id    user id who inited order
    * @param    string $payment_system_name    name of payment system
    */
    protected function initEntries($order_id, $user_id, $payment_system_name)
    {
        Func::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
        $oEntries = new entries();     
                
        $entry_amount = FUNC::SESSION("charge_amount");   
        $entry_id = $oEntries->addEntry($order_id, $user_id, $entry_amount, $entry_amount);
        
        $this->initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount);
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
       // TODO: implement
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
        $this->changeOrderStatus($entry["entry_id"], "completed");   
        return true;   
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
                
                return false;
            }
        }
        //all transactions is completed successfully so we can change entry status to pending (money can be refunded in a month)
        $oEntries->changeEntryStatus($entry_id, "completed");  
        return true;   
    }
    
    /**
    * refund previously completed order (wiil refund pending money)
    * @param    int $order_id    id of order need to be refunded
    */
    public function refundOrder($order_id)
    {
        
    }
    
    /**
    * refund exact order entry
    * @param    int $entry_id    id of order entry need to be refunded
    */
    protected function refundEntry($entry_id)
    {
        
    }
    
    /**
    * cancel order where some error happened. cancel all completed transactions
    * @param    int $order_id    id of order need to be canceled
    */
    public function cancelOrder($order_id)
    {
        
    }
    
    /**
    * cancel entry transactions that are completed
    * @param    int $entry_id    id of order entry need to be canceled
    */
    protected function cancelEntry($entry_id)
    {
        
    }
}

?>