<?php

namespace Biz\WeChatNotification\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Testpaper\Service\TestpaperService;

class HomeWorkOrTestPaperReviewNotificationJob extends AbstractNotificationJob
{
    public function execute()
    {
        $key = $this->args['key'];
        $sendTime = $this->args['sendTime'];
        $templateId = $this->getWeChatService()->getTemplateId($key);

        if (empty($templateId)) {
            return;
        }

        $date = date('Y-m-d', time());
        $sendTime = strtotime($date.$sendTime.':00');
        $conditions = array(
            'beginTime_LE' => $sendTime,
            'beginTime_GE' => $sendTime - 24 * 60 * 60,
            'role' => 'teacher',
            'types' => array('homework', 'testpaper'),
            'status' => 'reviewing',
        );
        $nums = $this->getTestPaperService()->searchTestpaperResultsCountJoinCourseMemberGroupByUserId($conditions);

        $userIds = ArrayToolkit::column($nums, 'userId');
        if (0 == count($nums)) {
            return;
        }

        $data = array(
            'first' => array('value' => '尊敬的老师，您今日仍有作业/试卷未批改'.PHP_EOL),
            'keyword1' => array('value' => date('Y-m-d', time())),
            'keyword2' => array('value' => ''),
            'remark' => array('value' => '请及时批改'),
        );

        $templateData = array();
        foreach ($nums as $num) {
            $data['keyword2'] = array('value' => $num['num']);
            $options = array('type' => 'miniprogram');
            $templateData[$num['userId']] = array(
                'template_id' => $templateId,
                'template_args' => $data,
                'goto' => $options,
            );
        }

        $this->sendNotifications($key, 'wechat_notify_lesson_publish', $userIds, $templateData, 0);
    }

    /**
     * @return TestpaperService
     */
    protected function getTestPaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }
}
