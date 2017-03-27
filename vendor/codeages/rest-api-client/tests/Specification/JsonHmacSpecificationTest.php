<?php

use Codeages\RestApiClient\Specification\JsonHmacSpecification;

class JsonHmacSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function testPackToken()
    {
        $config = array('accessKey' => 'test access key.', 'secretKey' => 'test secret key.');
        $uri = '/api/v1/test';
        $body = '';
        $deadline = time() + 600;
        $once = md5(time());

        $spec = new JsonHmacSpecification();
        $signed = $spec->packToken($config, $uri, $body, $deadline, $once);

        $token = $spec->unpackToken($signed);
        $this->assertEquals($config['accessKey'], $token['accessKey']);
        $this->assertEquals($deadline, $token['deadline']);
        $this->assertEquals($once, $token['once']);

        $expectedSign = $spec->signature($config, $uri, $body, $token['deadline'], $token['once']);

        $this->assertEquals($expectedSign, $token['signature']);
    }

    public function testUnpackToken()
    {

    }

    public function testSerialize()
    {
        $spec = new JsonHmacSpecification();

        $data = array('hello' => 'world');
        $serialized = $spec->serialize($data);

        $this->assertEquals(json_encode($data), $serialized);
    }

    public function testUnserialize()
    {
        $spec = new JsonHmacSpecification();

        $json = $spec->serialize(array('hello' => 'world'));

        $data = $spec->unserialize($json);

        $this->assertEquals($data['hello'], 'world');
    }

}
