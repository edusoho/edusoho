<?php 

namespace Custom\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\WebBundle\Controller\BaseController;

class CCViedoManageController extends BaseController
{
    public function showAction(Request $request, $courseId)
    {
        $lessonId = $request->query->get('lessonId');
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);
        $courseware = $this->getCoursewareService()->getCourseware($lesson['coursewareId']);
        return $this->render('CustomWebBundle:CCViedoManage:CCViedoTemplate.html.twig',array(
            'courseware' => $courseware
        ));
    }

    public function questionPreviewAction(Request $request, $questionId)
    {
        $question = $this->getQuestionService()->getQuestion($questionId);

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

        $mainKnowledge = $this->getKnowledgeService()->getKnowledge($question['mainKnowledgeId']);
        $tags = $this->getTagService()->findTagsByIds($question['tagIds']);
        $relatedKnowledges = $this->getKnowledgeService()->findKnowledgeByIds($question['relatedKnowledgeIds']);

        return $this->render('CustomWebBundle:CCViedoManage:question-preview-modal.html.twig', array(
            'item' => $item,
            'type' => $type,
            'mainKnowledge' => $mainKnowledge,
            'relatedKnowledges' => $relatedKnowledges,
            'tags' => $tags,
        ));
    }

    public function answerCheckAction(Request $request, $questionId)
    {
        $answer = $request->request->all();
        if(empty($answer)){
            $result = array('answer' => 'false', 'message'=>'没有答案');
        }
        $question = $this->getQuestionService()->getQuestion($questionId);
        $trueAnswer =json_decode($question['answer']);

        if ($answer == $trueAnswer){
            $result = array('answer' => 'true', 'message'=>'回答正确');
        } else {
            $result = array('answer' => 'false', 'message'=>'回答错误');
        }

        return $this->createJsonResponse($result);
    }

    private function getCoursewareService()
    {
        return $this->getServiceKernel()->createService('Courseware.CoursewareService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getKnowledgeService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.KnowledgeService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Tag.TagService');
    }

    private function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }
}