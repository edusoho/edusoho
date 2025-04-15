<?php

namespace Biz\Question\Traits;

trait ItemTypeChineseNameTrait
{
    protected $chineseNames = [
        'single_choice' => '单选题',
        'choice' => '多选题',
        'uncertain_choice' => '不定项选择题',
        'determine' => '判断题',
        'fill' => '填空题',
        'essay' => '问答题',
        'material' => '材料题',
    ];
}
