<?php

namespace AppBundle\Controller\Callback\Marketing;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\ArrayToolkit;

class CourseController extends BaseController
{
    public function fullCourseAction(Request $request, $id)
    {
        if (empty($id)) {
            return array();
        }

        $course = $this->getCourseService()->getCourse($id);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $previewActivity = $this->getPreviewActivity($course['id']);
        $teachers = $this->getCourseTeachers($course);

        $course = $this->filterCourse($course, $courseSet, $previewActivity);
        $course['teachers'] = $teachers;

        return $this->createJsonResponse($course);
    }

    private function getPreviewActivity($courseId)
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
        $activity = $this->getActivityService()->getActivity($previewTask['activityId'], true);

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

    private function filterCourse($course, $courseSet, $previewActivity)
    {
        $result = array();
        $result['source_id'] = $course['id'];
        $result['source_link'] = $this->generateUrl('course_show', array('id' => $course['id']));
        if ($course['title'] == '默认教学计划') {
            $result['name'] = '《'.$courseSet['title'].'》';
        } else {
            $result['name'] = '课程《'.$courseSet['title'].'》的教学计划'.$course['title'];
        }
        $courseCover = $courseSet['cover'] ? $courseSet['cover']['large'] : '';
        $result['cover'] = $this->getWebExtension()->getFurl($courseCover, 'course.png');
        $result['about'] = $courseSet['summary'];
        $result['price'] = $course['originPrice'];
        $result['type'] = 'course';
        if (!empty($previewActivity)) {
            $result['video_no'] = $previewActivity['ext']['globalId'];
        }

        return $result;
    }

    protected function getWebExtension()
    {
        return $this->container->get('web.twig.extension');
    }

    /**
     * @return ActivityService
     */
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
