<?php

namespace Topxia\WebBundle\Extensions\DataTag;

use Topxia\Common\ArrayToolkit;

class ClassroomMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的班级中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          班级数量
     *   missionCount   任务数量
     *
     * @param  array $arguments                       参数
     * @return array 按班级分组的任务列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('userId', 'count', 'missionCount'))) {
            throw new \InvalidArgumentException("参数缺失");
        }

        return $this->getClassroomStudyMissions($arguments);
    }

    private function getClassroomStudyMissions($arguments)
    {
        $userId = $arguments['userId'];

        $sortedClassrooms = array();

        $memberConditions = array(
            'userId' => $userId,
            'locked' => 0,
            'role'   => 'student'
        );
        $sort          = array('createdTime', 'DESC');
        $classroomMems = $this->getClassroomService()->searchMembers($memberConditions, $sort, 0, $arguments['count']);
        $classroomIds  = ArrayToolkit::column($classroomMems, 'classroomId');

        if (!empty($classroomIds)) {
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

            foreach ($classroomMems as $member) {
                if (empty($classrooms[$member['classroomId']])) {
                    continue;
                }

                $classroom = $classrooms[$member['classroomId']];

                $sortedClassrooms[] = $classroom;
            }

            foreach ($sortedClassrooms as $key => &$classroom) {
                $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);

                if (!empty($courses)) {
                    $courseIds = ArrayToolkit::column($courses, 'id');
                    /**
                     * 找出学过的课时
                     */
                    $learnedConditions = array(
                        'userId'    => $userId,
                        'courseIds' => $courseIds
                    );
                    $sort                = array('finishedTime', 'ASC');
                    $learnedCount        = $this->getCourseService()->searchLearnCount($learnedConditions);
                    $learneds            = $this->getCourseService()->searchLearns($learnedConditions, $sort, 0, $learnedCount);
                    $learnedsGroupStatus = ArrayToolkit::group($learneds, 'status');

                    $finishs   = isset($learnedsGroupStatus['finished']) ? $learnedsGroupStatus['finished'] : array();
                    $finishIds = ArrayToolkit::column($finishs, 'lessonId');

                    $learnings    = isset($learnedsGroupStatus['learning']) ? $learnedsGroupStatus['learning'] : array();
                    $learningsIds = ArrayToolkit::column($learnings, 'lessonId');

                    $notLearnedConditions = array(
                        'status'        => 'published',
                        'courseIds'     => $courseIds,
                        'notLearnedIds' => $finishIds
                    );
                    $sort = array(
                        'seq', 'ASC'
                    );
                    $notLearnedLessons = $this->getCourseService()->searchLessons($notLearnedConditions, $sort, 0, $arguments['missionCount']);

                    $classroomLessonNum = 0;

                    foreach ($courses as $course) {
                        //迭代班级下课时总数
                        $classroomLessonNum += $course['lessonNum'];
                    }

                    if (empty($notLearnedLessons)) {
                        unset($sortedClassrooms[$key]);
                    } else {
                        foreach ($notLearnedLessons as &$notLearnedLesson) {
                            if (in_array($notLearnedLesson['id'], $learningsIds)) {
                                $notLearnedLesson['isLearned'] = 'learning';
                            } else {
                                $notLearnedLesson['isLearned'] = '';
                            }
                        }

                        $classroom['lessons']          = $notLearnedLessons;
                        $classroom['learnedLessonNum'] = count($finishIds);
                        $classroom['allLessonNum']     = $classroomLessonNum;
                    }
                } else {
                    unset($sortedClassrooms[$key]);
                }
            }
        }

        return $sortedClassrooms;
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
