<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class CourseMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的班级中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          课程数量
     *   missionCount   任务数量
     *
     * @param  array $arguments                       参数
     * @return array 按课程分组的任务列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('userId', 'count', 'missionCount'))) {
            throw new \InvalidArgumentException("参数缺失");
        }

        return $this->getCourseStudyMissions($arguments);
    }

    private function getCourseStudyMissions($arguments)
    {
        $userId = $arguments['userId'];

        $sortedCourses = array();

        $courseMemConditions = array(
            'userId'      => $userId,
            'locked'      => 0,
            'classroomId' => 0,
            'role'        => 'student'
        );

        $courseMem = $this->getCourseService()->searchMembers($courseMemConditions, array('createdTime', 'DESC'), 0, 5);
        $courseIds = ArrayToolkit::column($courseMem, 'courseId');

        if (!empty($courseIds)) {
            $courseConditions = array(
                'courseIds' => $courseIds,
                'parentId'  => 0
            );
            $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, $arguments['count']);
            $courses = ArrayToolkit::index($courses, 'id');

            foreach ($courseMem as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course          = $courses[$member['courseId']];
                $sortedCourses[] = $course;
            }

            foreach ($sortedCourses as $key => &$course) {
                /**
                 * 找出学过的课时
                 */

                $learnedConditions = array(
                    'userId'   => $userId,
                    'status'   => 'finished',
                    'courseId' => $course['id']
                );
                $sort     = array('finishedTime', 'ASC');
                $learneds = $this->getCourseService()->findUserLearnedLessons($userId, $course['id']);
                /**
                 * 找出未学过的课时
                 */
                $learnedsGroupStatus = ArrayToolkit::group($learneds, 'status');

                $finishs   = isset($learnedsGroupStatus['finished']) ? $learnedsGroupStatus['finished'] : array();
                $finishIds = ArrayToolkit::column($finishs, 'lessonId');

                $learnings    = isset($learnedsGroupStatus['learning']) ? $learnedsGroupStatus['learning'] : array();
                $learningsIds = ArrayToolkit::column($learnings, 'lessonId');

                $notLearnedConditions = array(
                    'status'        => 'published',
                    'courseId'      => $course['id'],
                    'notLearnedIds' => $finishIds
                );

                $sort = array(
                    'seq', 'ASC'
                );
                $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions, $sort, 0, $arguments['missionCount']);

                if (empty($notLearnedLessons)) {
                    unset($sortedCourses[$key]);
                } else {
                    foreach ($notLearnedLessons as &$notLearnedLesson) {
                        if (in_array($notLearnedLesson['id'], $learningsIds)) {
                            $notLearnedLesson['isLearned'] = 'learning';
                        } else {
                            $notLearnedLesson['isLearned'] = '';
                        }
                    }

                    $course['lessons']          = $notLearnedLessons;
                    $course['learnedLessonNum'] = count($finishIds);
                    $course['allLessonNum']     = $course['lessonNum'];
                }
            }
        }

        return $sortedCourses;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
