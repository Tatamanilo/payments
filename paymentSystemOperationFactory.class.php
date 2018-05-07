<?php
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
