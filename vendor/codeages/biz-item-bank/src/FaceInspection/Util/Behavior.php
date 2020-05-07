<?php

namespace Codeages\Biz\ItemBank\FaceInspection\Util;

class Behavior
{
    const NO_FACE = 'no_face';

    const MANY_FACE = 'many_face';

    const NOT_SELF = 'not_self';

    const PAGE_HIDE = 'page_hide';

    const UNKNOWN = 'unknown';

    public static function getErrorMsg($name)
    {
        $errors = [
            self::NO_FACE => '未检测到正脸',
            self::MANY_FACE => '检测到多张人脸',
            self::NOT_SELF => '非本人',
            self::PAGE_HIDE => '页面失去焦点',
            self::UNKNOWN => '未知错误',
        ];

        return empty($errors[$name]) ? $errors[self::UNKNOWN] : $errors[$name];
    }
}
