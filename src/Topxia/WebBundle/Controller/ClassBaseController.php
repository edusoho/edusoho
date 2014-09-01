<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

abstract class ClassBaseController extends BaseController
{
    protected function tryViewClass($classId)
    {
        list($class, $member) = $this->getClassesService()->canViewClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException('班级不存在');
        }

        if (empty($member)) {
            throw $this->createAccessDeniedException('您无权限查看班级');
        }

        return array($class, $member);
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}