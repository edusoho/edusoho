<?php
namespace WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\ResourceNotFoundException;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $homeworkId)
    {
        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework)) {
            throw new ResourceNotFoundException('homework', $homeworkId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($homework['courseId']);

        $result = $this->getTestpaperService()->startTestpaper($homeworkId, $homework['lessonId']);

        if ($result['status'] == 'doing') {
            return $this->redirect($this->generateUrl('homework_show', array(
                'resultId' => $result['id']
            )));
        } else {
            return $this->redirect($this->generateUrl('homework_result_show', array(
                'resultId' => $result['id']
            )));
        }
    }

    public function doTestAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$result) {
            throw new ResourceNotFoundException('homeworkResult', $resultId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($result['courseId']);

        $homework = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$homework) {
            throw new ResourceNotFoundException('homework', $result['testId']);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($result['id']);

        return $this->render('WebBundle:Homework:do.html.twig', array(
            'paper'       => $homework,
            'questions'   => $questions,
            'course'      => $course,
            'paperResult' => $result,
            'showTypeBar' => 0,
            'showHeader'  => 0
        ));
    }

    public function showResultAction(Request $request, $resultId)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $homework = $this->getTestpaperService()->getTestpaper($homeworkResult['testId']);

        if (!$homework) {
            throw $this->createResourceNotFoundException('homework', $homeworkResult['testId']);
        }

        if (in_array($homeworkResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        $canLookHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);

        if (!$canLookHomework) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权查看作业！'));
        }

        $builder   = $this->getTestpaperService()->getTestpaperBuilder($homework['type']);
        $questions = $builder->showTestItems($homeworkResult['id']);

        $student = $this->getUserService()->getUser($homeworkResult['userId']);

        $attachments = $this->findAttachments($homework['id']);
        return $this->render('WebBundle:Homework:do.html.twig', array(
            'questions'   => $questions,
            'paper'       => $homework,
            'paperResult' => $homeworkResult,
            'student'     => $student,
            'attachments' => $attachments
        ));
    }

    public function getItemSetByHomeworkId($homeworkId)
    {
        $items       = $this->getTestpaperService()->findItemsByTestId($homeworkId);
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $questions   = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));

        $validQuestionIds = array();

        foreach ($indexdItems as $index => $item) {
            $item['question'] = empty($questions[$item['questionId']]) ? null : $questions[$item['questionId']];

            if (empty($item['parentId'])) {
                $indexdItems[$index] = $item;
                continue;
            }

            if (empty($indexdItems[$item['parentId']]['subItems'])) {
                $indexdItems[$item['parentId']]['subItems'] = array();
            }

            $indexdItems[$item['parentId']]['subItems'][] = $item;
            unset($indexdItems[$item['questionId']]);
        }

        $set = array(
            'items'       => ArrayToolkit::index($indexdItems, 'questionId'),
            'questionIds' => array(),
            'total'       => 0
        );

        foreach ($set['items'] as $item) {
            if (!empty($item['subItems'])) {
                $set['total'] += count($item['subItems']);
                $set['questionIds'] = array_merge($set['questionIds'], ArrayToolkit::column($item['subItems'], 'questionId'));
            } else {
                $set['total']++;
                $set['questionIds'][] = $item['questionId'];
            }
        }

        return $set;
    }

    public function submitAction(Request $request, $courseId, $homeworkResultId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();

            //$homeworkResult = $this->getHomeworkService()->getResult($homeworkResultId);
            $homeworkResult = $this->getHomeworkService()->submitHomework($homeworkResultId, $data);

            if (!empty($homeworkResult) && !empty($homeworkResult['lessonId'])) {
                return $this->createJsonResponse(array(
                    'courseId'         => $courseId,
                    'lessonId'         => $homeworkResult['lessonId'],
                    'homeworkId'       => $homeworkResult['homeworkId'],
                    'homeworkResultId' => $homeworkResult['id'],
                    'userId'           => $homeworkResult['userId']
                ));
            }
        }
    }

    public function saveAction(Request $request, $courseId, $homeworkResultId)
    {
        if ($request->getMethod() == 'POST') {
            $data           = $request->request->all();
            $answers        = !empty($data['data']) ? $data['data'] : array();
            $homeworkResult = $this->getHomeworkService()->saveHomework($homeworkResultId, $answers);

            if ($homeworkResult && !empty($homeworkResult['lessonId'])) {
                return $this->createJsonResponse(array('courseId' => $courseId, 'lessonId' => $homeworkResult['lessonId']));
            }
        }
    }

    public function continueAction(Request $request, $courseId, $homeworkId, $resultId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $homework              = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'], $this->getCurrentuser()->id, $resultId);
        return $this->render('HomeworkBundle:CourseHomework:do.html.twig', array(
            'homework'         => $homework,
            'itemSetResult'    => $itemSetResult,
            'course'           => $course,
            'lesson'           => $lesson,
            'homeworkResultId' => $resultId,
            'questionStatus'   => 'doing'
        ));
    }

    public function resultAction(Request $request, $courseId, $homeworkId, $resultId, $userId)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('您尚未登录用户，请登录后再查看！');
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createNotFoundException('此课程不存在或者已删除！');
        }

        $canLookHomeworkResult = $this->getHomeworkService()->canLookHomeworkResult($resultId);

        if (!$canLookHomeworkResult) {
            throw $this->createAccessDeniedException('无权查看作业！');
        }

        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSetResult  = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'], $userId, $resultId);
        $homeworkResult = $this->getHomeworkService()->getResult($resultId);

        return $this->render('HomeworkBundle:CourseHomework:result.html.twig', array(
            'homework'         => $homework,
            'itemSetResult'    => $itemSetResult,
            'course'           => $course,
            'lesson'           => $lesson,
            'teacherSay'       => $homeworkResult['teacherSay'],
            'userId'           => $homeworkResult['userId'],
            'questionStatus'   => $homeworkResult['status'],
            'passedStatus'     => $homeworkResult['passedStatus'],
            'homeworkResultId' => $homeworkResult['id']
        ));
    }

    public function checkShowAction(Request $request, $courseId, $homeworkResultId, $userId)
    {
        return $this->render('HomeworkBundle:CourseHomework:check-modal.html.twig', array(
            'courseId'         => $courseId,
            //'homeworkId' => $homeworkId,
            'userId'           => $userId,
            'homeworkResultId' => $homeworkResultId,
            'targetId'         => $request->query->get('targetId'),
            'source'           => $request->query->get('source', 'course')
        ));
    }

    public function checkListAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $status = $request->query->get('status', 'all');
        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('courseId' => $courseId);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchHomeworkCount($conditions)
            , 10
        );

        $homeworks = $this->getHomeworkService()->searchHomeworks(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessonIds = ArrayToolkit::column($homeworks, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);

        foreach ($homeworks as $key => $homework) {
            $homeworks[$key]['name'] = $lessons[$homework['lessonId']] ? '课时《'.$lessons[$homework['lessonId']]['title'].'》的作业' : '课时作业';
        }

        $user      = $this->getCurrentUser();
        $isTeacher = $this->getCourseService()->hasTeacherRole($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('HomeworkBundle:CourseHomework:check-list.html.twig', array(
            'status'    => $status,
            'homeworks' => $homeworks,
            'course'    => $course,
            'lessons'   => $lessons,
            'paginator' => $paginator,
            'isTeacher' => $isTeacher
        ));
    }

    public function resultListAction(Request $request, $id, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $user   = $this->getCurrentUser();

        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        $status  = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('homeworkId' => $homework['id']);
        if ($status != 'all') {
            $conditions['status'] = $status;
        }

        if (!empty($keyword)) {
            $searchUser           = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $paginator = new Paginator(
            $request,
            $this->getHomeworkService()->searchResultsCount($conditions),
            10
        );

        $HomeworkResults = $this->getHomeworkService()->searchResults(
            $conditions,
            $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge($course['teacherIds'], ArrayToolkit::column($HomeworkResults, 'userId'));
        $users   = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('HomeworkBundle:CourseHomework:homework-result-list.html.twig', array(
            'course'       => $course,
            'homework'     => $homework,
            'status'       => $status,
            'paperResults' => $HomeworkResults,
            'paginator'    => $paginator,
            'users'        => $users,
            'isTeacher'    => $this->getCourseService()->hasTeacherRole($id, $user['id']) || $user->isSuperAdmin()
        ));
    }

    public function checkAction(Request $request, $courseId, $homeworkResultId, $userId)
    {
        $homeworkResult = $this->getHomeworkService()->getResult($homeworkResultId);
        $homeworkId     = $homeworkResult['homeworkId'];

        $canCheckHomework = $this->getHomeworkService()->canCheckHomework($homeworkId);

        if (!$canCheckHomework) {
            throw $this->createAccessDeniedException('无权批改作业！');
        }

        $course   = $this->getCourseService()->getCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homeworkResult['status'] != 'reviewing') {
            return $this->createMessageResponse('warning', '作业已批阅或者未做完!');
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        if ($request->getMethod() == 'POST') {
            $checkHomeworkData = $request->request->all();
            $checkHomeworkData = empty($checkHomeworkData['data']) ? "" : $checkHomeworkData['data'];
            $this->getHomeworkService()->checkHomework($homeworkResultId, $userId, $checkHomeworkData);

            return $this->createJsonResponse(
                array(
                    'courseId' => $courseId,
                    'lessonId' => $homework['lessonId']
                )
            );
        }

        $itemSetResult = $this->getHomeworkService()->getItemSetResultByHomeworkIdAndUserId($homework['id'], $userId, $homeworkResult['id']);

        return $this->render('HomeworkBundle:CourseHomework:check.html.twig', array(
            'homework'         => $homework,
            'itemSetResult'    => $itemSetResult,
            'course'           => $course,
            'lesson'           => $lesson,
            'userId'           => $userId,
            'questionStatus'   => 'reviewing',
            'homeworkResultId' => $homeworkResultId,
            'targetId'         => $request->query->get('targetId'),
            'source'           => $request->query->get('source', 'course'),
            'canCheckHomework' => $canCheckHomework

        ));
    }

    public function previewAction(Request $request, $courseId, $homeworkId)
    {
        $course   = $this->getCourseService()->tryManageCourse($courseId);
        $homework = $this->getHomeworkService()->getHomework($homeworkId);

        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        if ($homework['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($homework['courseId'], $homework['lessonId']);

        if (empty($lesson)) {
            return $this->createMessageResponse('info', '作业所属课时不存在！');
        }

        $itemSet = $this->getHomeworkService()->getItemSetByHomeworkId($homework['id']);

        return $this->render('HomeworkBundle:CourseHomework:preview.html.twig', array(
            'homework'       => $homework,
            'itemSet'        => $itemSet,
            'course'         => $course,
            'lesson'         => $lesson,
            'questionStatus' => 'previewing'
        ));
    }

    public function lessonHomeworkShowAction()
    {
        $user     = $this->getCurrentUser();
        $homework = $this->getHomeworkService()->getHomeworkByLessonId($lessonId);
        $homework = $this->getHomeworkService()->getResultByHomeworkIdAndUserId($homework['id'], $user['id']);

        if (empty($homework)) {
            return $this->createJsonResponse(array('status' => 'none'));
        }

        return $this->createJsonResponse($homework);
    }

    protected function findAttachments($testId)
    {
        $items       = $this->getTestpaperService()->findItemsByTestId($testId);
        $questionIds = ArrayToolkit::column($items, 'questionId');
        $conditions  = array(
            'type'        => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds'   => $questionIds
        );
        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);
        array_walk($attachments, function (&$attachment) {
            $attachment['dkey'] = $attachment['targetType'].$attachment['targetId'];
        });

        return ArrayToolkit::group($attachments, 'dkey');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
