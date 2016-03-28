<?php

namespace Mooc\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Course\Impl\CourseServiceImpl as BaseCourseServiceImpl;

class CourseServiceImpl extends BaseCourseServiceImpl
{
    protected function _filterCourseFields($fields)
    {
        $fields = parent::_filterCourseFields($fields);
        $fields = ArrayToolkit::filter($fields, array(
            'title'         => '',
            'subtitle'      => '',
            'about'         => '',
            'expiryDay'     => 0,
            'serializeMode' => 'none',
            'categoryId'    => 0,
            'vipLevelId'    => 0,
            'goals'         => array(),
            'audiences'     => array(),
            'tags'          => '',
            'startTime'     => 0,
            'endTime'       => 0,
            'locationId'    => 0,
            'address'       => '',
            'maxStudentNum' => 0,
            'watchLimit'    => 0,
            'approval'      => 0,
            'maxRate'       => 0,
            'locked'        => 0,
            'tryLookable'   => 0,
            'tryLookTime'   => 0,
            'buyable'       => 0,
            'studyModel'    => 'normal',
            'rootId'        => 0,
            'certi'         => 0,
        ));

        return $fields;
    }

    protected function _prepareCourseConditions($conditions)
    {
        if (isset($conditions['nickname']) && empty($conditions['nickname'])) {
            unset($conditions['nickname']);
        }

        $conditions = parent::_prepareCourseConditions($conditions);

        if (!empty($conditions['organizationId']) && !empty($conditions['includeChildren'])) {
            $userConditions['organizationId']  = $conditions['organizationId'];
            $userConditions['includeChildren'] = $conditions['includeChildren'];
            $count                             = $this->getUserService()->searchUserCount($userConditions);
            $users                             = $this->getUserService()->searchUsers($userConditions, array('createdTime', 'DESC'), 0, $count);
            $conditions['userIds']             = ArrayToolkit::column($users, 'id');

            if (empty($conditions['userIds'])) {
                $conditions['userIds'] = array(0);
            }

            unset($conditions['organizationId']);
            unset($conditions['includeChildren']);
        }

        return $conditions;
    }

    public function findOtherPeriods($courseId)
    {
        $course = $this->getCourseDao()->getCourse($courseId);

        $courses = CourseSerialize::unserializes(
            $this->getCourseDao()->findOtherPeriods($course)
        );

        return ArrayToolkit::index($courses, 'periods');
    }

    public function deleteCourse($id)
    {
        $course = $this->getCourse($id);

        if (!parent::deleteCourse($id)) {
            throw $this->createServiceException('删除课程出错');
        };

        if ('periodic' == $course['type']) {
            $this->getCourseDao()->subPeriodsByRootId($course['rootId'], $course['periods']);
        }

        return true;
    }

    public function loadCourse($id)
    {
        if (empty($id)) {
            throw $this->createNotFoundException("课程关键字为空！");
        }

        $course = $this->getCourse($id);

        if (empty($course)) {
            throw $this->createNotFoundException("课程{id}不存在！");
        }

        return $course;
    }

    public function loadLesson($id)
    {
        if (empty($id)) {
            throw $this->createNotFoundException("课时关键字为空！");
        }

        $lesson = $this->getLesson($id);

        if (empty($lesson)) {
            throw $this->createNotFoundException("课时{id}不存在！");
        }

        return $lesson;
    }

    public function getLesson($id)
    {
        $lesson = $this->getLessonDao()->getLesson($id);
        return LessonSerialize::unserialize($lesson);
    }

    public function findCourseStudentsAll($courseId)
    {
        return $this->getMemberDao()->findMembersAllByCourseIdAndRole($courseId, 'student');
    }
}

class CourseSerialize
{
    public static function serialize(array &$course)
    {
        if (isset($course['tags'])) {
            if (is_array($course['tags']) && !empty($course['tags'])) {
                $course['tags'] = '|'.implode('|', $course['tags']).'|';
            } else {
                $course['tags'] = '';
            }
        }

        if (isset($course['goals'])) {
            if (is_array($course['goals']) && !empty($course['goals'])) {
                $course['goals'] = '|'.implode('|', $course['goals']).'|';
            } else {
                $course['goals'] = '';
            }
        }

        if (isset($course['audiences'])) {
            if (is_array($course['audiences']) && !empty($course['audiences'])) {
                $course['audiences'] = '|'.implode('|', $course['audiences']).'|';
            } else {
                $course['audiences'] = '';
            }
        }

        if (isset($course['teacherIds'])) {
            if (is_array($course['teacherIds']) && !empty($course['teacherIds'])) {
                $course['teacherIds'] = '|'.implode('|', $course['teacherIds']).'|';
            } else {
                $course['teacherIds'] = null;
            }
        }

        return $course;
    }

    public static function unserialize(array $course = null)
    {
        if (empty($course)) {
            return $course;
        }

        $course['tags'] = empty($course['tags']) ? array() : explode('|', trim($course['tags'], '|'));

        if (empty($course['goals'])) {
            $course['goals'] = array();
        } else {
            $course['goals'] = explode('|', trim($course['goals'], '|'));
        }

        if (empty($course['audiences'])) {
            $course['audiences'] = array();
        } else {
            $course['audiences'] = explode('|', trim($course['audiences'], '|'));
        }

        if (empty($course['teacherIds'])) {
            $course['teacherIds'] = array();
        } else {
            $course['teacherIds'] = explode('|', trim($course['teacherIds'], '|'));
        }

        return $course;
    }

    public static function unserializes(array $courses)
    {
        return array_map(function ($course) {
            return CourseSerialize::unserialize($course);
        }, $courses);
    }
}

class LessonSerialize
{
    public static function serialize(array $lesson)
    {
        return $lesson;
    }

    public static function unserialize(array $lesson = null)
    {
        return $lesson;
    }

    public static function unserializes(array $lessons)
    {
        return array_map(function ($lesson) {
            return LessonSerialize::unserialize($lesson);
        }, $lessons);
    }
}
