<?php
FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/operation.class.php');   

/**
* implementation of charge operation
* @author       tatana
*/
abstract class chargeOperation implements operation
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
    * init new operation, calls order initialization and payment system operation initialization
    */
    public function initOperation()
    {
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/chargeOrders.class.php'); 
        
        $oChargeOrders = new chargeOrders(); 
        $order_id = $oChargeOrders->initOrder($this->payment_system_name); 
        
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
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/chargeOrders.class.php'); 
        
        $oChargeOrders = new chargeOrders(); 
        $oChargeOrders->changeOrderStatus($order_id, $result["result"], $result["result_info"]);
        
        //debug($result);
        
        if ($result["result"] == "completed")
        {
            if ($oChargeOrders->processOrder($order_id))
            {
                return "success";
            }
        } 
        return "error";
    }
}

?>