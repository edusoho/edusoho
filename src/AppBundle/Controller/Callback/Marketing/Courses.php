<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Util\CourseTitleUtils;

class Courses extends MarketingBase
{
    public function fetch(Request $request)
    {
        $id = $request->query->get('courseId');
        if (empty($id)) {
            return array();
        }

        $course = $this->getCourseService()->getCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $activity = $this->getFirstFreeActivity($course['id']);
        $teachers = $this->getCourseTeachers($course);

        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        $tasks = ArrayToolkit::column($tasks, 'title');
        $course = $this->filterCourse($course, $courseSet, $activity);
        $course['teachers'] = $teachers;
        $course['tasks'] = $tasks;

        return $course;
    }

    public function search(Request $request)
    {
        $keywords = $request->query->get('q');

        if (empty($keywords)) {
            return array();
        }
        $conditions = array(
            'status' => 'published',
            'parentId' => '0',
        );
        $conditions['title'] = $keywords;
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'desc'),
            0,
            100
        );

        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
        $results = array();
        foreach ($courses  as $courseId => $course) {
            if ('published' != $course['status'] || $course['originPrice'] < 1) {
                continue;
            }
            $courseSet = $courseSets[$course['courseSetId']];
            $result = array();
            $result['id'] = $course['id'];
            $courseCover = $courseSet['cover'] ? $courseSet['cover']['small'] : '';
            if (!empty($courseCover)) {
                $result['cover'] = $this->getWebExtension()->getFurl($courseCover);
            }
            $result['title'] = CourseTitleUtils::getDisplayedTitle($course);
            $results[] = $result;
            if (count($results) >= 5) {
                break;
            }
        }

        return $results;
    }

    private function getFirstFreeActivity($courseId)
    {
        $previewTask = $this->getTaskService()->searchTasks(
            array('courseId' => $courseId,
                  'type' => 'video',
                  'isFree' => '1',
                  'status' => 'published', ),
            array('seq' => 'ASC'),
            0,
            1
        );
        if (empty($previewTask)) {
            return array();
        }
        $activity = $this->getActivityService()->getActivity($previewTask[0]['activityId'], true);

        return $activity;
    }

    private function getCourseTeachers($course)
    {
        $showTeacherIds = $course['teacherIds'];
        $teachers = $this->getUserService()->findUsersByIds($showTeacherIds);
        $teachers = ArrayToolkit::index($teachers, 'id');

        $teachersProfile = $this->getUserService()->findUserProfilesByIds($showTeacherIds);
        $teachersProfile = ArrayToolkit::index($teachersProfile, 'id');

        $teachers = $this->filterTeachers($teachers, $teachersProfile);

        return $teachers;
    }

    private function filterTeachers($teachers, $teachersProfile)
    {
        $users = array();
        foreach ($teachers as $key => $teacher) {
            $user = array();
            $user['source_id'] = $key;
            $user['name'] = $teacher['nickname'];
            // $user['smallAvatar'] = $teacher['smallAvatar'];
            // $user['mediumAvatar'] = $teacher['mediumAvatar'];
            $user['avatar'] = $this->getWebExtension()->getFurl($teacher['largeAvatar'], 'avatar.png');
            $user['about'] = $teachersProfile[$key]['about'];
            $users[] = $user;
        }

        return $users;
    }

    private function filterCourse($course, $courseSet, $activity)
    {
        $result = array();
        $result['source_id'] = $course['id'];
        $result['source_link'] = $this->generateUrl('course_show', array('id' => $course['id']));
        $result['name'] = CourseTitleUtils::getDisplayedTitle($course);
        $courseCover = $courseSet['cover'] ? $courseSet['cover']['large'] : '';
        if (!empty($courseCover)) {
            $result['cover'] = $this->getWebExtension()->getFurl($courseCover);
        }
        $result['about'] = $courseSet['summary'];
        $result['price'] = $course['originPrice'] * 100;
        $result['type'] = 'course';

        if (!empty($activity) && isset($activity['ext']['file']['globalId'])) {
            $result['free_video'] = $activity['ext']['file']['globalId'];
        }

        return $result;
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
