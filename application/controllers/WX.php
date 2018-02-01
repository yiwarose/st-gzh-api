<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WX extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * http://example.com/index.php/welcome
	 * - or -
	 * http://example.com/index.php/welcome/index
	 * - or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 *
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */

	public function __construct() {

		parent::__construct();

		$this->load->library('wxgzh');

		//$this->load->helper('url_helper');
	}

	public function index() {

		$this->load->helper('url');

		$this->lang->load('cn');

		$this->load->helper('language');

		//$this->wxgzh->valid();

		$this->wxgzh->createMenu();

		//$this->weixin->responseMsg();
	}
}
