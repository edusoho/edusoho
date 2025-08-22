<?php

namespace Biz\User\Support;

class RoleHelper
{
    /**
     * 是否为学员
     * @param array $roles
     * @return bool
     */
    public static function isStudent(array $roles): bool
    {
        if (empty($roles)) {
            throw new \InvalidArgumentException('roles is empty');
        }
        return count($roles) === 1 && in_array('ROLE_USER', $roles);
    }

    /**
     * 是否为非学员（管理员、教师...等赋予的各种角色）
     * @param array $roles
     * @return bool
     */
    public static function nonStudent(array $roles): bool
    {
        return !self::isStudent($roles);
    }
}