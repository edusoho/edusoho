<?php
namespace Custom\Service\User\Dao;


interface UserDao
{
    public function getUserByStaffNo($staffNo);
    public function resetUserOrganizationId($organizationId);
}