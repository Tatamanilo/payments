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
* payment class is controller for module payment
* @author       tatana
*/
class payment extends cBase
{
    
    function payment($is_plugin = 0)
    {
        if (empty($this->plugin_names))
        {
            $this->plugin_names = array('categories', 'reviews', 'statistics', 'tags'); 
        }
        
        parent::CBase( $this, '', $is_plugin);
        parent::initPlugins(__CLASS__); 
    }
    
    public function mainContent()
    {
        
    }
    
    /**
    * the function controlles the purchase steps. f.e.:
    * 1st - choose pyament system
    * 2nd - init payment
    * 3rd - confirm
    * 4th - process payment
    * 5th - finish payment
    * @return   string
    */
    public function cartProductsPurchase()
    {
        //check whether user is logined
        global $oSecurity;
        $oSecurity->RestoreFromSession();
        if( !$oSecurity->isLogined() )
        {
            $auto_fields = FUNC::SESSION("auto_fields") ? FUNC::SESSION("auto_fields") : array();
            $auto_fields ['return_loc'] = FUNC::getLocationFrom(__CLASS__, array("cartProductsPurchase"), __CLASS__);
            FUNC::setSESSION("auto_fields",$auto_fields);
            FUNC::JSRedirect(MAINSITE_URL.FUNC::getLocationFrom('login', array("autoJoin"), 'login').".htm");
            return;
        }
        
        
        $step = FUNC::POSTGET("step") ? FUNC::POSTGET("step") : "1";
        $operation_name = "purchase";
        $object = "products";

        switch ($step)
        {
            case "1": // choose payment system
                global $oSm;
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
                $oPaymentShared = new payment_shared();
                $payment_systems = $oPaymentShared->getPaymentSystems($operation_name);
                
                // get view for payment systems selection
                $sm_file = Func::getTemplate( "ñhoosePaymentSystem", __FILE__ ); 
                $oSm->template_dir =  $sm_file['path'];
                $oSm->assign('payment_systems', $payment_systems);
                $res= $oSm->fetch( $sm_file['file'] );
        
                return $res; // return view
                break;

            case "2":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                
                FUNC::setSESSION("payment_system_name", $payment_system_name);
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $cartProductsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("cartProductsPurchase"), __CLASS__);      
                $viewCartProducts_locname = FUNC::getLocationFrom(__CLASS__, array("viewCartProducts"), __CLASS__);  
                $oPaymentSystemOperation->url_confirm = MAINSITE_URL."step/3/".$cartProductsPurchase_locname.".htm";
                //debug($oPaymentSystemOperation->url_confirm);
                //echo "--";
                $oPaymentSystemOperation->url_back = MAINSITE_URL.$viewCartProducts_locname.".htm";
                $oPaymentSystemOperation->url_cancel = MAINSITE_URL.$viewCartProducts_locname.".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$cartProductsPurchase_locname.".htm";
                //rewrite url proccess, because paypal returns parameters by GET method
                if ($payment_system_name == "paypal")
                {
                    $oPaymentSystemOperation->url_process = MAINSITE_URL."index.php?s=".$cartProductsPurchase_locname."&step=4&payment_system_name=".$payment_system_name;
                }
                
                return $oPaymentSystemOperation->initOperation();
                break;
                
            case "3":
                $payment_system_name = FUNC::POST("payment_system_name");
                $order_id = FUNC::POST("order_id");
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $cartProductsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("cartProductsPurchase"), __CLASS__);      
                $viewCartProducts_locname = FUNC::getLocationFrom(__CLASS__, array("viewCartProducts"), __CLASS__);  
                $oPaymentSystemOperation->url_back = MAINSITE_URL.$viewCartProducts_locname.".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$cartProductsPurchase_locname.".htm";
                
                return $oPaymentSystemOperation->confirmOperation($order_id);
                break;
                
            case "4":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                $order_id = FUNC::POSTGET("order_id"); 
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $res = $oPaymentSystemOperation->processOperation($order_id);
                //debug($res);
                return $res;
                break;
        }
    }
    
    
    /**
    * the function controls the charge steps. f.e.:
    * 1st - input amount
    * 2nd - choose pyament system    
    * 3rd - init payment 
    * 4th - confirm
    * 5th - process payment
    * 6th - finish payment
    * @return   string
    */
    public function charge()
    {
        $step = FUNC::POSTGET("step") ? FUNC::POSTGET("step") : "0";
        $operation_name = "charge";
        $object = "";

        switch ($step)
        {
            case "0": // input amount to charge
                global $oSm;
                
                $charge_locname = FUNC::getLocationFrom(__CLASS__, array("charge"), __CLASS__);      
                $next_step_url = MAINSITE_URL."step/1/".$charge_locname.".htm";
                
                // get view for payment systems selection
                $sm_file = Func::getTemplate( "chargeInputAmount", __FILE__ ); 
                $oSm->template_dir =  $sm_file['path'];
                $oSm->assign('next_step_url', $next_step_url);
                $res= $oSm->fetch( $sm_file['file'] );
        
                return $res; // return view
                break;
                
            case "1": // choose payment system
                global $oSm;
                
                //debug(FUNC::POSTGET("charge_amount"));
                FUNC::setSESSION("charge_amount", FUNC::POSTGET("charge_amount"));
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
                $oPaymentShared = new payment_shared();
                $payment_systems = $oPaymentShared->getPaymentSystems($operation_name);
                
                // get view for payment systems selection
                $sm_file = Func::getTemplate( "ñhoosePaymentSystem", __FILE__ ); 
                $oSm->template_dir =  $sm_file['path'];
                $oSm->assign('payment_systems', $payment_systems);
                $res= $oSm->fetch( $sm_file['file'] );
        
                return $res; // return view
                break;

            case "2":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                
                FUNC::setSESSION("payment_system_name", $payment_system_name);
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                
                $charge_locname = FUNC::getLocationFrom(__CLASS__, array("charge"), __CLASS__);      
                $oPaymentSystemOperation->url_confirm = MAINSITE_URL."step/3/".$charge_locname.".htm";
                //debug($oPaymentSystemOperation->url_confirm);
                //echo "--";
                $oPaymentSystemOperation->url_back = MAINSITE_URL.$charge_locname.".htm";
                $oPaymentSystemOperation->url_cancel = MAINSITE_URL.$charge_locname.".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$charge_locname.".htm";
                
                return $oPaymentSystemOperation->initOperation();
                break;
                
            case "3":
                $payment_system_name = FUNC::POST("payment_system_name");
                $order_id = FUNC::POST("order_id");
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                
                $charge_locname = FUNC::getLocationFrom(__CLASS__, array("charge"), __CLASS__);      
                $oPaymentSystemOperation->url_back = MAINSITE_URL.$charge_locname.".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$charge_locname.".htm";
                
                return $oPaymentSystemOperation->confirmOperation($order_id);
                break;
                
            case "4":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                $order_id = FUNC::POSTGET("order_id"); 
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                
                $res = $oPaymentSystemOperation->processOperation($order_id);
                //debug($res);
                return $res;
                break;
        }
    }
    
    /**
    * the function controlles adding objects to cart
    */
    public function processProductCart()
    {
        Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartProducts.class.php');       
        $oCartProducts = new cartProducts();           
        
        switch (FUNC::POSTGET("action"))
        {
            case "add":
                return $oCartProducts->addObject(FUNC::POSTGET("object_id"), (FUNC::POSTGET("count") ? FUNC::POSTGET("count") : 1)); 
                break;
                
            case "change":
                return $oCartProducts->changeObjectCount(FUNC::POSTGET("object_id"), FUNC::POSTGET("count"));    
                break;
            
            case "remove":
                return $oCartProducts->removeObject(FUNC::POSTGET("object_id"));    
                break;
                
            case "clear":
                return $oCartProducts->clearCart();
                break;
        }
    }
    
    /**
    * the function controlles adding objects to cart
    */
    public function viewCartProducts()
    {
        global $oSm;
              
        //FUNC::setSESSION("cart_new", false);
        //debug(FUNC::SESSION("cart_new"));
        Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartProducts.class.php');   
        $oCartProducts = new cartProducts();
        
        // if is return to this page from payment page - restore processing cart objects to initial status
        if (FUNC::POST("back"))
        {
            $oCartProducts->changeCartStatusToInitial();       
        }
        
        list($objects, $total_amount) = $oCartProducts->getCartObjectsInfo("initial", true); 
        
        $cartProductsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("cartProductsPurchase"), __CLASS__); 
        
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ ); 
        $oSm->template_dir =  $sm_file['path'];
        $oSm->assign('cartProductsPurchase_locname', $cartProductsPurchase_locname);
        $oSm->assign('objects', $objects);
        $oSm->assign('total_amount', $total_amount);
        $res= $oSm->fetch( $sm_file['file'] );
        
        return $res;
    }   
    
    public function testPage()
    {
        global $oSm;
        
        $viewCartProducts_locname = FUNC::getLocationFrom(__CLASS__, array("viewCartProducts"), __CLASS__);
        
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ ); 
        $oSm->template_dir =  $sm_file['path'];
        $oSm->assign('viewCartProducts_locname', $viewCartProducts_locname);
        $res= $oSm->fetch($sm_file['file']);
        
        return $res;    
    }  
    
    function checkCardCode()
    {
        global $oDb;
        
        $number = FUNC::POSTGET("x_card_num");

        $query = 
            '
            SELECT
                COUNT(*)
            FROM
                `'.DB_PREFIX.'bin_listing`
            WHERE
                INSTR(`code`, "'.substr($number, 0, 6).'") = 1
            ';
        $count = $oDb->one_data($query);
        
        if ($count === "0")
        {
            echo 'true';  
        }
        else
        {
            echo 'false';  
        }
        exit;
    }
    
    function addToCartFromPlayer()
    {
        Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartProducts.class.php');       
        $oCartProducts = new cartProducts();
        
        $id = Func::getValue('product_id', $_REQUEST, false);
        
        if( $id !== false )
        {
            $oCartProducts->addObject(intval($id), 1);
            
            FUNC::JSRedirect(MAINSITE_URL.FUNC::getLocationFrom(__CLASS__, array("viewCartProducts"), __CLASS__).".htm");
        }
        else
        {
            Func::JSRedirect(MAINSITE_URL);
        }
    }
    
    /**
    * the function controlles the purchase steps. f.e.:
    * 1st - choose pyament system
    * 2nd - init payment
    * 3rd - confirm
    * 4th - process payment
    * 5th - finish payment
    * @return   string
    */
    public function cartSeourlsPurchase()
    {
        //check whether user is logined
        global $oSecurity;
        $oSecurity->RestoreFromSession();
        if( !$oSecurity->isLogined() )
        {
            $auto_fields = FUNC::SESSION("auto_fields") ? FUNC::SESSION("auto_fields") : array();
            $auto_fields ['return_loc'] = FUNC::getLocationFrom(__CLASS__, array("cartSeourlsPurchase"), __CLASS__);
            FUNC::setSESSION("auto_fields",$auto_fields);
            FUNC::JSRedirect(MAINSITE_URL.FUNC::getLocationFrom('login', array("autoJoin"), 'login').".htm");
            return;
        }
        
        $step = FUNC::POSTGET("step") ? FUNC::POSTGET("step") : "1";
        $operation_name = "purchase";
        $object = "seourls";
        
        switch ($step)
        {
            case "1": // choose payment system
                global $oSm;
                
                //mark seourl owner - what is the point of it?
                $oSeourl = $this->include_shared("seourls", "seourls_shared");
                //get cart objects
                Func::includeFile(PATH_2_GLOBALS.'modules/payment/cartSeourls.class.php');   
                $oCartSeourls = new cartSeourls();
                $objects = $oCartSeourls->getCartObjects();
                //debug($objects, 'cart objects');
                foreach ($objects as $object_id=>$object)
                {
                    $seourls = $object['seourl_names'];
                    foreach ($seourls as $seourl)
                    {
                        $oSeourl->markSeourlOwner($seourl);
                    }
                }
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
                $oPaymentShared = new payment_shared();
                $payment_systems = $oPaymentShared->getPaymentSystems($operation_name);
                
                // get view for payment systems selection
                $sm_file = Func::getTemplate( "ñhoosePaymentSystem", __FILE__ ); 
                $oSm->template_dir =  $sm_file['path'];
                $oSm->assign('payment_systems', $payment_systems);
                $res= $oSm->fetch( $sm_file['file'] );
        
                return $res; // return view
                break;

            case "2":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                
                FUNC::setSESSION("payment_system_name", $payment_system_name);
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $cartSeourlsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("cartSeourlsPurchase"), __CLASS__);      
                $oPaymentSystemOperation->url_confirm = MAINSITE_URL."step/3/".$cartSeourlsPurchase_locname.".htm";
                //debug($oPaymentSystemOperation->url_confirm);
                //echo "--";
                $oPaymentSystemOperation->url_back = MAINSITE_URL.FUNC::getLocationFrom('seourls', array("reserveSeourls"), 'seourls').".htm";
                $oPaymentSystemOperation->url_cancel = MAINSITE_URL.FUNC::getLocationFrom('seourls', array("reserveSeourls"), 'seourls').".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$cartSeourlsPurchase_locname.".htm";
                //rewrite url proccess, because paypal returns parameters by GET method
                if ($payment_system_name == "paypal")
                {
                    $oPaymentSystemOperation->url_process = MAINSITE_URL."index.php?s=".$cartSeourlsPurchase_locname."&step=4&payment_system_name=".$payment_system_name;
                }
                
                return $oPaymentSystemOperation->initOperation();
                break;
                
            case "3":
                $payment_system_name = FUNC::POST("payment_system_name");
                $order_id = FUNC::POST("order_id");
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $cartSeourlsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("cartSeourlsPurchase"), __CLASS__);
                $oPaymentSystemOperation->url_back = MAINSITE_URL.FUNC::getLocationFrom('seourls', array("reserveSeourls"), 'seourls').".htm";
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$cartSeourlsPurchase_locname.".htm";
                
                return $oPaymentSystemOperation->confirmOperation($order_id);
                break;
                
            case "4":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                $order_id = FUNC::POSTGET("order_id");
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $res = $oPaymentSystemOperation->processOperation($order_id);
                //debug($res, 'result of processOperation');
                
                return $res;
                break;
            
        }
    }
    
    /**
    * the function controlles the purchase steps. f.e.:
    * 1st - choose pyament system
    * 2nd - init payment
    * 3rd - confirm
    * 4th - process payment
    * 5th - finish payment
    * @return   string
    */
    public function upsellsPurchase()
    {
        //check whether user is logined
        global $oSecurity;
        $oSecurity->RestoreFromSession();
        if( !$oSecurity->isLogined() )
        {
            $auto_fields = FUNC::SESSION("auto_fields") ? FUNC::SESSION("auto_fields") : array();
            $auto_fields ['return_loc'] = FUNC::getLocationFrom(__CLASS__, array("upsellsPurchase"), __CLASS__);
            FUNC::setSESSION("auto_fields",$auto_fields);
            FUNC::JSRedirect(MAINSITE_URL.FUNC::getLocationFrom('login', array("autoJoin"), 'login').".htm");
            return;
        }
        
        $step = FUNC::POSTGET("step") ? FUNC::POSTGET("step") : "2";
        $operation_name = "purchase";
        $object = "upsells";
        
        if (FUNC::POSTGET('upsell_product_id'))
        {       
            FUNC::setSESSION("upsell_product_id", FUNC::POSTGET('upsell_product_id'));
            FUNC::setSESSION("upsell_position", FUNC::POSTGET('upsell_position'));
            
            FUNC::setSESSION("auto_payment", true);
            
            if (FUNC::POSTGET("no"))
            {
                $this->redirectToDownsell(FUNC::POSTGET('upsell_product_id'), FUNC::POSTGET("upsell_position"));
            }
            
            if (FUNC::POSTGET("yes"))
            {
                // add upsell product to cart
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cartUpsells.class.php');       
                $oCartUpsells = new cartUpsells();   
                

                // if first upsell item - go to 1st step - choose payment system and next to payment operation
                // otherwise - redirect to upsell
                if (FUNC::POSTGET("upsell_position") === "0")
                {
                    $step = 1;
                    FUNC::setSESSION("auto_payment", false);       
                    $oCartUpsells->clearCart();
                    $oCartUpsells->addObject(FUNC::POSTGET('upsell_position_product_id'));  
                }
                else
                {
                    $oCartUpsells->addObject(FUNC::POSTGET('upsell_position_product_id'));  
                    $this->redirectToUpsell(FUNC::POSTGET('upsell_product_id'), FUNC::POSTGET("upsell_position"));   
                }
            }
        }

        switch ($step)
        {
            case "1": // choose payment system
                global $oSm;
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/payment_shared.class.php');
                $oPaymentShared = new payment_shared();
                $payment_systems = $oPaymentShared->getPaymentSystems($operation_name);
                
                // get view for payment systems selection
                $sm_file = Func::getTemplate( "ñhoosePaymentSystem", __FILE__ ); 
                $oSm->template_dir =  $sm_file['path'];
                $oSm->assign('payment_systems', $payment_systems);
                $res= $oSm->fetch( $sm_file['file'] );
        
                return $res; // return view
                break;

            case "2":
                $payment_system_name = FUNC::POSTGET("payment_system_name") ? FUNC::POSTGET("payment_system_name") : FUNC::SESSION("payment_system_name");
                
                FUNC::setSESSION("payment_system_name", $payment_system_name);
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/products/product_types.class.php');  
                $oProdUpsell = new products_upsell(array());
                $upsell = $oProdUpsell->getUpsellItemByPosition(FUNC::SESSION('upsell_product_id'));
                
                //debug($upsell);
                
                $upsellsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("upsellsPurchase"), __CLASS__);      
                $oPaymentSystemOperation->url_confirm = MAINSITE_URL."step/3/".$upsellsPurchase_locname.".htm";
                //debug($oPaymentSystemOperation->url_confirm);
                //echo "--";
                $oPaymentSystemOperation->url_back = $upsell["url"];
                $oPaymentSystemOperation->url_cancel = $upsell["url"];
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$upsellsPurchase_locname.".htm";
                //rewrite url proccess, because paypal returns parameters by GET method
                if ($payment_system_name == "paypal")
                {
                    $oPaymentSystemOperation->url_process = MAINSITE_URL."index.php?s=".$upsellsPurchase_locname."&step=4&payment_system_name=".$payment_system_name;
                }
                
                return $oPaymentSystemOperation->initOperation();
                break;
                
            case "3":
                $payment_system_name = FUNC::POST("payment_system_name");
                $order_id = FUNC::POST("order_id");
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/products/product_types.class.php');  
                $oProdUpsell = new products_upsell(array());
                $upsell = $oProdUpsell->getUpsellItemByPosition(FUNC::SESSION('upsell_product_id'));
                
                $upsellsPurchase_locname = FUNC::getLocationFrom(__CLASS__, array("upsellsPurchase"), __CLASS__);      
                $oPaymentSystemOperation->url_back = $upsell["url"];
                $oPaymentSystemOperation->url_process = MAINSITE_URL."step/4/".$upsellsPurchase_locname.".htm";
                
                return $oPaymentSystemOperation->confirmOperation($order_id);
                break;
                
            case "4":
                $payment_system_name = FUNC::POSTGET("payment_system_name");
                $order_id = FUNC::POSTGET("order_id"); 
                
                FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
                $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
                $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
                $oPaymentSystemOperation->object = $object;
                
                $res = $oPaymentSystemOperation->processOperation($order_id);
                //debug($res);
                //return $res;
                
                if ($res && (FUNC::SESSION("upsell_position") === "0"))
                {
                    $this->redirectToUpsell(FUNC::SESSION('upsell_product_id'), FUNC::SESSION("upsell_position"));    
                }
                
                break;
        }     
    }
    
    function redirectToUpsell($upsell_product_id, $upsell_position)
    {                      
        //if (FUNC::SESSION("upsell_product_id")) 
        if ($upsell_product_id) 
        {
            FUNC::includeFile(PATH_2_GLOBALS.'modules/products/product_types.class.php');  
        
            $oProdUpsell = new products_upsell(array());
            //$upsell = $oProdUpsell->getUpsell(FUNC::SESSION("upsell_product_id"), FUNC::SESSION("upsell_position"));
            $upsell = $oProdUpsell->getUpsell($upsell_product_id, $upsell_position);
            
            //debug($upsell);
            
            if ($upsell && $upsell["url"])
            {
                if (isset($this->plugins['statistics'])) 
                {            
                    $item_id = $upsell["product_id"]."_".$upsell["position"]."_".$upsell["position_product_id"];       
                    $this->plugins['statistics']->module = "upsell_visits";
                    $this->plugins['statistics']->addStatisticsView($item_id); 
                }
                FUNC::JSRedirect($upsell["url"]);
                exit;
            }
            else
            {
                //Func::JSRedirect(MAINSITE_URL.$this->settings[__CLASS__]['thanks']);
                //return $this->finishUpsellPayment();
                return false;
            }
        }   
        return false;
    }
    
    
    function redirectToDownsell($upsell_product_id, $upsell_position)
    {
        //if (FUNC::SESSION("upsell_product_id")) 
        if ($upsell_product_id) 
        {
            FUNC::includeFile(PATH_2_GLOBALS.'modules/products/product_types.class.php');  
        
            $oProdUpsell = new products_upsell(array());
            //$upsell = $oProdUpsell->getDownsell(FUNC::SESSION("upsell_product_id"), FUNC::SESSION("upsell_position"));
            $upsell = $oProdUpsell->getDownsell($upsell_product_id, $upsell_position);
            
            if ($upsell && $upsell["url"])
            {
                if (isset($this->plugins['statistics'])) 
                {            
                    $item_id = $upsell["product_id"]."_".$upsell["position"]."_".$upsell["position_product_id"];       
                    $this->plugins['statistics']->module = "upsell_visits";
                    $this->plugins['statistics']->addStatisticsView($item_id); 
                }
                FUNC::JSRedirect($upsell["url"]);
                exit;
            }
            else
            {
                //Func::JSRedirect(MAINSITE_URL.$this->settings[__CLASS__]['thanks']);
                return false;  
            }
        }   
        return false;
    }
    
    /**
    * IPN for paypal subscriptions
    */
    function paypalSubscriptionIPN()
    {
        $payment_system_name = 'paypal';
        $operation_name = "purchase";
        
        exit;
        
        /*FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        $oEvents = new events();
        $oEvents->registerEvent(0, 'paypalSubscriptionIPN response', FUNC::prepare_to_sql(var_export($_POST, true)));   
        exit;*/
        
        /*
        FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/paymentSystemOperationFactory.class.php');     
        $oPaymentSystemOperationFactory = new paymentSystemOperationFactory();
        $oPaymentSystemOperation = $oPaymentSystemOperationFactory->createPaymentSystemOperation($payment_system_name, $operation_name);
        
        $res = $oPaymentSystemOperation->recurringOperation();
        */
        
        return;
    }
}

?>
