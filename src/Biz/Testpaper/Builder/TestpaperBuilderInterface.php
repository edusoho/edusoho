<?php

namespace Biz\Testpaper\Builder;

interface TestpaperBuilderInterface
{
    public function build($fields);

    public function canBuild($options);

    public function showTestItems($testId, $resultId = 0, $options = array());

    public function updateSubmitedResult($resultId, $usedTime, $options = array());
}
