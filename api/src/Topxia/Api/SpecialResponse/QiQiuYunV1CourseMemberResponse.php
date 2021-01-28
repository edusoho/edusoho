<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1CourseMemberResponse implements SpecialResponse
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
                'course' => $member['course'],
                'role' => array($member['role']),
                'createdTime' => $member['createdTime'],
            );

        }
        $data['resources'] = $resources;
        return $data;
    }
}