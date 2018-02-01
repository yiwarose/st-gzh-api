<?php
defined('BASEPATH') OR exit('No direct script access allowed');

define('appid', 'wxa84d9e87bd92d3ed');

define('secret', '8fab28f1b7964616fa66e37358999457');

define('token', 'mz7w0BXZWbSTu3J6c');

define('merchantid', '');

define('paysecret', ''); //denglili1983daizhuolin1981

class WXGZH {

	protected $CI;

	public function __construct() {

		$this->CI = &get_instance();

		$this->CI->load->helper('url');
	}

	public function unifieOrder($orderId, $body, $fee, $openid) {

		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

		$callBackUrl = "http://numaweixin.applinzi.com/index.php/api/pay/uifieordernotify";

		$data["appid"] = appid;

		$data["body"] = $body;

		$data["mch_id"] = merchantid;

		$data["nonce_str"] = $this->getRandChar(32);

		$data["notify_url"] = $callBackUrl;

		$data["out_trade_no"] = $orderId;

		$data["spbill_create_ip"] = $this->get_client_ip();

		$data["total_fee"] = $fee;

		$data["trade_type"] = "JSAPI";

		$data["openid"] = $openid;

		$sign = $this->getSign($data, false);

		$data["sign"] = $sign;

		$xmldata = $this->arrayToXml($data);

		$response = $this->postXmlCurl($xmldata, $url);

		if ($response) {

			$this->result = $this->xmlToArray($response);

			if ($this->result["return_code"] == 'SUCCESS') {

				$prepay_id = $this->result["prepay_id"];

				return $prepay_id;

			} else {

				return "err";
			}

		} else {

			return "err";
		}

	}

	public function prepareJsApiParams($prepay_id) {

		$jsApiObj["appId"] = appid;

		$timeStamp = time();

		$jsApiObj["timeStamp"] = sprintf("%s", $timeStamp);

		$jsApiObj["nonceStr"] = $this->getRandChar(32);

		$jsApiObj["package"] = "prepay_id=" . $prepay_id;

		$jsApiObj["signType"] = "MD5";

		$jsApiObj["paySign"] = $this->getSign($jsApiObj);

		$this->parameters = json_encode($jsApiObj);

		return $this->parameters;

	}
	public function getUserInfoByCode($code) {

		try {

			$url = sprintf("https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code", appid, secret, $code);

			$access_tokenArr = $this->getJsonArray($url, 'GET', '');

			if (isset($access_tokenArr->{'errcode'})) {

				return 'error';

			} else {

				$accessToken = $access_tokenArr->{'access_token'};

				$openid = $access_tokenArr->{'openid'};

				$url = sprintf('https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN', $accessToken, $openid);

				$access_userInfoArr = $this->getJsonArray($url, 'GET', '');

				if (isset($access_userInfoArr->{'errorcode'})) {

					return 'error';

				} else {

					//return '{"openid":'.$access_userInfoArr->{'openid'}.',"headimgurl":'.$access_userInfoArr->{'headimgurl'}.'}';//$access_userInfoArr->{'openid'};
					return $access_userInfoArr;
				}

			}

		} catch (Exception $e) {

			return 'error';
		}

	}

	public function responseMsg() {

		//$url='http://numaweixin.applinzi.com/index.php/frame#/tab/meal';

		//echo urlencode($url);

		//$this->load->library('weixin');

		//echo $this->weixin->token;

		$postStr = file_get_contents('php://input'); //$GLOBALS["HTTP_RAW_POST_DATA"];

		//echo $postStr;

		if (!empty($postStr)) {

			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

			$fromUsername = $postObj->FromUserName;

			$toUsername = $postObj->ToUserName;

			$msgType = $postObj->MsgType;

			switch ($msgType) {

			case "event":

				$event = $postObj->Event;

				$contentStr = "";

				if ($event == 'subscribe') {

					$contentStr = lang('wechat_subs_reply');

					$this->replayMsg($fromUsername, $toUsername, "text", $contentStr);
				}

				if ($event == 'CLICK') {

					$key = $postObj->EventKey; //点击菜单

					if ($key == 'minnor_help') {

						$msgType = "text";

						$contentStr = lang('minnor_help_reply');

						$this->replayMsg($fromUsername, $toUsername, $msgType, $contentStr);

					}

				}

				break;

			case "text": //发送文字信息

				$content = trim($postObj->Content);

				$msgType = "text";

				$contentStr = lang('wechat_text_reply');

				$this->replayMsg($fromUsername, $toUsername, $msgType, $contentStr);

				break;
			}

		} else {

			echo "";

			//exit;
		}
	}

	public function createMenu() {

		$url = sprintf("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s", appid, secret);

		$access_tokenArr = $this->getJsonArray($url, 'GET', '');

		$accessToken = $access_tokenArr->{'access_token'};

		$url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=" . $accessToken;

		$result = $this->getJsonArray($url, 'GET', '');

		//echo $accessToken;

		if (!isset($result->{'menu'}->{'button'})) {

			$menu = array(

				"button" => array(

					array(

						'type' => 'view',
						'name' => urlencode('工作台'), //urlencode('我的系统'),

						'url' => sprintf('https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=http://111.231.100.183/ionic&scope=snsapi_userinfo&state=STATE#wechat_redirect', appid),
						//'url'=>'http://wechat.nu-ma.com',
					),
					array(

						'type' => 'click',
						'name' => urlencode('帮助'), //urlencode('关于我们'),
						"key" => "minnor_help",
					),

				),
			);

			$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $accessToken;

			$jsonData = urldecode(json_encode($menu));

			$createMenuResult = $this->getJsonArray($url, 'POST', $jsonData);

			echo $createMenuResult->{'errmsg'};

		}

	}

	public function valid() {
		//ob_clean();
		$echoStr = $this->CI->input->get("echostr"); // $_GET ["echostr"];
		//$echoStr=$_GET ["echostr"];

		// valid signature , option
		if ($this->checkSignature()) {
			//ob_clean();
			echo $echoStr;
			exit();
		}
	}
	private function checkSignature() {

		// you must define TOKEN by yourself
		if (!defined("token")) {
			throw new Exception('TOKEN is not defined!');
		}

		//echo token;

		$signature = $this->CI->input->get("signature"); //$_GET ["signature"];//

		$timestamp = $this->CI->input->get("timestamp"); // $_GET ["timestamp"];//

		$nonce = $this->CI->input->get("nonce"); // $_GET ["nonce"];//

		$token = token;

		$tmpArr = array(
			$token,
			$timestamp,
			$nonce,
		);
		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);

		$tmpStr = implode($tmpArr);

		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

	private function replayMsg($fromUsername, $toUsername, $msgType, $contentStr) {

		$textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>0</FuncFlag></xml>";

		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, date("Y-m-d H:i:s"), $msgType, $contentStr);

		echo $resultStr;

		//exit;
	}

	private function getJsonArray($url, $method, $jsonData, $second = 30) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

		$result = curl_exec($ch);

		if ($result) {
			curl_close($ch);

			return json_decode($result);
		} else {
			$error = curl_errno($ch);

			//echo "CURL错误码:$error"."<br>";

			//echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";

			curl_close($ch);

			return false;
		}
	}

	public function postXmlCurl($xml, $url, $second = 30) {
		//echo $url."<br/>";

		//echo $xml;

		//初始化curl
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);

		//curl_close($ch);
		//返回结果
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			echo $error;
			//echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

	private function postXmlSSLCurl($xml, $url, $second = 30) {
		$ch = curl_init();
		//超时时间
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		//这里设置代理，如果有的话
		//curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
		//curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		//设置证书
		//使用证书：cert 与 key 分别属于两个.pem文件
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, WxPayConf_pub::SSLCERT_PATH);
		//默认格式为PEM，可以注释
		curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLKEY, WxPayConf_pub::SSLKEY_PATH);
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$data = curl_exec($ch);
		//返回结果
		if ($data) {
			curl_close($ch);
			return $data;
		} else {
			$error = curl_errno($ch);
			echo "curl出错，错误码:$error" . "<br>";
			echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
			curl_close($ch);
			return false;
		}
	}

	private function get_client_ip() {
		if ($_SERVER['REMOTE_ADDR']) {
			$cip = $_SERVER['REMOTE_ADDR'];
		} elseif (getenv("REMOTE_ADDR")) {
			$cip = getenv("REMOTE_ADDR");
		} elseif (getenv("HTTP_CLIENT_IP")) {
			$cip = getenv("HTTP_CLIENT_IP");
		} else {
			$cip = "unknown";
		}
		return $cip;
	}

	private function getRandChar($length) {

		$str = null;

		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";

		$max = strlen($strPol) - 1;

		for ($i = 0; $i < $length; $i++) {

			$str .= $strPol[rand(0, $max)]; //rand($min,$max)生成介于min和max两个数之间的一个随机整数

		}

		return $str;
	}

	/**
	 *  作用：格式化参数，签名过程需要使用
	 */
	private function formatBizQueryParaMap($paraMap, $urlencode) {
		$buff = "";
		ksort($paraMap);
		foreach ($paraMap as $k => $v) {
			if ($urlencode) {
				$v = urlencode($v);
			}
			//$buff .= strtolower($k) . "=" . $v . "&";
			$buff .= $k . "=" . $v . "&";
		}
		$reqPar;
		if (strlen($buff) > 0) {
			$reqPar = substr($buff, 0, strlen($buff) - 1);
		}
		return $reqPar;
	}

	private function getSign($Obj) {
		foreach ($Obj as $k => $v) {
			$Parameters[$k] = $v;
		}
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$String = $this->formatBizQueryParaMap($Parameters, false);
		//echo '【string1】'.$String.'</br>';
		//签名步骤二：在string后加入KEY
		$String = $String . "&key=" . paysecret;
		//echo "【string2】".$String."</br>";
		//签名步骤三：MD5加密
		$String = md5($String);
		//echo "【string3】 ".$String."</br>";
		//签名步骤四：所有字符转为大写
		$result_ = strtoupper($String);
		//echo "【result】 ".$result_."</br>";
		return $result_;
	}

	private function arrayToXml($arr) {
		$xml = "<xml>";
		foreach ($arr as $key => $val) {
			if (is_numeric($val)) {
				$xml .= "<" . $key . ">" . $val . "</" . $key . ">";

			} else {
				$xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
			}

		}
		$xml .= "</xml>";
		return $xml;
	}
	public function xmlToArray($xml) {
		//将XML转为array
		$array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $array_data;
	}

	public function genOrderId($phone) {

		$orderid = sprintf('%s%s', $phone, time());

		return $orderid;
	}

}