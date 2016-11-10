<?php
namespace Biz\Testpaper\Builder;

interface TestpaperLibBuilder
{
    public function build($fields);

    public function submit($resultId, $answers);
}
