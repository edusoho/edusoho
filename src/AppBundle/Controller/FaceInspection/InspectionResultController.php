<?php

namespace AppBundle\Controller\FaceInspection;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\FaceInspection\Service\FaceInspectionService;
use Biz\System\Service\Impl\SettingServiceImpl;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Symfony\Component\HttpFoundation\Request;

class InspectionResultController extends BaseController
{
    public function indexAction(Request $request, $activityId)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }
        $activity = $this->getActivityService()->getActivity($activityId, true);
        $this->getCourseService()->tryManageCourse($activity['fromCourseId']);

        $members = $this->getCourseMemberService()->searchMembers(['role' => 'student', 'courseId' => $activity['fromCourseId']], [], 0, PHP_INT_MAX, ['userId']);
        $courseMemberUserIds = empty($members) ? [-1] : ArrayToolkit::column($members, 'userId');

        $answerSceneId = empty($activity['ext']['answerSceneId']) ? 0 : $activity['ext']['answerSceneId'];
        $answerRecords = $this->getAnswerRecordService()->search(
            ['answer_scene_id' => $answerSceneId, 'user_ids' => $courseMemberUserIds],
            [],
            0,
            PHP_INT_MAX,
            ['id', 'user_id']
        );
        $recordIds = empty($answerRecords) ? [-1] : ArrayToolkit::column($answerRecords, 'id');
        $recordUserIds = ArrayToolkit::column($answerRecords, 'user_id');

        $inspectionResults = $this->getSDKFaceInspectionService()->searchRecord(['answer_record_ids' => $recordIds], [], 0, PHP_INT_MAX, ['user_id']);
        $userIds = ArrayToolkit::column($inspectionResults, 'user_id');
        $conditions['user_ids'] = empty($userIds) ? [-1] : $userIds;

        $paginator = new Paginator(
            $request,
            $this->getFaceInspectionService()->countUserFaces($conditions),
            24
        );
        $uerFaces = $this->getFaceInspectionService()->searchUserFaces(
            $conditions,
            ['id' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $recordUserIds = array_unique($recordUserIds);
        $userIds = array_unique($userIds);

        return $this->render('face-inspection/assessment-result.html.twig', [
            'activityId' => $activityId,
            'userFaces' => $uerFaces,
            'paginator' => $paginator,
            'memberCount' => count($courseMemberUserIds),
            'recordUserCount' => count($recordUserIds),
            'InspectionUserCount' => count($userIds),
            'noCheatingUserCount' => count($recordUserIds) - count($userIds),
            'courseId' => $activity['fromCourseId'],
        ]);
    }

    public function detailAction(Request $request, $userId, $activityId)
    {
        if (!$this->getCurrentUser()->isLogin()) {
            return $this->redirect($this->generateUrl('login'));
        }
        $activity = $this->getActivityService()->getActivity($activityId, true);
        $this->getCourseService()->tryManageCourse($activity['fromCourseId']);

        $answerSceneId = empty($activity['ext']['answerSceneId']) ? 0 : $activity['ext']['answerSceneId'];
        $answerRecords = $this->getAnswerRecordService()->search(['user_id' => $userId, 'answer_scene_id' => $answerSceneId], [], 0, PHP_INT_MAX, ['id', 'user_id']);
        $recordIds = empty($answerRecords) ? [-1] : ArrayToolkit::column($answerRecords, 'id');
        $paginator = new Paginator(
            $request,
            $this->getSDKFaceInspectionService()->countRecord(['answer_record_ids' => $recordIds]),
            6
        );
        $inspectionResults = $this->getSDKFaceInspectionService()->searchRecord(
            ['answer_record_ids' => $recordIds],
            ['created_time' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('face-inspection/assessment-detail-modal.html.twig', [
            'inspectionResults' => $inspectionResults,
            'paginator' => $paginator,
        ]);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return SettingServiceImpl
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return FaceInspectionService
     */
    protected function getFaceInspectionService()
    {
        return $this->createService('FaceInspection:FaceInspectionService');
    }

    /**
     * @return \Codeages\Biz\ItemBank\FaceInspection\Service\FaceInspectionService
     */
    protected function getSDKFaceInspectionService()
    {
        return $this->createService('ItemBank:FaceInspection:FaceInspectionService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }
}
