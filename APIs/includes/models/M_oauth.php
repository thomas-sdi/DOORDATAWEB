<?php

class M_oAuth{
	private static $client = 'DOORDATA_iOS_Pzm4eQFkxQzjN8WwZJpc';
	private static $privateKey = 'JBqtQoDovMFP2aTBavJLEGnwQ4FkeZyiqpgsbJom';
	private $method;
	
	function __construct($method){
		$this->method = $method;
	}
	
	public static function genetatePublicKey($method){
			
		$ivLen = openssl_cipher_iv_length($method);
		
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$iv = '';
    	for ($i = 0; $i < $ivLen; $i++) {
    		$pos = rand(0, strlen($characters) - 1);
    		$iv .= substr($characters, $pos, 1);
    	}
		
		return $iv;
	}
	
	public function encrypt($publicKey){
		$encrypted = openssl_encrypt(self::$client, 'AES-256-CBC', self::$privateKey, 0, $publicKey);
		return $encrypted;
	}
	
	public static function getEncryptionMethods(){
		return openssl_get_cipher_methods(true);
	}
}

?>
