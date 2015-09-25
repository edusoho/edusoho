<?php
/**
 * Created by PhpStorm.
 * User: retamia
 * Date: 15/9/17
 * Time: 14:01
 */

namespace Custom\Service\User\Dao;


interface UserDao
{
    public function getUserByStaffNo($staffNo);
}