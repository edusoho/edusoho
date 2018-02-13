<?php

namespace Tests\Unit\AppBundle\Util;

use Biz\BaseTestCase;
use AppBundle\Util\UploadToken;

class UploadTokenTest extends BaseTestCase
{
    public function testMake()
    {
        $user = $this->getCurrentUser();
        $uploadToken = new UploadToken();
        $token = $uploadToken->make('course');
        $result = base64_decode($token);
        $contents = explode('|', $result);
        $this->assertEquals($user['id'], $contents[0]);
        $this->assertEquals('course', $contents[1]);
        $this->assertEquals('image', $contents[2]);
        $this->assertEquals(time() + 18000, $contents[3]);
        $secret = $this->getServiceKernel()->getParameter('secret');
        $this->assertEquals(md5("{$user['id']}|course|image|".(time() + 18000)."|$secret"), $contents[4]);
    }

    public function testParse()
    {
        $uploadToken = new UploadToken();
        //传入空
        $result = $uploadToken->parse(null);
        $this->assertNull($result);

        //传入过时的token
        $result = $uploadToken->parse('MXxjb3Vyc2V8aW1hZ2V8MTgwMDB8Mzg2YTk3NWM3M2JmNjNhNDhhYTkyYmRlZmM0MDRlZWI');
        $this->assertNull($result);

        //传入不符合sign的token
        $token = $uploadToken->make('course');
        $result = base64_decode($token);
        $contents = explode('|', $result);
        $result = $uploadToken->parse(base64_encode($contents[0].'|'.$contents[1].'|'.$contents[2].'|'.$contents[3].'|'.$contents[4].'extractstr'));
        $this->assertNull($result);

        //正常传入的token
        $user = $this->getCurrentUser();
        $token = $uploadToken->make('course');
        $result = $uploadToken->parse($token);
        $this->assertEquals($user['id'], $result['userId']);
        $this->assertEquals('course', $result['group']);
        $this->assertEquals('image', $result['type']);
    }
}
