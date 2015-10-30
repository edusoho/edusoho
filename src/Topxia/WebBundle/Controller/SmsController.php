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
    public function prepareAction(Request $request, $targetType, $id)
    {   
        $item = array();
        $verifiedMobileUserNum = 0;
        $url = '';
        $smsType = 'sms_'.$targetType.'_publish';
        if ($targetType == 'classroom') {
            $item = $this->getClassroomService()->getClassroom($id);
            $verifiedMobileUserNum = $this->getUserService()->searchUserCount(array('hasVerifiedMobile' => true, 'locked' => 0));
            $url = $this->generateUrl('classroom_show',array('id' => $id));
        } elseif ($targetType == 'course') {
            $item = $this->getCourseService()->getCourse($id);
            $url = $this->generateUrl('course_show',array('id' => $id));
            if ($item['parentId'] ) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($item['id']);
                if ($classroom) {
                    $verifiedMobileUserNum = $this->getClassroomService()->findMobileVerifiedMemberCountByClassroomId($classroom['classroomId'],1);
                }
            } else {
                $verifiedMobileUserNum = $this->getUserService()->searchUserCount(array('hasVerifiedMobile' => true, 'locked' => 0));
            }
        }
        $item['title'] = StringToolkit::cutter($item['title'], 20, 15, 4);
        return $this->render('TopxiaWebBundle:Sms:sms-send.html.twig',array(
            'item' => $item,
            'targetType' => $targetType,
            'url' => $url,
            'count' => $verifiedMobileUserNum,
            'index' => 1,
            'isOpen' => $this->getSmsService()->isOpen($smsType),
        ));
    }

    public function sendAction(Request $request, $targetType, $id)
    {
        $smsType = 'sms_'.$targetType.'_publish';
        $index = $request->query->get('index');
        $onceSendNum = 1000;
        $url = $request->query->get('url');
        $count = $request->query->get('count');
        $parameters = array();
        if ($targetType == 'classroom') {
            $classroom = $this->getClassroomService()->getClassroom($id);
            $classroomSetting  = $this->getSettingService()->get("classroom");
            $classroomName = isset($classroomSetting['name'])?$classroomSetting['name']:'班级';
            $classroom['title'] = StringToolkit::cutter($classroom['title'], 20, 15, 4);
            $parameters['classroom_title'] = $classroomName.'：《'.$classroom['title'].'》';
            $description = $parameters['classroom_title'].'发布';
            $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,$onceSendNum);
        } elseif ($targetType == 'course') {
            $course = $this->getCourseService()->getCourse($id);
            $course['title'] = StringToolkit::cutter($course['title'], 20, 15, 4);
            $parameters['course_title'] = '课程：《'.$course['title'].'》';
            $description = $parameters['course_title'].'发布';
            if ($course['parentId'] ) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
                if ($classroom) {
                    $count = $this->getClassroomService()->searchMemberCount(array('classroomId' => $classroom['classroomId']));
                    $students = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['classroomId']), array('createdTime','Desc'), $index, $onceSendNum);
                }
            } else {
                $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,$onceSendNum);
            }
        }

        if ( !$this->getSmsService()->isOpen($smsType) ) {
            throw new \RuntimeException("请先开启相关设置!");
        }
        $parameters['url'] = $url.' ';
        if (!empty($students)) {
            if ($targetType == 'course' && $course['parentId']) {
                $studentIds = ArrayToolkit::column($students, 'userId');
            } else {
                $studentIds = ArrayToolkit::column($students, 'id');
            }
            $users = $this->getUserService()->findUsersByIds($studentIds);
            foreach ($users as $key => $value ) {
                if (strlen($value['verifiedMobile']) == 0 || $value['locked']) {
                    unset($users[$key]);
                }
            }
            if (!empty($users)) {
                $userIds = ArrayToolkit::column($users, 'id');
                $result = $this->getSmsService()->smsSend($smsType, $userIds, $description, $parameters);
            }
        }

        if ($count > $index + $onceSendNum ) {
            return $this->createJsonResponse(array('index' => $index + $onceSendNum, 'process' => intval(($index + $onceSendNum) / $count * 100)));
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
        $shortUrl = $arrResponse['tinyurl'].' ';

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
