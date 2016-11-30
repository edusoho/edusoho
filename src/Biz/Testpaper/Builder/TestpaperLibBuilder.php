<?php
namespace Biz\Testpaper\Builder;

interface TestpaperLibBuilder
{
    public function build($fields);

    public function canBuild($options);

    public function showTestItems($resultId);

    public function updateSubmitedResult($resultId, $usedTime);
}
