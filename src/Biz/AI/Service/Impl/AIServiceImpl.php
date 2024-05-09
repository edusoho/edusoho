<?php

namespace Biz\AI\Service\Impl;

use Biz\AI\DifyClient;
use Biz\AI\Service\AIService;
use Biz\BaseService;

class AIServiceImpl extends BaseService implements AIService
{
    public function generateAnswer($prompt)
    {
        $client = new DifyClient();
        $client->request($prompt);
    }
}
