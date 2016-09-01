<?php

namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\SmsToolkit;
use Topxia\Common\StringToolkit;

class SmsController extends BaseController
{
    public function prepareAction(Request $request, $targetType, $id)
    {
        $item                  = array();
        $verifiedMobileUserNum = 0;
        $url                   = '';
        $smsType               = 'sms_' . $targetType . '_publish';

        if ($targetType == 'classroom') {
            $item                  = $this->getClassroomService()->getClassroom($id);
            $verifiedMobileUserNum = $this->getUserService()->getUserCountByMobileNotEmpty();
            $url                   = $this->generateUrl('classroom_show', array('id' => $id));
        } elseif ($targetType == 'course') {
            $item = $this->getCourseService()->getCourse($id);
            $url  = $this->generateUrl('course_show', array('id' => $id));

            if ($item['parentId']) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($item['id']);

                if ($classroom) {
                    $verifiedMobileUserNum = $this->getClassroomService()->findMobileVerifiedMemberCountByClassroomId($classroom['classroomId'], 1);
                }
            } else {
                $verifiedMobileUserNum = $this->getUserService()->getUserCountByMobileNotEmpty();
            }
        }

        $item['title'] = StringToolkit::cutter($item['title'], 20, 15, 4);
        return $this->render('TopxiaWebBundle:Sms:sms-send.html.twig', array(
            'item'       => $item,
            'targetType' => $targetType,
            'url'        => $url,
            'count'      => $verifiedMobileUserNum,
            'index'      => 1,
            'isOpen'     => $this->getSmsService()->isOpen($smsType)
        ));
    }

    public function sendAction(Request $request, $targetType, $id)
    {
        $smsType     = 'sms_' . $targetType . '_publish';
        $index       = $request->query->get('index');
        $onceSendNum = 100;
        $url         = $request->query->get('url');
        $count       = $request->query->get('count');
        $parameters  = array();

        if ($targetType == 'classroom') {
            $classroom                     = $this->getClassroomService()->getClassroom($id);
            $classroomSetting              = $this->getSettingService()->get("classroom");
            $classroomName                 = isset($classroomSetting['name']) ? $classroomSetting['name'] : '班级';
            $classroom['title']            = StringToolkit::cutter($classroom['title'], 20, 15, 4);
            $parameters['classroom_title'] = $classroomName . '：《' . $classroom['title'] . '》';
            $description                   = $parameters['classroom_title'] . '发布';
            $profiles                      = $this->getUserService()->searchUserProfiles(array('mobile' => '1'), array('id', 'DESC'), 0, PHP_INT_MAX);
            $userIds                       = ArrayToolkit::column($profiles, 'id');
            $students                      = $this->getUserService()->searchUsers(array('locked' => 0, 'ids' => $userIds), array('createdTime', 'DESC'), $index, $onceSendNum);
        } elseif ($targetType == 'course') {
            $course                     = $this->getCourseService()->getCourse($id);
            $course['title']            = StringToolkit::cutter($course['title'], 20, 15, 4);
            $parameters['course_title'] = '课程：《' . $course['title'] . '》';
            $description                = $parameters['course_title'] . '发布';

            if ($course['parentId']) {
                $classroom = $this->getClassroomService()->findClassroomByCourseId($course['id']);

                if ($classroom) {
                    $count    = $this->getClassroomService()->searchMemberCount(array('classroomId' => $classroom['classroomId']));
                    $students = $this->getClassroomService()->searchMembers(array('classroomId' => $classroom['classroomId']), array('createdTime', 'Desc'), $index, $onceSendNum);
                }
            } else {
                $profiles = $this->getUserService()->searchUserProfiles(array('mobile' => '1'), array('id', 'DESC'), 0, PHP_INT_MAX);
                $userIds  = ArrayToolkit::column($profiles, 'id');
                $students = $this->getUserService()->searchUsers(array('locked' => 0, 'ids' => $userIds), array('createdTime', 'DESC'), $index, $onceSendNum);
            }
        }

        if (!$this->getSmsService()->isOpen($smsType)) {
            throw new \RuntimeException("请先开启相关设置!");
        }

        $parameters['url'] = $url . ' ';

        if (!empty($students)) {
            if ($targetType == 'course' && $course['parentId']) {
                $studentIds = ArrayToolkit::column($students, 'userId');
            } else {
                $studentIds = ArrayToolkit::column($students, 'id');
            }

            $result = $this->getSmsService()->smsSend($smsType, $studentIds, $description, $parameters);
        }

        if ($count > $index + $onceSendNum) {
            return $this->createJsonResponse(array('index' => $index + $onceSendNum, 'process' => intval(($index + $onceSendNum) / $count * 100)));
        } else {
            return $this->createJsonResponse(array('status' => 'success', 'process' => 100));
        }
    }

    public function changeLinkAction(Request $request)
    {
        $url = $request->getHost();
        $url .= $request->query->get('url');

        $shortUrl = SmsToolkit::getShortLink($url);
        $url      = empty($shortUrl) ? 'http://' . $url : $shortUrl;

        return $this->createJsonResponse(array('url' => $url . ' '));
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
