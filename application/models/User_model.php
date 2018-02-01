<?php

class User_model extends CI_Model {

	public function __construct() {
		$this->load->database();
	}
	public function existUser($openId) {

		$sql = sprintf("select * from tb_users where fd_openid='%s' and fd_phone is not null and LENGTH(fd_phone)=11", $openId);

		$query = $this->db->query($sql);

		if ($query->num_rows() >= 1) {return true;} else {return false;}
	}

	public function addUser($phone, $openId) {

		$user = array(
			"fd_phone" => $phone,
			"fd_openid" => $openId,
		);

		$this->db->insert('tb_users', $user);

		return true;
	}
	public function updateFace($openId, $faceId, $phone, $imageId) {

		$user = array(
			'fd_faceid' => $faceId,
			'fd_imageid' => $imageId,
		);

		$this->db->where(array('fd_openid' => $openId, 'fd_phone' => $phone))->update('tb_users', $user);

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