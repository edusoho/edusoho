<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Question\QuestionService;

class CategoryQuestionManageController extends BaseController
{
    /* @to-do 权限判断 */
    public function indexAction(Request $request, $categoryId)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);
        
        $conditions = $request->query->all();

        if (empty($conditions['target'])) {
            $conditions['targetPrefix'] = "subject-{$categoryId}";
        }

        if (!empty($conditions['keyword'])) {
            $conditions['stem'] = $conditions['keyword'];
        }

        if (!empty($conditions['parentId'])) {

            $parentQuestion = $this->getQuestionService()->getQuestion($conditions['parentId']);
            if (empty($parentQuestion)){
                return $this->redirect($this->generateUrl('admin_category_manage_question',array('categoryId' => $categoryId)));
            }

            $orderBy = array('createdTime' ,'ASC');
        } else {
            $conditions['parentId'] = 0;
            $parentQuestion = null;
            $orderBy = array('createdTime' ,'DESC');
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getQuestionService()->searchQuestionsCount($conditions),
            10
        );

        $questions = $this->getQuestionService()->searchQuestions(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId'));

    /*    $targets = $this->get('topxia.target_helper')->getTargets(ArrayToolkit::column($questions, 'target'));*/

        return $this->render('TopxiaAdminBundle:CategoryQuestionManage:index.html.twig', array(
            'category' => $category,
            'questions' => $questions,
            'users' => $users,
/*            'targets' => $targets,*/
            'paginator' => $paginator,
            'parentQuestion' => $parentQuestion,
            'conditions' => $conditions,
 /*           'targetChoices' => $this->getQuestionTargetChoices($course),*/
        ));
    }

    public function createAction(Request $request, $categoryId, $type)
    {

        $category = $this->getCategoryService()->getCategory($categoryId);
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data['target'] = "subject-{$categoryId}/";
            $question = $this->getQuestionService()->createQuestion($data);

            if ($data['submission'] == 'continue') {
                $urlParams = ArrayToolkit::parts($question, array('target', 'difficulty', 'parentId'));
                $urlParams['type'] = $type;
                $urlParams['categoryId'] = $categoryId;
                $urlParams['goto'] = $request->query->get('goto', null);
                $this->setFlashMessage('success', '题目添加成功，请继续添加。');
                return $this->redirect($this->generateUrl('admin_category_manage_question_create', $urlParams));
            } elseif ($data['submission'] == 'continue_sub') {
                $this->setFlashMessage('success', '题目添加成功，请继续添加子题。');
                return $this->redirect($request->query->get('goto', $this->generateUrl('admin_category_manage_question', array('categoryId' => $categoryId, 'parentId' => $question['id']))));
            } else {
                $this->setFlashMessage('success', '题目添加成功。');
                return $this->redirect($request->query->get('goto', $this->generateUrl('admin_category_manage_question', array('categoryId' => $categoryId))));
            }
        }

        $question = array(
            'id' => 0,
            'type' => $type,
  /*          'target' => $request->query->get('target'),*/
            'categoryId' => $categoryId,
            'difficulty' => $request->query->get('difficulty', 'normal'),
            'parentId' => $request->query->get('parentId', 0),
        );

        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
            if (empty($parentQuestion)){
                return $this->createMessageResponse('error', '父题不存在，不能创建子题！');
            }
        } else {
            $parentQuestion = null;
        }

        if ($this->container->hasParameter('enabled_features')) {
            $features = $this->container->getParameter('enabled_features');
        } else {
            $features = array();
        }

        $enabledAudioQuestion = in_array('audio_question', $features);

        return $this->render("TopxiaAdminBundle:CategoryQuestionManage:question-form-{$type}.html.twig", array(
            'category' => $category,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
/*            'targetsChoices' => $this->getQuestionTargetChoices($course),
            'categoryChoices' => $this->getQuestionCategoryChoices($course),*/
            'enabledAudioQuestion' => $enabledAudioQuestion
        ));
    }

    public function updateAction(Request $request, $categoryId, $id)
    {
        $category = $this->getCategoryService()->getCategory($categoryId);

        if ($request->getMethod() == 'POST') {
            $question = $request->request->all();

            $question = $this->getQuestionService()->updateQuestion($id, $question);

            $this->setFlashMessage('success', '题目修改成功！');

            return $this->redirect($request->query->get('goto', $this->generateUrl('admin_category_manage_question',array('categoryId' => $categoryId,'parentId' => $question['parentId']))));
        }

        $question = $this->getQuestionService()->getQuestion($id);
        if ($question['parentId'] > 0) {
            $parentQuestion = $this->getQuestionService()->getQuestion($question['parentId']);
        } else {
            $parentQuestion = null;
        }

        return $this->render("TopxiaAdminBundle:CategoryQuestionManage:question-form-{$question['type']}.html.twig", array(
            'category' => $category,
            'question' => $question,
            'parentQuestion' => $parentQuestion,
/*            'targetsChoices' => $this->getQuestionTargetChoices($course),
            'categoryChoices' => $this->getQuestionCategoryChoices($course),*/
        ));

    }

    public function deleteAction(Request $request, $categoryId, $id)
    {
        $question = $this->getQuestionService()->getQuestion($id);
        $this->getQuestionService()->deleteQuestion($id);
        
        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $categoryId)
    {   
        $ids = $request->request->get('ids');
        foreach ($ids ? : array() as $id) {
            $this->getQuestionService()->deleteQuestion($id);
        }

        return $this->createJsonResponse(true);
    }

    public function uploadFileAction (Request $request, $categoryId, $type)
    {
        $course = $this->getCourseService()->tryManageCourse($categoryId);

        if ($request->getMethod() == 'POST') {
            $originalFile = $this->get('request')->files->get('file');
            $file = $this->getUploadFileService()->addFile('quizquestion', 0, array('isPublic' => 1), 'local', $originalFile);
            return new Response(json_encode($file));
        }
    }


    /**
     * @todo refact it, to xxvholic.
     */
    public function previewAction (Request $request, $categoryId, $id)
    {
        $isNewWindow = $request->query->get('isNew');

        $category = $this->getCategoryService()->getCategory($categoryId);

        $question = $this->getQuestionService()->getQuestion($id);

        if (empty($question)) {
            throw $this->createNotFoundException('题目不存在！');
        }

        $item = array(
            'questionId' => $question['id'],
            'questionType' => $question['type'],
            'question' => $question
        );

        if ($question['subCount'] > 0) {
            $questions = $this->getQuestionService()->findQuestionsByParentId($id);

            foreach ($questions as $value) {
                $items[] = array(
                    'questionId' => $value['id'],
                    'questionType' => $value['type'],
                    'question' => $value
                );
            }

            $item['items'] = $items;
        }

        $type = in_array($question['type'], array('single_choice', 'uncertain_choice')) ? 'choice' : $question['type'];
        $questionPreview = true;

        if($isNewWindow){
            return $this->render('TopxiaAdminBundle:QuizQuestionTest:question-preview.html.twig', array(
                'item' => $item,
                'type' => $type,
                'questionPreview' => $questionPreview
            ));
        }

        return $this->render('TopxiaAdminBundle:QuizQuestionTest:question-preview-modal.html.twig', array(
            'item' => $item,
            'type' => $type,
            'questionPreview' => $questionPreview
        ));
    }

    public function tagsAction(Request $request, $categoryId)
    {
        $tagIds = $this->getQuestionService()->getQuestion();
    }

    private function getQuestionTargetChoices($course)
    {
        $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        $choices = array();
        $choices["course-{$course['id']}"] = '本课程';
        foreach ($lessons as $lesson) {
            if ($lesson['type'] == 'testpaper') {
                continue;
            }
            $choices["course-{$course['id']}/lesson-{$lesson['id']}"] = "课时{$lesson['number']}：{$lesson['title']}";
        }
        return $choices;
    }

    private function getQuestionCategoryChoices($course)
    {
        $categories = $this->getQuestionService()->findCategoriesByTarget("course-{$course['id']}", 0, QuestionService::MAX_CATEGORY_QUERY_COUNT);
        $choices = array();
        foreach ($categories as $category) {
            $choices[$category['id']] = $category['name'];
        }
        return $choices;
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}