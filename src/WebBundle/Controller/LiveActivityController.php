<?php

namespace WebBundle\Controller;

use Topxia\Service\Common\ServiceKernel;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;

class LiveActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
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
        return $this->render('WebBundle:LiveActivity:show.html.twig', array(
            'activity' => $activity
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id);
        // var_dump($this->formatTimeFields($activity));exit();
        return $this->render('WebBundle:LiveActivity:modal.html.twig', array(
            'activity' => $this->formatTimeFields($activity)
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:LiveActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    public function liveEntryAction(Request $request, $courseId, $id)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你好像忘了登录哦？'), null, 3000, $this->generateUrl('login'));
        }

        $activity = $this->getActivityService()->getActivity($id);

        if (empty($activity)) {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('直播任务不存在！'));
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

        if ($this->getCourseService()->isCourseTeacher($courseId, $user['id'])) {
            $teachers = $this->getCourseService()->findCourseTeachers($courseId);
            $teacher  = array_shift($teachers);

            if ($teacher['userId'] == $user['id']) {
                $params['role'] = 'teacher';
            } else {
                $params['role'] = 'speaker';
            }
        } elseif ($this->getCourseService()->isCourseStudent($courseId, $user['id'])) {
            $params['role'] = 'student';
        } else {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('您不是课程学员，不能参加直播！'));
        }

        $params['id']       = $user['id'];
        $params['nickname'] = $user['nickname'];
        return $this->forward('TopxiaWebBundle:Liveroom:_entry', array('id' => $activity['ext']['liveId']), $params);
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    //int to datetime
    protected function formatTimeFields($fields)
    {
        $format = 'Y-m-d H:i';
        if (isset($fields['startTime'])) {
            $fields['startTime'] = date($format, $fields['startTime']);
        }
        if (isset($fields['endTime'])) {
            $fields['endTime'] = date($format, $fields['endTime']);
        }

        return $fields;
    }
}
