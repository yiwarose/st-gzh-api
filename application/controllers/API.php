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

			$this->log_model->frontEnd($phone, "request", "xcode");

			if ($phone === null || !is_numeric($phone) || strlen($phone) != 11) {

				$this->response($this->returnMsg('1', 'invalid phone number', true), REST_Controller::HTTP_OK);

			} else {

				try {

					$code = rand(100000, 999999);

					$result = $this->qqsms->sendSMS($code, $phone, "15");

					if ($result == 'OK') {

						$this->login_model->saveValidationCode($phone, $code, $from);

						$this->response($this->returnMsg('0', $result, false), REST_Controller::HTTP_OK);

					} else {

						$this->response($this->returnMsg('1', 'system error', true), REST_Controller::HTTP_OK);

					}

				} catch (Exception $e) {

					$this->log_model->system('error', $e);

					$this->response($this->returnMsg('1', 'system error', true), REST_Controller::HTTP_OK);
				}
			}

		} catch (Exception $e) {

			$this->log_model->system('error', $e);

			$this->response($this->returnMsg('1', 'system error', true), REST_Controller::HTTP_OK);
		}
	}

	public function auth_post() {

		try {

			$phone = $this->post('phone');

			$code = $this->post('code');

			$from = $this->post('from');

			$openId = $this->post('openid');

			$this->log_model->frontEnd($phone, "login", "login");

			if ($phone === null || !is_numeric($phone) || strlen($phone) != 11 || strlen($code) != 6) {

				$this->response($this->returnMsg('1', 'invalid parameter', true), REST_Controller::HTTP_OK);

			} else {

				try {

					$result = $this->login_model->userCheck($phone, $code, $from, true);

					if ($result) {

						$token = $this->user_model->generateToken(40);

						$this->user_model->updateUser($phone, $openId, $token);

						$this->response($this->returnMsg('0', $token, false), REST_Controller::HTTP_OK);

					} else {

						$this->response($this->returnMsg('1', 'invalid code', true), REST_Controller::HTTP_OK);

					}

				} catch (Exception $e) {

					$this->log_model->system('error', $e);

					$this->response($this->returnMsg('1', 'system error', true), REST_Controller::HTTP_OK);
				}
			}

		} catch (Exception $e) {

			$this->log_model->system('error', $e);

			$this->response($this->returnMsg('1', 'system error', true), REST_Controller::HTTP_OK);
		}
	}

	private function returnMsg($code, $msg, $needTranslate) {

		if ($needTranslate) {

			$ret = Array('code' => $code, 'message' => lang($msg));

		} else {

			$ret = Array('code' => $code, 'message' => $msg);
		}

		return $ret;

	}
}

?>