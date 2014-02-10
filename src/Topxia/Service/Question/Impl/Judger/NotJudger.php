<?php
namespace Topxia\Service\Question\Impl\Judger;

class NotJudger implements Judger
{
    public function judge(array $question, $answer)
    {
        return array('status' => 'unableJudge');
    }
}