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