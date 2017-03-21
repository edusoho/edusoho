<?php
namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class MyLearning extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $user = $this->getCurrentUser();

        $count = $this->getCourseService()->countUserLearningCourses($user['id']);
        $courses = $this->getCourseService()->findUserLearningCourses(
            $user['id'],
            0,
            $count
        );
        $courses = ArrayToolkit::index($courses, 'id');

        $learningData = $this->buildLearningData($courses);
        $learningData = $this->filter($learningData);

        return $this->wrap($learningData, count($learningData));
    }

    public function filter($learningData)
    {
        foreach ($learningData as &$data) {
            $data = $this->callFilter('Course', $data);
        }

        return $learningData;
    }

    protected function buildLearningData($courses)
    {
        $learningData = array();
        $user = $this->getCurrentUser();

        if (empty($courses)) {
            return $learningData;
        }

        $courseIds = ArrayToolkit::column($courses, 'id');
        $conditions = array(
            'courseIds' => $courseIds,
            'userId' => $user['id'],
        );
        $members = $this->getMemberService()->searchMembers($conditions, null, 0, 1000);

        $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);

        $groupMembers = ArrayToolkit::group($members, 'joinedType');

        if (!empty($groupMembers['classroom'])) {
            $classroomIds = ArrayToolkit::column($groupMembers['classroom'], 'classroomId');
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
            $classrooms = ArrayToolkit::index($classrooms, 'id');
        }

        foreach ($members as $key => $member) {
            $course = $courses[$member['courseId']];
            $learningData[$key] = $course;

            $learningData[$key]['courseSet'] = $courseSets[$course['courseSetId']];
            $learningData[$key]['lastViewTime'] = empty($member['lastLearnTime']) ? 0 : date('c', $member['lastLearnTime']);
            $learningData[$key]['joinedType'] = $member['joinedType'];
            if ('classroom' == $member['joinedType']) {
                $learningData[$key]['classroomTitle'] = empty($classrooms[$member['classroomId']]) ? '' : $classrooms[$member['classroomId']]['title'];
            }
        }
        return $learningData;
    }

    protected function findCoursesByIds(array $courseIds)
    {
        return $this->findCoursesByIds($courseIds);
    }

    protected function findClassroomsByIds(array $classroomIds)
    {
        return $this->findClassroomsByIds($classroomIds);
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
