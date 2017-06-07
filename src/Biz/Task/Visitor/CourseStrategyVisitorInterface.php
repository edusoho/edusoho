<?php

namespace Biz\Task\Visitor;

use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;

interface CourseStrategyVisitorInterface
{
    public function visitDefaultStrategy(DefaultStrategy $defaultStrategy);

    public function visitNormalStrategy(NormalStrategy $normalStrategy);
}
