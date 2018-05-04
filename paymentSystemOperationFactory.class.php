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
* the class that creates class object of certain payment system and operation
* (using pattern "factory method")
* @author       tatana
*/
class paymentSystemOperationFactory
{
    
    /**
    * creates class object of certain payment system and operation and return it
    * @param    string $payment_system_name    
    * @param    string $operation_name    
    * @return   operation
    */
    public function createPaymentSystemOperation($payment_system_name, $operation_name)
    {
        ///*
        $class_name = $operation_name.'Operation'.ucfirst($payment_system_name);
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/'.$class_name.'.class.php');  
        return $oPaymentSystemOperation = new $class_name();
        //*/
        
        /*
        //only for php editor. to see the functions of class object
        switch ($operation_name)
        {
            case "purchase":
                switch ($payment_system_name)    
                {
                    case "local";
                        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOperationLocal.class.php');  
                        return $oPaymentSystemOperation = new purchaseOperationLocal();   
                        break;
                        
                    case "paypal";
                        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOperationPaypal.class.php');  
                        return $oPaymentSystemOperation = new purchaseOperationPaypal();   
                        break;
                        
                    case "authnet";
                        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOperationAuthnet.class.php');  
                        return $oPaymentSystemOperation = new purchaseOperationAuthnet();   
                        break;
                }
        }
        */
        
    }
}

?>
