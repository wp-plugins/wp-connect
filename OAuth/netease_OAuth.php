<?php
/** 
 * 网易微博操作类 
 * 
 * @package sae 
 * @author Easy Chen 
 * @version 1.0 
 */ 
class neteaseClient 
{ 

    // 构造函数 
    function __construct( $akey , $skey , $accecss_token , $accecss_token_secret ) 
    { 
        $this->oauth = new neteaseOAuth( $akey , $skey , $accecss_token , $accecss_token_secret ); 
    }
	
    // 最新关注人微博
    function home_timeline() 
    { 
        return $this->oauth->get('http://api.t.163.com/statuses/home_timeline.json'); 
    }
    
    // 发表微博(文本、图片) 
    function update( $text, $value = '' )
    { 
		$param = array();
        $param['status'] = $text; 
		$param['source'] = "<a href=\"http://www.smyx.net/wp-connect.html\">WordPress连接微博</a>"; 

		if ($value[0] == "image" && $value[1]) {
			$p = array();
			$p['pic'] = $value[1];
			$pic = $this->oauth->post( 'http://api.t.163.com/statuses/upload.json' , $p , true );
			$param['status'] .= " ".$pic['upload_image_url'];
		}

        return $this->oauth->post( 'http://api.t.163.com/statuses/update.json' , $param );
    } 

    // 获取自己信息
    function verify_credentials() 
    { 
        return $this->oauth->get( 'http://api.t.163.com/account/verify_credentials.json' );
    }

	// 修改头像
    function update_avatar( $pic_path )
	{
		$param = array();
		$param['image'] = "@".$pic_path;
        
        return $this->oauth->post( 'http://api.t.163.com/account/update_profile_image.json' , $param , true ); 
	
	}

}

/** 
 * 网易微博 OAuth 认证类 
 * 
 * @package sae 
 * @author Easy Chen 
 * @version 1.0 
 */ 
class neteaseOAuth { 
    // Contains the last HTTP status code returned.  
    public $http_code; 
    // Contains the last API call. 
    public $url; 
    // Set up the API root URL. 
    public $host = "http://api.t.163.com/"; 
    // Set timeout default. 
    public $timeout = 30; 
    // Set connect timeout. 
    public $connecttimeout = 30;  
    // Verify SSL Cert. 
    public $ssl_verifypeer = FALSE; 
    // Respons format. 
    public $format = 'json'; 
    // Decode returned json data. 
    public $decode_json = TRUE; 
    // Contains the last HTTP headers returned. 
    public $http_info; 
    // Set the useragnet. 
    public $useragent = 'neteaseOAuth v0.0.1';
    /* Immediately retry the API call if the response was not successful. */ 
    //public $retry = TRUE; 
    
    /** 
     *Set API URLS
     */
    function accessTokenURL()  { return 'http://api.t.163.com/oauth/access_token'; } 
    function authenticateURL() { return 'http://api.t.163.com/oauth/authenticate'; } 
    function authorizeURL()    { return 'http://api.t.163.com/oauth/authorize'; } 
    function requestTokenURL() { return 'http://api.t.163.com/oauth/request_token'; } 


    /** 
     * Debug helpers 
     */ 
    function lastStatusCode() { return $this->http_status; } 
    function lastAPICall() { return $this->last_api_call; } 

    /** 
     * construct WeiboOAuth object 
     */ 
    function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) { 
        $this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1(); 
        $this->consumer = new OAuthConsumer($consumer_key, $consumer_secret); 
        if (!empty($oauth_token) && !empty($oauth_token_secret)) { 
            $this->token = new OAuthConsumer($oauth_token, $oauth_token_secret); 
        } else { 
            $this->token = NULL; 
        } 
    } 


    /** 
     * Get a request_token from Weibo 
     * 
     * @return array a key/value array containing oauth_token and oauth_token_secret 
     */ 
    function getRequestToken($oauth_callback = NULL) { 
        $parameters = array(); 
        if (!empty($oauth_callback)) { 
            $parameters['oauth_callback'] = $oauth_callback; 
        }  

        $request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters); 
        $token = OAuthUtil::parse_parameters($request); 
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']); 
        return $token; 
    } 

    /** 
     * Get the authorize URL 
     * 
     * @return string 
     */ 
    function getAuthorizeURL($token, $sign_in_with_Weibo = TRUE , $url) { 
        if (is_array($token)) { 
            $token = $token['oauth_token']; 
        } 
        return $this->authenticateURL() . "?oauth_token={$token}&oauth_callback=" . urlencode($url); 
    } 

    /** 
     * Exchange the request token and secret for an access token and 
     * secret, to sign API calls. 
     * 
     * @return array array("oauth_token" => the access token, 
     *                "oauth_token_secret" => the access secret) 
     */ 
    function getAccessToken($oauth_verifier = FALSE, $oauth_token = false) { 
        $parameters = array(); 
        if (!empty($oauth_verifier)) { 
            $parameters['oauth_verifier'] = $oauth_verifier; 
        } 


        $request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters); 
        $token = OAuthUtil::parse_parameters($request); 
        $this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']); 
        return $token; 
    } 

    /** 
     * GET wrappwer for oAuthRequest. 
     * 
     * @return mixed 
     */ 
    function get($url, $parameters = array()) { 
        $response = $this->oAuthRequest($url, 'GET', $parameters); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * POST wreapper for oAuthRequest. 
     * 
     * @return mixed 
     */ 
    function post($url, $parameters = array() , $multi = false) { 
        
        $response = $this->oAuthRequest($url, 'POST', $parameters , $multi ); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * DELTE wrapper for oAuthReqeust. 
     * 
     * @return mixed 
     */ 
    function delete($url, $parameters = array()) { 
        $response = $this->oAuthRequest($url, 'DELETE', $parameters); 
        if ($this->format === 'json' && $this->decode_json) { 
            return json_decode($response, true); 
        } 
        return $response; 
    } 

    /** 
     * Format and sign an OAuth / API request 
     * 
     * @return string 
     */ 
    function oAuthRequest($url, $method, $parameters , $multi = false) { 

        if (strrpos($url, 'http://') !== 0 && strrpos($url, 'http://') !== 0) { 
            $url = "{$this->host}{$url}.{$this->format}"; 
        } 

        // echo $url ; 
        $request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters); 
        $request->sign_request($this->sha1_method, $this->consumer, $this->token); 
        switch ($method) { 
        case 'GET': 
            //echo $request->to_url(); 
            return $this->http($request->to_url(), 'GET'); 
        default: 
            return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata($multi) , $multi,$request->to_header() ); 
        } 
    } 

    /** 
     * Make an HTTP request 
     * 
     * @return string API results 
     */ 
    function http($url, $method, $postfields = NULL , $multi = false,$headermulti = "") { 
        $this->http_info = array(); 
        $ci = curl_init(); 
        /* Curl settings */ 
        curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent); 
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout); 
        curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout); 
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE); 

        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer); 

        curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader')); 

        curl_setopt($ci, CURLOPT_HEADER, FALSE); 

        switch ($method) { 
        case 'POST': 
            curl_setopt($ci, CURLOPT_POST, TRUE); 
            if (!empty($postfields)) { 
                curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields); 
                //echo "=====post data======\r\n";
                //echo $postfields;
            } 
            break; 
        case 'DELETE': 
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE'); 
            if (!empty($postfields)) { 
                $url = "{$url}?{$postfields}"; 
            } 
        } 

        $header_array = array(); 
        


        $header_array2=array(); 
        if( $multi ) 
        	$header_array2 = array("Content-Type: multipart/form-data; boundary=" . OAuthUtil::$boundary , "Expect: ");
        foreach($header_array as $k => $v) 
            array_push($header_array2,$k.': '.$v); 
	     array_push($header_array2,$headermulti); 

        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array2 ); 
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE ); 

        //echo $url."<hr/>"; 

        curl_setopt($ci, CURLOPT_URL, $url); 

        $response = curl_exec($ci); 
        $this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE); 
        $this->http_info = array_merge($this->http_info, curl_getinfo($ci)); 
        $this->url = $url; 

        //echo '=====info====='."\r\n";
        //print_r( curl_getinfo($ci) ); 
        
        //echo '=====$response====='."\r\n";
        //print_r( $response ); 

        curl_close ($ci); 
        return $response; 
    } 

    /** 
     * Get the header info to store. 
     * 
     * @return int 
     */ 
    function getHeader($ch, $header) { 
        $i = strpos($header, ':'); 
        if (!empty($i)) { 
            $key = str_replace('-', '_', strtolower(substr($header, 0, $i))); 
            $value = trim(substr($header, $i + 2)); 
            $this->http_header[$key] = $value; 
        } 
        return strlen($header); 
    } 
} 
?>