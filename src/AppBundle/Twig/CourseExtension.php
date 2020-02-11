<?php

namespace AppBundle\Twig;

use AppBundle\Util\AvatarAlert;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Course\Util\CourseTitleUtils;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DynUrlToolkit;

class CourseExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('course_show_metas', array($this, 'getCourseShowMetas')),
            new \Twig_SimpleFunction('is_buy_course_from_modal', array($this, 'isBuyCourseFromModal')),
            new \Twig_SimpleFunction('buy_course_need_approve', array($this, 'needApproval')),
            new \Twig_SimpleFunction('is_member_expired', array($this, 'isMemberExpired')),
            new \Twig_SimpleFunction('course_chapter_alias', array($this, 'getCourseChapterAlias')),
            //课程视频转音频完成率
            new \Twig_SimpleFunction('video_convert_completion', array($this, 'getAudioConvertionStatus')),
            new \Twig_SimpleFunction('is_support_enable_audio', array($this, 'isSupportEnableAudio')),
            new \Twig_SimpleFunction('get_course', array($this, 'getCourse')),
            new \Twig_SimpleFunction('course_daily_tasks_num', array($this, 'getCourseDailyTasksNum')),
            new \Twig_SimpleFunction('dyn_url', array($this, 'getDynUrl')),
            new \Twig_SimpleFunction('get_course_types', array($this, 'getCourseTypes')),
            new \Twig_SimpleFunction('is_task_available', array($this, 'isTaskAvailable')),
            new \Twig_SimpleFunction('is_discount', array($this, 'isDiscount')),
            new \Twig_SimpleFunction('get_course_count', array($this, 'getCourseCount')),
            new \Twig_SimpleFunction('is_un_multi_courseset', array($this, 'isUnMultiCourseSet')),
            new \Twig_SimpleFunction('has_mul_courses', array($this, 'hasMulCourses')),
            new \Twig_SimpleFunction('get_course_title', array($this, 'getCourseTitle')),
            new \Twig_SimpleFunction('get_formated_course_title', array($this, 'getFormatedCourseTitle')),
            new \Twig_SimpleFunction('task_list_json_data', array($this, 'taskListJsonData')),
            new \Twig_SimpleFunction('get_course_tasks', array($this, 'getCourseTasks')),
            new \Twig_SimpleFunction('is_teacher', array($this, 'isTeacher')),
            new \Twig_SimpleFunction('next_task', array($this, 'getNextTask')),
            new \Twig_SimpleFunction('latest_live_task', array($this, 'getLatestLiveTask')),
        );
    }

    public function getLatestLiveTask()
    {
        $user = $this->biz['user'];
        if (!$user->isLogin()) {
            return null;
        }
        $liveNotifySetting = $this->getSettingService()->get('homepage_live_notify', array());
        if (empty($liveNotifySetting['enabled'])) {
            return null;
        }
        $startTime = time() + $liveNotifySetting['preTime'] * 60;
        $endTimeRange = 15 * 60; //结束前固定15分钟
        $task = $this->getTaskService()->getUserCurrentPublishedLiveTask($user['id'], $startTime, $endTimeRange);
        if ($task) {
            $task['courseSet'] = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);
        }

        return $task;
    }

    public function getNextTask($taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($task)) {
            return null;
        }
        $conditions = array(
            'courseId' => $task['courseId'],
            'status' => 'published',
            'seq_GT' => $task['seq'],
        );
        $tasks = $this->getTaskService()->searchTasks($conditions, array('seq' => 'ASC'), 0, 1);

        return reset($tasks);
    }

    public function isTeacher($courseId)
    {
        $user = $this->biz['user'];
        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            return $user->isTeacher();
        }

        return false;
    }

    public function getCourseTasks($courseId, $conditions = array())
    {
        $conditions['courseId'] = $courseId;

        return empty($courseId) ? array() : $this->getTaskService()->searchTasks($conditions, array(), 0, PHP_INT_MAX);
    }

    public function getCourse($id)
    {
        return $this->getCourseService()->getCourse($id);
    }

    public function taskListJsonData($courseItems, $showOptional = false)
    {
        if (empty($courseItems)) {
            return json_encode(array());
        }

        $results = array();
        foreach ($courseItems as $item) {
            if ($showOptional || !$this->isOptionalTaskLesson($item)) {
                $default = array(
                    'lock' => '',
                    'status' => '',
                    'isOptional' => '',
                    'type' => '',
                    'isFree' => '',
                    'activity' => array(),
                    'tryLookable' => '',
                );
                $item = array_merge($default, $item);
                $mediaType = empty($item['activity']['mediaType']) ? 'video' : $item['activity']['mediaType'];
                $results[] = array(
                    'itemType' => $item['itemType'],
                    'number' => $item['number'],
                    'published_number' => empty($item['published_number']) ? 0 : $item['published_number'],
                    'title' => $item['title'],
                    'result' => empty($item['result']['id']) ? '' : $item['result']['id'],
                    'resultStatus' => empty($item['result']['status']) ? '' : $item['result']['status'],
                    'lock' => $item['lock'],
                    'status' => $item['status'],
                    'taskId' => $item['id'],
                    'isOptional' => $item['isOptional'],
                    'type' => $item['type'],
                    'isTaskFree' => $item['isFree'],
                    'watchLimitRemaining' => isset($item['watchLimitRemaining']) ? $this->container->get('web.twig.extension')->durationTextFilter($item['watchLimitRemaining']) : false,
                    'replayStatus' => empty($item['activity']['ext']['replayStatus']) ? '' : $item['activity']['ext']['replayStatus'],
                    'activityStartTimeStr' => empty($item['activity']['startTime']) ? '' : date('m-d H:i', $item['activity']['startTime']),
                    'activityStartTime' => empty($item['activity']['startTime']) ? '' : $item['activity']['startTime'],
                    'activityLength' => empty($item['activity']['length']) ? '' : $this->getActivityExtension()->lengthFormat($item['activity']['length'], $mediaType),
                    'activityEndTime' => empty($item['activity']['endTime']) ? '' : $item['activity']['endTime'],
                    'fileStorage' => empty($item['activity']['ext']['file']['storage']) ? '' : $item['activity']['ext']['file']['storage'],
                    'isTaskTryLookable' => $item['tryLookable'],
                    'isSingleTaskLesson' => empty($item['isSingleTaskLesson']) ? false : $item['isSingleTaskLesson'],
                );
            }
        }

        return json_encode($results);
    }

    public function getCourseCount($courseSetId, $isPublish = 0)
    {
        $conditions = array(
            'courseSetId' => $courseSetId,
        );
        if ($isPublish) {
            $conditions['status'] = 'published';
        }

        return $this->getCourseService()->countCourses($conditions);
    }

    //是否为非多计划的课程，如：直播课程，约排课课程，班级课程等特殊课程类型（公开课不在此列）
    public function isUnMultiCourseSet($courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return in_array($courseSet['type'], array('live', 'reservation')) || !empty($courseSet['parentId']);
    }

    /**
     * 判断一个课程是否有多个计划
     */
    public function hasMulCourses($courseSetId, $isPublish = 0)
    {
        return $this->getCourseService()->hasMulCourses($courseSetId, $isPublish);
    }

    public function getCourseTitle($course)
    {
        return empty($course['title']) ? $course['courseSetTitle'] : $course['title'];
    }

    public function getFormatedCourseTitle($course)
    {
        return CourseTitleUtils::getDisplayedTitle($course);
    }

    public function getCourseDailyTasksNum($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $taskNum = $course['taskNum'];
        if ('days' == $course['expiryMode']) {
            $finishedTaskPerDay = empty($course['expiryDays']) ? false : $taskNum / $course['expiryDays'];
        } else {
            $diffDay = ($course['expiryEndDate'] - $course['expiryStartDate']) / (24 * 60 * 60);
            $finishedTaskPerDay = empty($diffDay) ? false : $taskNum / $diffDay;
        }

        return round($finishedTaskPerDay, 0);
    }

    public function getDynUrl($baseUrl, $params)
    {
        return DynUrlToolkit::getUrl($this->biz, $baseUrl, $params);
    }

    public function isDiscount($course)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $discountPlugin = $this->container->get('kernel')->getPluginConfigurationManager()->isPluginInstalled('Discount');

        $isDiscount = false;
        if ($discountPlugin && $courseSet['discountId'] > 0) {
            $discount = $this->getDiscountService()->getDiscount($courseSet['discountId']);
            if (!empty($discount)) {
                if (($course['price'] < $course['originPrice']) && 0 == $course['parentId']) {
                    $isDiscount = true;
                }
            }
        }

        return $isDiscount;
    }

    public function isSupportEnableAudio($enableAudioStatus)
    {
        return $this->getCourseService()->isSupportEnableAudio($enableAudioStatus);
    }

    public function getAudioConvertionStatus($courseId)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'video', true);
        $medias = ArrayToolkit::column($activities, 'ext');

        return $this->getUploadFileService()->getAudioConvertionStatus(array_unique(ArrayToolkit::column($medias, 'mediaId')));
    }

    public function getCourseChapterAlias($type)
    {
        if ('lesson' == $type) {
            return 'site.data.lesson';
        }
        $defaultCourseChapterAlias = array(
            'chapter' => 'site.data.chapter',
            'unit' => 'site.data.part',
            'part' => 'site.data.part',
            'task' => 'site.data.task',
        );

        $courseSetting = $this->getSettingService()->get('course');

        if (empty($courseSetting['custom_chapter_enabled'])) {
            return $defaultCourseChapterAlias[$type];
        }

        $settingKey = array(
            'chapter' => 'chapter_name',
            'unit' => 'part_name',
            'part' => 'part_name',
            'task' => 'task_name',
        );

        return isset($courseSetting[$settingKey[$type]]) ? $courseSetting[$settingKey[$type]] : $defaultCourseChapterAlias[$type];
    }

    public function isMemberExpired($course, $member)
    {
        if (empty($course) || empty($member)) {
            return false;
        }

        if ($course['parentId'] > 0) {
            $classroomRef = $this->getClassroomService()->getClassroomCourseByCourseSetId($course['courseSetId']);
            if (!empty($classroomRef)) {
                $user = $this->biz['user'];
                $member = $this->getClassroomService()->getClassroomMember($classroomRef['classroomId'], $user['id']);

                return $member['deadline'] > 0 && $member['deadline'] < time();
            }
        }

        return !$this->getMemberService()->isMemberNonExpired($course, $member);
    }

    public function getCourseShowMetas($mode = 'guest')
    {
        $metas = $this->container->get('course.extension')->getCourseShowMetas();

        return $metas["for_{$mode}"];
    }

    public function isBuyCourseFromModal($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $user = $this->biz['user'];

        return !$user->isLogin()
            || $this->shouldUserinfoFill()
            || $this->needApproval($courseId)
            || $this->isUserAvatarEmpty();
    }

    public function needApproval($courseId)
    {
        $user = $this->biz['user'];
        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return false;
        }

        return $course['approval'] && 'approved' !== $user['approvalStatus'];
    }

    protected function isUserAvatarEmpty()
    {
        $user = $this->biz['user'];

        return AvatarAlert::alertJoinCourse($user);
    }

    public function shouldUserinfoFill()
    {
        $setting = $this->getSettingService()->get('course');

        return !empty($setting['buy_fill_userinfo']);
    }

    public function getCourseTypes()
    {
        $courseTypes = $this->container->get('extension.manager')->getCourseTypes();
        $visibleCourseTypes = array_filter($courseTypes, function ($type) {
            return 1 == $type['visible'];
        });

        uasort($visibleCourseTypes, function ($type1, $type2) {
            if ($type1['priority'] == $type2['priority']) {
                return 0;
            }

            return $type1['priority'] > $type2['priority'] ? -1 : 1;
        });

        return $visibleCourseTypes;
    }

    public function isTaskAvailable($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        if ('published' == $task['status'] and 'published' == $course['status']) {
            return true;
        }
        $result = $this->getCourseService()->canLearnTask($task['id']);

        return 'success' == $result['code'];
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }

    public function getName()
    {
        return 'topxia_course_twig';
    }

    protected function getActivityExtension()
    {
        return $this->container->get('web.twig.activity_extension');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    private function isOptionalTaskLesson($item)
    {
        return in_array($item['itemType'], array('task', 'lesson')) && $item['isOptional'];
    }

    protected function getDiscountService()
    {
        return $this->biz->service('DiscountPlugin:Discount:DiscountService');
    }
}
