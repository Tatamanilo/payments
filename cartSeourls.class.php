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

FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cart.class.php');  

/**
* model for work with seourls cart
* @author       tatana
*/
class cartSeourls extends cartNew
{
    
    /**
    * @var      string
    */
    public $object = "seourls";
    
    /**
    * add new object to cart
    * @param    int $object_id    id of object to be added
    * @param    int $count    objects to be added count
    * @return   boolean
    */
    public function addObject($object_id, $count = 1, $additional_info = array())
    {
        $cart = FUNC::SESSION("cart_new");
        $objects = isset($cart[$this->object]["initial"]) ? $cart[$this->object]["initial"] : array();
        
        if (!isset($objects[$object_id]))
        {
            $objects[$object_id]["count"] = 0;
        }       
        $objects[$object_id]["count"] = $objects[$object_id]["count"] + $count;
        
        foreach ($additional_info as $key=>$value)
        {
            $objects[$object_id][$key] = array_merge(isset($objects[$object_id][$key]) ? $objects[$object_id][$key] : array(),$value);
        }
        
        $cart[$this->object]["initial"] = $objects;
        FUNC::setSESSION("cart_new", $cart);
        
        return true;
    }
    
    
    /**
    * get array of products in cart including "name", "link", "price"    
    * returns structure:
    * array( 
    *     object_type => array(
    *         object_id => array(
    *             ["count"] = ...,
    *             ["info"]  => array(
    *               ["name"] = ..., 
    *               ["link"] = ...,
    *               ["price"] = ...
    *               ["owner_user_id"] = ...
    *             )
    *         ),
    *         ...
    *     ),
    *     ...
    * )
    * @param    string $status    initial or processing
    * @param    boolean $return_total_amount    defines whether there is need to return both cart objects and its total price amount in one array
    * @return   array
    */
    public function getCartObjectsInfo($status = "initial", $return_total_amount = false)
    {
        $objects = $this->getCartObjects($status);
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/seourls/seourls_shared.class.php');      
        $oSeourl = new seourls_shared(array(), array());
        
        if ($objects)
        {
            $total_amount = 0;
            
            foreach ($objects as $object_id=>$object)
            {
                if (($object["count"] > 0))
                {
                    $objects_info[$object_id]["count"] = $object["count"];
                    
                    $info = $oSeourl->getSeourlPriceByType($object_id);
                    //if there is trial period, price should be trial_amount
                    $objects_info[$object_id]["info"]["price"] = $info["price"];
                    
                    $objects_info[$object_id]["info"]["recurring_params"] = array( 'period' => 'm', 'frequency' => 1, 'cycles' => $info["duration"], 'amount' => $info["price"]);
                    
                    $objects_info[$object_id]["info"]["name"] = implode(',',$object["seourl_names"]);
                    
                    $objects_info[$object_id]["info"]["owner_user_id"] = 0;
                    
                    $objects_info[$object_id]["info_additional"] = $object["seourl_names"];
                                        
                    if ($return_total_amount)
                    {
                        $total_amount += $objects_info[$object_id]["info"]["price"] * $objects_info[$object_id]["count"];       
                    }
                }    
            }
            if ($return_total_amount) 
            {
                return array($objects_info, $total_amount);
            }
            else
            {
                return $objects_info;
            }
        }
        else
        {
            if ($return_total_amount) 
            {
                return array(array(), 0);
            }
            else
            {
                return array();
            }
        }
    }
    
    /**
    * get the total amount value of objects in cart
    * @param    string $status    initial or processing
    * @return   float
    */
    public function getCartTotalAmount($status = "initial")
    {
        $objects_info = $this->getCartObjectsInfo($status);
        
        $total_amount = 0;
        
        foreach ($objects_info as $object_info)
        {
            if ($object_info["count"] > 0)
            {
                $total_amount += $object_info["info"]["price"] * $object_info["count"];       
            }
        }
        //echo "<br />total_am_cart".$total_amount;
        return $total_amount;
    }
    
    function countObjectsInCart( $status = "initial" )
    {
        $cart = FUNC::SESSION("cart_new");
        
        $objects = $this->getCartObjects($status);
        
        $count = 0;
        if ($objects)
        {
            foreach ($objects as $object_info)
            {
                $count += $object_info["count"];
            }
        }
        return $count;
    }
}

?>
