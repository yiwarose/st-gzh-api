<?php

class User_model extends CI_Model {

	public function __construct() {
		$this->load->database();
	}
	public function existUser($phone) {

		$sql = sprintf("select * from tb_user where fd_phone='%s' and LENGTH(fd_phone)=11", $phone);

		$query = $this->db->query($sql);

		if ($query->num_rows() == 1) {return true;} else {return false;}
	}

	public function updateUser($phone, $openId, $token) {

		if ($this->existUser($phone)) {

			$user = array(
				"fd_wxopenid" => $openId,
				"fd_token" => $token,
			);

			$this->db->where(array('fd_phone' => $phone))->update('tb_user', $user);

		} else {

			$user = array(
				"fd_phone" => $phone,
				"fd_wxopenid" => $openId,
				"fd_token" => $token,
			);

			$this->db->insert('tb_user', $user);
		}

		return true;
	}

	public function generateToken($len = 20) {

		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';

		$string = time();

		for (; $len >= 1; $len--) {
			$position = rand() % strlen($chars);
			$position2 = rand() % strlen($string);
			$string = substr_replace($string, substr($chars, $position, 1), $position2, 0);
		}
		return $string;
	}

}