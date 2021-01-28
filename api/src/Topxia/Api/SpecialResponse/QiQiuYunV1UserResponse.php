<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1UserResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }
        
        foreach ($data['resources'] as &$user) {
            if (in_array('ROLE_TEACHER', $user['roles'])) {
                $user['roles'] = array('teacher');
            } else {
                $user['roles'] = array('student');
            }

            $user['avatar'] = $user['largeAvatar'];
            unset($user['smallAvatar']);
            unset($user['mediumAvatar']);
            unset($user['largeAvatar']);
        }

        return $data;
    }
}