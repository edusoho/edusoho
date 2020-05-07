<?php

namespace Codeages\Biz\ItemBank\Util\Validator;

use Valitron\Validator as CoreValidator;

/**
 * 对 vlucas/valitron 数据校验类的封装.
 *
 * @see https://github.com/vlucas/valitron
 */
class Validator
{
    /**
     * @param array $data  需校验的数据
     * @param array $rules 校验规则
     * @return array 校验并过滤后得数据
     */
    public function validate(array $data, array $rules)
    {
        $v = new CoreValidator($data, array_keys($rules));

        $v->mapFieldsRules($rules);
        if (!$v->validate()) {
            throw new ValidatorException($v->errors());
        }

        return $v->data();
    }
}
