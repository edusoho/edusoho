<?php
namespace Topxia\WebBundle\Twig\Extension;

use Topxia\Service\Common\ServiceKernel;
use Topxia\WebBundle\Util\CategoryBuilder;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Common\ConvertIpToolkit;

class K12Extension extends \Twig_Extension
{
    public function getFilters ()
    {
        return array(
        );
    }

    public function getFunctions()
    {
        return array(
            'check_class_permission' => new \Twig_Function_Method($this, 'checkClassPermission') ,
            'user_in_class_role' => new \Twig_Function_Method($this, 'getClassRole'),
            'can_remove_schedule' => new \Twig_Function_Method($this, 'canRemoveSchedule'),
        );
    }

    public function checkClassPermission($name, $classId)
    {
        $classId = (is_array($classId) && isset($classId['id'])) ? $classId['id'] : $classId;
        return $this->getClassesService()->checkPermission($name, $classId);
    }

    public function getClassRole($userId, $classId)
    {
        $member = $this ->getClassesService()->getMemberByUserIdAndClassId($userId, $classId);
        return empty($member) ? 'none': $member['role'];
    }

    public function canRemoveSchedule($user, $courses, $lessons, $schedule)
    {
        if($user->isAdmin()) {
            return true;
        } else if ($user->isTeacher()) {
            $course = $courses[$lessons[$schedule['lessonId']]['courseId']];
            if(in_array($user['id'], $course['teacherIds'])) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function getClassesService()
    {
        return ServiceKernel::instance()->createService('Classes.ClassesService');
    }

    public function getName ()
    {
        return 'topxia_k12_twig';
    }

}

