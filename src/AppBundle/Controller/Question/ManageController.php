<?php

namespace AppBundle\Controller\Question;

use AppBundle\Common\Paginator;
use Biz\Content\Service\FileService;
use Biz\Question\QuestionException;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\TokenService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Question\Service\QuestionService;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', array(
                'id' => $id,
                'sideNav' => 'question',
            ));
        }

        return $this->render('question-manage/index.html.twig', array(
            'courseSet' => $courseSet,
        ));
    }

    public function previewAction(Request $request, $courseSetId, $questionId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $isNewWindow = $request->query->get('isNew');

        $question = $this->getQuestionService()->get($questionId);

        if (!$question || $question['courseSetId'] != $courseSetId) {
            $this->createNewException(QuestionException::NOTFOUND_QUESTION());
        }

        if (!empty($question['matas']['mediaId'])) {
            $questionTypeObj = $this->getQuestionService()->getQuestionConfig($question['type']);
            $questionExtends = $questionTypeObj->get($question['matas']['mediaId']);
            $question = array_merge_recursive($question, $questionExtends);
        }

        if ($question['subCount'] > 0) {
            $questionSubs = $this->getQuestionService()->findQuestionsByParentId($question['id']);

            $question['subs'] = $questionSubs;
        }

        $template = 'question-manage/preview-modal.html.twig';
        if ($isNewWindow) {
            $template = 'question-manage/preview.html.twig';
        }

        return $this->render($template, array(
            'question' => $question,
            'showAnswer' => 1,
            'showAnalysis' => 1,
        ));
    }

    public function checkAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);
        $conditions = $request->request->all();
        $conditions['courseSetId'] = $courseSet['id'];

        if (!empty($conditions['types'])) {
            $conditions['types'] = explode(',', $conditions['types']);
        }

        $count = $this->getQuestionService()->searchCount($conditions);

        $result = false;
        if (!empty($conditions['itemCount']) && $count >= $conditions['itemCount']) {
            $result = true;
        }

        return $this->createJsonResponse($result);
    }

    public function questionPickerAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = $request->query->all();

        $conditions['parentId'] = 0;
        $conditions['courseSetId'] = $courseSet['id'];

        $paginator = new Paginator(
            $request,
            $this->getQuestionService()->searchCount($conditions),
            7
        );

        $questions = $this->getQuestionService()->search(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        return $this->render('question-manage/question-picker.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'replace' => empty($conditions['replace']) ? '' : $conditions['replace'],
            'paginator' => $paginator,
            'courseTasks' => $this->getQuestionRanges($request->query->get('courseId', 0)),
            'conditions' => $conditions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courses' => $manageCourses,
        ));
    }

    public function pickedQuestionAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $questionIds = $request->request->get('questionIds', array(0));

        if (!$questionIds) {
            return $this->createJsonResponse(array('result' => 'error', 'message' => '请先选择题目'));
        }

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        foreach ($questions as &$question) {
            if ($question['courseSetId'] != $courseSetId) {
                $this->createNewException(QuestionException::NOTFOUND_QUESTION());
            }
            if ($question['subCount'] > 0) {
                $question['subs'] = $this->getQuestionService()->findQuestionsByParentId($question['id']);
            }
        }

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');
        $courseTasks = $this->getTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $this->render('question-manage/question-picked.html.twig', array(
            'courseSet' => $courseSet,
            'questions' => $questions,
            'targetType' => $request->query->get('targetType', 'testpaper'),
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
        ));
    }

    public function showTasksAction(Request $request, $courseSetId)
    {
        $courseId = $request->request->get('courseId', 0);
        if (empty($courseId)) {
            return $this->createJsonResponse(array());
        }

        $this->getCourseService()->tryManageCourse($courseId);

        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseId);

        return $this->createJsonResponse($courseTasks);
    }

    public function showQuestionTypesNumAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $conditions = $request->request->all();
        $conditions['courseSetId'] = $courseSetId;
        $conditions['parentId'] = 0;

        $typesNum = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $typesNum = ArrayToolkit::index($typesNum, 'type');

        return $this->createJsonResponse($typesNum);
    }

    public function reEditAction(Request $request, $token)
    {
        return $this->forward('AppBundle:Question/QuestionParser:reEdit', array(
            'request' => $request,
            'token' => $token,
            'type' => 'question',
        ));
    }

    public function saveImportQuestionsAction(Request $request, $token)
    {
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
        if (!$this->getQuestionBankService()->validateCanManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $content = $request->getContent();
        $postData = json_decode($content, true);
        $this->getQuestionService()->importQuestions($postData['questions'], $token);

        return $this->createJsonResponse(true);
    }

    protected function getQuestionConfig()
    {
        return $this->get('extension.manager')->getQuestionTypes();
    }

    protected function getQuestionRanges($courseId)
    {
        if (empty($courseId)) {
            return array();
        }

        $courseTasks = $this->getTaskService()->findTasksByCourseId($courseId);

        return ArrayToolkit::index($courseTasks, 'id');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ServiceKernel
     */
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
