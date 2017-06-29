<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1ClassroomMemberResponse implements SpecialResponse
{
    public function filter($data)
    {
        if (isset($data['error'])) {
            return $data;
        }

        $resources = array();
        foreach ($data['resources'] as $member) {
            $resources[] = array(
                'user' => $member['user'],
                'classroom' => $member['classroom'],
                'role' => $member['role'],
                'createdTime' => $member['createdTime'],
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}