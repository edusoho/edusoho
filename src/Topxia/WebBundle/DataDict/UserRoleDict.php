<?php
namespace Topxia\WebBundle\DataDict;

use Topxia\WebBundle\DataDict\DataDictInterface;

class UserRoleDict implements DataDictInterface
{
    public function getDict()
    {
        return array(
            'ROLE_USER' => '学员',
            'ROLE_TEACHER' => '教师',
            'ROLE_ADMIN' => '管理员',
            'ROLE_SUPER_ADMIN' => '超级管理员',
        );
    }

    public function getGroupedDict()
    {
        return $this->getDict();
    }

    public function getRenderedDict()
    {
        return $this->getDict();
    }

}