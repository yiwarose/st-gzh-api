<?php
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsMultiSender.php';
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsSenderUtil.php';
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsSingleSender.php';
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsStatusPuller.php';
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsVoicePromptSender.php';
require_once dirname(dirname(dirname(__FILE__))) . '\application\libraries\QQSMS\SmsVoiceVerifyCodeSender.php';

use Qcloud\Sms\SmsSingleSender;

class QQSMS {
	protected $CI;

	private $appid = 1400058359;

	private $appkey = "fb8e2d901c0faada42c291f5880da43d";

	private $templId = 71582;

	public function __construct() {
		$this->CI = &get_instance();

		$this->CI->load->helper('url');

	}

	public function sendSMS($code, $phone, $minutes) {

		try {

			$sender = new SmsSingleSender($this->appid, $this->appkey);

			$params = [$code, $minutes];

			$result = $sender->sendWithParam("86", $phone, $this->templId, $params, "", "", "");

			$rsp = json_decode($result);

			//print_r($rsp);

			//echo $rsp->errmsg;

			return $rsp->errmsg;

		} catch (\Exception $e) {

			return var_dump($e);
		}

	}
}