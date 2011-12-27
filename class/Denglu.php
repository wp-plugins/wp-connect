<?php

/**
 * Ŀ�ģ��ѻ���������protected����ʽ��װ��base���ֱ��չ�ָ������û�
 * @author hyperion_cc
 * @version 1.0
 * @created 09-����-2011 15:00:39
 */
class Denglu
{
	protected $appID;
	protected $apiKey;

	/**
	 * denglu API��������Ĭ��http://open.denglu.cc
	 * ���ô������������Ժ��������������ض�������Ŀͻ�
	 */
	protected $domain = 'http://open.denglu.cc';
	/**
	 * DENGLU RESTful API�ĵ�ַ
	 */
	protected $apiPath = array(
		'bind' => '/api/v3/bind',
		'unbind' => '/api/v3/unbind',
		'login' => '/api/v3/send_login_feed',
		'getUserInfo' => '/api/v3/user_info',
		'register' => '/api/v4/create_account',
		'update' => '/api/v4/import_user',
		'share' => '/api/v3/share',
		'getMedia' => '/api/v3/get_media',
		'unbindAll' => '/api/v3/all_unbind'
	);


	/*
	 * ϵͳ�ı���
	 */
	protected $charset;
	/**
	 * Provider��ö�٣����������/transfer/[name]�ĵ�ַ��׺
	 */
	protected $providers = array(
		'google' => '/transfer/google',
		'windowslive' => '/transfer/windowslive',
		'sina' => '/transfer/sina',
		'tencent' => '/transfer/tencent',
		'sohu' => '/transfer/sohu',
		'netease' => '/transfer/netease',
		'renren' => '/transfer/renren',
		'kaixin001' => '/transfer/kaixin001',
		'douban' => '/transfer/douban',
		'yahoo' => '/transfer/yahoo',
		'qzone' => '/transfer/qzone',
		'alipay' => '/transfer/alipay',
		'taobao' => '/transfer/taobao',
		'tianya' => '/transfer/tianya',
		'alipayquick' => '/transfer/alipayquick',
		'baidu' => '/transfer/baidu',
	);
	/**
	 * ��ǰ�û��������Ե�һ������
	 */
	var $user;
	/**
	 * ��sdk�İ汾�ţ���ʼΪ1.0
	 */
	const VERSION = '1.0';

	/**
	 * ���ܷ���
	 */
	protected $signatureMethod = 'MD5';

	/**
	 * ���캯��
	 * @param appID	���غ�̨�����appID {@link http://open.denglu.cc}
	 * @param apiKey	���غ�̨�����apiKey {@link http://open.denglu.cc}
	 * #param charset ϵͳʹ�õı�������utf-8 ��gbk
	 * @param signatureMethod	ǩ���㷨����ʱֻ֧��MD5
	 */
	function Denglu($appID, $apiKey, $charset, $signatureMethod = 'MD5')//
	{
		
		$this->appID = $appID;
		$this->apiKey = $apiKey;
		$this->signatureMethod = $signatureMethod;
		$this->charset = $charset;
	}

	/**
	 * ��ȡ��½/������
	 * 
	 * @param isBind
	 *            �Ƿ����ڰ󶨣��ǰ���Ϊ��¼��
	 * @param Provider
	 *            ͨ��Denglu.Provider p = Denglu.Provider.guess(mediaNameEn) ��ȡ��
	 *            mediaNameEn��ȡý���б��еõ�
	 * @param uid
	 *            �û���վ���û�ID����ʱ��Ҫ
	 * @throws DengluException
	 */
	function getAuthUrl($Provider, $isBind = false, $uid = 0 )
	{
		$authUrl = $this->domain;
		
		if(isset($this->providers[$Provider])){
			$authUrl .= $this->providers[$Provider];
		}else{
			return array('errorCode'=>1,'errorDescription'=>'Please update your denglu-scripts to the latest version!');
		}
		
		if($isBind && $uid>0){
			$authUrl .= '?uid='.$uid;
		}
		
		return $authUrl;
	}

	/**
	 * ����token��ȡ�û���Ϣ
	 *
	 * @param token
	 * 
	 * ����ֵ eg:
	 * {
	 * 		"mediaID":7,							// ý��ID
	 * 		"createTime":"2011-05-20 16:44:19",		// ����ʱ��
	 * 		"friendsCount":0,						// ������
	 * 		"location":null,						// ��ַ
	 * 		"favouritesCount":0,					// �ղ���
	 * 		"screenName":"denglu",					// ��ʾ����
	 * 		"profileImageUrl":"http://head.xiaonei.com/photos/0/0/men_main.gif",		// ����ͷ��
	 * 		"mediaUserID":61,						// �û�ID
	 * 		"url":null,								// �û�����/��ҳ��ַ
	 * 		"city":null,							// ����
	 * 		"description":null,						// ��������
	 * 		"createdAt":"",							// ��ý���ϵĴ���ʱ��
	 * 		"verified":0,							// ��֤��־
	 * 		"name":null,							// �Ѻ���ʾ����
	 * 		"domain":null,							// �û����Ի�URL
	 * 		"province":null,						// ʡ��
	 * 		"followersCount":0,						// ��˿��
	 * 		"gender":1,								// �Ա� 1--�У�0--Ů,2--δ֪
	 * 		"statusesCount":0,						// ΢��/�ռ���
	 * 		"personID":120							// ����ID
	 * }
	 */
	function getUserInfoByToken($token, $refresh = false)
	{
		return $this->callApi('getUserInfo',array('token'=>$token));
	}

	/**
	 * ��ȡ��ǰӦ��ID�󶨵�������ữý�弰������
	 * 
	 * 
	 * ����ֵ eg:
	 * [
	 * 		{
	 * 			"mediaID":7,																		// ID
	 * 			"mediaIconImageGif":"http://test.denglu.cc/images/denglu_second_icon_7.gif",		// ��ữý����ɫIcon
	 * 			"mediaIconImage":"http://test.denglu.cc/images/denglu_second_icon_7.png",			// ��ữý����ɫIcon
	 * 			"mediaNameEn":"renren",																// ��ữý������Ƶ�ƴ��
	 * 			"mediaIconNoImageGif":"http://test.denglu.cc/images/denglu_second_icon_no_7.gif",	// ��ữý���ɫIcon
	 * 			"mediaIconNoImage":"http://test.denglu.cc/images/denglu_second_icon_no_7.png",		// ��ữý���ɫIcon
	 * 			"mediaName":"������",																// ��ữý�������
	 * 			"mediaImage":"http://test.denglu.cc/images/denglu_second_7.png",					// ��ữý���ͼ��
	 * 			"shareFlag":0,																		// �Ƿ��з������� 0��1��
	 * 			"apiKey":"704779c3dd474a44b612199e438ba8e2"											// ��ữý���Ӧ��apikey
	 * 		}
	 * ]
	 */
	function getMedia()
	{
		return $this->callApi('getMedia',array('appid'=>$this->appID));
	}

	function register_denglu($content)
	{
		return $this->callApi('register',array('data'=>$content) );
	}

	function update_denglu($content)
	{
		return $this->callApi('update',array('appid'=>$this->appID, 'data'=>$content) );
	}

	/**
	 * �û��󶨶����ữý���˺ŵ������˺���
	 * 
	 * @param mediaUID
	 *            ��ữý����û�ID
	 * @param uid
	 *            �û���վ�Ǳߵ��û�ID
	 * @param uname
	 *            �û���վ���ǳ�
	 * @param uemail
	 *            �û���վ������
	 * @return ����ֵ eg: {"result": "1"}
	 */
	function bind( $mediaUID, $uid, $uname, $uemail)
	{
		return $this->callApi('bind',array('appid'=>$this->appID,'muid'=>$mediaUID,'uid'=>$uid,'uname'=>$uname,'uemail'=>$uemail));
	}

	/**
	 * �û��������ữý���˺�
	 * 
	 * @param mediaUID    ��ữý����û�ID
	 *
	 * ����ֵ eg: {"result": "1"}
	 */
	function unbind( $mediaUID)
	{
		return $this->callApi('unbind',array('appid'=>$this->appID,'muid'=>$mediaUID));
	}

	/**
	 * ���͵�¼��������
	 * 
	 * @param mediaUserID    
	 *               �ӵ��ػ�ȡ��mediaUserID
	 *
	 * ����ֵ eg: {"result": "1"}
	 */
	function sendLoginFeed($mediaUserID)
	{
		return  $this->callApi('login',array('muid'=>$mediaUserID,'appid'=>$this->appID));
	}

	/**
	 * �û��������ӡ���־����Ϣʱ�����԰Ѵ���Ϣ������������
	 * 
	 * @param mediaUserID
	 * @param content    ������ʾ����Ϣ
	 * @param url    �鿴��Ϣ������
	 * @param uid    ��վ�û���Ψһ�Ա�ʶID
	 *
	 * ����ֵ eg: {"result": "1"}
	 */
	function share( $mediaUserID, $content, $url, $uid)
	{
		return $this->callApi('share',array('appid'=>$this->appID,'muid'=>$mediaUserID,'uid'=>$uid,'content'=>$content,'url'=>$url) );
	}
	
	/**
	 * �û�������а���ữý���˺�
	 * @param uid ��վ�û���Ψһ�Ա�ʶID
	 *
	 * ����ֵ eg: {"result": "1"} 
	 */
	function unbindAll($uid)
	{
		return $this->callApi('unbindAll',array('uid'=>$uid,'appid'=>$this->appID) );
	}

	/**
	 * ΪHTTP�����ǩ�� ǩ���㷨�� A�������������ʽ��Ϊ��key=value����ʽ
	 * B�������߸�ʽ���õĲ�����ֵ�ԣ����ֵ����������к�ƴ����һ�𣻡�key=valuekey=value��
	 * C������ƴ�Ӻõ��ַ���ĩβ׷����Ӧ�õ�api Key D�������ַ�����MD5ֵ��Ϊǩ����ֵ
	 * 
	 * @param request
	 */
	protected function signRequest($request)
	{
		ksort($request);
		$sig = '';
		foreach($request as $key=>$value) {
			$sig .= "$key=$value";
		}
		$sig .= $this->apiKey;
		return md5($sig);
	}
	
	/**
	 * ���ⲿ�������Ĳ���ת����http��ʽ
	 * @param param ����
	 */
	protected function createPostBody($param){
		foreach($param as $key => $v){
			if(is_array($v)){
				$param[$key] = implode(',',$v);
			}
			if(strtolower($this->charset)!='utf-8'){
				$param[$key] = $this->charsetConvert($v,'GBK','UTF-8');
			}
		}
		$param['timestamp'] = time().'000';
		$param['sign_type'] = $this->signatureMethod;
		$param['sign']  = $this->signRequest($param);
	
		$arr = array();
		foreach($param as $key => $v){
			$arr[] = $key.'='.urlencode($v);
		}
		return implode('&',$arr);
	}
	/**
	 * ����http���󲢻�÷�����Ϣ
	 * @param method �����api����
	 * @param request �����������͵Ĳ���
	 * @param return �������Ƿ��з���ֵ 
	 */
	protected function callApi($method,$request=array()){
		$apiPath = $this->getapiPath($method);
		$post = $this->createPostBody($request);
		$result = $this->makeRequest($apiPath,$post);
		
		$result = $this->parseJson($result);
		if(strtolower($this->charset)=='gbk'){
			$result = $this->charsetConvert($result, "GBK", "UTF8");
		}
		
		if(is_array($result) && isset($result['errorCode'])){
			$this->throwAPIException($result);
		}
		
		return $result;
	}
	/**
	 * ����ת��
	 * @param str ��Ҫת�����ַ���
	 * @param to Ҫת���ɵı���
	 * @param from �ַ����ĳ�ʼ����
	 */
	protected function charsetConvert($str,$to,$from){
		if(!function_exists('mb_convert_encoding')){
			function mb_convert_encoding($string,$to,$from)
			{
				if ($from == "UTF-8")
				$iso_string = utf8_decode($string);
				else
				if ($from == "UTF7-IMAP")
				$iso_string = imap_utf7_decode($string);
				else
				$iso_string = $string;
		
				if ($to == "UTF-8")
				return(utf8_encode($iso_string));
				else
				if ($to == "UTF7-IMAP")
				return(imap_utf7_encode($iso_string));
				else
				return($iso_string);
			}
		}
		if(is_array($str)){
			foreach($str as $k => $v){
				$k = $this->charsetConvert($k,$to,$from);
				$v = $this->charsetConvert($v,$to,$from);
				$str[$k] = $v;
			}
		}else{
			return  mb_convert_encoding($str,$to,$from);
		}
		return $str;
	}

	/**
	 *�׳��쳣
	 *@param result 
	 *
	 */
	protected function throwAPIException($result){
		$e = new DengluException($result);
		
		throw $e;
	}

	/**
	 * ����HTTP���󲢻����Ӧ
	 * @param url �����url��ַ
	 * @param request ���͵�http����
	 */
	///////function makeRequest($request)
	protected function makeRequest($url, $post = '' ) {
		$params = array(
			"timeout" => 30,
			"user-agent" => $_SERVER[HTTP_USER_AGENT],
			"sslverify" => false,
		);
		if ($post){
			$params['method'] = 'POST';
		    $params['body'] = $post;
		} else {
		    $params['method'] = 'GET';
		}
		return class_http($url, $params);//new
		$return = '';
		$matches = parse_url($url);
		$host = $matches['host'];
		if(empty($matches['query'])) $matches['query']='';
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = 80;
	
		if($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: '.strlen($post)."\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: \r\n\r\n";
			$out .= $post;
		} else {
			$out = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: \r\n\r\n";
		}
	
		if(function_exists('fsockopen')) {
			$fp = @fsockopen($host, $port, $errno, $errstr, 30);
		} elseif(function_exists('pfsockopen')) {
			$fp = @pfsockopen($host, $port, $errno, $errstr, 30);
		} else {
			return array('errorCode'=>1,'errorDescription'=>'Functions "fsockopen" and "pfsockopen" are not exists!');
		}
	
		if(!$fp) {
			return array('errorCode'=>1,'errorDescription'=>"Your website can't connect to denglu server!");
		} else {
			stream_set_blocking($fp, true);
			stream_set_timeout($fp, 30);
			@fwrite($fp, $out);
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				while (!feof($fp)) {
					if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
						break;
					}
				}
	
				$stop = false;
				while(!feof($fp) && !$stop) {
					$data = fread($fp,  8192);
					$return .= $data;
				}
			}
			@fclose($fp);
			return $return;
		}
	}

	/**
	 * ��apiPath����������Ӧmethod��ʵ�ʵ��õ�ַ
	 * 
	 * @param method
	 */
	protected function getApiPath($method)
	{
		return $this->domain.$this->apiPath[$method];
	}

	/**
	 * ����JSON�ַ���
	 * 
	 * �Ѵӽӿڻ�ȡ��������ת����json��ʽ���ڽ����н��нӿڷ��ش������
	 * 
	 * @param input
	 */
	protected function parseJson($input)
	{
		if(!function_exists('json_decode'))
		{
			function json_decode($input)
			{
				$comment = false;
				$out = '$x=';
	 
				for ($i=0; $i<strlen($input); $i++)
				{
					if (!$comment)
					{
					if (($input[$i] == '{') || ($input[$i] == '['))       $out .= ' array(';
					else if (($input[$i] == '}') || ($input[$i] == ']'))   $out .= ')';
					else if ($input[$i] == ':')    $out .= '=>';
					else                         $out .= $input[$i];         
				}
				else $out .= $input[$i];
				if ($input[$i] == '"' && $input[($i-1)]!="\\")    $comment = !$comment;
				}
				eval($out . ';');
				return $x;
			}
		}
		return json_decode($input, true);
	}

	/**
	 * 
	 * @param input
	 */
	protected function base64Encode($input)
	{
		return base64_encode($input);
	}

	/**
	 * 
	 * @param input
	 */
	protected function base64Decode($input)
	{
		return base64_decode($input);
	}

	/**
	 * 
	 * @param input
	 */

	function getapiKey()
	{
		return $this->apiKey;
	}

	/**
	 * 
	 * @param newVal
	 */
	function setapiKey($newVal)
	{
		$this->apiKey = $newVal;
	}

	function getappID()
	{
		return $this->appID;
	}

	/**
	 * 
	 * @param newVal
	 */
	function setappID($newVal)
	{
		$this->appID = $newVal;
	}

}

/**
 *�쳣��
* �������Ͷ��ձ�
 * Code Description
 * 1 	����������ο�API�ĵ�
 * 2 	վ�㲻����
 * 3 	ʱ�������
 * 4 	ֻ֧��md5ǩ��
 * 5 	ǩ������ȷ
 * 6 	token�ѹ���
 * 7 	ý���û�������
 * 8 	ý���û��Ѱ������û�
 * 9 	ý���û��ѽ��
 * 10 	δ֪����
 */ 

class DengluException extends Exception
{

	var $errorCode;
	var $errorDescription;

	function DengluException($result)
	{
		$this->result = $result;
		$this->errorCode = $result['errorCode'];
		$this->errorDescription = $result['errorDescription'];
		
		parent::__construct($this->errorDescription, $this->errorCode);
	}



	function geterrorCode()
	{
		return $this->errorCode;
	}

	/**
	 * 
	 * @param newVal
	 */
	function seterrorCode($newVal)
	{
		$this->errorCode = $newVal;
	}

	function geterrorDescription()
	{
		return $this->errorDescription;
	}
}

?>