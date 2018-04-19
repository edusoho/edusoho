<?php

namespace Tests\Unit\User\Register;

use Biz\BaseTestCase;
use Biz\Distributor\Common\DistributorCookieToolkit;
use AppBundle\Common\TimeMachine;
use Tests\Unit\User\Register\Tool\MockedHeader;
use Tests\Unit\User\Register\Tool\MockedResponse;
use Tests\Unit\User\Register\Tool\MockedRequest;
use AppBundle\Common\ReflectionUtils;

class DistributorCookieToolkitTest extends BaseTestCase
{
    public function testSetCookieTokenToFields()
    {
        $request = $this->mockCookieRequest();

        $result = DistributorCookieToolkit::setCookieTokenToFields($request, array(), 'user');
        $this->assertEquals('token-test', $result['distributorToken']);
        $request->cookies->shouldHaveReceived('get')->times(1);
    }

    public function testClearSetCookieToken()
    {
        $request = $this->mockCookieRequest();
        $response = $this->mockCookieResponse();
        $firstSetResponse = $this->setTokenToCookie($this->mockCookieResponse(), 'user');

        $cookieName = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'name');
        $cookieExpire = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'expire');
        $cookieValue = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'value');
        $this->assertEquals('distributor-token', $cookieName);
        $this->assertEquals(1521791574, $cookieExpire);
        $this->assertEquals('123123', $cookieValue);

        $secondSetResponse = DistributorCookieToolkit::clearCookieToken($request, $firstSetResponse, 'user');
        $cookieName = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'name');
        $cookieExpire = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'expire');
        $cookieValue = ReflectionUtils::getProperty($firstSetResponse->headers->getCookie(), 'value');
        $this->assertEquals('distributor-token', $cookieName);
        $this->assertEquals(0, $cookieExpire);
        $this->assertEquals('', $cookieValue);
    }

    private function mockCookieRequest()
    {
        $request = new MockedRequest();
        $request->cookies = $this->mockBiz(
            'requestCookies',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('distributor-token'),
                    'returnValue' => 'token-test',
                ),
            )
        );

        return $request;
    }

    private function mockCookieResponse()
    {
        $mockedHeader = new MockedHeader();
        $mockedResponse = new MockedResponse();
        $mockedResponse->headers = $mockedHeader;

        return $mockedResponse;
    }

    private function setTokenToCookie($mockedResponse)
    {
        TimeMachine::setMockedTime(1521186774);

        return DistributorCookieToolkit::setTokenToCookie($mockedResponse, '123123', 'user');
    }
}
