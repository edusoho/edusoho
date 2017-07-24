<?php
namespace Codeages\Weblib\Auth;

use PHPUnit\Framework\TestCase;

class SignatureTokenTest extends TestCase
{
    public function testParse_GoodToken()
    {
        $strategy = new SignatureTokenAlgo();

        $deadline = time() + 600;
        $once = 'test_once';
        $token = $strategy->parse("test_key_id:{$deadline}:{$once}:test_signature");

        $this->assertEquals('test_key_id', $token->keyId);
        $this->assertEquals($deadline, $token->deadline);
        $this->assertEquals($once, $token->once);
        $this->assertEquals('test_signature', $token->signature);
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testParse_badTokenFormat1()
    {
        $strategy = new SignatureTokenAlgo();
        $strategy->parse('test_key_id');
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testParse_badTokenFormat2()
    {
        $strategy = new SignatureTokenAlgo();
        $strategy->parse('test_key_id:');
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testParse_badTokenFormat3()
    {
        $strategy = new SignatureTokenAlgo();
        $strategy->parse('test_key_id:deadline:test_signature');
    }

    public function testCheck_Success()
    {
        $signingText = "/me?t1=1&t2=2\n{\"test\":\"value\"}";
        $deadline = time() + 600;
        $once = "test_once";

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $deadline, $once, $this->makeSignature($signingText, $key->secret, $deadline, $once));

        $strategy = new SignatureTokenAlgo();
        $checked = $strategy->check($token, $key, $signingText);

        $this->assertTrue($checked);
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testCheck_Failed_ErrorSecretKey()
    {
        $signingText = "/me?t1=1&t2=2\n{\"test\":\"value\"}";
        $deadline = time() + 600;
        $once = "test_once";

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $deadline, $once, $this->makeSignature($signingText, 'test_error_secret', $deadline, $once));

        $strategy = new SignatureTokenAlgo();
        $strategy->check($token, $key, $signingText);
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testCheck_Failed_ErrorSigningText()
    {
        $signingText = "/me?t1=1&t2=2\n{\"test\":\"value\"}";
        $deadline = time() + 600;
        $once = "test_once";

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $deadline, $once, $this->makeSignature($signingText, $key->secret, $deadline, $once));

        $strategy = new SignatureTokenAlgo();
        $strategy->check($token, $key, '/error');
    }

    protected function makeSignature($signingText, $secretKey, $deadline, $once)
    {
        $signingText = "{$once}\n{$deadline}\n{$signingText}";
        $signature = hash_hmac('sha1', $signingText, $secretKey, true);
        $signature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));

        return $signature;
    }
}