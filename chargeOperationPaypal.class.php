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

FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/chargeOperation.class.php'); 

/**
* @author       tatana
*/
class chargeOperationPaypal extends chargeOperation
{
    /**
    * name of payment system
    * @var      string
    */
    protected $payment_system_name = "paypal";
    public $order_id;
    
    /**
    * inits payment system operation
    * @param    int $order_id    order id
    */
    public function initPaymentSystemOperation($order_id)
    {
        //rewrite url proccess, because paypal returns parameters by GET method
        $charge_locname = FUNC::getLocationFrom("payment", array("charge"), "payment");
        $this->url_process = MAINSITE_URL."index.php?s=".$charge_locname."&step=4&payment_system_name=".$this->payment_system_name;
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOrders.class.php'); 
        $oPurchaseOrders = new purchaseOrders(); 
        $order = $oPurchaseOrders->getOrder($order_id);

        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/_libPaypal.class.php');        
        $oPaypal = new _libPaypal();
        /*
        $oPaypal->setApi(
            "seller_1250598626_biz_api1.fotosphere.info",
            "1250598637",
            "AJvTvWOwScfv2WdTYMqeTPlso0fTAyGLTYnBdM9OMOXuRQbQTwrJVl1r"
        );
        */
        $oPaypal->setApi(
            FUNC::getModuleSetting("payment", "paypal_apiUsername_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiPassword_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiSignature_test")
        );
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        $oEvents = new events();  
        
        $nvpstr="&".
            "PAYMENTREQUEST_0_AMT=".$order["amount"]."&".
            "PAYMENTREQUEST_0_DESC=".urlencode("Charge money")."&".
            "PAYMENTREQUEST_0_INVNUM=".$order_id."&".
            "PAYMENTREQUEST_0_CURRENCYCODE=".urlencode("USD")."&".
            "RETURNURL=".urlencode($this->url_process)."&".
            "CANCELURL=".urlencode($this->url_back)."&".
            "LANDINGPAGE=Login&".
            "CALLBACKTIMEOUT=6";        
        $oEvents->registerEvent($order_id, 'SetExpressCheckout call', FUNC::prepare_to_sql(var_export($nvpstr, true))); 
        //debug($nvpstr); 
        list($curl_ok, $resArray) = $oPaypal->nvpCall("SetExpressCheckout", $nvpstr);
        //debug($resArray);    
        
        $oEvents->registerEvent($order_id, 'SetExpressCheckout response', FUNC::prepare_to_sql(var_export($resArray, true)));
        
        $ack = strtoupper($resArray["ACK"]);  
        if ($ack == "SUCCESS")
        {
            // Redirect to paypal.com here
            $token = urldecode($resArray["TOKEN"]);   
            $payPalURL = $oPaypal->url_express.$token;
            header("Location: ".$payPalURL);
        }
        else
        {
            //Redirecting to APIError.php to display errors.
            //debug($resArray);
            return "Errors";
        }
    }
    
    /**
    * confirmation of payment information
    * @param    int $order_id    order_id
    */
    public function confirmPaymentSystemOperation($order_id)
    {
       // TODO: implement
    }
    
    /**
    * process (finishing) of payment operation
    * returns assoc array of 2 elements
    * -result(completed | pending | error)
    * -result info
    * @param    int $order_id    order id
    * @return   array
    */
    public function processPaymentSystemOperation($order_id)
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        $oEvents = new events();
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/_libPaypal.class.php');        
        $oPaypal = new _libPaypal();
        $oPaypal->setApi(
            FUNC::getModuleSetting("payment", "paypal_apiUsername_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiPassword_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiSignature_test")
        );
        
        $token = urlencode(FUNC::POSTGET("token"));

        /* Build a second API request to PayPal, using the token as the
        ID to get the details on the payment authorization
        */
        $nvpstr = "&TOKEN=".$token;
        
        /* Make the API call and store the results in an array.  If the
        call was a success, show the authorization details, and provide
        an action to complete the payment.  If failed, show the error
        */
        list($curl_ok, $resArray) = $oPaypal->nvpCall("GetExpressCheckoutDetails", $nvpstr);
        //debug($resArray);                          
        
        $this->order_id = $resArray["PAYMENTREQUEST_0_INVNUM"];    
        
        $oEvents->registerEvent($this->order_id, 'GetExpressCheckoutDetails response', FUNC::prepare_to_sql(var_export($resArray, true)));        
        
        $_SESSION['reshash'] = $resArray;
        $ack = strtoupper($resArray["ACK"]);
            
        if ($ack == "SUCCESS")
        {
            $token = $resArray["TOKEN"];
            $paymentAmount =  $resArray["PAYMENTREQUEST_0_AMT"];
            $descr =  $resArray["PAYMENTREQUEST_0_DESC"];
            $currCodeType =  $resArray["PAYMENTREQUEST_0_CURRENCYCODE"];
            $paymentType = "Sale";
            $payerID = urlencode(FUNC::POSTGET('PayerID'));
            $serverName = urlencode($_SERVER['SERVER_NAME']);
            
            $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_AMT='.$paymentAmount.'&PAYMENTREQUEST_0_INVNUM='.$this->order_id.'&PAYMENTREQUEST_0_CURRENCYCODE='.$currCodeType.'&PAYMENTREQUEST_0_DESC='.$descr.'&IPADDRESS='.$serverName ;

            list($curl_ok, $resArray) = $oPaypal->nvpCall("DoExpressCheckoutPayment", $nvpstr);             
            //debug($resArray);  
            $oEvents->registerEvent($this->order_id, 'DoExpressCheckoutPayment response', FUNC::prepare_to_sql(var_export($resArray, true)));        
            

            $ack = strtoupper($resArray["ACK"]);

            if ($ack == "SUCCESS")
            {
                $result["result"] = "completed";                                
            }
            else
            {
                $result["result"] = "error";
                $result['result_info'] = "Error ".$ack;
            }
        } 
        else  
        {
            $result["result"] = "error";
            $result['result_info'] = "Error ".$ack;
        }
        return $result;   
    }
}

?>