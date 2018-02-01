<?php
class Rise {
	protected $CI;

	public $privateKey = '';

	public $publicKey = '';

	public $STAPIURL = 'https://116.62.240.72:8443/';

	public $creditUrl = 'https://dsj.stholding.cn/api/useraction/getUserdrInfo';

	public function __construct() {
		$this->CI = &get_instance();

		$this->CI->load->helper('url');

		$pub_key = openssl_pkey_get_public(file_get_contents('C:\\ST\\key\\rsa_public_key.pem'));

		$keyData = openssl_pkey_get_details($pub_key);

		$this->publicKey = $keyData['key'];
	}
	public function publicEncrypt($data) {

		if (!is_string($data)) {

			return null;
		}

		$crypto = '';

		foreach (str_split($data, 117) as $chunk) {
			openssl_public_encrypt($chunk, $encrypted, $this->publicKey); //公钥加密

			$crypto .= $encrypted;
		}

		return base64_encode($crypto);
	}

	public function getMemberInfo($url, $jsonStr, $method, $second = 30) //$url, $jsonStr)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch, CURLOPT_URL, $this->STAPIURL . $url);

		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

		curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);

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

			curl_close($ch);

			return false;
		}
	}

	public function getMemberCredit($url, $method, $second = 30) //$url, $jsonStr)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch, CURLOPT_URL, $this->creditUrl . $url);

		curl_setopt($ch, CURLOPT_POST, 1);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

		//curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);

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

			curl_close($ch);

			return false;
		}
	}
}