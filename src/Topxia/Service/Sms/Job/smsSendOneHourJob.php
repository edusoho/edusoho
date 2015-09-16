<?php
namespace Topxia\Service\Sms\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class smsSendOneHourJob implements Job
{
	public function execute($params)
    {
        /*$hourSmsType = 'sms_live_play_one_hour';
        $hourIsOpen = $this->getSmsService()->isOpen($hourSmsType);
        $parameters = array();
        if ($hourIsOpen) {
            $targetType = $params['targetType'];
            $targetId = $params['targetId'];
            if ($targetType == 'lesson') {
                $lesson = $this->getCourseService()->getLesson($targetId);
                $parameters['lesson_title'] = '《'.$lesson['title'].'》';
                $parameters['startTime'] = date("Y-m-d h:i:s", $lesson['startTime'])
                $course = $this->getCourseService()->getCourse($lesson['courseId']);
                $parameters['course_title'] = '《'.$course['title'].'》';
                $description = $parameters['course_title'].' '.$parameters['lesson_title'].'预告';
                if ($course['parentId'] ) {
                    $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
                    if ($classroom) {
                        $count = $this->getClassroomService()->searchMemberCount(array('classroomId' => $classroom['classroomId']));
                        $students = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['classroomId']), array('createdTime','Desc'),0,$count);
                    }
                } else {
                    $students = $this->getCourseService()->findCourseStudentsByCourseIds(array($lesson['courseId']));
                }

                if (!empty($students)) {
                    $studentIds = ArrayToolkit::column($students, 'userId');
                    $users = $this->getUserService()->findUsersByIds($studentIds);
                    $to = '';
                    foreach ($users as $key => $value ) {
                        if (empty($value['verifiedMobile'])) {
                            unset($users[$key]);
                        }
                    }
                    if (!empty($users)) {
                        $userIds = ArrayToolkit::column($users, 'userId');
                        $this->getSmsService()->smsSend($hourSmsType, $userIds, $description, $parameters);
                    }

                }
            }
        }*/

    	$smsType = 'sms_live_play_one_hour';
        $dayIsOpen = $this->getSmsService()->isOpen($smsType);
        $parameters = array();
        if ($dayIsOpen) {
            $targetType = $params['targetType'];
            $targetId = $params['targetId'];
            $processor = SmsProcessorFactory::create($targetType);
            $return = $processor->getUrls($targetId, $smsType);
            $callbackUrls = $return['urls'];
            $count = $return['count'];
            try {
                    $api = CloudAPIFactory::create('leaf');
                    $result = $api->post("/sms/sendBatch", array('total' => $count, 'callbackUrls' => $callbackUrls));
                } catch (\RuntimeException $e) {
                    throw new RuntimeException("发送失败！");
            }   
        }
    }

    protected function getSmsService()
    {
        return ServiceKernel::instance()->createService('Sms.SmsService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}
