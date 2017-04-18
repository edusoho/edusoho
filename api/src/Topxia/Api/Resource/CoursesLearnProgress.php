<?php
namespace Topxia\Api\Resource;

use Biz\Course\Service\Impl\CourseServiceImpl;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class CoursesLearnProgress extends BaseResource
{
    public function get(Application $app, Request $request)
    {
        $courseIds = $request->query->get('courseIds', 0);

        $currentUser = $this->getCurrentUser();
        $courseIds = explode(',', $courseIds);

        $progressData =  $this->calculateLearnProgressByUserIdAndCourseIds($currentUser['id'], $courseIds);
        return $this->wrap($progressData, count($progressData));
    }

    public function filter($res)
    {
        return $res;
    }

    protected function calculateLearnProgressByUserIdAndCourseIds($userId, array $courseIds)
    {
        if (empty($userId) || empty($courseIds)) {
            return array();
        }
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseTaskCount = array();

        foreach ($courses as $course) {
            $conditions = array(
                'courseId' => $course['id'],
                'status' => 'published'
            );
            if ($course['type'] == 'live') {
                $conditions['types'] = array('testpaper','live');
            } else {
                $conditions['types'] = array('testpaper','video','audio','text','flash','ppt','doc');
            }

            $courseTaskCount[$course['id']] = $this->getTaskService()->countTasks($conditions);
        }

        $conditions = array(
            'courseIds' => $courseIds,
            'userId' => $userId,
        );
        $count = $this->getMemberService()->countMembers($conditions);
        $members = $this->getMemberService()->searchMembers(
            $conditions,
            array('id' => 'DESC'),
            0,
            $count
        );

        $learnProgress = array();
        foreach ($members as $member) {
            $taskCount = empty($courseTaskCount[$member['courseId']]) ? 0 : $courseTaskCount[$member['courseId']];
            $learnProgress[] = array(
                'courseId' => $member['courseId'],
                'totalLesson' => (string)$taskCount,
                'learnedNum' => $member['learnedNum'],
            );
        }

        return $learnProgress;
    }

    /**
     * @return CourseServiceImpl
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }
}
