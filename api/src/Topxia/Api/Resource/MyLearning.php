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
            array('lastViewTime', 'DESC', 'id', 'DESC'),
            0,
            $membersCount
        );

        $learningData = $this->buildLearningData($members);
        $learningData = $this->filter($learningData);

        return  $this->wrap($learningData, count($learningData));
    }

    public function filter($learningData)
    {
        foreach ($learningData as &$data) {
            $data = $this->callFilter('Course', $data);
        }

        return $learningData;
    }

    protected function buildLearningData($members)
    {
        $learningData = array();

        if (empty($members)) {
            return $learningData;
        }

        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $groupMembers = ArrayToolkit::group($members, 'joinedType');
        if (!empty($groupMembers['classroom'])) {
            $classroomIds = ArrayToolkit::column($groupMembers['classroom'], 'classroomId');
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
            $classrooms = ArrayToolkit::index($classrooms, 'id');
        }

        foreach ($members as $key => $member) {
            $learningData[$key] = $courses[$member['courseId']];
            $learningData[$key]['lastViewTime'] = empty($member['lastViewTime']) ? 0 : date('c', $member['lastViewTime']);
            $learningData[$key]['joinedType'] = $member['joinedType'];
            if ('classroom' == $member['joinedType']) {
                $learningData[$key]['classroomTitle'] = empty($classrooms[$member['classroomId']]) ? '' : $classrooms[$member['classroomId']]['title'];
            }
        }

        return $learningData;
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