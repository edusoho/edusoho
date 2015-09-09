<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Common\CurlToolkit;
use Topxia\Component\Payment\Payment;
use Topxia\WebBundle\Util\AvatarAlert;
use Symfony\Component\HttpFoundation\Response;
use Topxia\WebBundle\Controller\BaseController;

class SmsController extends BaseController
{
    public function showAction(Request $request, $targetType, $id)
    {   
        $item = array();
        $verifiedMobileUserNum = 0;
        $url = '';
        if ($targetType == 'classroom') {
            $item = $this->getClassroomService()->getClassroom($id);
            $verifiedMobileUserNum = $this->getUserService()->findUserHasVerifiedMobileCount();
            $url = $this->generateUrl('classroom_show',array('id' => $id));
        } elseif ($targetType == 'course') {
            $item = $this->getCourseService()->getCourse($id);
            $verifiedMobileUserNum = $this->getUserService()->findUserHasVerifiedMobileCount();
            $url = $this->generateUrl('course_show',array('id' => $id));
        } elseif ($targetType == 'lesson') {
            $lesson = $this->getCourseService()->getLesson($id);
            $item = $this->getCourseService()->getCourse($lesson['courseId']);
            $item['lesson_title'] = $lesson['title'];
            $verifiedMobileUserNum = $this->getCourseService()->getHasVerifiedMobileStudentsCountByCourseId($lesson['courseId']);
            $url = $this->generateUrl('course_learn',array('id' => $lesson['courseId']));
            $url .= '#lesson/'.$lesson['id'];
        }

        return $this->render('TopxiaWebBundle:Sms:smsSend.html.twig',array(
            'item' => $item,
            'targetType' => $targetType,
            'url' => $url,
            'count' => $verifiedMobileUserNum,
            'index' => 1,
        ));
    }

    public function sendAction(Request $request, $targetType, $id)
    {
        $index = $request->query->get('index');
        $url = $request->query->get('url');
        $count = $request->query->get('count');
        $smsType = 'sms_'.$targetType.'publish';
        $parameters = array();
        if ($targetType == 'classroom') {
            $classroom = $this->getClassroomService()->getClassroom($id);
            $parameters['clasroom_title'] = '《'.$classroom['title'].'》';
            $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,1000);
        } elseif ($targetType == 'course') {
            $course = $this->getCourseService()->getCourse($id);
            $parameters['course_title'] = '《'.$course['title'].'》';
            $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,1000);
        } elseif ($targetType == 'lesson') {
            $lesson = $this->getCourseService()->getLesson($id);
            $parameters['lesson_title'] = '《'.$lesson['title'].'》';
            if ($lesson['type'] == 'live') {
                $smsType = 'sms_live'.$targetType.'publish';
                $parameters['startTime'] = date("Y-m-d h:i:s", $lesson['startTime']); 
            } else {
                $smsType = 'sms_normal'.$targetType.'publish';
            }
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            $parameters['course_title'] = '《'.$course['title'].'》';
            $students = $this->getCourseService()->findCourseStudentsByCourseIds(array($lesson['courseId']));
            $index = $count;
        }
        $parameters['url'] = $url;
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
            }

            if ( $this->getSmsService()->isOpen($smsType) ) {
                $this->getSmsService()->smsSend($smsType, $userIds, $parameters);
            }
        }

        if ($count > $index + 1000 ) {
            return $this->createJsonResponse(array('index' => $index + 1000, 'process' => intval(($index + 1000) / $count * 100)));
        }
        else {
            return $this->createJsonResponse(array('status' => 'success', 'process' => 100));
        }
    }

    public function changeLinkAction(Request $request)
    {
        $url = $request->getHost();
        $url .= $request->query->get('url');
        $arrResponse = json_decode(CurlToolkit::postRequest("http://dwz.cn/create.php",array('url' => $url)),true);
        if ($arrResponse['status'] != 0) {
            throw new \RuntimeException("短链接生成失败!");
        }
        $shortUrl = $arrResponse['tinyurl'];

        return $this->createJsonResponse(array('url' => $shortUrl));
    }

    protected function getSettingService()
    {
      return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassroomService() 
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}
