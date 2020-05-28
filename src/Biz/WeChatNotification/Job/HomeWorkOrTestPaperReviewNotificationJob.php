<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Notification\WeChatTemplateMessage\TemplateUtil;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;

class HomeWorkOrTestPaperReviewNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $key = $this->args['key'];
        $templateId = $this->getWeChatService()->getTemplateId($key);

        if (empty($templateId)) {
            return;
        }

        $nums = $this->getSendUserIdAndNum();
        if (0 == count($nums)) {
            return;
        }

        $data = [
            'first' => ['value' => '尊敬的老师，您今日仍有作业/试卷未批改'],
            'keyword1' => ['value' => date('Y-m-d', time())],
            'keyword2' => ['value' => ''],
            'remark' => ['value' => '请及时批改'],
        ];

        $templateData = [];
        $templates = TemplateUtil::templates();
        $templateCode = isset($templates[$key]['id']) ? $templates[$key]['id'] : '';
        foreach ($nums as $num) {
            $data['keyword2'] = ['value' => $num['num']];
            $templateData[$num['userId']] = [
                'template_id' => $templateId,
                'template_code' => $templateCode,
                'template_args' => $data,
            ];
        }

        $this->sendNotifications($key, 'wechat_notify_lesson_publish', ArrayToolkit::column($nums, 'userId'), $templateData);
    }

    protected function getSendUserIdAndNum()
    {
        $endTime = strtotime(date('Y-m-d '.$this->args['sendTime'].':00'));
        $startTime = $endTime - 86400;

        $conditions = [
            'status' => 'reviewing',
            'beginTime_GT' => $startTime,
            'beginTime_ELT' => $endTime,
        ];

        $answerSceneGroups = ArrayToolkit::group(
            $this->getAnswerRecordService()->search($conditions, [], 0, $this->getAnswerRecordService()->count($conditions)),
            'answer_scene_id'
        );
        if (empty($answerSceneGroups)) {
            return [];
        }

        $activities = array_merge($this->getTestpaperActivities(array_keys($answerSceneGroups)), $this->getHomeworkActivities(array_keys($answerSceneGroups)));
        foreach ($activities as &$activity) {
            $activity['num'] = count($answerSceneGroups[$activity['answerSceneId']]);
        }

        $courses = ArrayToolkit::group($activities, 'fromCourseId');
        foreach ($courses as $courseId => &$course) {
            $course = [
                'courseId' => $courseId,
                'num' => array_sum(ArrayToolkit::column($course, 'num')),
            ];
        }

        $teachers = $this->getCourseService()->findTeachersByCourseIds(array_keys($courses));
        foreach ($teachers as &$teacher) {
            $teacher['num'] = $courses[$teacher['courseId']]['num'];
        }
        $teacherGroups = ArrayToolkit::group($teachers, 'userId');
        foreach ($teacherGroups as $userId => &$teacherGroup) {
            $teacherGroup = [
                'userId' => $userId,
                'num' => array_sum(ArrayToolkit::column($teacherGroup, 'num')),
            ];
        }

        return $teacherGroups;
    }

    protected function getHomeworkActivities($answerSceneIds)
    {
        $homeworkActivities = ArrayToolkit::index(
            $this->getHomeworkActivityService()->findByAnswerSceneIds($answerSceneIds),
            'id'
        );
        $activities = $this->getActivityService()->findActivitiesByMediaIdsAndMediaType(ArrayToolkit::column($homeworkActivities, 'id'), 'homework');
        foreach ($activities as &$activity) {
            $activity['answerSceneId'] = $homeworkActivities[$activity['mediaId']]['answerSceneId'];
        }

        return $activities;
    }

    protected function getTestpaperActivities($answerSceneIds)
    {
        $testpaperActivities = ArrayToolkit::index(
            $this->getTestpaperActivityService()->findByAnswerSceneIds($answerSceneIds),
            'id'
        );
        $activities = $this->getActivityService()->findActivitiesByMediaIdsAndMediaType(ArrayToolkit::column($testpaperActivities, 'id'), 'testpaper');
        foreach ($activities as &$activity) {
            $activity['answerSceneId'] = $testpaperActivities[$activity['mediaId']]['answerSceneId'];
        }

        return $activities;
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->biz->service('Activity:HomeworkActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->biz->service('Activity:TestpaperActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestPaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
