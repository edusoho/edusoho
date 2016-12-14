<?php
namespace AppBundle\Controller\Testpaper;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $conditions = array(
            'courseId' => $courseSet['id'],
            'type'     => 'testpaper'
        );

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updatedUserId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/index.html.twig', array(
            'courseSet'  => $courseSet,
            'testpapers' => $testpapers,
            'users'      => $users,
            'paginator'  => $paginator

        ));
    }

    public function createAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ($request->getMethod() == 'POST') {
            $fields                = $request->request->all();
            $fields['ranges']      = empty($fields['ranges']) ? array() : explode(',', $fields['ranges']);
            $fields['courseSetId'] = $courseSet['id'];
            $fields['pattern']     = 'questionType';

            $testpaper = $this->getTestpaperService()->buildTestpaper($fields, 'testpaper');

            return $this->redirect($this->generateUrl('course_set_manage_testpaper_questions', array('courseSetId' => $courseSet['id'], 'testpaperId' => $testpaper['id'])));
        }

        $types = $this->getQuestionTypes();

        $conditions['types']    = array_keys($types);
        $conditions['courseId'] = $courseSet['id'];

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        $conditions                              = array();
        $conditions['type']                      = 'material';
        $conditions['subCount']                  = 0;
        $questionNums['material']['questionNum'] = $this->getQuestionService()->searchCount($conditions);

        return $this->render('testpaper/manage/create.html.twig', array(
            'courseSet'    => $courseSet,
            'ranges'       => $this->getQuestionRanges($courseSet),
            'types'        => $types,
            'questionNums' => $questionNums
        ));
    }

    public function checkListAction(Request $request, $courseId, $type, $testpaperIds = array())
    {
        $conditions = array(
            'status' => 'open',
            'type'   => $type,
            'ids'    => $testpaperIds
        );

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($testpapers as $key => $testpaper) {
            $testpapers[$key]['resultStatusNum'] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id']);
        }

        return $this->render('testpaper/manage/check-list.html.twig', array(
            'testpapers' => ArrayToolkit::index($testpapers, 'id'),
            'paginator'  => $paginator
        ));
    }

    public function checkAction(Request $request, $resultId, $targetId, $source = 'course')
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$result) {
            throw $this->createResourceNotFoundException('testpaperResult', $resultId);
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $result['id']);
        }

        if ($result['status'] != 'reviewing') {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $result['id'])));
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            return $this->createJsonResponse(true);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $result['id']);

        $essayQuestions = $this->getCheckedEssayQuestions($questions);

        $student  = $this->getUserService()->getUser($result['userId']);
        $accuracy = $this->getTestpaperService()->makeAccuracy($result['id']);
        $total    = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        return $this->render('testpaper/manage/teacher-check.html.twig', array(
            'paper'         => $testpaper,
            'paperResult'   => $result,
            'questions'     => $essayQuestions,
            'student'       => $student,
            'accuracy'      => $accuracy,
            'questionTypes' => array('essay', 'material'),
            'total'         => $total,
            'source'        => $source,
            'targetId'      => $targetId,
            'isTeacher'     => true
        ));
    }

    public function resultListAction(Request $request, $testpaperId, $source, $targetId)
    {
        $user = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperId);
        }

        $status  = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('testId' => $testpaper['id']);
        if ($status != 'all') {
            $conditions['status'] = $status;
        }
        $conditions['type'] = $testpaper['type'];

        if (!empty($keyword)) {
            $searchUser           = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $testpaper['resultStatusNum'] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id']);

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('endTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpaperResults, 'userId');
        $users   = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/result-list.html.twig', array(
            'testpaper'    => $testpaper,
            'status'       => $status,
            'paperResults' => $testpaperResults,
            'paginator'    => $paginator,
            'users'        => $users,
            'source'       => $source,
            'targetId'     => $targetId,
            'isTeacher'    => true
        ));
    }

    public function buildCheckAction(Request $request, $courseId)
    {
        $course = $this->getCourseSetService()->tryManageCourseSet($courseId);

        $data           = $request->request->all();
        $data['ranges'] = empty($data['ranges']) ? array() : explode(',', $data['ranges']);
        $result         = $this->getTestpaperService()->canBuildTestpaper('QuestionType', $data);
        return $this->createJsonResponse($result);
    }

    public function updateAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('试卷不存在'));
        }

        if ($request->getMethod() == 'POST') {
            $data      = $request->request->all();
            $testpaper = $this->getTestpaperService()->updateTestpaper($testpaper['id'], $data);

            $this->setFlashMessage('success', $this->getServiceKernel()->trans('试卷信息保存成功！'));
            return $this->redirect($this->generateUrl('course_set_manage_testpaper', array('id' => $courseSet['id'])));
        }

        return $this->render('testpaper/manage/update.html.twig', array(
            'courseSet' => $courseSet,
            'testpaper' => $testpaper
        ));
    }

    public function deleteAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $this->getTestpaperService()->deleteTestpaper($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseSetId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $ids = $request->request->get('ids');

        $this->getTestpaperService()->deleteTestpapers($id);

        return $this->createJsonResponse(true);
    }

    public function publishAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user'      => $user,
            'courseSet' => $courseSet
        ));
    }

    public function closeAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user'      => $user,
            'courseSet' => $courseSet
        ));
    }

    public function questionsAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('试卷不存在'));
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (empty($fields['questions'])) {
                return $this->createMessageResponse('error', $this->getServiceKernel()->trans('试卷题目不能为空！'));
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $fields);

            $this->setFlashMessage('success', $this->getServiceKernel()->trans('试卷题目保存成功！'));

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('course_set_manage_testpaper', array('id' => $courseSetId))
            ));
        }

        $items     = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $hasEssay   = $this->getQuestionService()->hasEssay(ArrayToolkit::column($items, 'questionId'));
        $scoreTotal = 0;

        $passedScoreDefault = ceil($scoreTotal * 0.6);
        return $this->render('testpaper/manage/question.html.twig', array(
            'courseSet'          => $courseSet,
            'testpaper'          => $testpaper,
            'questions'          => $questions,
            'hasEssay'           => $hasEssay,
            'passedScoreDefault' => $passedScoreDefault
        ));
    }

    public function infoAction(Request $request, $id)
    {
        $course = $this->getCourseSetService()->tryManageCourseSet($id);

        $testpaperId = $request->request->get('testpaperId');

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $items    = $this->getTestpaperService()->getItemsCountByParams(array('testId' => $testpaperId, 'parentIdDefault' => 0), $gourpBy = 'questionType');
        $subItems = $this->getTestpaperService()->getItemsCountByParams(array('testId' => $testpaperId, 'parentId' => 0));

        $items = ArrayToolkit::index($items, 'questionType');

        $items['material'] = $subItems[0];

        return $this->render('testpaper/manage/item-get-table.html.twig', array(
            'items' => $items
        ));
    }

    public function previewAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            throw $this->createNotFoundException();
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('warning', '试卷已关闭，不能查看！');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/manage/preview.html.twig', array(
            'questions'     => $questions,
            'limitedTime'   => $testpaper['limitedTime'] * 60,
            'paper'         => $testpaper,
            'paperResult'   => array(),
            'total'         => $total,
            'attachments'   => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper)
        ));
    }

    protected function getQuestionRanges($course, $includeCourse = false)
    {
        $ranges = array('本课程');

        return $ranges;
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        $essayQuestions['essay'] = !empty($questions['essay']) ? $questions['essay'] : array();

        if (empty($questions['material'])) {
            return $essayQuestions;
        }

        foreach ($questions['material'] as $questionId => $question) {
            $questionTypes = ArrayToolkit::column(empty($question['subs']) ? array() : $question['subs'], 'type');

            if (in_array('essay', $questionTypes)) {
                $essayQuestions['material'][$questionId] = $question;
            }
        }

        return $essayQuestions;
    }

    protected function getCheckedQuestionType($testpaper)
    {
        $questionTypes = array();
        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if ($count > 0) {
                $questionTypes[] = $type;
            }
        }

        return $questionTypes;
    }

    protected function getQuestionTypes()
    {
        $typesConfig = $this->get('extension.default')->getQuestionTypes();

        $types = array();
        foreach ($typesConfig as $type => $typeConfig) {
            $types[$type] = array(
                'name'         => $typeConfig['name'],
                'hasMissScore' => $typeConfig['hasMissScore']
            );
        }

        return $types;
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
