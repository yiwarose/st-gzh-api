<?php
class Rsa
{
    public $privateKey = '';

    public $publicKey = '';
    
    public function __construct()
    {
    	/*$configargs = array(  
    	"config" => "E:/php-5.6.31-Win32-VC11-x64/extras/ssl/openssl.cnf"  
		);  
        $resource = openssl_pkey_new($configargs);
        openssl_pkey_export($resource, $this->privateKey);
        $detail = openssl_pkey_get_details($resource);
        $this->publicKey = $detail['key'];*/
       
       //echo openssl_pkey_get_public($this->publicKey);*/
       
       $pub_key = openssl_pkey_get_public(file_get_contents('E:\\yiwa\\rsa_public_key.pem')); 
       
	   $keyData = openssl_pkey_get_details($pub_key); 
	   
	   $this->publicKey=$keyData['key'];
	   
	   //echo $this->publicKey;
	   
    }

    public function publicEncrypt($data)
    {
    	//echo $this->publicKey;
    	
        openssl_public_encrypt($data, $encrypted, $this->publicKey);
        
        return $encrypted;
    }

    public function publicDecrypt($data)
    {
        openssl_public_decrypt($data, $decrypted, $this->publicKey);
        return $decrypted;
    }

    public function privateEncrypt($data, $privateKey)
    {
        openssl_private_encrypt($data, $encrypted, $privateKey);
        return $encrypted;
    }

    public function privateDecrypt($data, $privateKey)
    {
        openssl_private_decrypt($data, $decrypted, $privateKey);
        return $decrypted;
    }
}
?>