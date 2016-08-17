<?php
namespace Topxia\WebBundle\DataDict;

use Topxia\WebBundle\DataDict\DataDictInterface;
use Topxia\Service\Common\ServiceKernel;

class UserRoleDict implements DataDictInterface
{
    public function getDict()
    {
        return array(
            'ROLE_USER' => $this->getServiceKernel()->trans('学员'),
            'ROLE_TEACHER' => $this->getServiceKernel()->trans('教师'),
            'ROLE_ADMIN' => $this->getServiceKernel()->trans('管理员'),
            'ROLE_SUPER_ADMIN' => $this->getServiceKernel()->trans('超级管理员'),
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

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}