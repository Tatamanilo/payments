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
* interface for different operations (purchase, charge, withdrawal)
* @author       tatana
*/
interface operation
{
    
    /**
    * init new operation, calls order initialization and payment system operation initialization
    */
    public function initOperation();
    
    /**
    * confirm operation
    * @param    int $order_id    order id of operation to be confirmed
    */
    public function confirmOperation($order_id);
    
    /**
    * process operation
    * @param    int $order_id    order id of operation to be processed
    */
    public function processOperation($order_id);
    
    /**
    * inits payment system operation
    * @param    int $order_id    order id
    */
    public function initPaymentSystemOperation($order_id);
    
    /**
    * confirmation of payment information
    * @param    int $order_id    order_id
    */
    public function confirmPaymentSystemOperation($order_id);
    
    /**
    * process (finishing) of payment operation
    * returns assoc array of 2 elements
    * -result
    * -result info
    * @param    int $order_id    order id
    * @return   array
    */
    public function processPaymentSystemOperation($order_id);
}

?>
