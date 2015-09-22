<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 13:58
 */

namespace Custom\Service\User;


interface UserService
{
    public function getUserByStaffNo($staffNo);
}