<?php
 defined('BASEPATH') OR exit('No direct script access allowed'); 
class Apicrypter  {
    private $iv  = 'fdsfds85435nfdfs'; #Same as in JAVA
    private $key = '89432hjfsd891787'; #Same as in JAVA

    public function __construct() {
    }

    public function encrypt($str) { 
	  $str = $this->pkcs5_pad($str);   
	  $iv = $this->iv; 
	  $encrypted = openssl_encrypt($str, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
	  return bin2hex($encrypted);
    }

    public function decrypt($code) { 
	  $code = $this->hex2bin($code);
	  $iv = $this->iv; 
	  $decrypted = openssl_decrypt($code, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
	  $ut = mb_convert_encoding(trim($decrypted), 'UTF-8', 'ISO-8859-1');
	  return $this->pkcs5_unpad($ut);
    }

    protected function hex2bin($hexdata) {
	  $bindata = ''; 
	  for ($i = 0; $i < strlen($hexdata); $i += 2) {
	      $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
	  } 
	  return $bindata;
    } 

    protected function pkcs5_pad ($text) {
	  $blocksize = 16;
	  $pad = $blocksize - (strlen($text) % $blocksize);
	  return $text . str_repeat(chr($pad), $pad);
    }

    protected function pkcs5_unpad($text) {
	  $pad = ord($text[strlen($text)-1]);
	  if ($pad > strlen($text)) {
	      return false;	
	  }
	  if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
	      return false;
	  }
	  return substr($text, 0, -1 * $pad);
    }
}
?>