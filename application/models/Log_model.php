<?php

class Log_model extends CI_Model {

	public function __construct() {
		$this->load->database();
	}

	public function frontEnd($phone, $action, $content) {

		$log = array(
			"fd_handphone" => $phone,
			"fd_content" => $content,
			"fd_action" => $action,
		);

		$this->db->insert('tb_frontenduserlog', $log);

		return true;

	}

	public function system($type, $content) {

		$log = array(
			"fd_type" => $type,
			"fd_content" => $content,
		);

		$this->db->insert('tb_systemlog', $log);

		return true;

	}

}
