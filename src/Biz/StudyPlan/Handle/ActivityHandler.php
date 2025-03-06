<?php

namespace Biz\StudyPlan\Handle;

// 定义策略接口
interface ActivityHandler
{
    public function handle(array $activities): int;
}
