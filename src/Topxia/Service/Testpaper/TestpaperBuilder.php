<?php
namespace Topxia\Service\Testpaper;

interface TestpaperBuilder
{
    public function build($testpaper, $options);

    public function canBuild($options);
}