<?php
namespace Codeages\Weblib\Auth;

use PHPUnit\Framework\TestCase;

class SignatureTokenTest extends TestCase
{
    public function testParse_GoodToken()
    {
        $strategy = new SignatureTokenAlgo();
        $token = $strategy->parse('test_key_id:test_signature');

        $this->assertEquals('test_key_id', $token->keyId);
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
        $strategy->parse('test_key_id:test_signature:other');
    }

    public function testCheck_Success()
    {
        $signingText = "/me?t1=1&t2=2\n{\"test\":\"value\"}";

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $this->makeSignature($signingText, $key->secret));

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

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $this->makeSignature($signingText, 'test_error_secret'));

        $strategy = new SignatureTokenAlgo();
        $strategy->check($token, $key, $signingText);
    }

    /**
     * @expectedException \Codeages\Weblib\Auth\AuthException
     */
    public function testCheck_Failed_ErrorSigningText()
    {
        $signingText = "/me?t1=1&t2=2\n{\"test\":\"value\"}";

        $key = new AccessKey('test_key_id', 'test_key_secret');

        $token = new Token('test_key_id', '', $this->makeSignature($signingText, 'test_error_secret'));

        $strategy = new SignatureTokenAlgo();
        $strategy->check($token, $key, '/error');
    }

    protected function makeSignature($signingText, $secretKey)
    {
        $signature = hash_hmac('sha1', $signingText, $secretKey, true);
        $signature = str_replace(array('+', '/'), array('-', '_'), base64_encode($signature));

        return $signature;
    }
}