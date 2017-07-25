<?php

namespace Biz\Testpaper\Copy;

use Biz\AbstractCopy;

class TestpapersCopy extends AbstractCopy
{
    public function doCopy($source, $options)
    {
        var_dump('zhongj');
        exit;
    }

    protected function getFields()
    {

    }

    public function preCopy($source, $options)
    {

    }
}
