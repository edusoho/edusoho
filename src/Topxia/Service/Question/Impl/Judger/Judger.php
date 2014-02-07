<?php

namespace Topxia\Service\Question\Impl\Judger;

interface Judger 
{
    public function judge(array $question, $answer);
}