<?php
namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomCourses extends BaseResource
{
    public function getLearnProgress(Application $app, Request $request, $classroomId, $userId)
    {
        return $this->getClassroomService()->calculateClassroomCoursesLearnProgress($classroomId, $userId);
    }

    public function filter($res)
    {
        // TODO: Implement filter() method.
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}