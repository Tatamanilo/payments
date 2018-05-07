<?php
/**
* model for work with account and account type entities
* @author       tatana
*/
class accounts
{
    
    /**
    * check whether it is enough money on user's account
    * returns true if it is enough and false otherwise
    * @param    int $user_id    user id
    * @param    int $account_type_id    user account type id
    * @param    float $required_amount    amount need to be checked
    * @return   boolean
    */
    public function checkAccountAmount($user_id, $account_type_id, $required_amount)
    {
        $account_type_info = $this->getAccountTypeById($account_type_id);
        $system_user_id = FUNC::getModuleSetting("payment", "systemUserId");   
        
        // check only for local account types and not system users
        if ($account_type_info["is_local"] && ($user_id != $system_user_id))
        {
            $account = $this->getAccount($user_id, $account_type_id);
            
            //echo "<br />acc_amount".$account["amount"]." required".$required_amount;
            if ($account["amount"] >= $required_amount)
            {
                return true;
            }
        }
        else
        {
            return true;
        }
        return false;
    }
    
    /**
    * change the amount of account money
    * @param    int $user_id    user id
    * @param    int $account_type_id    user account type id
    * @param    float $amount_delta    relative value of amount to change
    */
    public function changeAccountAmount($user_id, $account_type_id, $amount_delta)
    {
        global $oDb;      
        
        //echo "<br />".
        $query = '
            SELECT
                COUNT(*)
            FROM
                '.DB_PREFIX.'accounts
            WHERE
                sid = "'.SID.'" AND
                user_id = "'.$user_id.'" AND
                account_type_id = "'.$account_type_id.'"
            ';
        $account_count = $oDb->one_data($query); 
        
        if ($account_count == 1)
        {
            //echo "<br />".
            $query = '
                UPDATE
                    '.DB_PREFIX.'accounts
                SET
                    amount = amount + '.$amount_delta.'
                WHERE
                    sid = "'.SID.'" AND
                    user_id = "'.$user_id.'" AND
                    account_type_id = "'.$account_type_id.'"
                ';
            return $oDb->execute($query);    
        }  
        else
        {
            //echo "<br />".
            $query = '
                INSERT INTO
                    '.DB_PREFIX.'accounts
                SET
                    amount = '.$amount_delta.',
                    sid = "'.SID.'",
                    user_id = "'.$user_id.'",
                    account_type_id = "'.$account_type_id.'"
                ';
            return $oDb->execute($query);    
        }
    }
    
    /**
    * get the info of user's account of certain type
    * @param    int $user_id    user id
    * @param    int $account_type_id    user account type id
    * @return   array
    */
    public function getAccount($user_id, $account_type_id)
    {
        global $oDb;
        
        //echo "<br />".
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'accounts
            WHERE
                sid = "'.SID.'" AND
                user_id = "'.$user_id.'" AND
                account_type_id = "'.$account_type_id.'"
            ';
        $account = $oDb->one_row_assoc($query);
        
        return $account;
    }
    
    /**
    * get account type info by id. among info the field is_local from payment system table is present
    * @param    int $account_type_id
    */
    public function getAccountTypeById($account_type_id)
    {
        global $oDb;     
        
        //echo "<br />".
        $query = '
            SELECT
                at.*,
                ps.is_local
            FROM
                '.DB_PREFIX.'account_types as at,
                '.DB_PREFIX.'payment_systems as ps
            WHERE
                at.sid = "'.SID.'" AND
                ps.sid = "'.SID.'" AND
                at.payment_system_name = ps.payment_system_name AND
                account_type_id = "'.$account_type_id.'"
            ';
        $account_type = $oDb->one_row_assoc($query);
        
        return $account_type;
    }
}

?>