<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;

class TestpaperController extends BaseController
{
    public function checkListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $status = $request->query->get('status', 'reviewing');
        $keywordType = $request->query->get('keywordType', 'nickname');
        $keyword = $request->query->get('keyword', '');

        $teacherCourses = $this->getCourseMemberService()->findTeacherMembersByUserId($user['id']);
        $courseIds = ArrayToolkit::column($teacherCourses, 'courseId');
        if (!empty($courseIds) && 'courseTitle' == $keywordType) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keyword);
            $courseSetIds = ArrayToolkit::column($courseSets, 'id');
            $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
            $courseIds = array_values(array_intersect($courseIds, ArrayToolkit::column($courses, 'id')));
        }

        $activities = ArrayToolkit::index(
            $this->getActivityService()->search(array('courseIds' => empty($courseIds) ? array(-1) : $courseIds, 'mediaType' => 'testpaper'), array(), 0, PHP_INT_MAX),
            'mediaId'
        );
        $testpeaperActivities = ArrayToolkit::index(
            $this->getTestpaperActivityService()->findActivitiesByIds(array_keys($activities)),
            'answerSceneId'
        );

        $conditions = array(
            'answer_scene_ids' => empty(array_keys($testpeaperActivities)) ? array(-1) : array_keys($testpeaperActivities),
            'status' => $status,
        );

        if ('nickname' == $keywordType && $keyword) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['user_id'] = $searchUser ? $searchUser['id'] : '-1';
        }

        if ($status == 'finished') {
            $answerRecordIds = ArrayToolkit::column(
                $this->getAnswerReportService()->search(array('review_user_id' => $user['id']), array(), 0, PHP_INT_MAX),
                'answer_record_id'
            );
            $conditions['ids'] = empty($answerRecordIds) ? array(-1) : $answerRecordIds;
        }

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        
        $orderBy = $status == 'reviewing' ? array('end_time' => 'ASC') : array('updated_time' => 'DESC');
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $answerReports = ArrayToolkit::index(
            $this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')),
            'id'
        );

        $userIds = ArrayToolkit::column($answerRecords, 'user_id');
        $userIds = array_merge($userIds, ArrayToolkit::column($answerReports, 'review_user_id'));
        $users = $this->getUserService()->findUsersByIds($userIds);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(ArrayToolkit::column($activities, 'fromCourseId'));
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id'));

        return $this->render('my/testpaper/check-list.html.twig', array(
            'answerRecords' => $answerRecords,
            'answerReports' => $answerReports,
            'paginator' => $paginator,
            'courses' => $courses,
            'users' => $users,
            'status' => $status,
            'assessments' => $assessments,
            'keywordType' => $keywordType,
            'keyword' => $keyword,
            'activities' => $activities,
            'testpeaperActivities' => $testpeaperActivities,
        ));
    }

    public function listAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $courseIds = ArrayToolkit::column(
            $this->getCourseMemberService()->searchMembers(array('userId' => $user['id']), array(), 0, PHP_INT_MAX),
            'courseId'
        );
        if (empty($courseIds)) {
            return $this->render('my/testpaper/my-testpaper-list.html.twig', array(
                'answerRecords' => array(),
                'nav' => 'testpaper',
            ));
        }
        $activities = ArrayToolkit::index(
            $this->getActivityService()->search(array('courseIds' => $courseIds, 'mediaType' => 'testpaper'), array(), 0, PHP_INT_MAX),
            'mediaId'
        );
        $testpeaperActivities = ArrayToolkit::index(
            $this->getTestpaperActivityService()->findActivitiesByIds(array_keys($activities)),
            'answerSceneId'
        );
        if (empty(array_keys($testpeaperActivities))) {
            return $this->render('my/testpaper/my-testpaper-list.html.twig', array(
                'answerRecords' => array(),
                'nav' => 'testpaper',
            ));
        }

        $conditions = array(
            'answer_scene_ids' => array_keys($testpeaperActivities),
            'user_id' => $user['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            array('begin_time' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if (empty($answerRecords)) {
            return $this->render('my/testpaper/my-testpaper-list.html.twig', array(
                'answerRecords' => array(),
                'nav' => 'testpaper',
            ));
        }

        $answerReports = ArrayToolkit::index(
            $this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')),
            'id'
        );
        $tasks = ArrayToolkit::index(
            $this->getTaskService()->findTasksByActivityIds(ArrayToolkit::column($activities, 'id')),
            'activityId'
        );
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $assessments = $this->getAssessmentService()->findAssessmentsByIds(ArrayToolkit::column($answerRecords, 'assessment_id'));

        return $this->render('my/testpaper/my-testpaper-list.html.twig', array(
            'testpeaperActivities' => $testpeaperActivities,
            'answerReports' => $answerReports,
            'answerRecords' => $answerRecords,
            'paginator' => $paginator,
            'courses' => $courses,
            'assessments' => $assessments,
            'tasks' => $tasks,
            'activities' => $activities,
            'nav' => 'testpaper',
        ));
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getBiz()->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerReportService
     */
    protected function getAnswerReportService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerReportService');
    }
}
