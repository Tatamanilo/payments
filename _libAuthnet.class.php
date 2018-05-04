<?php
class _libAuthnet
{
    private $url_real;
    public $url_test;
    
    function _libAuthnet()
    {
        $this->url_real = "https://secure.authorize.net/gateway/transact.dll";
        $this->url_test = "https://test.authorize.net/gateway/transact.dll";
    }
    
    function apiCall($post_string)
    {
        $post_string .= "x_delim_data=TRUE&x_delim_char=".urlencode("|")."&";
        $post_string = rtrim( $post_string, "& " );
        
        $post_url = $this->url_real;    

        $request = curl_init($post_url); // initiate curl object
        curl_setopt($request, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($request, CURLOPT_POSTFIELDS, $post_string); // use HTTP POST to send form data
        curl_setopt($request, CURLOPT_POST, 1);
        //curl_setopt($request, CURLOPT_SSL_VERIFYPEER, FALSE); // uncomment this line if you get no gateway response.
        $post_response = curl_exec($request); // execute curl post and store results in $post_response
        curl_close ($request); // close curl object
        
        $data = explode("|", $post_response);
        
        return $data;
    }
    
    /**
    * chack md5 hash value
    * 
    * @param    string  md5 hash that is set in authnet account settings
    * @param    string  x_login value that were sent to server
    * 
    * @return   array   first   bool    whether the curl operation is ok
    *                   second  array   an associtive array containing the response from the server.
    */
    function checkMd5Hash($md5Hash, $x_login)
    {
        $md5_value = md5($md5Hash.$x_login.FUNC::POST("x_trans_id").FUNC::POST("x_amount"));
        if (FUNC::POST('x_MD5_Hash') == strtoupper($md5_value))
        {
            return true;
        }
        return false;
    }
}
?>
