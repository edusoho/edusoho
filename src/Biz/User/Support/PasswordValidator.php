<?php

namespace Biz\User\Support;

/**
 * 密码校验器
 *
 * 注意：密码逻辑的修改，需要同步修改前端的密码校验逻辑 app/Resources/static-src/common/password.js
 */
class PasswordValidator
{
    const LEVEL_STRONG = 1;
    const LEVEL_NORMAL = 2;

    const LEVEL_NONE = 0;

    /**
     * 获得密码等级
     * @param string $password
     * @return int 1: 强密码（适用于管理员） 2: 普通密码（适用于非管理员） 0: 密码级别未知或不达标
     */
    public static function getLevel(string $password): int
    {
        if (self::validateStrong($password)) {
            return self::LEVEL_STRONG;
        } elseif (self::validate($password)) {
            return self::LEVEL_NORMAL;
        } else {
            return self::LEVEL_NONE;
        }
    }

    /**
     * 是否为有效的密码级别
     *
     * @param int $level
     * @return bool
     */
    public static function isValidLevel(int $level): bool {
        return in_array($level, [self::LEVEL_STRONG, self::LEVEL_NORMAL]);
    }

    /**
     * 是否为强密码级别
     *
     * @param int $level
     * @return bool
     */
    public static function isStrongLevel(int $level): bool {
        return $level === self::LEVEL_STRONG;
    }

    /**
     * 是否为普通密码级别
     *
     * @param int $level
     * @return bool
     */
    public static function isNormalLevel(int $level): bool {
        return $level === self::LEVEL_NORMAL;
    }

    /**
     * 校验密码
     * 规则：8-32位字符，包含字母、数字、符号任意两种及以上组合成的密码
     * @param $password string 待校验密码
     * @return bool 是否校验成功
     */
    public static function validate(string $password): bool {
        $len = strlen($password);
        if ($len < 8 || $len > 32) {
            return false;
        }

        $hasLetter = preg_match('/[a-zA-Z]/', $password);
        $hasNumber = preg_match('/[0-9]/', $password);
        $hasSymbol = preg_match('/[^a-zA-Z0-9]/', $password);

        $typeCount = 0;
        if ($hasLetter) $typeCount++;
        if ($hasNumber) $typeCount++;
        if ($hasSymbol) $typeCount++;

        return $typeCount >= 2;
    }

    /**
     * 校验强密码
     * 规则：8-32位字符，包含字母大小写、数字、符号四种字符组合成的密码
     * @param string $password
     * @return bool 是否校验成功
     */
    public static function validateStrong(string $password): bool {
        $length = strlen($password);
        if ($length < 8 || $length > 32) {
            return false;
        }

        $hasUpper  = preg_match('/[A-Z]/', $password);
        $hasLower  = preg_match('/[a-z]/', $password);
        $hasDigit  = preg_match('/[0-9]/', $password);
        $hasSymbol = preg_match('/[^A-Za-z0-9]/', $password); // 非字母数字的都算符号

        return $hasUpper && $hasLower && $hasDigit && $hasSymbol;
    }
}