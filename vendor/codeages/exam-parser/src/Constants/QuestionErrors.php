<?php

namespace ExamParser\Constants;

class QuestionErrors
{
    const NO_STEM = 100001;

    const NO_OPTION = 100002;

    const NO_ANSWER = 100003;

    const NO_SUB_QUESTIONS = 100004;

    const UNKNOWN = 999999;

    public static function getErrorMsg($code)
    {
        $errors = array(
            self::NO_STEM => '缺少题干',
            self::NO_OPTION => '缺少选项',
            self::NO_ANSWER => '缺少正确答案',
            self::NO_SUB_QUESTIONS => '缺少子题',
            self::UNKNOWN => '未知错误',
        );

        return empty($errors[$code]) ? $errors[self::UNKNOWN] : $errors[$code];
    }
}
