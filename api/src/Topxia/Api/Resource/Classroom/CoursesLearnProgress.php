<?php
namespace Topxia\Api\Resource\Classroom;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Resource\BaseResource;

class CoursesLearnProgress extends BaseResource
{
    public function get(Application $app, Request $request, $classroomId)
    {
        $currentUser = $this->getCurrentUser();
        $classroomMember = $this->getClassroomService()->getClassroomMember($classroomId, $currentUser['id']);
        if (empty($classroomMember)) {
            return $this->error(500, "ID为{$currentUser['id']}的学员未加入ID为{$classroomId}的班级");
        }
        return $this->getClassroomService()->calculateClassroomCoursesLearnProgress($classroomId, $currentUser['id']);
    }

    public function filter($res)
    {
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}