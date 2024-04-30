<?php

namespace Biz\AI\Service\Impl;

use Biz\AI\DifyClient;
use Biz\AI\Service\AnswerService;
use Biz\BaseService;

class AnswerServiceImpl extends BaseService implements AnswerService
{
    public function generateAnswer($prompt)
    {
        $client = new DifyClient();
        $client->request($prompt);
    }
}
