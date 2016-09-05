<?php

namespace Custom\Service\User;

interface UserService
{
    public function findUsersByOrgCode($orgCode);
}