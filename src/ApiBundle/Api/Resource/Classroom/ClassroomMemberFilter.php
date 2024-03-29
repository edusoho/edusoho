<?php

namespace ApiBundle\Api\Resource\Classroom;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;

class ClassroomMemberFilter extends Filter
{
    /**
     * @var array
     *            isOldUser 移动端需要，老接口使用的User对象avatar是个字符串，所有临时换个字符串
     * @TODO 下个大版本恢复simpleUser
     */
    protected $publicFields = [
        'id', 'classroomId', 'userId', 'noteNum', 'threadNum', 'locked', 'role', 'deadline', 'access', 'user', 'isOldUser', 'expire', 'joinedChannel',
    ];

    protected function publicFields(&$data)
    {
        if ($data['deadline']) {
            $data['deadline'] = date('c', $data['deadline']);
        }

        // 去掉长期有效
        if (isset($data['expire']['deadline']) && 0 == $data['expire']['deadline']) {
            unset($data['expire']['deadline']);
        }

        if (!empty($data['expire']['deadline'])) {
            $data['expire']['deadline'] = date('c', $data['expire']['deadline']);
        }

        if (!empty($data['user'])) {
            $userFilter = new UserFilter();
            $userFilter->filter($data['user']);

            if (!empty($data['isOldUser']) && is_array($data['user']['avatar'])) {
                $data['user']['avatar'] = $data['user']['avatar']['small'];
                unset($data['isOldUser']);
            }
        }
    }
}
