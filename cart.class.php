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
* model for work with cart entity.
* cart objects are stored in session variable "cart"
* it is an array of next structure:
* array( 
*     object_type => array(
*       status => array(   
*         object_id => array(
*             ["count"] = ...,
*         ),
*         ...
*     ),
*     ...
* )
* status can be:
*   - initial - for current objects in the cart
*   - processing - for objects are processed in that moment
* in general works with certain cart objects, the object type is stored in class property "object". so it wirks with session["cart"][$this->object]
* @author       tatana
*/
abstract class cartNew
{
    
    /**
    * the name of object cart workes with (default is products)
    * @var      string
    */
    public $object = "products";
    
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
        $cart[$this->object]["initial"] = $objects;
        FUNC::setSESSION("cart_new", $cart);
        
        return true;
    }
    
    /**
    * change count of object in cart
    * @param    int $object_id    the object id which count to be changed
    * @param    int $count    the new value of object count
    * @return   boolean
    */
    public function changeObjectCount($object_id, $count, $additional_info = array())
    {
        if ($count > 0)
        {
            $cart = FUNC::SESSION("cart_new");      
            $objects = isset($cart[$this->object]["initial"]) ? $cart[$this->object]["initial"] : array();        
            $objects[$object_id]["count"] = $count;
            $cart[$this->object]["initial"] = $objects;
            FUNC::setSESSION("cart_new", $cart);
            
            return true;
        }
        else
        {
            return $this->removeObject();
        }
    }
    
    /**
    * removes from cart objects with id object_id
    * @param    int $object_id    
    * @return   boolean
    */
    public function removeObject($object_id, $additional_info = array())
    {
        $cart = FUNC::SESSION("cart_new");
        $objects = isset($cart[$this->object]["initial"]) ? $cart[$this->object]["initial"] : array();        
        
        if (isset($objects[$object_id]))
        {
            unset($objects[$object_id]);
        }
        $cart[$this->object]["initial"] = $objects;
        FUNC::setSESSION("cart_new", $cart);
        
        return true;
    }
    
    /**
    * removes all objects from cart of certain object type
    * clears the session variable ["cart"][$this->object]
    * @return   boolean
    */
    public function clearCart()
    {
        $cart = FUNC::SESSION("cart_new");
        
        if (isset($cart[$this->object]["initial"]))
        {
            unset($cart[$this->object]["initial"]);
        }
        FUNC::setSESSION("cart_new", $cart);
        
        return true;
    }
    
    /**
    * mark initial cart objects as processing
    */
    public function changeCartStatusToProcessing()
    {
        $cart = FUNC::SESSION("cart_new");   
        
        if (isset($cart[$this->object]["initial"]) && $cart[$this->object]["initial"])
        {  
            $cart[$this->object]["processing"] = isset($cart[$this->object]["initial"]) ? $cart[$this->object]["initial"] : array();
            $cart[$this->object]["initial"] = array();
        }
        FUNC::setSESSION("cart_new", $cart);                       
        
        return true;        
        
        /*
        $objects = $this->getCartObjects();       
        
        if ($objects)
        {
            foreach ($objects as $object_id=>$object)
            {
                if ($object["status"] == "processing")
                {
                    unset($objects[$object_id]);    
                }
                elseif ($object["status"] == "initial")
                {
                    if ($object["count"] > 0)
                    {
                        $object["status"] == "processing";
                        $objects[$object_id] = $object;    
                    }
                }         
            }
        }
        */
    }
    
    /**
    * restore processing cart objects to initial
    */
    public function changeCartStatusToInitial()
    {
        $cart = FUNC::SESSION("cart_new");     
        $cart[$this->object]["initial"] = isset($cart[$this->object]["processing"]) ? $cart[$this->object]["processing"] : array();     
        $cart[$this->object]["processing"] = array();       
        FUNC::setSESSION("cart_new", $cart);                         
        
        return true;        
        
        /*
        $objects = $this->getCartObjects();       
        
        if ($objects)
        {
            foreach ($objects as $object_id=>$object)
            {
                if ($object["status"] == "initial")
                {
                    unset($objects[$object_id]);    
                }
                elseif ($object["status"] == "processing")
                {
                    if ($object["count"] > 0)
                    {
                        $object["status"] == "initial";
                        $objects[$object_id] = $object;    
                    }
                }         
            }
        }
        */
    } 
    
    /**
    * get array of cart objects
    * @param    string $status    initial or processing
    * @return   array
    */
    public function getCartObjects($status = "initial")
    {
        $cart = FUNC::SESSION("cart_new");
        
        if (isset($cart[$this->object][$status]))
        {
            return $cart[$this->object][$status];     
        }
        else
        {
            return false;
        }
    }
    
    /**
    * get the count of object in initial cart by object id
    * @param    int $object_id    
    * @return   int
    */
    public function getObjectCount($object_id)
    {
        $cart = FUNC::SESSION("cart_new");
        
        if (isset($cart[$this->object]["initial"][$object_id]["count"]))
        {
            return $cart[$this->object]["initial"][$object_id]["count"];
        }
        else
        {
            return 0;
        }
    }
    
    /**
    * get array of objects in cart with extended info
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
    public abstract function getCartObjectsInfo($status = "initial", $return_total_amount = false);         
    
    /**
    * get the total amount value of objects in cart
    * @param    string $status    initial or processing
    * @return   float
    */
    public abstract function getCartTotalAmount($status = "initial");
}

?>
