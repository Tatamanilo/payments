<?php
/**
* model for work with entries and entry fees entities
* @author       tatana
*/
class entries
{
    
    /**
    * adds new entry to db and calls new entry fees creation functions
    * @param    int $order_id    order id of entry
    * @param    int $main_credit_user_id    user id who is object owner
    * @param    float $entry_amount    total entry amount = object price * count
    * @param    float $object_amount    object price
    * @param    int $count    entry object count
    * @param    object $object    object type (f.e. products)
    * @param    int $object_id    object id
    * @param    string $object_name    name of object
    * @param    string $object_info    some object info - f.e. name of product
    * @param    array $fees    array of fees, the array that describes how much money will receive users are connected with this entry (f.e. during the purchase of product not only author should receive money, affiliates and system should receive some percentages too)
    * @param    int $recurring_number    current number of recurring payment
    * @param    int $recurring_total    total number of recurring payments
    * @param    boolean $enable_refund    whether refund is able for this entry
    * @return   int entry id
    */
    public function addEntry($order_id, $main_credit_user_id, $entry_amount, $object_amount, $count = 1, $object = "", $object_id = 0, $object_name = '', $object_info = '', $fees = array( ), $recurring_number = 1, $recurring_total = 1, $enable_refund = true)
    {
       global $oDb;
        
        $query = '
            INSERT INTO
                '.DB_PREFIX.'entries
            SET
                sid = "'.SID.'",
                order_id = "'.$order_id.'",
                main_credit_user_id = "'.$main_credit_user_id.'",
                recurring_number = "'.$recurring_number.'",
                recurring_total = "'.$recurring_total.'",
                object = "'.$object.'",
                object_id = "'.$object_id.'",
                object_amount = "'.$object_amount.'",
                object_name = "'.$object_name.'",
                object_info = "'.$object_info.'",
                entry_amount = "'.$entry_amount.'",
                count = "'.$count.'"
            ';
        $oDb->execute($query);
        
        $entry_id = $oDb->insert_id();
        
        if ($entry_id && $fees)
        {
            foreach ($fees as $fee)
            {
                $this->addEntryFee($entry_id, $fee["credit_user_id"], $fee["fee_amount"], $fee["absolute"], $fee["relative"], $fee["type"]);
            }
        }
        return $entry_id;
    }
    
    /**
    * add entry fee to db
    * @param    int $entry_id    entry id
    * @param    int $credit_user_id    user id - the credit side of fee
    * @param    float $fee_amount    fee amount
    * @param    float $absolute    absolute value of fee
    * @param    float $relative    relative value of fee
    * @param    string $type    type of entry fee (author, aff1, aff2, system)
    * @return   int entry fee id
    */
    private function addEntryFee($entry_id, $credit_user_id, $fee_amount, $absolute = 0, $relative = 0, $type = '')
    {
        global $oDb;       
        
        $query = '
            INSERT INTO
                '.DB_PREFIX.'entry_fees
            SET
                sid = "'.SID.'",
                entry_id = "'.$entry_id.'",
                type = "'.$type.'",
                credit_user_id = "'.$credit_user_id.'",
                absolute = "'.$absolute.'",
                relative = "'.$relative.'",
                fee_amount = "'.$fee_amount.'"
            ';
        $oDb->execute($query);    
        
        return $oDb->insert_id();  
    }
    
    /**
    * get the array of entries by order id
    * @param    int $order_id    order id of entries need to get
    * @param    int $statuses    array of possible statuses, are intrested in, by default get entries of any statuses
    */
    public function getOrderEntries($order_id, $statuses = array())
    {
        global $oDb;
        
        $where = '';
        if (!empty($statuses))
        {
            $where .= '
                status IN ("'.implode('","', $statuses).'") AND';
        }
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'entries
            WHERE
                '.$where.'
                sid = "'.SID.'" AND
                order_id = "'.$order_id.'"
            ';
        $entries = $oDb->select_assoc($query);
        
        return $entries;
    }
    
    /**
    * get entry info by id
    * @param    int $entry_id    entry id
    */
    public function getEntry($entry_id)
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'entries
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        $entry = $oDb->one_row_assoc($query);
        
        return $entry;
    }
    
    /**
    * change entry status
    * @param    int $entry_id    entry id, which status need to be changed
    * @param    string $result    result of operation - "inited","pending","completed","refunded","error"
    * @param    string $result_info    result description - it can be some error reason
    */
    public function changeEntryStatus($entry_id, $result, $result_info = "")
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            UPDATE
                '.DB_PREFIX.'entries
            SET
                status = "'.$result.'",
                error_reason = "'.$result_info.'"
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * append entry error reason string to current one
    * @param    int $entry_id    entry id
    * @param    string $result_info_append    info need to be appended
    */
    public function appendEntryErrorReason($entry_id, $result_info_append = "")
    {
        global $oDb;
        
        $query = '
            UPDATE
                '.DB_PREFIX.'entries
            SET
                error_reason = CONCAT(error_reason, "'.$result_info.'")
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * get entry fees array
    * @param    int $entry_id    entry id
    * @return   array
    */
    public function getEntryFees($entry_id)
    {
       // TODO: implement
       global $oDb;
       $query = '
            SELECT
                credit_user_id, fee_amount, absolute, relative, type
            FROM
                '.DB_PREFIX.'entry_fees
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        return $oDb->select_assoc($query);    
    }
    
    /**
    * adds entry for recurring entry that stores recurring parametrs
    * 
    * @param mixed $initial_entry_id
    * @param mixed $amount
    * @param mixed $cycles
    * @param mixed $period
    * @param mixed $frequency
    * @param mixed $trial_amount
    * @param mixed $trial_cycles
    * @return bool
    */
    public function addRecurringEntry($initial_entry_id, $amount, $cycles, $period = 'm', $frequency = 1, $trial_amount = 0, $trial_cycles = 0)
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            INSERT INTO
                `'.DB_PREFIX.'entries_recurring`
            SET
                `sid` = "'.SID.'",
                `entry_id` = "'.$initial_entry_id.'",
                `recurring_period` = "'.$period.'",
                `recurring_frequency` = '.$frequency.',
                `recurring_cycles` = '.$cycles.',
                `recurring_amount` = '.$amount.',
                `trial_cycles` = "'.$trial_cycles.'",
                `trial_amount` = "'.$trial_amount.'"
            ';
        return $oDb->execute($query);
    }
    
    /**
    * get entry info by id
    * @param    int $entry_id    entry id
    */
    public function getRecurringEntry($entry_id)
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'entries_recurring
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        $entry = $oDb->one_row_assoc($query);
        
        return $entry;
    }
    
    public function changeRecurringEntryStatus($entry_id, $result, $result_info = "")
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            UPDATE
                '.DB_PREFIX.'entries_recurring
            SET
                status = "'.$result.'",
                error_reason = "'.$result_info.'"
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        return $oDb->execute($query);
    }
    
    public function appendRecurringEntryErrorReason($entry_id, $result_info_append = "")
    {
        global $oDb;
        
        $query = '
            UPDATE
                '.DB_PREFIX.'entries_recurring
            SET
                error_reason = CONCAT(error_reason, "'.$result_info.'")
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        return $oDb->execute($query);
    }
    
    public function editRecurringEntry($entry_id, $profile_id = "", $current_cycle = 0, $last_payment = false)
    {
        global $oDb;
        
        $set= '';
        if ($profile_id != '')
        {
            $set .= '`profile_id` = "'.$profile_id.'"';
        }
        if ($current_cycle != 0)
        {
            $set .= '`current_cycle` = "'.$current_cycle.'"';
        }
        if ($last_payment)
        {
            $set .= '`last_payment` = CURRENT_TIMESTAMP';
        }
        
        
        //echo "<br />".
        $query = '
            UPDATE
                '.DB_PREFIX.'entries_recurring
            SET
                '.$set.'
            WHERE
                sid = "'.SID.'" AND
                entry_id = "'.$entry_id.'"
            ';
        $entry = $oDb->execute($query);
        
        return $entry;
    }
}

?>
