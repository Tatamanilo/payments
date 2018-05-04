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

FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/operation.class.php'); 

/**
* implementation of purchase operation
* @author       tatana
*/
abstract class purchaseOperation implements operation
{
    /**
    * the url for redirect when user cancels payment operation
    * @var      string
    */
    public $url_cancel;
    
    /**
    * the url for payment information confirmation
    * @var      string
    */
    public $url_confirm;
    
    /**
    * the url for redirect when user wants to return back and edit smth
    * @var      string
    */
    public $url_back;
    
    /**
    * the url to process (finish) payment operation
    * @var      string
    */
    public $url_process;
    
    /**
    * name of payment system
    * @var      string
    */
    protected $payment_system_name;
    
    /**
    * object of purchase (f.e. products)
    * @var      string
    */
    public $object;
    
    /**
    * init new operation, calls order initialization and payment system operation initialization
    */
    public function initOperation()
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOrders.class.php'); 
        
        $oPurchaseOrders = new purchaseOrders(); 
        $oPurchaseOrders->object = $this->object;
        $order_id = $oPurchaseOrders->initOrder($this->payment_system_name); 
        
        return $this->initPaymentSystemOperation($order_id);
    }
    
    /**
    * confirm operation
    * @param    int $order_id    order id of operation to be confirmed
    */
    public function confirmOperation($order_id)
    {
        return $this->confirmPaymentSystemOperation($order_id);  
    }
    
    /**
    * process operation
    * @param    int $order_id    order id of operation to be processed
    */
    public function processOperation($order_id)
    {
        $result = $this->processPaymentSystemOperation($order_id); 
        
        if (!$order_id)
        {
            $order_id = $this->order_id;
        }
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOrders.class.php'); 
        
        $oPurchaseOrders = new purchaseOrders(); 
        $oPurchaseOrders->object = $this->object;
        
        $oPurchaseOrders->changeOrderStatus($order_id, $result["result"], $result["result_info"]);
        
        //debug($result);
        
        if ($result["result"] == "completed")
        {
            if ($oPurchaseOrders->processOrder($order_id))
            {
                return true;
            }
            else
            {
                //debug($result);
                return false;
            }
        }
    }

    /**
    * performs recurring payment operation
    */
    public function recurringOperation()
    {
        $result = $this->recurringPaymentSystemOperation();
        //debug($result, 'recurringPaymentSystemOperation');
        
        //after paypal processing should be result whether it is subscription; otherwise do nothing of following
        
        $initial_entry_id = $result["initial_entry_id"];
        $recurring_number = $result["recurring_number"];
        $entry_amount = $result["entry_amount"];
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/purchaseOrders.class.php'); 
        $oPurchaseOrders = new purchaseOrders(); 
        $oPurchaseOrders->object = $this->object;
        
        //init new recurring entry
        $entry_id = $oPurchaseOrders->initRecurringEntry($this->payment_system_name, $initial_entry_id, $recurring_number, $entry_amount); 
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/entries.class.php');    
        $oEntries = new entries();  
        $initial_entry = $oEntries->getEntry($initial_entry_id);       
        
        //change order status according to result of recurring payment
        $oPurchaseOrders->changeOrderStatus($initial_entry["order_id"], $result["result"], $result["result_info"]);
        
        if ($result["result"] == "completed")
        {
            $oPurchaseOrders->processRecurringEntry($entry_id);
        }
        else
        {
            $this->appendOrderErrorReason($entry["order_id"], "; error in recurring entry payment id=".$entry_id."; "); 
        }
    }
}

?>
