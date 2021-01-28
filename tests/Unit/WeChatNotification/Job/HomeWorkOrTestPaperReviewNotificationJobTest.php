<?php

namespace Tests\Unit\WeChatNotification\Job;

use Biz\BaseTestCase;
use Biz\WeChatNotification\Job\HomeWorkOrTestPaperReviewNotificationJob;

class HomeWorkOrTestPaperReviewNotificationJobTest extends BaseTestCase
{
    public function testExecute()
    {
        $this->mockBiz('WeChat:WeChatService', array(
            array('functionName' => 'getTemplateId', 'returnValue' => 2),
            array('functionName' => 'findSubscribedUsersByUserIdsAndType', 'returnValue' => array(array('openId' => 123))),
            array('functionName' => 'getWeChatSendChannel', 'returnValue' => 'wechat'),
        ));
        $this->mockBiz('Testpaper:TestpaperService', array(
            array('functionName' => 'searchTestpaperResultsCountJoinCourseMemberGroupByUserId', 'returnValue' => array(array('userId' => 1, 'num' => 1))),
        ));

        $job = new HomeWorkOrTestPaperReviewNotificationJob(array(), $this->biz);
        $job->args = array('key' => 1, 'sendTime' => '00:00', 'url' => '');
        $result = $job->execute();

        $this->assertEmpty($result);
    }
}
