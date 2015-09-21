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
        $smsType = 'sms_'.$targetType.'_publish';
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
            if ($lesson['type'] == 'live') {
                $smsType = 'sms_live_'.$targetType.'_publish';
            } else {
                $smsType = 'sms_normal_'.$targetType.'_publish';
            }
            $item = $this->getCourseService()->getCourse($lesson['courseId']);
            $item['lesson_title'] = $lesson['title'];
            $item['id'] = $id;
            $url = $this->generateUrl('course_learn',array('id' => $lesson['courseId']));
            $url .= '#lesson/'.$lesson['id'];
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            if ($course['parentId'] ) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
                if ($classroom) {
                    $verifiedMobileUserNum = $this->getClassroomService()->searchMemberCount(array('classroomId' => $classroom['classroomId']));
                }
            } else {
                $verifiedMobileUserNum = $this->getCourseService()->getHasVerifiedMobileStudentsCountByCourseId($lesson['courseId']);
            }
        }

    return $this->render('TopxiaWebBundle:Sms:smsSend.html.twig',array(
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
            $parameters['classroom_title'] = '《'.$classroom['title'].'》';
            $description = $parameters['classroom_title'].'发布';
            $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,$onceSendNum);
        } elseif ($targetType == 'course') {
            $course = $this->getCourseService()->getCourse($id);
            $parameters['course_title'] = '《'.$course['title'].'》';
            $description = $parameters['course_title'].'发布';
            $students = $this->getUserService()->searchUsers(array('hasVerifiedMobile' => true),array('createdTime', 'DESC'),$index,$onceSendNum);
        } elseif ($targetType == 'lesson') {
            $lesson = $this->getCourseService()->getLesson($id);
            $parameters['lesson_title'] = '《'.$lesson['title'].'》';
            if ($lesson['type'] == 'live') {
                $smsType = 'sms_live_'.$targetType.'_publish';
                $parameters['startTime'] = date("Y-m-d h:i:s", $lesson['startTime']); 
            } else {
                $smsType = 'sms_normal_'.$targetType.'_publish';
            }
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            $parameters['course_title'] = '《'.$course['title'].'》';
            $description = $parameters['course_title'].' '.$parameters['lesson_title'].'发布';
            if ($course['parentId'] ) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);
                if ($classroom) {
                    $students = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['classroomId']), array('createdTime','Desc'),0,$count);
                }
            } else {
                $students = $this->getCourseService()->findCourseStudentsByCourseIds(array($lesson['courseId']));
            }
            $index = $count;
        }

        if ( !$this->getSmsService()->isOpen($smsType) ) {
            throw new \RuntimeException("请先开启相关设置!");
        }
        $parameters['url'] = $url;

        if (!empty($students)) {
            if ($targetType == 'lesson') {
                $studentIds = ArrayToolkit::column($students, 'userId');
            } else {
                $studentIds = ArrayToolkit::column($students, 'id');
            }
            $users = $this->getUserService()->findUsersByIds($studentIds);
            foreach ($users as $key => $value ) {
                if (strlen($value['verifiedMobile']) == 0) {
                    unset($users[$key]);
                }
            }
            if (!empty($users)) {
                $userIds = ArrayToolkit::column($users, 'id');
                $this->getSmsService()->smsSend($smsType, $userIds, $description, $parameters);
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
