<?php

namespace Biz\TestTool;

use Symfony\Component\HttpFoundation\Request;
use Mockery;

class MockedRequest extends Request
{
    public $request;
    public $query;

    /**
     * @param $config 值为
     *   array(
     *      'request' => array(
     *          'username' => 'aok',
     *          'password' => 'dds',   //$request->request->get('password') 将返回 dss
     *      )
     *   )
     */
    public static function mockRequest($configs)
    {
        $mockedSubRequest = null;
        $mockedSubQuery = null;

        $mockedRequest = Mockery::mock('Symfony\Component\HttpFoundation\Request');

        foreach ($configs as $configKey => $config) {
            if ('request' == $configKey) {
                $mockedSubRequest = Mockery::mock('SubRequest');
                foreach ($config as $key => $value) {
                    $mockedSubRequest->shouldReceive('get')->withArgs(array($key))->andReturn($value);
                }
            } elseif ('query' == $configKey) {
                $mockedSubQuery = Mockery::mock('SubQuery');
                foreach ($config as $key => $value) {
                    $mockedSubQuery->shouldReceive('get')->withArgs(array($key))->andReturn($value);
                }
            } else {
                $mockedRequest->shouldReceive($configKey)->andReturn($config);
            }
        }

        $mockedRequest->request = $mockedSubRequest;
        $mockedRequest->query = $mockedSubQuery;

        return $mockedRequest;
    }
}
