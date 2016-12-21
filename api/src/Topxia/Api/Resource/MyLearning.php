<?php
namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class MyLearning extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();
        $beginTime = strtotime("-6 months");
        $conditions = array(
            'lastViewTime_GE' => $beginTime,
            'userId'          => $user['id']
        );

        $membersCount = $this->getCourseService()->searchMemberCount($conditions);
        $members = $this->getCourseService()->searchMembers(
            $conditions,
            array('lastViewTime', 'DESC'),
            0,
            $membersCount
        );
        $members = $this->filterDuplicateClassroom($members);

        $groupMembers = ArrayToolkit::group($members, 'joinedType');
        $classrooms = $this->getSourcesByType($groupMembers, 'classroom');
        $courses = $this->getSourcesByType($groupMembers, 'course');

        $learningData = array();
        foreach ($members as $member) {
            $learningData[] = $this->buildLearningData($member, $courses, $classrooms);
        }

        return $learningData;
    }

    public function filter($res)
    {
    }

    protected function filterDuplicateClassroom(array $members)
    {
        if (empty($members)) {
            return array();
        }

        $classroomIds = array();

        foreach ($members as $key => $member) {
            if (!empty($members[$key]['classroomId'])) {
                if (empty($classroomIds) || !in_array($members[$key]['classroomId'], $classroomIds)) {
                    array_push($classroomIds, $members[$key]['classroomId']);
                } else {
                    unset($members[$key]);
                }
            }
        }
        return $members;
    }

    protected function getSourcesByType($members, $type)
    {
        if (empty($members) ||empty($members[$type])) {
            return array();
        }

        $data = array();

        if ('course' == $type) {
            $courseIds = ArrayToolkit::column($members['course'], 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);
            $data = ArrayToolkit::index($courses, 'id');
        }

        if ('classroom' == $type) {
            $classroomIds = ArrayToolkit::column($members['classroom'], 'classroomId');
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
            $data = ArrayToolkit::index($classrooms, 'id');
        }

        return $data;
    }

    protected function buildLearningData($member, $courses, $classrooms)
    {
        if (!empty($member['classroomId'])) {
            return $classrooms[$member['classroomId']];
        } else {
            return $courses[$member['courseId']];
        }
    }

    protected function findCoursesByIds(array $courseIds)
    {
        return $this->getCourseService()->findCoursesByIds($courseIds);
    }

    protected function findClassroomsByIds(array $classroomIds)
    {
        return $this->getClassroomService()->findClassroomsByIds($classroomIds);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }
}