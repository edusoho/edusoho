<?php

namespace Tests\Unit\User\Register\Tool;

class MockedHeader
{
    private $cookie;

    public function setCookie($cookie)
    {
        $this->cookie = $cookie;
    }

    public function getCookie()
    {
        return $this->cookie;
    }
}
