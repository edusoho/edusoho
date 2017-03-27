<?php

use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;
use Codeages\RestApiClient\HttpRequest\MockHttpRequest;
use Codeages\RestApiClient\Tests\TestLogger;

class RestApiClientTest extends \PHPUnit_Framework_TestCase
{

    public function testSendAuthCode()
    {
        $config = array(
            'accessKey' => 'test_acess_key',
            'secretKey' => 'test_secret_key',
            'endpoint' => 'http://passport.dev.com/api/v1',
        );
        $spec = new JsonHmacSpecification();
        $logger = new TestLogger();

        $client = new RestApiClient($config, $spec, null, $logger, true);

        // $result = $client->get('/mobiles/13757199220/validation');
        // $result = $client->post('/users', [
        //     'username' => 'x4',
        //     'email' => 'x4@xxx.com',
        //     'mobile' => 13757199221,
        //     'password' => 'kaifazhe',
        // ]);

        $result = $client->post('/clients', [
            'name' => '测试',
            'domain' => 'http://xxx.xxx.com',
            'notify_url' => 'http://xxx.xxx.com/notify_url',
            'notify_user' => 'http://xxx.xxx.com/notify_url',
        ]);

        // $result = $client->post('/mobiles/13757199220/code');



        var_dump($result);exit();


        // $this->assertEquals(1, $user['id']);
    }

}
