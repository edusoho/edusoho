<?php

namespace OAuth2\HttpFoundationBridge;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    /** @dataProvider provideAddParameters */
    public function testAddParameters($expected, $parameters, $content = null)
    {
        $response = new Response();

        if ($content) {
            $response->setContent($content);
        }

        $response->addParameters($parameters);
        $this->assertEquals($expected, $response->getContent());
    }

    public function provideAddParameters()
    {
        return array(
            array('[]', array()),
            array('{"test":"foo"}', array('test' => 'foo')),
            array('{"test2":"foo2","test":"foo"}', array('test' => 'foo'), '{"test2":"foo2"}'),
        );
    }

    /** @dataProvider provideAddHttpHeaders */
    public function testAddHttpHeaders($expected, $headers)
    {
        $response = new Response();
        $response->addHttpHeaders($headers);

        $this->assertContains($expected, (string) $response->headers);
    }

    public function provideAddHttpHeaders()
    {
        return array(
            array('Cache-Control: no-store', array('Cache-Control' => array('no-store'))),
            array('Header:        value', array('foo' => 'bar', 'header' => 'value')),
            array('Content-Type:  application/xml', array('content-type' => 'application/xml')),
        );
    }

    /** @dataProvider provideGetParameter */
    public function testGetParameter($expected, $content, $name)
    {
        $response = new Response();
        $response->setContent($content);

        $this->assertEquals($expected, $response->getParameter($name));
    }

    public function provideGetParameter()
    {
        return array(
            array(null, '', 'foo'),
            array('foo', '{"test":"foo"}', 'test'),
            array(array('bar', 'baz'), '{"foo":["bar","baz"]}', 'foo'),
        );
    }

    /** @dataProvider provideSetError */
    public function testSetError($expected, $statusCode, $error, $error_description = null, $error_uri = null)
    {
        $response = new Response();
        $response->setError($statusCode, $error, $error_description, $error_uri);

        $this->assertEquals($expected, $response->getContent());
        $this->assertEquals($statusCode, $response->getStatusCode());
    }

    public function provideSetError()
    {
        return array(
            array('{"error":"invalid_argument"}', 400, 'invalid_argument'),
            array('{"error":"invalid_argument","error_description":"missing required parameter"}', 400, 'invalid_argument', 'missing required parameter'),
            array('{"error":"invalid_argument","error_description":"missing required parameter","error_uri":"http:\/\/brentertainment.com"}', 400, 'invalid_argument', 'missing required parameter', 'http://brentertainment.com'),
        );
    }

    /** @dataProvider provideSetRedirect */
    public function testSetRedirect($expected, $url, $state = null, $error = null, $error_description = null, $error_uri = null)
    {
        $response = new Response();

        $response->setRedirect(301, $url, $state, $error, $error_description, $error_uri);
        $this->assertEquals($expected, $response->headers->get('Location'));
    }

    public function provideSetRedirect()
    {
        return array(
            array('http://test.com/path?error=foo', 'http://test.com/path', null, 'foo'),
            array('https://sub.test.com/path?query=string&error=foo', 'https://sub.test.com/path?query=string', null, 'foo'),
            array('http://test.com/path?error=foo&error_description=this+is+a+description', 'http://test.com/path', null, 'foo', 'this is a description'),
            array('http://test.com/path?state=xyz&error=foo&error_description=this+is+a+description', 'http://test.com/path', 'xyz', 'foo', 'this is a description'),
            array('http://test.com/path?state=xyz&error=foo&error_description=this+is+a+description&error_uri=http%3A%2F%2Fbrentertainment.com', 'http://test.com/path', 'xyz', 'foo', 'this is a description', 'http://brentertainment.com'),
        );
    }
}
