<?php

namespace ESCloud\SDK\Tests\Service;

use ESCloud\SDK\Service\SearchService;
use ESCloud\SDK\Tests\BaseTestCase;
use ESCloud\SDK\Auth;

class SearchServiceTest extends BaseTestCase
{
    protected function createAuth($accessKey = null, $secretKey = null)
    {
        $accessKey = $accessKey ? $accessKey : $this->accessKey;
        $secretKey = $secretKey ? $secretKey : $this->secretKey;

        return new Auth($accessKey, $secretKey, true);
    }

    public function testCreateAccount()
    {
        $mock = ['success' => true];
        $httpClient = $this->mockHttpClient($mock);
        $service = new SearchService($this->auth, array(), null, $httpClient);
        $result = $service->createAccount();

        $this->assertTrue($result['success']);
    }

    public function testReport()
    {
        $mock = ['success' => true];
        $httpClient = $this->mockHttpClient($mock);
        $service = new SearchService($this->auth, array(), null, $httpClient);
        $result = $service->report('course', ['resources' =>
            json_encode([
                ['id' => 1, 'title' => 'course1', 'lessons' => [['title' => 'lesson1'], ['title' => 'lesson2']]],
                ['id' => 2, 'title' => 'course2', 'lessons' => [['title' => 'lesson3'], ['title' => 'lesson4']]]
            ])]);

        $this->assertTrue($result['success']);
    }

    public function testDeleteData()
    {
        $mock = ['success' => true];
        $httpClient = $this->mockHttpClient($mock);
        $service = new SearchService($this->auth, array(), null, $httpClient);
        $result = $service->deleteData('course', 1);

        $this->assertTrue($result['success']);
    }

    public function testRestartReport()
    {
        $mock = ['success' => true];
        $httpClient = $this->mockHttpClient($mock);
        $service = new SearchService($this->auth, array(), null, $httpClient);
        $result = $service->restartReport(['categories' => json_encode(['course', 'classroom'])]);

        $this->assertTrue($result['success']);
    }

    public function testRestartReportFinish()
    {
        $mock = ['success' => true];
        $httpClient = $this->mockHttpClient($mock);
        $service = new SearchService($this->auth, array(), null, $httpClient);
        $result = $service->restartReportFinish(['categories' => json_encode(['course', 'classroom'])]);

        $this->assertTrue($result['success']);
    }
}
