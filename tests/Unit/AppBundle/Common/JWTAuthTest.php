<?php

namespace AppBundle\Common;

use Biz\BaseTestCase;

class JWTAuthTest extends BaseTestCase
{
    public function testAuth()
    {
        $key = md5('JWTAuthTest');

        $JWTAuth = new JWTAuth($key);
        $result = $JWTAuth->auth(array(), array('exp' => time()));
        $result = explode('.', $result);
        $this->assertEquals('eyJhbGciOiJzaGExIiwidHlwIjoiSldUIn0', $result[0]);
    }

    public function testValid()
    {
        $key = md5('JWTAuthTest');
        $JWTAuth = new JWTAuth($key);
        $payload = $JWTAuth->auth(array(), array('exp' => time()));
        $result1 = $JWTAuth->valid($payload);

        $result2 = $JWTAuth->auth(array(), array('iat' => time() + 3600, 'exp' => time() + 7200));
        $result2 = $JWTAuth->valid($result2);

        $result3 = $JWTAuth->auth(array(), array('iat' => time() - 7200, 'exp' => time() - 3600));
        $result3 = $JWTAuth->valid($result3);

        $this->assertEquals(3600, $result1['exp'] - $result1['iat']);
        $this->assertTrue(!$result2);
        $this->assertTrue(!$result3);
    }
}
