<?php
class XXTEATest extends PHPUnit_Framework_TestCase {
    public function testEncrypt() {
        $str = "Hello World! 你好，中国🇨🇳！";
    	$key = "1234567890";
    	$encrypt_data = xxtea_encrypt($str, $key);
        $this->assertEquals(base64_encode($encrypt_data), "D4t0rVXUDl3bnWdERhqJmFIanfn/6zAxAY9jD6n9MSMQNoD8TOS4rHHcGuE=");
    }
    public function testDecrypt() {
        $str = "Hello World! 你好，中国🇨🇳！";
    	$key = "1234567890";
    	$encrypt_data = xxtea_encrypt($str, $key);
    	$decrypt_data = xxtea_decrypt($encrypt_data, $key);
        $this->assertEquals($decrypt_data, $str);
    }
}
