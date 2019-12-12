<?php

namespace AppBundle\Controller\Question;

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
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $content = $request->getContent();
        $postData = json_decode($content, true);
        $this->getQuestionService()->importQuestions($postData['questions'], $token);

        return $this->createJsonResponse(true);
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
