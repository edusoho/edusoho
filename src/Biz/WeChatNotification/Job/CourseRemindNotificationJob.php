<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\LearningDataAnalysisService;

class CourseRemindNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $key = $this->args['key'];
        $url = $this->args['url'];

        $templateId = $this->getWeChatService()->getTemplateId($key);
        if (empty($templateId)) {
            return;
        }

        $allBindUserIds = $this->getWeChatService()->findAllBindUserIds();
        $userIds = ArrayToolkit::column($allBindUserIds, 'userId');
        if (empty($userIds)) {
            return;
        }

        $courseMembers = $this->getCourseMemberService()->findLastLearnTimeRecordStudents($userIds);
        if (empty($courseMembers)) {
            return;
        }
        $userIds = ArrayToolkit::column($courseMembers, 'userId');

        $courseIds = ArrayToolkit::column($courseMembers, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $data = array(
            'first' => array('value' => '亲爱的学员，今日也要坚持学习哦'),
            'keyword1' => array('value' => ''),
            'keyword2' => array('value' => ''),
            'remark' => array('value' => '请前往查看'),
        );
        $templateData = array();
        $options = array('url' => $url, 'type' => 'url');
        foreach ($courseMembers as $courseMember) {
            if (empty($courseMember['courseId'])) {
                continue;
            }
            $courseTitle = !empty($courses[$courseMember['courseId']]['title']) ? $courses[$courseMember['courseId']]['title'] : $courses[$courseMember['courseId']]['courseSetTitle'];
            $courseCompulsoryTaskNum = isset($courses[$courseMember['courseId']]['compulsoryTaskNum']) ? $courses[$courseMember['courseId']]['compulsoryTaskNum'] : '0';
            $process = (0 == $courseCompulsoryTaskNum) ? 0 : $courseMember['learnedCompulsoryTaskNum'] ? round($courseMember['learnedCompulsoryTaskNum'] / $courseCompulsoryTaskNum, 2) * 100 : 0;
            $keyword2 = date('Y-m-d', time())."\r学习进度：".$process.'%';
            $data['keyword1'] = array('value' => empty($courseTitle) ? '' : '《'.$courseTitle.'》');
            $data['keyword2'] = array('value' => $keyword2);
            $templateData[$courseMember['userId']] = array(
                'template_id' => $templateId,
                'template_args' => $data,
                'goto' => $options,
            );
        }

        $this->sendNotifications($key, 'wechat_notify_course_remind', $userIds, $templateData);
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->biz->service('Course:LearningDataAnalysisService');
    }
}
