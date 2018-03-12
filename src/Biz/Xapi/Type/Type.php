<?php

namespace Biz\Xapi\Type;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseNoteService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\File\Service\UploadFileService;
use Biz\Marker\Service\QuestionMarkerResultService;
use Biz\Marker\Service\QuestionMarkerService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Biz\Xapi\Service\XapiService;
use Codeages\Biz\Framework\Context\BizAware;
use QiQiuYun\SDK\XAPIActivityTypes;

abstract class Type extends BizAware
{
    abstract public function package($statement);

    abstract public function packages($statements);

    /**
     * @param $subject
     * @param $dao
     * @param $columns
     * @param array $conditions
     *
     * @return array array('id' => 1, 'column1' => 2)
     */
    protected function find($subject, $dao, $columns, $conditions = array())
    {
        $ids = ArrayToolkit::column($subject[0], $subject[1]);
        $conditions = array_merge(array('ids' => $ids), $conditions);
        $columns = array_unique(array_merge(array('id'), $columns));
        $results = $this->createDao($dao)->search($conditions, array(), 0, PHP_INT_MAX, $columns);

        return ArrayToolkit::index($results, 'id');
    }

    protected function findTasks($subject, $conditions = array())
    {
        return $this->find(
            $subject,
            'Task:TaskDao',
            array('activityId', 'type', 'courseId'),
            $conditions
        );
    }

    protected function findCourses($subject, $conditions = array())
    {
        $courses = $this->find(
            $subject,
            'Course:CourseDao',
            array('courseSetId', 'title'),
            $conditions
        );

        $courseSets = $this->findCourseSets(
            array($courses, 'courseSetId')
        );

        foreach ($courses as &$course) {
            if (!empty($courseSets[$course['courseSetId']])) {
                $courseSet = $courseSets[$course['courseSetId']];
                $course['description'] = empty($courseSet['subtitle']) ? '' : $courseSet['subtitle'];
                $course['title'] = $courseSet['title'].'-'.$course['title'];
            }
        }

        return $courses;
    }

    protected function findCourseSets($subject, $conditions = array())
    {
        return $this->find(
            $subject,
            'Course:CourseSetDao',
            array('title', 'subtitle'),
            $conditions
        );
    }

    protected function findCourseThreads($subject, $conditions = array())
    {
        return $this->find(
            $subject,
            'Course:ThreadDao',
            array('taskId', 'courseId', 'courseSetId'),
            $conditions
        );
    }

    protected function findActivityWatchLogs($subject, $conditions = array())
    {
        return $this->find(
            $subject,
            'Xapi:ActivityWatchLogDao',
            array('course_id', 'task_id'),
            $conditions
        );
    }

    protected function findActivities($subject)
    {
        $activityIds = ArrayToolkit::column($subject[0], $subject[1]);
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');

        $resourceIds = array();
        foreach ($activities as $activity) {
            if (in_array($activity['mediaType'], array('video', 'audio', 'doc', 'ppt', 'flash'))) {
                if (!empty($activity['ext']['mediaId'])) {
                    $resourceIds[] = $activity['ext']['mediaId'];
                }
            }
        }
        $resources = $this->getUploadFileService()->findFilesByIds($resourceIds);
        $resources = ArrayToolkit::index($resources, 'id');

        return array($activities, $resources);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }

    /**
     * @param $alias
     *
     * @return \Codeages\Biz\Framework\Dao\GeneralDaoInterface
     */
    protected function createDao($alias)
    {
        return $this->biz->dao($alias);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return XapiService
     */
    protected function getXapiService()
    {
        return $this->createService('Xapi:XapiService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return QuestionMarkerResultService
     */
    protected function getQuestionMarkerResultService()
    {
        return $this->createService('Marker:QuestionMarkerResultService');
    }

    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    /**
     * @return QuestionMarkerService
     */
    protected function getQuestionMarkerService()
    {
        return $this->createService('Marker:QuestionMarkerService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    protected function getActor($userId)
    {
        $currentUser = $this->getUserService()->getUser($userId);
        $userProfile = $this->getUserService()->getUserProfile($userId);
        $siteSettings = $this->getSettingService()->get('site', array());

        $host = empty($siteSettings['url']) ? '' : $siteSettings['url'];

        return array(
            'account' => array(
                'id' => $currentUser['id'],
                'name' => $currentUser['nickname'],
                'email' => empty($currentUser['email']) ? '' : md5($currentUser['email']),
                'phone' => empty($userProfile['mobile']) ? '' : md5($userProfile['mobile']),
                'homePage' => $host,
            ),
        );
    }

    /**
     * @return \QiQiuYun\SDK\Service\XAPIService
     */
    public function createXAPIService()
    {
        return $this->getXapiService()->getXapiSdk();
    }

    protected function num_to_capital($num)
    {
        $char = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return $char[$num];
    }

    protected function convertMediaType($mediaType)
    {
        $list = array(
            'audio' => 'audio',
            'video' => 'video',
            'doc' => 'document',
            'ppt' => 'document',
            'discuss' => 'online-discussion',
            'testpaper' => 'testpaper',
            'homework' => 'homework',
            'exercise' => 'exercise',
            'download' => 'document',
            'live' => 'live',
            'text' => 'document',
            'flash' => 'document',
        );

        return empty($list[$mediaType]) ? $mediaType : $list[$mediaType];
    }

    protected function getDefinitionType($type)
    {
        static $map = array(
            'article' => XAPIActivityTypes::MESSAGE,
            'thread' => XAPIActivityTypes::QUESTION,
            'course' => XAPIActivityTypes::COURSE,
            'classroom' => XAPIActivityTypes::CLASS_ONLINE,
        );

        return $map[$type];
    }
}
