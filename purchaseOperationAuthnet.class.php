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

FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOperation.class.php'); 

/**
* implementation of authnet purchase operation
* @author       tatana
*/
class purchaseOperationAuthnet extends purchaseOperation
{
    /**
    * name of payment system
    * @var      string
    */
    public $payment_system_name = "authnet";
    
    /**
    * inits new payment operation
    * @param    int $order_id    order id
    */
    public function initPaymentSystemOperation($order_id)
    {
        if (FUNC::SESSION("auto_payment"))
        {
            return $this->processOperation($order_id);
        }
        global $oSm;   
        global $oSecurity;   
        
        $user_id = $oSecurity->GetUserID();
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOrders.class.php'); 
        FUNC::includeFile(PATH_2_GLOBALS.'modules/login/user.class.php'); 
        
        $oPurchaseOrders = new purchaseOrders(); 
        $order = $oPurchaseOrders->getOrder($order_id);
        
        $oUser = new user($user_id);
        $payer_data = $oUser->get(); 
              
        $data['x_relay_response'] = "FALSE"; 
        $data['x_type']         = "AUTH_CAPTURE"; 
        $data['x_login']        = FUNC::getModuleSetting("payment", "authnet_apiLogin"); // !!!
        $data['x_tran_key']     = FUNC::getModuleSetting("payment", "authnet_transactionKey"); // !!!
        $data['x_version']      = "3.1"; 
        $data['x_amount']       = $order["amount"]; 
        $data['x_invoice_num']  = $order_id; 
        $data['x_method']       = "CC";    
        $data['x_test_request'] = FUNC::getModuleSetting("payment", "authnet_isTest"); // !!!
        $data['url']            = $this->url_confirm;

        $payment_data = array();

        if ($payer_data)
        {
            $payment_data["x_first_name"] = $payer_data["first"];
            $payment_data["x_last_name"] = $payer_data["last"];
            $payment_data["x_email"] = $payer_data["email"];
            $payment_data["x_address"] = $payer_data["address1"];
            $payment_data["x_city"] = $payer_data["city"];
            $payment_data["x_state"] = $payer_data["state"];
            $payment_data["x_zip"] = $payer_data["zip"];
            $payment_data["x_country"] = $payer_data["country"];
            $payment_data["x_phone"] = $payer_data["phone"];
            
            $payment_data["x_ship_to_address"] = $payer_data["shipaddress1"];
            $payment_data["x_ship_to_city"] = $payer_data["shipcity"];
            $payment_data["x_ship_to_state"] = $payer_data["shipstate"];
            $payment_data["x_ship_to_zip"] = $payer_data["shipzip"];
            $payment_data["x_ship_to_country"] = $payer_data["shipcountry"];
            $payment_data["x_ship_to_phone"] = $payer_data["shipphone"];
            
            $payment_data["x_company"] = $payer_data["company"];
            $payment_data["id"] = $payer_data["id"];
        }
        $session_payment_data = FUNC::SESSION("authnet_payment_data");
        //debug($session_payment_data);
        $payment_data = array_merge($payment_data, (isset($session_payment_data[$payer_data["id"]]) ? $session_payment_data[$payer_data["id"]] : array()));
        //debug($payment_data);
        
        // prepare and show form 
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ );
        $oSm->template_dir = $sm_file['path'];
        $oSm->assign('data', $data);
        $oSm->assign('url_confirm', $this->url_confirm);    
        $oSm->assign('url_back', $this->url_back);    
        $oSm->assign('payment_data', $payment_data);
        $oSm->assign('payment_system_name', $this->payment_system_name);
        $oSm->assign('order_id', $order_id);
        $res= $oSm->fetch($sm_file['file']);
        
        return $res;
    }
    
    /**
    * confirmation of payment information
    * @param    int $order_id    order id
    */
    public function confirmPaymentSystemOperation($order_id)
    {
        global $oSm;  
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php'); 
        
        $oEntry = new entries(); 
        $products_info = $oEntry->getOrderEntries($order_id);
        //$products_info = FUNC::SESSION("products_info");     
        
        foreach ($_POST as $key => $value )
        { 
                $session_payment_data[$_POST["id"]][$key] = $value;
        }
        FUNC::setSESSION("authnet_payment_data", $session_payment_data);
        
        // prepare and show form 
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ );
        $oSm->template_dir = $sm_file['path'];
        $oSm->assign('data', $_POST);
        $oSm->assign('products_info', $products_info);
        $oSm->assign('url_back', $this->url_back);
        $oSm->assign('url_process', $this->url_process);
        $oSm->assign('payment_system_name', $this->payment_system_name);
        $oSm->assign('order_id', $order_id);
        $res= $oSm->fetch($sm_file['file']);

        return $res; 
    }
    
    /**
    * process (finishing) of payment operation
    * returns assoc array of 2 elements
    * -result(completed | pending | error)     
    * -result_info
    * @param    int $order_id    order id
    * @return   array
    */
    public function processPaymentSystemOperation($order_id)
    {
        global $oSecurity;   
        
        $oSecurity->RestoreFromSession();
        $user_id = $oSecurity->GetUserID();
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php'); 
        $oPaymentShared = new payment_shared();
        $oPaymentShared->saveCreditInfo($order_id, FUNC::POST("card_type"), FUNC::POST("x_card_num"), FUNC::POST("x_card_code"), FUNC::POST("x_exp_date_month").FUNC::POST("x_exp_date_year"));
        
        $session_payment_data = FUNC::SESSION("authnet_payment_data");
        
        //$nvpstr = '&IPADDRESS='.$client_ip;  
        $nvpstr = '';
        
        $post_string = "";
        //$post = $_POST;  
        $post = $session_payment_data[$user_id];
        
        
        
        if (isset($post["yes"]) && $post["yes"])
        {
            $post["x_ship_to_address"] = $post["x_address"];
            $post["x_ship_to_city"] = $post["x_city"];
            $post["x_ship_to_state"] = $post["x_state"];
            $post["x_ship_to_zip"] = $post["x_zip"];
            $post["x_ship_to_country"] = $post["x_country"];
        }
            
            
        foreach ($post as $key => $value )
        { 
            if ($value && $key!="x_exp_date_month" && $key!="x_exp_date_year")
            {
                $post_string .= "$key=" . urlencode( $value ) . "&"; 
                //$session_payment_data[$key] = $value;
            }
        }
        //FUNC::setSESSION("authnet_payment_data", $session_payment_data);
        
        $post_string .= "x_exp_date=" . urlencode( $post["x_exp_date_month"].$post["x_exp_date_year"] ) . "&";

        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/_libAuthnet.class.php');        
        $oAuthnet = new _libAuthnet();
        
        $data = $oAuthnet->apiCall($post_string);
        
        $result = array();
        switch ($data[0])
        {                                               
            case "1":                                   // 1 = Approved   
                $result["result"] = "completed";                                
                break;    
                
            case "4":                                   // 4 = Held for Review      
                $result["result"] = "pending";
                $result['result_info'] = "Error ".$data[2].": ".$data[3];
                break;
                
            default:                                    // 2 = Declined  3 = Error
                $result["result"] = "error";
                $result['result_info'] = "Error ".$data[2].": ".$data[3];
                break; 
        }
        return $result;
    }
    
    /**
    * performs recurring payment procedure for exact payment system
    * return assoc array:
    * initial_entry_id - entry id if first entry of recurring payment
    * recurring_number - current number of recurring payment
    * entry_amount - entry amount that was paid for recurring entry
    * result - completed | pending | error
    * result_info
    * @return   array
    */
    public function recurringPaymentSystemOperation()
    {
        // if authnet recurring is run by cron, get the variables from $_GET (if they are sent by get method)
        
        // $result["initial_entry_id"] = $_GET["..."];
        // $result["recurring_number"] = $_GET["..."] ;
        // $result["entry_amount"] = $_GET["..."];
        
        // == get credit card data ==
        
        // == do new authnet payment api call ==
        // x_invoice_num will be smth like orderId_entryId to keep unique number, 
        // because in general x_invoice_num is order id, and here we exec payment not for whole order 
        // but for exact entry, and entry id can coincide with order id
        
        // $data = $oAuthnet->apiCall($post_string);
        
        switch ($data[0])
        {                                               
            case "1":                                   // 1 = Approved   
                $result["result"] = "completed";                                
                break;    
                
            case "4":                                   // 4 = Held for Review      
                $result["result"] = "pending";
                $result['result_info'] = "Error ".$data[2].": ".$data[3];
                break;
                
            default:                                    // 2 = Declined  3 = Error
                $result["result"] = "error";
                $result['result_info'] = "Error ".$data[2].": ".$data[3];
                break; 
        }
        return $result;
    }
}

?>
