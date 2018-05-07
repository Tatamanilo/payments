<?php
FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/cart.class.php');  

/**
* model for work with products cart
* @author       tatana
*/
class cartProducts extends cartNew
{
    
    /**
    * @var      string
    */
    public $object = "products";
    
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
        
        FUNC::includeFile(PATH_2_GLOBALS.'modules/products/products_shared.class.php');      
        $oProdShared = new products_shared(array(), array());
        
        if ($objects)
        {
            $total_amount = 0;
            
            foreach ($objects as $object_id=>$object)
            {
                if (($object["count"] > 0))
                {
                    $objects_info[$object_id]["count"] = $object["count"];
                    
                    $info = $oProdShared->getProductById($object_id, false);  
                    $objects_info[$object_id]["info"]["price"] = $info["price_initial"];
                    $objects_info[$object_id]["info"]["name"] = $info["name"];
                    $objects_info[$object_id]["info"]["link"] = $info["link"];
                    $objects_info[$object_id]["info"]["owner_user_id"] = $info["user_info"]["user_id"];
                    
                    $objects_info[$object_id]["info_additional"] = $info;
                                        
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
}

?>
