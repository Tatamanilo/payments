<?php
class _libPaypal
{
    private $url;
    public $url_express;
    private $url_nvp;
    
    private $apiUsername;
    private $apiPassword;
    private $apiSignature;
    
    function _libPaypal()
    {
        if(SERVER_TYPE == 2)
        {
            $this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';  //.sandbox
            $this->url_express = 'https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=';  //.sandbox
            $this->url_nvp = 'https://api-3t.sandbox.paypal.com/nvp';  //.sandbox
        }
        else
        {
            $this->url = 'https://www.paypal.com/cgi-bin/webscr';  //.sandbox
            $this->url_express = 'https://www.paypal.com/webscr&cmd=_express-checkout&token=';  //.sandbox
            $this->url_nvp = 'https://api-3t.paypal.com/nvp';  //.sandbox    
        }
    }
    
    /**
    * @desc     sets paypal api data
    */
    function setApi($apiUsername, $apiPassword, $apiSignature)
    {
        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
        $this->apiSignature = $apiSignature;
    }
    
    /**
    * hash_call: Function to perform the API call to PayPal using API signature
    * 
    * @param    string  is name of API  method.
    * @param    string  nvpStr is nvp string.
    * 
    * @return   array   first   bool    whether the curl operation is ok
    *                   second  array   an associtive array containing the response from the server.
    */
    function nvpCall($methodName, $nvpStr)
    {
        echo $apiEndpoint = $this->url_nvp;
        //$apiEndpoint = 'https://api-3t.sandbox.paypal.com/nvp';

        //$version = '58.0';
        $version = '63.0';
        
        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiEndpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        //turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);

        //NVPRequest for submitting to server
        $nvpReq = "METHOD=".urlencode($methodName)."&VERSION=".urlencode($version)."&PWD=".urlencode($this->apiPassword)."&USER=".urlencode($this->apiUsername)."&SIGNATURE=".urlencode($this->apiSignature).$nvpStr;

        //FUNC::includeFile(PATH_2_GLOBALS.'modules/payment/events.class.php');     
        //$oEvents = new events();  
        //$oEvents->registerEvent($order_id, $methodName.' call', FUNC::prepare_to_sql(var_export($nvpReq, true)));  
        
        //echo $nvpReq."<br>";
        //setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpReq);

        //getting response from server
        $response = curl_exec($ch);

        if (curl_errno($ch)) 
        {
            // moving to display page to display curl errors
            $curl_error_no = curl_errno($ch) ;
            $curl_error_msg = curl_error($ch);
            return array(false, array($curl_error_no, $curl_error_msg));
        } 
        else 
        {
             //closing the curl
             curl_close($ch);
        }
        
        //convrting NVPResponse to an Associative Array
        $nvpResArray = $this->deformatNVP($response);

        return array(true, $nvpResArray);
    }

    /** 
    * This function will take NVPString and convert it to an Associative Array and it will decode the response.
    * It is usefull to search for a particular key and displaying arrays.
    * 
    * @param    string  NVPString.
    * @param    array   nvpArray is Associative Array.
    */
    function deformatNVP($nvpstr)
    {
        $intial=0;
        $nvpArray = array();

        while(strlen($nvpstr))
        {
            //postion of Key
            $keypos= strpos($nvpstr,'=');
            //position of value
            $valuepos = strpos($nvpstr,'&') ? strpos($nvpstr,'&'): strlen($nvpstr);

            /*getting the Key and Value values and storing in a Associative Array*/
            $keyval=substr($nvpstr,$intial,$keypos);
            $valval=substr($nvpstr,$keypos+1,$valuepos-$keypos-1);
            //decoding the respose
            $nvpArray[urldecode($keyval)] =urldecode( $valval);
            $nvpstr=substr($nvpstr,$valuepos+1,strlen($nvpstr));
        }
        return $nvpArray;
    }
}
?>
