<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1UserResponse implements SpecialResponse
{
    public function filter($data)
    {
        return $data;
    }
}