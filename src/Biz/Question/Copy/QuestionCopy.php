<?php

namespace Biz\Question\Copy;

use Biz\AbstractCopy;

class QuestionCopy extends AbstractCopy
{
    public function doCopy($source, $options)
    {
var_dump(123123);
var_dump(5555);
    }

    protected function getFields()
    {

    }

    public function preCopy($source, $options)
    {

    }
}
