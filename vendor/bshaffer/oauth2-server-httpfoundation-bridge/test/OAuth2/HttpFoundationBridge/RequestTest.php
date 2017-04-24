<?php

namespace OAuth2\HttpFoundationBridge;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testFixAuthHeader()
    {
    	require_once __DIR__ .'/../../includes/apache_request_headers.php';

    	\set_apache_request_headers(array('Authorization' => 'Bearer xyz'));

        $request = Request::createFromGlobals();

        $this->assertEquals('Bearer xyz', $request->headers('Authorization'));
    }
}
