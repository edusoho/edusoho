<?php

namespace Tests\Validation;

use Codeages\Biz\Framework\Validation\RuleExtension;

class CustomValidatorRule implements RuleExtension
{
    public function rules()
    {
        return array(
            'chinese_alpha_num' => function ($field, $value, $params) {
                return (bool) preg_match('/^[\x{4e00}-\x{9fa5}a-zA-z0-9]+$/u', $value);
            },
        );
    }

    public function messages()
    {
        return array(
            'chinese_alpha_num' => '{key} must be chinese.',
        );
    }
}
