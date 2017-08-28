<?php

namespace Tests\Targetlog;

use Codeages\Biz\Framework\Targetlog\Targetlogger;
use Tests\IntegrationTestCase;

class TargetloggerTestCase extends IntegrationTestCase
{
    public function testDebug()
    {
        $logger = $this->createLogger('example', 1);
        $logger->debug('hello world.', array(
            '@action' => 'test',
            '@user_id' => 1,
            '@ip' => '127.0.0.1',
            'test_key' => 'test_value',
        ));
    }

    protected function createLogger($targetType, $targetId)
    {
        return new Targetlogger($this->biz, $targetType, $targetId);
    }

    protected function getTargetlogService()
    {
        return $this->biz->service['Targetlog:TargetlogService'];
    }
}
