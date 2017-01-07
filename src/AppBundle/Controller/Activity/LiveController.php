<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class LiveController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true) ;
        $format   = 'Y-m-d H:i';
        if (isset($activity['startTime'])) {
            $activity['startTimeFormat'] = date($format, $activity['startTime']);
        }
        if (isset($activity['endTime'])) {
            $activity['endTimeFormat'] = date($format, $activity['endTime']);
        }
        $activity['nowDate'] = time();
        //FIXME 应当判断是否是当前任务的teacher
        $activity['isTeacher'] = $this->getUser()->isTeacher();
        $summary               = $activity['remark'];
        unset($activity['remark']);
        return $this->render('activity/live/show.html.twig', array(
            'activity' => $activity,
            'summary'  => $summary
        ));
    }

    public function previewAction(Request $request, $task)
    {
        return $this->render('activity/text/preview.html.twig');
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        return $this->render('activity/live/modal.html.twig', array(
            'activity' => $this->formatTimeFields($activity)
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/live/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    public function liveEntryAction(Request $request, $courseId, $activityId)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你好像忘了登录哦？'), null, 3000, $this->generateUrl('login'));
        }

        $activity = $this->getActivityService()->getActivity($activityId, $fetchMedia = true);

        if (empty($activity)) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播任务不存在！'));
        }
        if ($activity['fromCourseId'] != $courseId) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('参数非法！'));
        }

        if (empty($activity['ext']['liveId'])) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播教室不存在！'));
        }

        if ($activity['startTime'] - time() > 7200) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播还没开始!'));
        }

        if ($activity['endTime'] < time()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播已结束!'));
        }

        $params = array();
        if ($this->getCourseMemberService()->isCourseTeacher($courseId, $user['id'])) {
            $teachers = $this->getCourseService()->findTeachersByCourseId($courseId);
            $teacher  = array_shift($teachers);

            if ($teacher['userId'] == $user['id']) {
                $params['role'] = 'teacher';
            } else {
                $params['role'] = 'speaker';
            }
        } elseif ($this->getCourseMemberService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('您不是课程学员，不能参加直播！'));
        }

        $params['id']       = $user['id'];
        $params['nickname'] = $user['nickname'];
        return $this->forward('AppBundle:Liveroom:_entry', array(
            'roomId'     => $activity['ext']['liveId'],
            'courseId'   => $courseId,
            'activityId' => $activityId
        ), $params);
    }

    public function triggerAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $user = $this->getUser();

        $activity = $this->getActivityService()->getActivity($activityId);
        if ($activity['mediaType'] !== 'live') {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_live'));
        }
        $now = time();
        if ($activity['startTime'] > $now) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'not_start'));
        }

        // $eventName = $request->query->get('eventName', 'doing');
        // $this->getActivityService()->trigger($activityId, $eventName, array());

        //当前业务逻辑：看过即视为完成
        $this->getActivityService()->trigger($activityId, 'finish', array());

        if ($activity['endTime'] < $now) {
            return $this->createJsonResponse(array('success' => true, 'status' => 'live_end'));
        }

        return $this->createJsonResponse(array('success' => true, 'status' => 'on_live'));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    //int to datetime
    protected function formatTimeFields($fields)
    {
        $format = 'Y-m-d H:i';
        if (isset($fields['startTime'])) {
            if ($fields['startTime'] <= time()) {
                $fields['timeDisabled'] = 1;
            }
            $fields['startTime'] = date($format, $fields['startTime']);
        }
        if (isset($fields['endTime'])) {
            $fields['endTime'] = date($format, $fields['endTime']);
        }

        return $fields;
    }
}
