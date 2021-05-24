<?php

namespace Biz\SCRM\Service;

interface SCRMUserService
{
    public function getUserByToken($token);
}
