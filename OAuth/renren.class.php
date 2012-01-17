<?php
if (isset($_SERVER['SCRIPT_FILENAME']) && 'renren.class.php' == basename($_SERVER['SCRIPT_FILENAME']))
die ('Please do not load this page directly. Thanks!');
/*
 * 总体配置文件，包括API Key, Secret Key，以及所有允许调用的API列表
 * 要求最低的PHP版本是5.2.0，并且还要支持以下库：cURL, Libxml 2.6.0
 * The requirement of PHP version is 5.2.0 or above, and support as below:
 * cURL, Libxml 2.6.0
 */

//$config	= new stdClass;

# modify by tom.wang at 2011-05-12 : add relate url for oauth flow
$config->AUTHORIZEURL = 'https://graph.renren.com/oauth/authorize';  //进行连接授权的地址，不需要修改
$config->ACCESSTOKENURL = 'https://graph.renren.com/oauth/token'; //获取access token的地址，不需要修改
$config->SESSIONKEYURL = 'https://graph.renren.com/renren_api/session_key'; //获取session key的地址，不需要修改
//$config->CALLBACK = ''; //回调地址，注意和您申请的应用一致

$config->APIURL		= 'http://api.renren.com/restserver.do'; //RenRen网的API调用地址，不需要修改
//$config->APIKey		= '';	//你的API Key，请自行申请
//$config->SecretKey	= '';	//你的API 密钥
$config->APIVersion	= '1.0';	//当前API的版本号，不需要修改
$config->decodeFormat	= 'json';	//默认的返回格式，根据实际情况修改，支持：json,xml
/*
 *@ 以下接口内容来自http://wiki.dev.renren.com/wiki/API，编写时请遵守以下规则：
 *  key  (键名)		: API方法名，直接Copy过来即可，请区分大小写
 *  value(键值)		: 把所有的参数，包括required及optional，除了api_key,method,v,format不需要填写之外，
 *					  其它的都可以根据你的实现情况来处理，以英文半角状态下的逗号来分割各个参数。
 */
$config->APIMapping		= array( 
		'admin.getAllocation' => '',
		'connect.getUnconnectedFriendsCount' => '',
		'friends.areFriends' => 'uids1,uids2',
		'friends.get' => 'page,count',
		'friends.getFriends' => 'page,count',
		'notifications.send' => 'to_ids,notification',
		'status.set' => 'status',
		'users.getInfo'	=> 'uids,fields,session_key',
		/* 更多的方法，请自行添加 
		   For more methods, please add by yourself.
		*/
);

/*
 * 调用远程RESTful的客户端类
 * @Version: 0.0.1 alpha
 * @Created: 11:06:48 2010/11/23
 */

 class RESTClient{
  
  #cURL Object
	private $ch;
  #Contains the last HTTP status code returned.
	public $http_code;
  #Contains the last API call.
	private $http_url;
  #Set up the API root URL.
	public $api_url;
  #Set timeout default.
	public $timeout = 10;
  #Set connect timeout.
	public $connecttimeout = 30; 
  #Verify SSL Cert.
	public $ssl_verifypeer = false;
  #Response format.
	public $format = ''; // Only support json & xml for extension
	public $decodeFormat = 'json'; //default is json
  #Decode returned json data.
	//public $decode_json = true;
  #Contains the last HTTP headers returned.
	public $http_info = array();
	public $http_header = array();
	private $contentType;
	private $postFields;
	private static $paramsOnUrlMethod = array('GET','DELETE');
	private static $supportExtension  = array('json','xml');
  #For tmpFile
	private $file = null;
  #Set the useragnet.
	private static $userAgent = 'Timescode_RESTClient v0.0.1-alpha';


	public function __construct(){

		$this->ch = curl_init();
		/* cURL settings */
		curl_setopt($this->ch, CURLOPT_USERAGENT, self::$userAgent);
		curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($this->ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($this->ch, CURLOPT_HEADER, FALSE);

	}

     /**
      * Execute calls
      * @param $url String
      * @param $method String
      * @param $postFields String 
      * @param $username String
      * @param $password String
      * @param $contentType String 
      * @return RESTClient
      */
	public function call($url,$method,$postFields=null,$username=null,$password=null,$contentType=null){
		
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0 && !empty($this->format)) {
				$url = "{$this->api_url}{$url}.{$this->format}";
			}

		$this->http_url		= $url;
		$this->contentType	= $contentType;
		$this->postFields	= $postFields;

		$url				= in_array($method, self::$paramsOnUrlMethod) ? $this->to_url() : $this->get_http_url();

		is_object($this->ch) or $this->__construct();

		switch ($method) {
		  case 'POST':
			curl_setopt($this->ch, CURLOPT_POST, TRUE);
			if ($this->postFields != null) {
			  curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->postFields);
			}
			break;
		  case 'DELETE':
			curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;
		  case 'PUT':
			curl_setopt($this->ch, CURLOPT_PUT, TRUE);
			if ($this->postFields != null) {
				$this->file = tmpFile();
				fwrite($this->file, $this->postFields);
				fseek($this->file, 0);
			  curl_setopt($this->ch, CURLOPT_INFILE,$this->file);
			  curl_setopt($this->ch, CURLOPT_INFILESIZE,strlen($this->postFields));
			}
			break;
		}

		$this->setAuthorizeInfo($username, $password);
		$this->contentType != null && curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-type:'.$this->contentType));

		curl_setopt($this->ch, CURLOPT_URL, $url);

		$response = curl_exec($this->ch);
		$this->http_code = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($this->ch));

		$this->close();

		return $response;
	}


     /**
      * POST wrapper for insert data
      * @param $url String
      * @param $params mixed 
      * @param $username String
      * @param $password String
      * @param $contentType String
      * @return RESTClient
      */
     public function _POST($url,$params=null,$username=null,$password=null,$contentType=null) {
         $response = $this->call($url,'POST',$params,$username,$password,$contentType);
		 return $this->parseResponse($response);
     }

     /**
      * PUT wrapper for update data
      * @param $url String
      * @param $params mixed 
      * @param $username String
      * @param $password String
      * @param $contentType String
      * @return RESTClient
      */
     public function _PUT($url,$params=null,$username=null,$password=null,$contentType=null) {
         $response = $this->call($url,'PUT',$params,$username,$password,$contentType);
		 return $this->parseResponse($response);
     }

     /**
      * GET wrapper for get data
      * @param $url String
      * @param $params mixed
      * @param $username String
      * @param $password String
      * @return RESTClient
      */
     public function _GET($url,$params=null,$username=null,$password=null) {
         $response = $this->call($url,'GET',$params,$username,$password);
		 return $this->parseResponse($response);
     }

     /**
      * DELETE wrapper for delete data
      * @param $url String
      * @param $params mixed
      * @param $username String
      * @param $password String
      * @return RESTClient
      */
     public function _DELETE($url,$params=null,$username=null,$password=null) {
		 #Modified by Edison tsai on 09:50 2010/11/26 for missing part
		 $response = $this->call($url,'DELETE',$params,$username,$password);
		 return $this->parseResponse($response);
     }

	 /*
	 * Parse response, including json, xml, plain text
	 * @param $resp String
	 * @param $ext	String, including json/xml
	 * @return String
	 */
	 public function parseResponse($resp,$ext=''){
		
		$ext = !in_array($ext, self::$supportExtension) ? $this->decodeFormat : $ext;
		
		switch($ext){
				case 'json':
					# modify by tom.wang at 2011-05-15 : add 2nd param to generate array
					$resp = json_decode($resp, true);break;
				case 'xml':
					$resp = self::xml_decode($resp);break;
		}
			return $resp;
	 }

	 /*
	 * XML decode
	 * @param $data String
	 * @param $toArray boolean, true for make it be array
	 * @return String
	 */
	  public static function xml_decode($data,$toArray=false){
		  /* TODO: What to do with 'toArray'? Just write it as you need. */
			$data = simplexml_load_string($data);
			return $data;
	  }

	  public static function objectToArray($obj){
			
	  }

	   /**
	   * parses the url and rebuilds it to be
	   * scheme://host/path
	   */
	  public function get_http_url() {
		$parts = parse_url($this->http_url);

		$port = @$parts['port'];
		$scheme = $parts['scheme'];
		$host = $parts['host'];
		$path = @$parts['path'];

		$port or $port = ($scheme == 'https') ? '443' : '80';

		if (($scheme == 'https' && $port != '443')
			|| ($scheme == 'http' && $port != '80')) {
		  $host = "$host:$port";
		}
		return "$scheme://$host$path";
	  }

	  /**
	   * builds a url usable for a GET request
	   */
	  public function to_url() {
		$post_data = $this->to_postdata();
		$out = $this->get_http_url();
		if ($post_data) {
		  $out .= '?'.$post_data;
		}
		return $out;
	  }

	  /**
	   * builds the data one would send in a POST request
	   */
	  public function to_postdata() {
		return http_build_query($this->postFields);
	  }

     /**
      * Settings that won't follow redirects
      * @return RESTClient
      */
     public function setNotFollow() {
         curl_setopt($this->ch,CURLOPT_AUTOREFERER,FALSE);
         curl_setopt($this->ch,CURLOPT_FOLLOWLOCATION,FALSE);
         return $this;
     }

     /**
      * Closes the connection and release resources
      * @return void
      */
     public function close() {
         curl_close($this->ch);
         if($this->file !=null) {
             fclose($this->file);
         }
     }

     /**
      * Sets the URL to be Called
	  * @param $url String
      * @return void
      */
     public function setURL($url) {
         $this->url = $url; 
     }

     /**
      * Sets the format type to be extension
	  * @param $format String
      * @return boolean
      */
	 public function setFormat($format=null){
		if($format==null)return false;
		$this->format = $format;
		return true;
	 }

     /**
      * Sets the format type to be decoded
	  * @param $format String
      * @return boolean
      */
	 public function setDecodeFormat($format=null){
		if($format==null)return false;
		$this->decodeFormat = $format;
		return true;
	 }

     /**
      * Set the Content-Type of the request to be send
      * Format like "application/json" or "application/xml" or "text/plain" or other
      * @param string $contentType
      * @return void
      */
     public function setContentType($contentType) {
         $this->contentType = $contentType;
     }

     /**
      * Set the authorize info for Basic Authentication
      * @param $username String
      * @param $password String
      * @return void
      */
     public function setAuthorizeInfo($username,$password) {
         if($username != null) { #The password might be blank
             curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
             curl_setopt($this->ch, CURLOPT_USERPWD, "{$username}:{$password}");
         }
     }

     /**
      * Set the Request HTTP Method
      * @param $method String
      * @return void
      */
     public function setMethod($method) {
         $this->method=$method;
     }

     /**
      * Set Parameters to be send on the request
      * It can be both a key/value par array (as in array("key"=>"value"))
      * or a string containing the body of the request, like a XML, JSON or other
      * Proper content-type should be set for the body if not a array
      * @param $params mixed
      * @return void
      */
     public function setParameters($params) {
         $this->postFields=$params;
     }

	  /**
	   * Get the header info to store.
	   */
	  public function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
		  $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
		  $value = trim(substr($header, $i + 2));
		  $this->http_header[$key] = $value;
		}
		return strlen($header);
	  }
	  
 }

/*
 * 调用人人网RESTful API的客户端类，本类需要继承RESTClient类方可使用
 * @Version: 0.0.5 alpha
 * @Created: 17:09:40 2010/11/23
 */

 class RenRenClient extends RESTClient{

	private $_config;
	private	$_postFields	= '';
	private $_params		=	array();
	private $_currentMethod;
	private static $_sigKey = 'sig';
	private	$_sig			= '';
	private $_call_id		= '';
	private $_session_key	= '';
	private $_keyMapping	= array(
				'api_key'	=>	'',
				'method'	=>	'',
				'v'			=>	'',
				'format'	=>	'',
				'call_id'	=>	'',
				'session_key'=>	'',
			);
	
	public function __construct(){
		global $config;
		
		parent::__construct();
		
		$this->_config = $config;
		
		if(empty($this->_config->APIURL) || empty($this->_config->APIKey) || empty($this->_config->SecretKey)){
			throw new exception('Invalid API URL or API key or Secret key, please check config.inc.php');
		}

	}

     /**
      * GET wrapper
      * @param method String
      * @param parameters Array
      * @return mixed
      */
	public function GET(){

		$args = func_get_args();
		$this->_currentMethod	= trim($args[0]); #Method
		$this->paramsMerge($args[1])
			 ->getCallId()
			 ->getSessionKey()
			 ->setConfigToMapping()
			 ->generateSignature();

		#Invoke
		unset($args);

		return $this->_GET($this->_config->APIURL, $this->_params);
	
	}

     /**
      * POST wrapper
      * @param method String
      * @param parameters Array
      * @return mixed
      */
	public function POST(){

		$args = func_get_args();
		$this->_currentMethod	= trim($args[0]); #Method
		$this->paramsMerge($args[1])
			 ->getCallId()
			 ->getSessionKey()
			 ->setConfigToMapping()
			 ->generateSignature();

		#Invoke
		unset($args);

		return $this->_POST($this->_config->APIURL, $this->_params);
	
	}

     /**
      * PUT wrapper
      * @param method String
      * @param parameters Array
      * @return mixed
      */
	public function PUT(){

		$args = func_get_args();
		$this->_currentMethod	= trim($args[0]); #Method
		$this->paramsMerge($args[1])
			 ->getCallId()
			 ->getSessionKey()
			 ->setConfigToMapping()
			 ->generateSignature();

		#Invoke
		unset($args);

		return $this->_PUT($this->_config->APIURL, $this->_params);
	
	}

     /**
      * DELETE wrapper
      * @param method String
      * @param parameters Array
      * @return mixed
      */
	public function DELETE(){

		$args = func_get_args();
		$this->_currentMethod	= trim($args[0]); #Method
		$this->paramsMerge($args[1])
			 ->getCallId()
			 ->getSessionKey()
			 ->setConfigToMapping()
			 ->generateSignature();

		#Invoke
		unset($args);

		return $this->_DELETE($this->_config->APIURL, $this->_params);
	
	}

     /**
      * Generate signature for sig parameter
      * @param method String
      * @param parameters Array
      * @return RenRenClient
      */
	private function generateSignature(){

			$arr = array_merge($this->_params, $this->_keyMapping);
			ksort($arr);
			reset($arr);
			$str = '';
			foreach($arr AS $k=>$v){
				$str .= $k.'='.$v;
			}
			
			$this->_params = $arr;
			$str = md5($str.$this->_config->SecretKey);
			$this->_params[self::$_sigKey] = $str;
			$this->_sig = $str;

			unset($str, $arr);

			return $this;
	}

     /**
      * Parameters merge
      * @param $params Array
	  * @modified by Edison tsai on 15:56 2011/01/13 for fix non-object bug
	  * @modified by Tom on 15:53 2011/06/24 for fix sig param bug
      * @return RenRenClient
      */
	private function paramsMerge($params){

		$valid = true;
		if(!is_array($params) || !isset($this->_config->APIMapping[$this->_currentMethod])) {
			$valid = false;
		} else {
			$arr1 = explode(',', $this->_config->APIMapping[$this->_currentMethod]);
			if(count($params) != count($arr1)) $valid = false;
		}

		if(!$valid) {
			$this->_params = array();
			return $this;
		}

		$arr2 = array_combine($arr1, $params);

		if(count($arr2)<1 || !$arr2){

			foreach($arr1 AS $k=>$v){
				$arr2[$v] = $params[$k];
			} #end foreach

		} #end if

		$this->_params = $arr2;

		unset($arr1, $arr2);

		return $this;
	}

     /**
      * Setting mapping value
	  * @modified by Edison tsai on 15:04 2011/01/13 for add call id & session_key
      * @return RenRenClient
      */
	private function setConfigToMapping(){

			$this->_keyMapping['api_key']	= $this->_config->APIKey;
			$this->_keyMapping['method']	= $this->_currentMethod;
			$this->_keyMapping['v']			= $this->_config->APIVersion;
			$this->_keyMapping['format']	= $this->_config->decodeFormat;
			$this->_keyMapping['call_id']	= $this->_call_id;
			$this->_keyMapping['session_key']=$this->_session_key;

		return $this;
	}

	private function setAPIURL($url){
			$this->_config->APIURL = $url;
	}

  /**
    * Generate call id
	* @author Edison tsai
	* @created 14:48 2011/01/13
    * @return RenRenClient
	* 
	* @modify by tom.wang at 2011-05-12 : add isGotCallId support
    */
	public function getCallId(){
		$this->isGotCallId() or $this->_call_id = str_pad(mt_rand(1, 9999999999), 10, 0, STR_PAD_RIGHT);
		return $this;
	}

  /**
    * Set call id
	* @param $call_id float or integer, default is zero '0'
	* @author Edison tsai
	* @created 15:06 2011/01/13
    * @return null
	*
	* @modify by tom.wang 2011-05-12 : change private to public
    */
	public function setCallId($call_id=0){
		$this->_call_id = $call_id;
		return $this->_call_id;
	}

  /**
    * Get session key
	* @author Edison tsai
	* @created 15:09 2011/01/13
    * @return RenRenClient
    */
	private function getSessionKey(){
		$this->isGotSessionKey() or $this->_session_key	= $_COOKIE[$this->_config->APIKey.'_session_key'];
		return $this;
	}

   /**
    * Set session key
	* @param $session_key, String
	* @author Edison tsai
	* @created 15:10 2011/01/13
    * @return RenRenClient
    */
	public function setSessionKey($session_key){
		!empty($session_key) and $this->_session_key = $session_key;
		return $this;
	}

  /**
    * Is got session key or not?
	* @author Edison tsai
	* @created 15:11 2011/01/13
    * @return boolean
    */
	private function isGotSessionKey(){
		return empty($this->_session_key) ? false : true;
	}
	
	/**
	 * is got call id or not
	 *
	 * @author tom.wang<superlinyzu@qq.com>
	 * @return boolean
	 */
	private function isGotCallId() {
		return empty($this->_call_id) ? false : true;
	}
 }

/**
 * renren oauth flow(web server flow)
 * please oauth2 details here : http://wiki.dev.renren.com/wiki/%E4%BD%93%E9%AA%8C%E4%BA%BA%E4%BA%BAOAuth2.0%E9%AA%8C%E8%AF%81%E6%8E%88%E6%9D%83%E5%9F%BA%E6%9C%AC%E6%B5%81%E7%A8%8B
 */
class RenRenOauth extends RESTClient {
	private $_config;
	
	/**
	 * construct function
	 *
	 * @author tom.wang<superlinyzu@qq.com>
	 */
	public function __construct(){
		global $config;
		
		parent::__construct();
		
		$this->_config = $config;
		
		if(empty($this->_config->AUTHORIZEURL) || empty($this->_config->ACCESSTOKENURL) || empty($this->_config->SESSIONKEYURL) || empty($this->_config->CALLBACK)){
			throw new exception('Invalid AUTHORIZEURL or ACCESSTOKENURL or SESSIONKEYURL or CALLBACK, please check config.inc.php');
		}

	}
	/**
	 * get authorize url
	 * please read details at : http://wiki.dev.renren.com/wiki/%E8%8E%B7%E5%8F%96Authorization_Code
	 *
	 * @author tom.wang<superlinyzu@qq.com>
	 * @return string : the url used for user to authorize
	 */
	public function getAuthorizeUrl() {
		$url = $this->_config->AUTHORIZEURL . '?response_type=code&scope=publish_blog,publish_feed,publish_share,status_update,publish_comment&client_id=' . $this->_config->APIKey . '&redirect_uri=' . urlencode($this->_config->CALLBACK);
		
		return $url;
	}
	
	/**
	 * get access token
	 * please read details at : http://wiki.dev.renren.com/wiki/%E4%BD%BF%E7%94%A8Authorization_Code%E8%8E%B7%E5%8F%96Access_Token
	 *
	 * @author tom.wang<superlinyzu@qq.com>
	 * @param string $code : the authorized code
	 * @return string : access token
	 */
	public function getAccessToken($code) {
		$url = $this->_config->ACCESSTOKENURL;
		$params = array(
			'client_id' => $this->_config->APIKey,
			'client_secret' => $this->_config->SecretKey,
			'redirect_uri' => $this->_config->CALLBACK,
			'grant_type' => 'authorization_code',
			'code' => $code,
		);
		$ret = $this->call($url, 'POST', $params);
		$ret = json_decode($ret, true);
		
		# check error
		if(isset($ret['error_description'])) {
			throw new Exception($ret['error_description']);
		}
		
		return $ret;
	}
	
	/**
	 * get session key
	 *
	 * @author tom.wang<superlinyzu@qq.com>
	 * @param string $access_token
	 * @return string : session key
	 */
	public function getSessionKey($access_token) {
		$url = $this->_config->SESSIONKEYURL;
		$params = array(
			'oauth_token' => $access_token,
		);
		$ret = $this->call($url, 'POST', $params);
		$ret = json_decode($ret, true);
		
		# check error
		if(isset($ret['error_description'])) {
			throw new Exception($ret['error_description']);
		}
		
		return $ret;
	}
}
?>