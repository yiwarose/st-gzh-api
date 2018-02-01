<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . 'libraries\REST_Controller.php';

class API extends REST_Controller {

	public $faceBy = 'csz';

	public function __construct() {

		parent::__construct();

		$this->load->model('user_model');

		$this->load->library('rise');

		$this->load->library('WXGZH');

		$this->load->library('QQSMS');

		$this->load->model('login_model');

		$this->load->model('log_model');

		$this->lang->load('cn');

		$this->load->helper('language');

	}

	public function xCode_post() {

		try {

			$phone = $this->post('phone');

			$openid = $this->post('openid');

			$from = $this->post('from');

			$this->log_model->add($phone, $openid . " request xcode from " . $from, "xcode", "xcx request");

			$existMember = $this->existMemberIT($phone);

			if ($existMember->Ret == 1 && $from == 'xcx') {

				$this->response($this->returnMsg('2', lang("member already at request code")), REST_Controller::HTTP_OK);

				return;

			}

			if ($existMember->Ret == 0 && $from == 'xcx-bind') {

				$this->response($this->returnMsg('2', lang("member not exist at request code")), REST_Controller::HTTP_OK);

				return;
			}

			if ($phone === null || !is_numeric($phone) || strlen($phone) != 11) {

				$this->response($this->returnMsg('-1', "invalid phone number"), REST_Controller::HTTP_OK);

			} else {

				try {

					$code = rand(100000, 999999);

					$result = $this->qqsms->sendSMS($code, $phone, "15");

					if ($result == 'OK') {

						$this->login_model->saveValidationCode($phone, $code, $from);

						$this->response($this->returnMsg('0', $result), REST_Controller::HTTP_OK);

					} else {

						$this->response($this->returnMsg('1', $result), REST_Controller::HTTP_OK);

					}

				} catch (Exception $e) {

					$this->response($this->returnMsg('2', $e), REST_Controller::HTTP_OK);
				}
			}

		} catch (Exception $e) {

			$this->response($this->returnMsg('-1', lang('system error')), REST_Controller::HTTP_OK);
		}
	}

	private function returnMsg($code, $msg) {

		$ret = Array('code' => $code, 'message' => $msg);

		return $ret;

	}
}

?>