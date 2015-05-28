<?php
namespace Topxia\Service\User\Dao;

interface UserCommonAdminDao
{
    public function getCommonAdmin($id);

    public function findCommonAdminByUserId($userId);

    public function getCommonAdminByUserIdAndUrl($userId, $url);

    public function addCommonAdmin($admin);

    public function deleteCommonAdmin($id);

}