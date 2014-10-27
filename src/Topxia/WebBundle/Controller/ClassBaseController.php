<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

abstract class ClassBaseController extends BaseController
{
    protected function tryViewClass($classId)
    {
        $class = $this->getClassesService()->getClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException('班级不存在');
        }

        $checked = $this->getClassesService()->checkPermission('view', $classId);

        if (empty($checked)) {
            throw $this->createAccessDeniedException('您无权查看班级');
        }

        return $class;
    }

    protected function tryManageClass($classId)
    {
        $class = $this->getClassesService()->getClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException('班级不存在');
        }

        $checked = $this->getClassesService()->checkPermission('manage', $classId);

        if (empty($checked)) {
            throw $this->createAccessDeniedException('您无权操作');
        }

        return $class;
    }

    protected function tryManageSchedule($classId)
    {
        $class = $this->getClassesService()->getClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException('班级不存在');
        }

        $checked = $this->getClassesService()->checkPermission('manageSchedule', $classId);

        if (empty($checked)) {
            throw $this->createAccessDeniedException('您无权操作');
        }

        return $class;
    }

    protected function tryViewSchedule($classId)
    {
        $class = $this->getClassesService()->getClass($classId);
        if (empty($class)) {
            throw $this->createNotFoundException('班级不存在');
        }

        $checked = $this->getClassesService()->checkPermission('viewSchedule', $classId);

        if (empty($checked)) {
            throw $this->createAccessDeniedException('您无权操作');
        }

        return $class;
    }

    protected function getClassesService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }
}