<?php

class Login_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();

        $this->load->model('user_model');
    }

    public function getPhoneState($phone,$from){

        $sql=sprintf("select * from tb_validation where fd_phone='%s' and TIMESTAMPDIFF(SECOND,fd_time,now())<60 and fd_expired=0 and fd_from='%s'",$phone,$from);

    

        $query = $this->db->query($sql);

        if ($query->num_rows() >= 1) {return true;} else {return false;}


    }

    public function saveValidationCode($phone,$code,$from){

        if($phone!=null && strlen($phone)==11 && is_numeric($phone) && strlen($code)==6){

            date_default_timezone_set("Asia/Shanghai");

            $until=date("Y-m-d H:i:s",strtotime("+15 minutes"));

            $item=array(
                    "fd_phone"=>$phone,
                    "fd_code"=>$code,
                    "fd_until"=>$until,
                    "fd_from"=>$from
                );
            
            $updateItem=array("fd_expired"=>1,"fd_from"=>$from);

            $this->db->where(array('fd_phone'=>$phone,'fd_expired'=>0))->update('tb_validation',$updateItem);

            $this -> db -> insert('tb_validation',$item);
        }

    }

    public function userCheck($phone,$code,$from,$update=true){

        try{

            if($phone!=null && strlen($phone)==11 && is_numeric($phone) && strlen($code)==6){

                $query = $this->db->get_where('tb_validation', array('fd_phone' => $phone,'fd_from'=>$from,'fd_code'=>$code,'fd_expired'=>0,'fd_until>='=>date('Y-m-d H:i:s')));

                 if($query->num_rows()==1){

                    if($update){

                        $updateItem=array("fd_expired"=>1);

                        $this->db->where(array('fd_phone'=>$phone,'fd_code'=>$code,'fd_expired'=>0,'fd_from'=>$from))->update('tb_validation',$updateItem);

                        return true;

                    }else{

                        return true;
                    }

                    /*$token=$this->user_model->updateUser($phone);

                    if($token){

                        $this->saveLog($phone,"登录成功");

                        return $token;


                    }else{

                        $this->saveLog($phone,"不接受新手机注册");

                        return false;
                    }*/

                    

                 }else{

                    return false;
                 }

            }else{

                $this->saveLog($phone,"登录失败:手机号码不对/验证码错误");

                return false;
            }

        }catch(Exception $e){

            $this->saveLog($phone,"登录失败:".$e);

            return false;
        }
    }

    /*private function userExist($phone){//,$openid){

         $query = $this->db->get_where('tb_users', array('fd_handphone' => $phone));//,'fd_wechat_openid'=>$openid));

        if($query->num_rows()==1){return true;}else{return false;}
    }

    private function regisUser($phone){//,$openid,$headimgurl,$gender,$province,$city,$nickename){

         $item=array(
                    "fd_handphone"=>$phone
                    //"fd_wechat_openid"=>$openid,
                    //"fd_headimgurl"=>$headimgurl,
                    //"fd_gender"=>$gender,
                    //"fd_province"=>$province,
                    //"fd_city"=>$city,
                    //"fd_nickname"=>$nickename,
                );

            $this -> db -> insert('tb_users',$item);
    }
    private function updateUser($phone){//,$openid,$headimgurl,$gender,$province,$city,$nickename){

         $item=array(
                    "fd_handphone"=>$phone
                    //"fd_wechat_openid"=>$openid,
                    //"fd_headimgurl"=>$headimgurl,
                    //"fd_gender"=>$gender,
                    //"fd_province"=>$province,
                    //"fd_city"=>$city,
                    //"fd_nickname"=>$nickename,
                );

            $this->db->where(array('fd_handphone'=>$phone,'fd_wechat_openid'=>$openid))->update('tb_users',$item);

    }*/

    public function saveLog($phone,$content){

                $item=array(
                    "fd_handphone"=>$phone,
                    "fd_content"=>$content
                );

            $this -> db -> insert('tb_log',$item);

    }
}