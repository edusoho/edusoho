<?php

namespace Tests\Unit\Distributor\Util;

use Biz\BaseTestCase;
use Biz\Distributor\Util\DistributorCookieToolkit;
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

        $result = DistributorCookieToolkit::setCookieTokenToFields($request, array(), DistributorCookieToolkit::USER);
        $this->assertEquals('token-test', $result['distributorToken']);
        $request->cookies->shouldHaveReceived('get')->times(1);
    }

    public function testClearSetCookieToken()
    {
        $request = $this->mockCookieRequest();
        $response = $this->mockCookieResponse();
        $response = $this->setTokenToCookie($this->mockCookieResponse(), DistributorCookieToolkit::USER);

        $cookieName = ReflectionUtils::getProperty($response->headers->getCookie(), 'name');
        $cookieExpire = ReflectionUtils::getProperty($response->headers->getCookie(), 'expire');
        $cookieValue = ReflectionUtils::getProperty($response->headers->getCookie(), 'value');
        $this->assertEquals('distributor-user-token', $cookieName);
        $this->assertEquals(1521791574, $cookieExpire);
        $this->assertEquals('123123', $cookieValue);

        // cookie 内不存在 商品分销，无法触发清除操作
        $response = DistributorCookieToolkit::clearCookieToken(
            $request,
            $response,
            array('checkedType' => DistributorCookieToolkit::PRODUCT_ORDER)
        );
        $cookieExpire = ReflectionUtils::getProperty($response->headers->getCookie(), 'expire');
        $this->assertEquals(0, $cookieExpire);

        // cookie 内不存在 用户拉新，自动触发清除操作
        $response = DistributorCookieToolkit::clearCookieToken(
            $request,
            $response,
            array('checkedType' => DistributorCookieToolkit::USER)
        );
        $cookieName = ReflectionUtils::getProperty($response->headers->getCookie(), 'name');
        $cookieExpire = ReflectionUtils::getProperty($response->headers->getCookie(), 'expire');
        $cookieValue = ReflectionUtils::getProperty($response->headers->getCookie(), 'value');
        $this->assertEquals('distributor-user-token', $cookieName);
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
                    'withParams' => array('distributor-user-token'),
                    'returnValue' => 'token-test',
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('distributor-productOrder-token'),
                    'returnValue' => null,
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

        return DistributorCookieToolkit::setTokenToCookie($mockedResponse, '123123', DistributorCookieToolkit::USER);
    }
}
