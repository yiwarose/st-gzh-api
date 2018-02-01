<?php

class Log_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }
    

    public function add($phone,$content,$type,$action){

        $log=array(
                "fd_handphone"=>$phone,
                "fd_content"=>$content,
                "fd_action"=>$action,
                "fd_type"=>$type
            );

        $this -> db -> insert('tb_log',$log);

        return true;

    } 

    public function log($content,$type){

        $item=array('fd_content'=>$content,'fd_time'=>date('Y-m-d H:i:s'),'fd_type'=>$type);

        $this->db->insert('tb_systemlog',$item);

        return true;


    }
}
