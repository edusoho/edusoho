<?php

namespace Topxia\Api\SpecialResponse;


class QiQiuYunV1ThreadResponse implements SpecialResponse
{
    public function filter($data)
    {
        return $data;
    }
}