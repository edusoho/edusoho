<?php

namespace AppBundle\Twig;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DynUrlToolkit;
use AppBundle\Util\AvatarAlert;
use Biz\Activity\Service\ActivityService;
use Biz\Certificate\Service\CertificateService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LessonService;
use Biz\Course\Service\MemberService;
use Biz\Course\Util\CourseTitleUtils;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Util\EdusohoLiveClient;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;

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
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('course_show_metas', [$this, 'getCourseShowMetas']),
            new \Twig_SimpleFunction('is_buy_course_from_modal', [$this, 'isBuyCourseFromModal']),
            new \Twig_SimpleFunction('buy_course_need_approve', [$this, 'needApproval']),
            new \Twig_SimpleFunction('is_member_expired', [$this, 'isMemberExpired']),
            new \Twig_SimpleFunction('course_chapter_alias', [$this, 'getCourseChapterAlias']),
            //课程视频转音频完成率
            new \Twig_SimpleFunction('video_convert_completion', [$this, 'getAudioConvertionStatus']),
            new \Twig_SimpleFunction('is_support_enable_audio', [$this, 'isSupportEnableAudio']),
            new \Twig_SimpleFunction('get_course', [$this, 'getCourse']),
            new \Twig_SimpleFunction('course_daily_tasks_num', [$this, 'getCourseDailyTasksNum']),
            new \Twig_SimpleFunction('dyn_url', [$this, 'getDynUrl']),
            new \Twig_SimpleFunction('get_course_types', [$this, 'getCourseTypes']),
            new \Twig_SimpleFunction('is_task_available', [$this, 'isTaskAvailable']),
            new \Twig_SimpleFunction('is_discount', [$this, 'isDiscount']),
            new \Twig_SimpleFunction('get_course_count', [$this, 'getCourseCount']),
            new \Twig_SimpleFunction('is_un_multi_courseset', [$this, 'isUnMultiCourseSet']),
            new \Twig_SimpleFunction('has_mul_courses', [$this, 'hasMulCourses']),
            new \Twig_SimpleFunction('get_course_title', [$this, 'getCourseTitle']),
            new \Twig_SimpleFunction('get_formated_course_title', [$this, 'getFormatedCourseTitle']),
            new \Twig_SimpleFunction('task_list_json_data', [$this, 'taskListJsonData']),
            new \Twig_SimpleFunction('get_course_tasks', [$this, 'getCourseTasks']),
            new \Twig_SimpleFunction('is_teacher', [$this, 'isTeacher']),
            new \Twig_SimpleFunction('next_task', [$this, 'getNextTask']),
            new \Twig_SimpleFunction('latest_live_task', [$this, 'getLatestLiveTask']),
            new \Twig_SimpleFunction('can_obtain_certificates', [$this, 'canObtainCertificates']),
            new \Twig_SimpleFunction('can_buy_course', [$this, 'canBuyCourse']),
            new \Twig_SimpleFunction('display_task_title', [$this, 'displayTaskTitle']),
        ];
    }

    /**
     * @param $course
     * @param $userId
     * 是否可以购买课程
     */
    public function canBuyCourse($course)
    {
        $user = $this->biz['user'];
        if (!$course['buyable']) {
            if (!$user->isLogin()) {
                return false;
            }
            if (!$this->isPluginInstalled('vip')) {
                return false;
            }
            //会员免费学满足免费学条件，无视课程是否允许购买
            $status = $this->createService('VipPlugin:Vip:VipService')->checkUserVipRight($user['id'], CourseVipRightSupplier::CODE, $course['id']);

            return 'ok' === $status;
        }
        $currentTime = time();
        //是否超过有效期，超过有效期也不允许
        return !(
            ($course['buyExpiryTime'] && $course['buyExpiryTime'] < $currentTime)
            ||
            ($course['expiryEndDate'] && $course['expiryEndDate'] < $currentTime)
        );
    }

    public function getLatestLiveTask()
    {
        $user = $this->biz['user'];
        if (!$user->isLogin()) {
            return null;
        }
        $liveNotifySetting = $this->getSettingService()->get('homepage_live_notify', []);
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
        $conditions = [
            'courseId' => $task['courseId'],
            'status' => 'published',
            'seq_GT' => $task['seq'],
        ];
        $tasks = $this->getTaskService()->searchTasks($conditions, ['seq' => 'ASC'], 0, 1);

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

    public function getCourseTasks($courseId, $conditions = [])
    {
        $conditions['courseId'] = $courseId;

        return empty($courseId) ? [] : $this->getTaskService()->searchTasks($conditions, [], 0, PHP_INT_MAX);
    }

    public function getCourse($id)
    {
        return $this->getCourseService()->getCourse($id);
    }

    public function taskListJsonData($courseItems, $showOptional = false, $preview = false)
    {
        if (empty($courseItems)) {
            return json_encode([]);
        }
        $preview = $preview && $this->getCourseService()->hasCourseManagerRole();

        $results = [];
        foreach ($courseItems as $item) {
            if ($showOptional || !$this->isOptionalTaskLesson($item)) {
                $default = [
                    'lock' => '',
                    'status' => '',
                    'isOptional' => '',
                    'type' => '',
                    'isFree' => '',
                    'activity' => [],
                    'tryLookable' => '',
                ];
                $item = array_merge($default, $item);
                $mediaType = empty($item['activity']['mediaType']) ? 'video' : $item['activity']['mediaType'];
                $result = [
                    'itemType' => $item['itemType'],
                    'number' => $item['number'],
                    'published_number' => empty($item['published_number']) ? 0 : $item['published_number'],
                    'title' => $item['title'],
                    'result' => empty($item['result']['id']) ? '' : $item['result']['id'],
                    'resultStatus' => empty($item['result']['status']) ? '' : $item['result']['status'],
                    'lock' => $preview ? false : $item['lock'],
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
                    'isTaskShowModal' => $item['tryLookable'] || $item['isFree'],
                    'isSingleTaskLesson' => empty($item['isSingleTaskLesson']) ? false : $item['isSingleTaskLesson'],
                ];
                if ('live' === $item['type']) {
                    $currentTime = time();
                    $result['liveStatus'] = $liveStatus = $item['activity']['ext']['progressStatus'];
                    if ('created' === $liveStatus && $currentTime > $result['activityStartTime']) {
                        $result['liveStatus'] = EdusohoLiveClient::LIVE_STATUS_LIVING;
                    }
                    if ('created' === $liveStatus && $currentTime > $result['activityEndTime']) {
                        $result['liveStatus'] = EdusohoLiveClient::LIVE_STATUS_CLOSED;
                    }
                }
                $results[] = $result;
            }
        }

        return json_encode($results);
    }

    public function getCourseCount($courseSetId, $isPublish = 0)
    {
        $conditions = [
            'courseSetId' => $courseSetId,
        ];
        if ($isPublish) {
            $conditions['status'] = 'published';
        }

        return $this->getCourseService()->countCourses($conditions);
    }

    //是否为非多计划的课程，如：直播课程，约排课课程，班级课程等特殊课程类型（公开课不在此列）
    public function isUnMultiCourseSet($courseSetId)
    {
        $courseSet = $this->getCourseSetService()->getCourseSet($courseSetId);

        return in_array($courseSet['type'], ['live', 'reservation']) || !empty($courseSet['parentId']);
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
        $defaultCourseChapterAlias = [
            'chapter' => 'site.data.chapter',
            'unit' => 'site.data.part',
            'part' => 'site.data.part',
            'task' => 'site.data.task',
        ];

        $courseSetting = $this->getSettingService()->get('course');

        if (empty($courseSetting['custom_chapter_enabled'])) {
            return $defaultCourseChapterAlias[$type];
        }

        $settingKey = [
            'chapter' => 'chapter_name',
            'unit' => 'part_name',
            'part' => 'part_name',
            'task' => 'task_name',
        ];

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
                $classroom = $this->getClassroomService()->getClassroom($classroomRef['classroomId']);
                $member = $this->getClassroomService()->getClassroomMember($classroomRef['classroomId'], $user['id']);
                if (!empty($member)) {
                    return !$this->getClassroomService()->isMemberNonExpired($classroom, $member);
                } else {
                    return $member['deadline'] > 0 && $member['deadline'] < time();
                }
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

    public function displayTaskTitle($task)
    {
        $number = $this->getTaskService()->countTasks(['categoryId' => $task['categoryId'], 'seq_LT' => $task['seq']]);
        if ($number) {
            $task['number'] = $number;
        } else {
            $lesson = $this->getLessonService()->getLesson($task['categoryId']);
            $task['number'] = $lesson['number'];
        }
        if ($task['isOptional']) {
            return $this->trans('course.task.display', [
                '%taskName%' => $this->trans('course.optional_task'),
                '%taskNumber%' => '',
                '%taskTitle%' => $task['title'],
            ]);
        }
        if ('lesson' == $task['mode']) {
            return $this->trans('course.lesson.display', [
                '%part_name%' => $this->trans('site.data.lesson'),
                '%number%' => $task['number'],
                '%title%' => $task['title'],
            ]);
        }

        return $this->trans('course.task.display', [
            '%taskName%' => $this->trans($this->getCourseChapterAlias('task')),
            '%taskNumber%' => $task['number'],
            '%taskTitle%' => $task['title'],
        ]);
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

    public function canObtainCertificates($targetId, $targetType)
    {
        $targetIds = [$targetId];
        if ('courseSet' == $targetType) {
            $targetType = 'course';
            $courses = $this->getCourseService()->findCoursesByCourseSetId($targetId);
            $targetIds = ArrayToolkit::column($courses, 'id');
        }
        $certificates = $this->getCertificateService()->search(
            ['targetIds' => $targetIds, 'targetType' => $targetType, 'status' => 'published'],
            [],
            0,
            1
        );

        return empty($certificates) ? false : true;
    }

    protected function trans($key, $parameters = [])
    {
        return $this->container->get('translator')->trans($key, $parameters);
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
     * @return LessonService
     */
    protected function getLessonService()
    {
        return $this->biz->service('Course:LessonService');
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
        return in_array($item['itemType'], ['task', 'lesson']) && $item['isOptional'];
    }

    protected function getDiscountService()
    {
        return $this->biz->service('DiscountPlugin:Discount:DiscountService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->biz->service('Certificate:CertificateService');
    }

    protected function isPluginInstalled($name)
    {
        return $this->container->get('kernel')->getPluginConfigurationManager()->isPluginInstalled($name);
    }

    protected function createService($alias)
    {
        return $this->biz->service($alias);
    }
}
