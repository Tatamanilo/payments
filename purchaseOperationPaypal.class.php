<?php
FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOperation.class.php');     

/**
* implementation of paypal purchase operation
* @author       tatana
*/
class purchaseOperationPaypal extends purchaseOperation
{
    
    /**
    * name of payment system
    * @var      string
    */
    protected $payment_system_name = "paypal";
    public $order_id;
    
    /**
    * inits new payment operation
    * @param    int $order_id    order id
    */
    public function initPaymentSystemOperation($order_id)
    {
        
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
        
        /*$oPaypal->setApi(
            "tanya_1280312860_biz_api1.gmail.com",
            "1280312865",
            "AnyRLKT4uyqzWWVHbxnXd5D2Z5fRAlIC8OoiCdzMKixT2OxaUVr.SNP1"
        );*/
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        $oEvents = new events();  
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');     
        $oEntries = new entries();  
        
        /*
        $nvpstr="&AMT=".$order["amount"]."&".
            "RETURNURL=".urlencode($this->url_process)."&".
            "CANCELURL=".urlencode($this->url_back)."&".
            "CURRENCYCODE=USD&".
            "INVNUM=".$order_id."&".
            "LANDINGPAGE=Login&".
            "CALLBACKTIMEOUT=6&".
            "PAYMENTACTION=Sale";
            
             "L_PAYMENTREQUEST_0_NAME0=".urlencode("product")."&".
            "L_PAYMENTREQUEST_0_AMT0=10.00&".
            "L_PAYMENTREQUEST_0_NUMBER0=10&".
            */      
        
        $entries = $oEntries->getOrderEntries($order_id);
        
        $nvpstr_items_info = '';
        $nvpstr_recurring_info = '';
        $recurring_key = 0;
        
        foreach ($entries as $key=>$entry)
        {
            $nvpstr_items_info .= 
                "L_PAYMENTREQUEST_0_NAME".$key."=".urlencode($entry["object_name"])."&".
                "L_PAYMENTREQUEST_0_DESC".$key."=".urlencode($entry["object_info"])."&".
                "L_PAYMENTREQUEST_0_AMT".$key."=".urlencode($entry["object_amount"])."&".
                "L_PAYMENTREQUEST_0_QTY".$key."=".urlencode($entry["count"])."&".
                "L_PAYMENTREQUEST_0_NUMBER".$key."=".urlencode($entry["object_id"])."&";   
            
            // if item is recurring add recurring info
            if ($entry["recurring_total"] > 1)
            {
                $nvpstr_recurring_info .=
                    "L_BILLINGTYPE".$recurring_key."=".urlencode('RecurringPayments')."&".
                    "L_BILLINGAGREEMENTDESCRIPTION".$recurring_key."=".urlencode($entry["object_name"])."&";    
                    
                $recurring_key++;
            } 
        }
            
        $nvpstr="&".
            "PAYMENTREQUEST_0_AMT=".$order["amount"]."&".
            "PAYMENTREQUEST_0_INVNUM=".$order_id."&".
            "PAYMENTREQUEST_0_CURRENCYCODE=".urlencode("USD")."&".
            $nvpstr_items_info.
            $nvpstr_recurring_info.
            "RETURNURL=".urlencode($this->url_process)."&".
            "CANCELURL=".urlencode($this->url_back)."&".
            "LANDINGPAGE=Login&".
            "CALLBACKTIMEOUT=6";        
        $oEvents->registerEvent($order_id, 'SetExpressCheckout call', FUNC::prepare_to_sql(var_export($nvpstr, true))); 
        //debug($nvpstr); 
        list($curl_ok, $resArray) = $oPaypal->nvpCall("SetExpressCheckout", $nvpstr);
        //debug($resArray, "SetExpressCheckout");    
        
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
    * @param    int $order_id    order id
    */
    public function confirmPaymentSystemOperation($order_id)
    {
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
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        $oEvents = new events();
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/_libPaypal.class.php');        
        $oPaypal = new _libPaypal();
        $oPaypal->setApi(
            FUNC::getModuleSetting("payment", "paypal_apiUsername_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiPassword_test"), 
            FUNC::getModuleSetting("payment", "paypal_apiSignature_test")
        );
        /*$oPaypal->setApi(
            "tanya_1280312860_biz_api1.gmail.com",
            "1280312865",
            "AnyRLKT4uyqzWWVHbxnXd5D2Z5fRAlIC8OoiCdzMKixT2OxaUVr.SNP1"
        );*/
        
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
        //debug($resArray, 'GetExpressCheckoutDetails');                          
        
        $this->order_id = $resArray["PAYMENTREQUEST_0_INVNUM"];    
        
        $oEvents->registerEvent($this->order_id, 'GetExpressCheckoutDetails response', FUNC::prepare_to_sql(var_export($resArray, true)));        
        
        $_SESSION['reshash'] = $resArray;
        $ack = strtoupper($resArray["ACK"]);
            
        if ($ack == "SUCCESS")
        {
            $token = $resArray["TOKEN"];
            $paymentAmount =  $resArray["PAYMENTREQUEST_0_AMT"];
            $currCodeType =  $resArray["PAYMENTREQUEST_0_CURRENCYCODE"];
            $paymentType = "Sale";
            $payerID = urlencode(FUNC::POSTGET('PayerID'));
            $serverName = urlencode($_SERVER['SERVER_NAME']);
            
            $nvpstr_items_info = '';
            for ($i = 0; $i<=9; $i++)
            {
                if (isset($resArray["L_PAYMENTREQUEST_0_NAME".$i]))
                {
                    $nvpstr_items_info .= 
                    "&L_PAYMENTREQUEST_0_NAME".$i."=".$resArray["L_PAYMENTREQUEST_0_NAME".$i].
                    "&L_PAYMENTREQUEST_0_DESC".$i."=".$resArray["L_PAYMENTREQUEST_0_DESC".$i].
                    "&L_PAYMENTREQUEST_0_AMT".$i."=".$resArray["L_PAYMENTREQUEST_0_AMT".$i].
                    "&L_PAYMENTREQUEST_0_QTY".$i."=".$resArray["L_PAYMENTREQUEST_0_QTY".$i].
                    "&L_PAYMENTREQUEST_0_NUMBER".$i."=".$resArray["L_PAYMENTREQUEST_0_NUMBER".$i]; 
                }
            }

            $nvpstr='&TOKEN='.$token.'&PAYERID='.$payerID.'&PAYMENTREQUEST_0_AMT='.$paymentAmount.'&PAYMENTREQUEST_0_INVNUM='.$this->order_id.'&PAYMENTREQUEST_0_CURRENCYCODE='.$currCodeType.'&IPADDRESS='.$serverName.$nvpstr_items_info ;

            /* Make the call to PayPal to finalize payment
            If an error occured, show the resulting errors
            */
            list($curl_ok, $resArray) = $oPaypal->nvpCall("DoExpressCheckoutPayment", $nvpstr);             
            //debug($resArray, 'DoExpressCheckoutPayment');  
            $oEvents->registerEvent($this->order_id, 'DoExpressCheckoutPayment response', FUNC::prepare_to_sql(var_export($resArray, true)));        

            // == add subscription requests ==
            // ...
            // ...
            // ...
            
            /* Display the API response back to the browser.
            If the response from PayPal was a success, display the response parameters'
            If the response was an error, display the errors received using APIError.php.
            */
            $ack = strtoupper($resArray["ACK"]);

            if ($ack == "SUCCESS")
            {
                
                //if first payments are ok
                // == add subscription requests ==
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php'); 
                $oEntries = new entries(); 
                $entries = $oEntries->getOrderEntries($this->order_id);
                //debug($entries, 'searching recurring entries');
                
                foreach ($entries as $key=>$entry)
                {
                    // if item is recurring
                    if ($entry["recurring_total"] > 1)
                    {
                        $recurring = $oEntries->getRecurringEntry($entry["entry_id"]);
                        //debug($recurring, '$recurring');
                        
                        if ($recurring['recurring_period'] == 'm')
                        {
                            $startDate= date("Y-m-d", strtotime("+ ".$recurring['recurring_frequency']." month"));
                            $period='Month';
                        }
                        else
                        {
                            $startDate= date("Y-m-d", strtotime("+ ".$recurring['recurring_frequency']." day"));
                            $period='Day';
                        }
                        
                        if ($recurring['trial_amount']>0 && $recurring['trial_cycles']>0)
                        {
                            $recurring['trial_cycles'] = $recurring['trial_cycles'] - 1;
                        }
                        else
                        {
                            $recurring['recurring_cycles'] = $recurring['recurring_cycles'] - 1;
                        }
                            
                        //debug($recurring['recurring_cycles'], 'recurring_cycles');                            
                            
                        $nvpstr="&TOKEN=".$resArray["TOKEN"]."&".
                            "DESC=".urlencode($entry["object_name"])."&".
                            "PROFILESTARTDATE=".urlencode($startDate."T0:0:0")."&".
                            "PROFILEREFERENCE=".urlencode($this->order_id.'_'.$entry['entry_id'])."&".
                            "BILLINGPERIOD=".urlencode($period)."&".
                            "BILLINGFREQUENCY=".urlencode($recurring['recurring_frequency'])."&".
                            "TOTALBILLINGCYCLES=".urlencode($recurring["recurring_cycles"])."&".
                            "AMT=".sprintf("%.2f",$recurring['recurring_amount'])."&";
                        
                        //if there is need in trial period
                        if ($recurring['trial_amount']>0 && $recurring['trial_cycles']>0)
                        {
                            $nvpstr.="TRIALBILLINGPERIOD=".urlencode($period)."&".
                                "TRIALBILLINGFREQUENCY=".urlencode($recurring['recurring_frequency'])."&".
                                "TRIALTOTALBILLINGCYCLES=".urlencode($subscr['trial_cycles'])."&".
                                "TRIALAMT=".urlencode($subscr['trial_amount'])."&";
                        }
                        
                        $nvpstr.="CURRENCYCODE=".urlencode("USD");
                        //debug($nvpstr,'CreateRecurringPaymentsProfile');
                        
                        list($curl_ok, $resArray) = $oPaypal->nvpCall("CreateRecurringPaymentsProfile", $nvpstr);             
                        //debug($resArray,'CreateRecurringPaymentsProfile');
                        
                        $oEvents->registerEvent($this->order_id, 'CreateRecurringPaymentsProfile response', FUNC::prepare_to_sql(var_export($resArray, true)));
                        
                        $ack = strtoupper($resArray["ACK"]);

                        if ($ack == "SUCCESS" || $ack== "SUCCESSWITHWARNING")
                        {
                            //update entry and save profileID
                            //$update_array['profile_id'] = $resArray["PROFILEID"];
                            //$update_array['profile_time'] = $resArray["TIMESTAMP"];
                            
                            $oEntries->editRecurringEntry($entry["entry_id"], $resArray["PROFILEID"]);
                            
                            if ($resArray["PROFILESTATUS"]=='ActiveProfile')
                            {
                                $oEntries->changeRecurringEntryStatus($entry["entry_id"], 'active');
                            }
                            elseif ($resArray["PROFILESTATUS"]=='PendingProfile')
                            {
                                $oEntries->changeRecurringEntryStatus($entry["entry_id"], 'pending', 'CreateRecurringPaymentsProfile');
                                
                                $oEmail = new email(); 
                                $oEmail->set_from($this->settings["general"]["support_email"]);
                                $oEmail->set_to($this->settings["general"]["support_email"]);
                                $oEmail->set_text("User (id = ".$oSecurity->GetUserID().") has paid for subscription but profile is pending (".$entry["entry_id"].")");   
                                $oEmail->set_subject("Paypal subscription pending");
                                //echo $oEmail->text_body;
                                //$oEmail->send();
                            }
                            else
                            {
                                $oEntries->changeRecurringEntryStatus($entry["entry_id"], 'error', 'CreateRecurringPaymentsProfile');
                                
                                $oEmail = new email(); 
                                $oEmail->set_from($this->settings["general"]["support_email"]);
                                $oEmail->set_to($this->settings["general"]["support_email"]);
                                $oEmail->set_text("User (id = ".$oSecurity->GetUserID().") has paid for subscription but profile is not created (".$entry["entry_id"].")");   
                                $oEmail->set_subject("Paypal subscription creation error");
                                //echo $oEmail->text_body;
                                //$oEmail->send();
                            }
                            
                        }
                        else
                        {
                            //error creating recurring profile
                            
                            $oEmail = new email(); 
                            $oEmail->set_from($this->settings["general"]["support_email"]);
                            $oEmail->set_to($this->settings["general"]["support_email"]);
                            $oEmail->set_text("User (id = ".$oSecurity->GetUserID().") has paid for subscription but profile is not created (".$entry["entry_id"].")");   
                            $oEmail->set_subject("Paypal subscription creation error");
                            //echo $oEmail->text_body;
                            //$oEmail->send();
                        }
                        
                    } 
                }
                
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
        // get variables in a way that paypal returns them as for recurring payment result   
        
        // $result["initial_entry_id"] = $_POST["..."];
        // $result["recurring_number"] = $_POST["..."];
        // $result["entry_amount"] = $_POST["..."];
        // $result["result"] = $_POST["..."];
        // $result["result_info"] = $_POST["..."];
        
        //here paypal ipn 
        
        /*
        $referenceID = FUNC::POSTGET('rp_invoice_id');  //should be orderId_entry_Id
        $profile_id=FUNC::POST("recurring_payment_id");
        if not profile_id => not subscription => shouldn't be processed'
        
        switch (FUNC::POSTGET("txn_type"))
        {
            case "recurring_payment":
                //payment
                //get payment parametrs from db -> $subscr
                if (FUNC::POST("mc_currency") != "USD")
                {
                    //wrong currency type
                    return $result;
                }
                //select amount
                if (FUNC::POSTGET("period_type")=="Trial")
                {
                    $amount=$subscr['trial_price'];
                }
                else
                {
                    $amount=$subscr['price'];
                }
                
                if (FUNC::POSTGET("mc_gross") != $amount)
                {
                    //amount is wrong;
                    return $result;
                }
                
                $result['txn_id']=FUNC::POSTGET("txn_id");
                $result['txn_timestamp']=FUNC::POSTGET("payment_date");
                
                if (FUNC::POSTGET("payment_status") != "Completed" && FUNC::POSTGET("pending_reason") != "intl")
                {
                    $result['reason']=FUNC::POSTGET("pending_reason");
                    //payment status and pending reason is '.FUNC::POSTGET("pending_reason")
                }
                else
                {
                    //ok
                }
                break;
                
            default:
                //subscription expired  - recurring_payment_expired
                //subscription cancelled - recurring_payment_profile_cancel
                
                //subscr unsubscribed or subscription has expired => inform support?
                break;
        }
        
        */
        
        return $result;
    }
}

?>