<?php

namespace ExamParser\Parser;

class Keyword
{
    public function convertList()
    {
        return array(
            '<#答案#>' => array(
                '答案',
                '正确答案',
                '参考答案',
                '[答案]',
                '[正确答案]',
                '[参考答案]',
                '【答案】',
                '【正确答案】',
                '【参考答案】',
            ),
            '<#难度#>' => array(
                '难度',
                '[难度]',
                '【难度】',
            ),
            '<#分数#>' => array(
                '分数',
                '[分数]',
                '【分数】',
            ),
            '<#解析#>' => array(
                '解析',
                '[解析]',
                '【解析】',
            ),
        );
    }
}
