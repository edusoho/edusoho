<?php
use Codeages\RestApiClient\HttpRequest\MockHttpRequest;

class MockHttpRequestTest extends \PHPUnit_Framework_TestCase
{

    public function testSendAuthCode()
    {
        $http = new MockHttpRequest([]);

        $http->mock(function() {
            return 1;
        });
        $http->mock(2);
        $http->mock(3);

        $result = $http->request('GET', '/test', '');
        $this->assertEquals(1, $result);

        $result = $http->request('GET', '/test', '');
        $this->assertEquals(2, $result);

        $result = $http->request('GET', '/test', '');
        $this->assertEquals(3, $result);
    }

}
