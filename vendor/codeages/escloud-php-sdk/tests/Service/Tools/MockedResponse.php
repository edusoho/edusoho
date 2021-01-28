<?php

namespace ESCloud\SDK\Tests\Service\Tools;

class MockedResponse
{
    public function getBody()
    {
        return '{"success":"true"}';
    }
}
