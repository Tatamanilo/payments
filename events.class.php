<?php
/**
* model for order events entity
* @author       tatana
*/
class events
{
    
    /**
    * creates new event for order
    * @param    int $order_id    order id event belongs to
    * @param    string $name    event name
    * @param    string $descr    event description
    */
    public function registerEvent($order_id, $name, $descr)
    {
        global $oDb;
        
        $query = '
            INSERT INTO
                '.DB_PREFIX.'events
            SET
                sid = "'.SID.'",
                order_id = "'.$order_id.'",
                name = "'.$name.'",
                descr = "'.$descr.'"
            ';
        $oDb->execute($query);
        
        return $oDb->insert_id();
    }
}

?>