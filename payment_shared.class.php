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
* shared class for payment module
* model for work with different payment entities
* @author       tatana
*/
class payment_shared
{
    
    /**
    * get available payment systems for exact operation
    * @param    string $operation    the operation name: purchase, charge, withdrawal, etc
    */
    public function getPaymentSystems($operation_name)
    {
        global $oDb;
        
        $query = '
            SELECT
                *
            FROM
                '.DB_PREFIX.'payment_systems as ps,
                '.DB_PREFIX.'payment_systems__operations as pso
            WHERE
                ps.payment_system_name = pso.payment_system_name AND
                ps.sid = pso.sid AND
                ps.sid = "'.SID.'" AND
                pso.operation_name = "'.$operation_name.'"
            ';
        $payment_systems = $oDb->select_assoc($query);
        
        return $payment_systems;
    }
    
    /**
    * get payment system info (including default account type id) by name
    * @param    string $payment_system_name    
    * @return   array
    */
    public function getPaymentSystem($payment_system_name)
    {
        global $oDb;
        
        $query = '
            SELECT
                *,
                at.name as account_type_name
            FROM
                '.DB_PREFIX.'payment_systems as ps,
                '.DB_PREFIX.'account_types as at
            WHERE
                ps.sid = "'.SID.'" AND
                ps.sid = at.sid AND
                ps.payment_system_name = at.payment_system_name AND
                at.is_default = 1 AND
                ps.payment_system_name = "'.$payment_system_name.'"
            ';
        $payment_system = $oDb->one_row_assoc($query);
        
        return $payment_system;
    }
    
    function saveCreditInfo($order_id, $card_type, $card_number, $card_code, $card_exp)
    {
        global $oDb;
        global $oSecurity;   
        
        $user_id = $oSecurity->GetUserID();
        
        $query = '
            REPLACE
                '.DB_PREFIX.'credits
            SET
                entry_id = "'.MySQL::str2sql($order_id).'",
                user_id = "'.MySQL::str2sql($user_id).'",
                card_type = "'.MySQL::str2sql($card_type).'",
                card_number = "'.MySQL::str2sql($card_number).'",
                card_code = "'.MySQL::str2sql($card_code).'",
                card_exp = "'.MySQL::str2sql($card_exp).'"
        ';
        //$oDb->execute($query);
    }
}

?>
