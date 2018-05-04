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
* implementation of local purchase operation
* @author       tatana
*/
class purchaseOperationLocal extends purchaseOperation
{
    /**
    * name of payment system
    * @var      string
    */
    public $payment_system_name = "local";    
    
    /**
    * inits new payment operation
    * @param    int $order_id    order id
    */
    public function initPaymentSystemOperation($order_id)
    {
        // show confirm page 
        // it is possible to call processOperation function ($this->processOperation($order_id); ) if there is no need to show confirm page
        return $this->confirmOperation($order_id);
    }
    
    /**
    * confirmation of payment information
    * @param    int $order_id    order id
    */
    public function confirmPaymentSystemOperation($order_id)
    {
        global $oSm;
        
        //debug(FUNC::SESSION("cart_new")); 
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ ); 
        $oSm->template_dir =  $sm_file['path'];
        $oSm->assign('payment_system_name', $this->payment_system_name);
        $oSm->assign('order_id', $order_id);
        //debug($this);
        $oSm->assign('url_back', $this->url_back);
        $oSm->assign('url_process', $this->url_process);
        $res= $oSm->fetch( $sm_file['file'] );
        
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
        return array("result"=>"completed", "result_info"=>"");
    }
}

?>
