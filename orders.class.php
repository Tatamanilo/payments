<?php
/**
* model for work with order entities
* @author       tatana
*/
abstract class orders
{
    /**
    * add new order to db
    * @param    int $user_id    user id who inited the order
    * @param    string $payment_system_name    payment system name of order
    * @param    string $operation_name    operation name of order
    * @param    float $amount    total order amount
    * @return   int order id
    */
    public final function addOrder($user_id, $payment_system_name, $operation_name, $amount)
    {
        global $oDb;
        
        $query = '
            INSERT INTO
                '.DB_PREFIX.'orders
            SET
                sid = "'.SID.'",
                operation_name = "'.$operation_name.'",
                payment_system_name = "'.$payment_system_name.'",
                user_id = "'.$user_id.'",
                amount = "'.$amount.'"
            ';
        $oDb->execute($query);
        
        return $oDb->insert_id();
    }
    
    /**
    * mark order field has_refunds to 1
    * @param    int $order_id
    */
    public final function markOrderHasRefunds($order_id)
    {
        global $oDb;
        
        $query = '
            UPDATE
                '.DB_PREFIX.'orders
            SET
                has_refunds = 1
            WHERE
                sid = "'.SID.'" AND
                order_id = "'.$order_id.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * get order by order id
    * @param    int $order_id    order id
    * @return   array
    */
    public final function getOrder($order_id)
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'orders
            WHERE
                sid = "'.SID.'" AND
                order_id = "'.$order_id.'"
            ';
        $order = $oDb->one_row_assoc($query);
        
        return $order;
    }
    
    /**
    * change order status
    * @param    int $order_id    order id, which status need to be changed
    * @param    string $result    result of operation - completed, pending, error
    * @param    string $result_info    result description - it can be some pending or error reason
    */
    public final function changeOrderStatus($order_id, $result, $result_info = "")
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            UPDATE
                '.DB_PREFIX.'orders
            SET
                status = "'.$result.'",
                error_reason = "'.$result_info.'"
            WHERE
                sid = "'.SID.'" AND
                order_id = "'.$order_id.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * append order error reason string to current one
    * @param    int $order_id    order id
    * @param    string $result_info_append    info need to be appended
    */
    public final function appendOrderErrorReason($order_id, $result_info_append = "")
    {
        global $oDb;
        
        $query = '
            UPDATE
                '.DB_PREFIX.'orders
            SET
                error_reason = CONCAT(error_reason, "'.$result_info.'")
            WHERE
                sid = "'.SID.'" AND
                order_id = "'.$order_id.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * init creation of new order, calls init entries
    * @param    string $payment_system_name    payment system name
    * @param    int $main_account_type_id    the main account type id for the transactions
    */
    public abstract function initOrder($payment_system_name, $main_account_type_id = 0);
    
    /**
    * init creation of order entries and correspondant entries fees
    * @param    int $order_id    order id is been working with
    * @param    int $user_id    user id who inited order
    * @param    string $payment_system_name    name of payment system
    */
    protected abstract function initEntries($order_id, $user_id, $payment_system_name);
    
    /**
    * init creation of entry transactions
    * @param    int $entry_id    entry id is been working with
    * @param    int $user_id    user id who inited order
    * @param    string $payment_system_name    name of payment system are working with
    * @param    float $entry_amount    total entry amount
    * @param    array $fees    array of entry fees
    */
    protected abstract function initTransactions($entry_id, $user_id, $payment_system_name, $entry_amount, $fees = array());
    
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
    public abstract function getFees($user_id, $object_id = 0, $entry_amount = 0);
    
    /**
    * process order, that was previously inited
    * @param    int $order_id    id of order need to be processed
    */
    public abstract function processOrder($order_id);
    
    /**
    * process exact entry of order
    * @param    int $entry_id    id of order entry need to be processed
    */
    protected abstract function processEntry($entry_id);
    
    /**
    * refund previously completed order (wiil refund pending money)
    * @param    int $order_id    id of order need to be refunded
    */
    public abstract function refundOrder($order_id);
    
    /**
    * refund exact order entry
    * @param    int $entry_id    id of order entry need to be refunded
    */
    protected abstract function refundEntry($entry_id);
    
    /**
    * cancel order where some error happened. cancel all completed transactions
    * @param    int $order_id    id of order need to be canceled
    */
    public abstract function cancelOrder($order_id);
    
    /**
    * cancel entry transactions that are completed
    * @param    int $entry_id    id of order entry need to be canceled
    */
    protected abstract function cancelEntry($entry_id);
}

?>