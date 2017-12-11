<?php

namespace Tests\Unit\Mail\Tool;

class MockedApi
{
    public function post($uri, array $params = array())
    {
        $this->uri = $uri;
        $this->params = $params;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getParams()
    {
        return $this->params;
    }
}
