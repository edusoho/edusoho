<?php

namespace Omnipay\Alipay\Tests\Common;

use Omnipay\Alipay\Common\Signer;

class SignerTest extends \PHPUnit_Framework_TestCase
{

    protected $params;

    protected $key;

    protected $privateKey;


    public function testSignWithMD5()
    {
        $signer = new Signer($this->params);
        $sign   = $signer->signWithMD5($this->key);
        $this->assertEquals('7e63e20bcc6ad2ba695305e340592ffd', $sign);
    }


    public function testSignWithRSA()
    {
        $this->assertFileExists($this->privateKey);

        $signer = new Signer($this->params);
        $sign   = $signer->signWithRSA($this->privateKey);
        $this->assertEquals(
            'Uwz+4a4mKJBNDsGpC+nIZnHYsA30NpoxycIjvlAKC9sLS2t3a/5H7p7EEwSOAQQV2sAu54oIH7wZ4hXXkzYoqRO1T+51hYF+r+uEb9rGYyJzbg3xzV8WFUjypGgxNd8HCAKV9qhkEGdfZ94/VCxYkS+1qxkgqD0MzzHVR20C0NI=',
            $sign
        );
    }


    public function testSort()
    {
        $params1 = [
            'aaa' => '111',
            'bbb' => '2222',
            'ccc' => '3333',
        ];

        $params2 = [
            'bbb' => '2222',
            'ccc' => '3333',
            'aaa' => '111',
        ];

        $signer = new Signer($params1);
        $sign1  = $signer->signWithMD5($this->key);

        $signer = new Signer($params2);
        $sign2  = $signer->signWithMD5($this->key);

        $this->assertEquals($sign1, $sign2);
    }


    public function testIgnore()
    {
        $this->assertSame(['sign', 'sign_type'], (new Signer())->getIgnores());

        $params1 = [
            'aaa'   => '111',
            'bbb'   => '2222',
            'ccc'   => '3333',
            'apple' => 'jobs',
        ];

        $params2 = [
            'bbb' => '2222',
            'ccc' => '3333',
            'aaa' => '111',
        ];

        $signer = new Signer($params1);
        $signer->setIgnores(['apple']);
        $sign1 = $signer->signWithMD5($this->key);

        $signer = new Signer($params2);
        $signer->setIgnores(['apple']);
        $sign2 = $signer->signWithMD5($this->key);
        $this->assertEquals($sign1, $sign2);

        $signer = new Signer($params1);
        $signer->setIgnores([]);
        $sign3 = $signer->signWithMD5($this->key);

        $this->assertNotEquals($sign1, $sign3);
    }


    public function testGetParamsToSign()
    {
        $params1 = [
            'bbb'   => '2222',
            'ccc'   => '3333',
            'aaa'   => '111',
            'apple' => 'jobs',
        ];

        $signer = new Signer($params1);
        $signer->setIgnores(['apple']);
        $params = $signer->getParamsToSign();

        $this->assertSame(
            [
                'aaa' => '111',
                'bbb' => '2222',
                'ccc' => '3333',
            ],
            $params
        );
    }


    public function testGetContentToSign()
    {
        $params1 = [
            'bbb'   => '2222',
            'ccc'   => '3333',
            'aaa'   => '111',
            's'     => '"."',
            'e'     => '',
            'apple' => 'jobs',
        ];

        $signer = new Signer($params1);
        $signer->setIgnores(['apple']);
        $content = $signer->getContentToSign();

        $this->assertEquals('aaa=111&bbb=2222&ccc=3333&s="."', $content);
    }

    public function testConvert(){
        $key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRAFljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQEB/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5KsiNG9zpgmLCUYuLkxpLQIDAQAB';

        $signer = new Signer();
        $key = $signer->convertKey($key, Signer::KEY_TYPE_PUBLIC);

        $this->assertEquals('-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCnxj/9qwVfgoUh/y2W89L6BkRA
FljhNhgPdyPuBV64bfQNN1PjbCzkIM6qRdKBoLPXmKKMiFYnkd6rAoprih3/PrQE
B/VsW8OoM8fxn67UDYuyBTqA23MML9q1+ilIZwBC2AQ2UBVOrFXfFl75p6/B5Ksi
NG9zpgmLCUYuLkxpLQIDAQAB
-----END PUBLIC KEY-----', $key);
    }


    protected function setUp()
    {
        parent::setUp();

        $this->params = [
            'aaa' => '111',
            'bbb' => '2222',
            'ccc' => '3333',
            'dd'  => '',
            'eee' => null,
            'fff' => false,
            'ggg' => true,
        ];

        $this->key = 'hello';

        $this->privateKey = ALIPAY_ASSET_DIR . '/dist/aop/rsa_private_key.pem';
    }
}
