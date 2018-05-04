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
        parent::CBase($this, '', $is_plugin);
    }
        
    function viewIncomingMoney()
    {
        if (!FUNC::POSTGET("xml"))
        {
            global $oSm;
            
            $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ );
            $oSm->template_dir =  $sm_file['path'];
            $res = $oSm->fetch( $sm_file['file'] );

            return $res;
        }
        else
        {
            return $this->getIncomingMoneyXml();
        }
    }
    
    function getIncomingMoneyXml()
    {
        global $oSecurity;
        global $oDb;
            
        $oSecurity->RestoreFromSession();
        
        $user_id = $oSecurity->GetUserID();
        
        $oDb_ewallet = new MySQL();
        $oDb_ewallet->connect_simple(DB_HOST_EWALLET, DB_USER_EWALLET, DB_PASS_EWALLET, DB_NAME_EWALLET);
        $oDb_ewallet->execute('set charset utf8');
        $oDb_ewallet->execute("SET NAMES 'utf8' COLLATE 'utf8_general_ci';"); 
        
        $tips_types = array("3", "73", "83", "63", "93");       
        
        // conditions
        $where = '';
        
        $date_start = FUNC::POST("date_start");
        $date_end = FUNC::POST("date_end");
        $type = FUNC::POST("type");
        $is_commission = FUNC::POST("is_commission");
        $net_min = FUNC::POST("net_min");
        $net_max = FUNC::POST("net_max");
        
        if ($date_start)
        {
            $where .= '
                DATE_FORMAT(t.date, "%Y-%m-%d") >= "'.MySQL::str2sql($date_start).'" AND
            ';
        }
        if ($date_end)
        {
            $where .= '
                DATE_FORMAT(t.date, "%Y-%m-%d") <= "'.MySQL::str2sql($date_end).'" AND
            ';
        }
        if ($type)
        {
            if ($type == "Tips")
            {
                $where .= '
                    type in ("'.implode('","', $tips_types).'") AND
                ';
            }
            else
            {
                $where .= '
                    type NOT IN ("'.implode('","', $tips_types).'") AND
                ';
            }
        }
        if ($is_commission)
        {
            if ($is_commission == "Yes")
            {
                $where .= '
                    isAff = 1 AND
                ';   
            }
            else
            {
                $where .= '
                    isAff = 0 AND
                ';   
            }
        }
        if ($net_min)
        {
            $where .= '
                t.amount >= "'.MySQL::str2sql($net_min).'" AND
            ';
        }
        if ($net_max)
        {
            $where .= '
                t.amount <= "'.MySQL::str2sql($net_max).'" AND
            ';
        }
        
        // limit
        $page = FUNC::POST("page") ? FUNC::POST("page") : 1;
        $rows = FUNC::POST("rows") ? FUNC::POST("rows") : 10;
        
        $query_count = '
            SELECT
                COUNT(*)
            FROM
                `'.DB_PREFIX_EWALLET.'entries` AS e,
                `'.DB_PREFIX_EWALLET.'transactions` AS t,
                `'.DB_PREFIX_EWALLET.'accounts` AS a
            WHERE
                '.$where.'
                t.d_account != 0 AND
                t.c_account = a.account AND
                e.entry_id = t.entry_id AND
                a.merchant_id = '.SID.' AND
                a.mlocac_id = '.MySQL::str2sql($user_id).'
            ';
        $records_count = $oDb_ewallet->one_data($query_count);
        
        $limit = '
            LIMIT
                '.($rows * ($page - 1)).', '.$rows.'
            ';  
        
        $fields = '
                e.entry_id,
                e.product_id as product_id,
                e.status,
                e.type,
                e.affilates,
                e.date_start,
                t.date as t_date,
                t.d_subaccount,
                t.c_subaccount,
                t.isAff,
                t.tranz_id,
                t.product_id as t_product_id,
                t.amount as t_amount,
                e.amount as amount,
                t.d_account as t_d_account,
                t.c_account as t_c_account,
                e.d_account as d_account,
                e.c_account as c_account
        ';
        
        $order = '';
        if (FUNC::POST("sidx"))
        {
            $order .= '
                '.FUNC::POST("sidx").' '.FUNC::POST("sord").',
            ';    
        }
        
        $query = '
            SELECT
                '.$fields.'
            FROM
                `'.DB_PREFIX_EWALLET.'entries` AS e,
                `'.DB_PREFIX_EWALLET.'transactions` AS t,
                `'.DB_PREFIX_EWALLET.'accounts` AS a
            WHERE
                '.$where.'
                t.d_account != 0 AND
                t.c_account = a.account AND
                e.entry_id = t.entry_id AND
                a.merchant_id = '.SID.' AND
                a.mlocac_id = '.MySQL::str2sql($user_id).'
            ORDER BY 
                '.$order.'
                t.date DESC
            '.$limit.'
            ';
        $items = $oDb_ewallet->select($query, MYSQL_ASSOC);
        
        $items_new = array();
        foreach ($items as $key=>$item)
        {
            $credits = array();
            //debug($item);
            if ($item["product_id"])
            {
                $items_new[$key]["product_id"] =  $item["product_id"];  //
                $credits[$item["product_id"]]["credit_id"] = $item["c_account"];
                $credits[$item["product_id"]]["amount"] = $item["amount"];
                $credits[$item["product_id"]]["affs_str"] = $item["affilates"];
                $credits[$item["product_id"]]["affs"] = $this->parseAff($item["affilates"]);
            }
            elseif ($item["t_product_id"])
            {            
                $items_new[$key]["product_id"] =  $item["t_product_id"];       
                
                $credits_str_arr = explode(';', $item['c_account'] );
                  
                if ($credits_str_arr)
                {
                    $data_exploded = array();
                    
                    foreach ($credits_str_arr as $credits_str)
                    {
                        $credits_exploded = explode(',',$credits_str);
                        
                        //debug($credits_exploded);
                        
                        $credit = false;
                        $amount = 0;
                        $affilates = '';
                        
                        if( isset($credits_exploded[0]) && $credits_exploded[0] != "" )
                        {
                            //echo "<br />ins";
                            $credit = (int)$credits_exploded[0];

                            if( isset($credits_exploded[1]) )
                            {
                                $amount = floatval( $credits_exploded[1] );
                            }
                                  
                            if( isset($credits_exploded[2]) )
                            {
                                $affilates = $credits_exploded[2];
                            }
                                  
                            $product_id = 0;
                            if( isset($credits_exploded[3]) )
                            {
                                //echo "pid".
                                $product_id = $credits_exploded[3];
                            }
                            
                            $credits[$product_id]["credit_id"] = $credit;
                            $credits[$product_id]["affs_str"] = $affilates;
                            $credits[$product_id]["amount"] = $amount;
                            $credits[$product_id]["affs"] = $this->parseAff($affilates);
                            //debug($credits);
                        }
                    }
                }
            }     
            
            $oProdShared = $this->include_shared("products", "products_shared");   
            $items_new[$key]["product"] = $oProdShared->getProductById($items_new[$key]["product_id"]);
            
            $current_credit = $credits[$items_new[$key]["product_id"]];
            $items_new[$key]["date"] =  $item["t_date"];
            $items_new[$key]["entry_id"] =  $item["entry_id"];
            $items_new[$key]["tranz_id"] =  $item["tranz_id"];
            $items_new[$key]["type"] =  in_array($item["type"], $tips_types) ? "Tip" : "Sale";
            $items_new[$key]["status"] =  ($item["c_subaccount"] == 2) ? "Pending" : "Paid";
            $items_new[$key]["is_comission"] =  $item["isAff"];
            
            $oUser = new user();   
            
            $items_new[$key]["buyer_id"] =  $item["d_account"]; //    
            $items_new[$key]["author_id"] =  $current_credit["credit_id"]; //    
            $items_new[$key]["aff1"] =  isset($current_credit["affs"][1]) ? $current_credit["affs"][1] : false; //  
            $items_new[$key]["aff2"] =  isset($current_credit["affs"][2]) ? $current_credit["affs"][2] : false; //            
            
            //echo "sql:".
            $query_accounts = '
                SELECT
                    a.`mlocac_id` AS `local_id`, 
                    a.`account` as `external_id`
                FROM
                    `'.DB_PREFIX_EWALLET.'accounts` AS a
                WHERE
                    a.`merchant_id` = '.SID.' AND
                    (
                    '.((isset($current_credit["affs"][1]["id"]) && ($current_credit["affs"][1]["id"] > 0)) ? 'a.account = "'.$current_credit["affs"][1]["id"].'" OR' : '').'
                    '.((isset($current_credit["affs"][2]["id"]) && ($current_credit["affs"][2]["id"] > 0)) ? 'a.account = "'.$current_credit["affs"][2]["id"].'" OR' : '').'
                    '.((isset($items_new[$key]["buyer_id"]) && ($items_new[$key]["buyer_id"] > 0)) ? 'a.account = "'.$items_new[$key]["buyer_id"].'" OR' : '').'
                    '.((isset($items_new[$key]["author_id"]) && ($items_new[$key]["author_id"] > 0)) ? 'a.account = "'.$items_new[$key]["author_id"].'" OR' : '').'
                    0
                    )
                ';
            //echo "end sql;";
            $accounts = $oDb_ewallet->select($query_accounts, MYSQL_ASSOC);
            //var_dump($accounts);
            
            $accounts_by_id = FUNC::arrangeArrayByField($accounts, "external_id", "local_id");
            
            // buyer,   $items_new[$key]["buyer_id"] is ewallet account id
            $oUser->userID = $accounts_by_id[$items_new[$key]["buyer_id"]];
            $items_new[$key]["buyer"]["info"] = $oUser->get(); 
            
            // author,  $items_new[$key]["author_id"] is ewallet account id    
            $oUser->userID = $accounts_by_id[$items_new[$key]["author_id"]];
            $items_new[$key]["author"]["info"] = $oUser->get(); 
            $oSeourl = $this->include_shared("seourls", "seourls_shared");
            $items_new[$key]["author"]["seourl"] = $oSeourl->getSeourl("authors", FUNC::getAuthorIdByUserId($items_new[$key]["author_id"]));
            
            // aff1     $current_credit["affs"][1]["id"] is already local id
            $oUser->userID = isset($current_credit["affs"][1]["id"]) ? $current_credit["affs"][1]["id"] : false;
            $items_new[$key]["aff1"]["info"] = $oUser->get(); 
            
            // aff2     $current_credit["affs"][2]["id"] is already local id            
            $oUser->userID = isset($current_credit["affs"][2]["id"]) ? $current_credit["affs"][2]["id"] : false;       
            $items_new[$key]["aff2"]["info"] = $oUser->get(); 
            
            if ($item["isAff"])
            {
                //if current user is the first aff - put to the comission1 field the value of transaction amount, etc "-"
                if (isset($current_credit["affs"][1]["id"]) && ($current_credit["affs"][1]["id"] == $user_id))
                {
                    $items_new[$key]["commission1"] =  $item["t_amount"]; 
                    $items_new[$key]["commission2"] =  "-"; 
                }
                //if current user is the second aff - put to the comission2 field the value of transaction amount, etc "-"       
                elseif (isset($current_credit["affs"][2]["id"]) && ($current_credit["affs"][2]["id"] == $user_id))
                {
                    $items_new[$key]["commission2"] =  $item["t_amount"]; 
                    $items_new[$key]["commission1"] =  "-";
                }
                $items_new[$key]["total_amount"] =  "-"; 
                $items_new[$key]["fee"] =  "-"; 
                $items_new[$key]["net"] =  $item["t_amount"]; 
            }
            else
            {
                $items_new[$key]["commission1_perc"] =  (isset($current_credit["affs"][1]["commission"]) && $current_credit["affs"][1]["id"]) ? $current_credit["affs"][1]["commission"] : 0; 
                $items_new[$key]["commission1"] =  $items_new[$key]["commission1_perc"] * $current_credit["amount"] / 100;  
                $items_new[$key]["commission2_perc"] =  (isset($current_credit["affs"][2]["commission"]) && $current_credit["affs"][2]["id"]) ? $current_credit["affs"][2]["commission"] : 0; 
                $items_new[$key]["commission2"] =  $items_new[$key]["commission2_perc"] * $current_credit["amount"] / 100;
                $items_new[$key]["total_amount"] =  $current_credit["amount"]; 
                $items_new[$key]["net"] =  $item["t_amount"]; 
                $items_new[$key]["fee"] =  $items_new[$key]["total_amount"] - $items_new[$key]["commission1"] - $items_new[$key]["commission2"] - $items_new[$key]["net"]; 
            }
        }
        
        //debug($items_new);
        //exit;
        
        global $oSm;
        
        
        //$records_count = count($records_count);
        $pages_count = ceil($records_count / $rows);       
        
        $sm_file = Func::getTemplate( __FUNCTION__, __FILE__ );  
        $oSm->template_dir =  $sm_file['path'];
        $oSm->assign( 'page', $page );
        $oSm->assign( 'records_count', $records_count );
        $oSm->assign( 'pages_count', $pages_count );
        $oSm->assign( 'rows', $rows );
        $oSm->assign( 'class', __CLASS__ );
        $oSm->assign( 'items', $items_new );
        $res= $oSm->fetch( $sm_file['file'] );

        include_once(PATH_2_GLOBALS."inc/pagebuilder.class.php");
        $res = Pagebuilder::postProcessor($res);    
        header("Content-type: text/xml");
        return $res; 
    }
}
?>