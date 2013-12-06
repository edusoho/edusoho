<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionService;
use Topxia\Common\ArrayToolkit;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    public function getQuestionTarget($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if(empty($course)){
            return null;
        }
        $targets = array();
        $targets[] = array('type' => 'course','id' => $course['id'],'name' => '课程');
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        foreach ($lessons as  $lesson) {
            $targets[] = array('type' => 'lesson','id' => $lesson['id'],'name' => '课时'.$lesson['number']);
        }
        return $targets;
    }

    public function getQuestion($id)
    {
        return $this->getQuizQuestionsDao()->getQuestion($id);
    }

    public function addQuestion($courseId, $question)
    {
        $questionField = $this->checkCommonFields($courseId, $question);

        if ($question['type'] == 'choice'){
            if (!ArrayToolkit::requireds($question, array('choices'))) {
                throw $this->createServiceException('缺少必要字段，创建课程失败！');
            }
            $choiceField = $this->filterChoiceFields($question);
            
            $questionResult =  QuestionSerialize::unserialize(
                $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
            );
            
            $choices = array();
            $choice['quesitonId'] = $questionResult['id'];
            foreach ($choiceField['choices'] as $key => $content) {
                $choice['content'] = $content;
                $choiceResult = $this->getQuizQuestionChoiceDao()->addQuestionChoice($choice);
                if (in_array($key, $choiceField['answers'])){
                    $choices[] = $choiceResult;
                }
            }
            $questionField = array();
            $questionField['answer'] =  ArrayToolkit::column($choices,'id');
            $questionResult =  QuestionSerialize::unserialize(
                $this->getQuizQuestionDao()->updateQuestion($questionResult['id'], QuestionSerialize::serialize($questionField))
            );

        }else if ($question['type'] == 'essay' || $question['type'] == 'determine'){

            $questionField['answer'] = $question['answers'];
            $questionResult =  QuestionSerialize::unserialize(
                $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
            );
        }
        return $questionResult;   
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
    }

    private function checkCommonFields($courseId,$question)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        
        if (!in_array($question['type'], array('choice', 'fill', 'material', 'essay', 'determine'))) {
            $question['type'] = 'choice';
        }

        if (!ArrayToolkit::requireds($question, array('target', 'difficulty', 'stem', 'answers', 'type'))) {
                throw $this->createServiceException('缺少必要字段，创建课程失败！');
        }

        $field = array();

        $field['questionType'] = $question['type'];

        $target = explode('-', $question['target']);
        if (count($target) != 2){
            throw $this->createServiceException("target参数不正确");
        }
        $field['targetType'] = $target['0'];
        $field['targetId'] = $target['1'];

        if (!in_array($field['targetType'], array('course','lesson'))){
            throw $this->createServiceException("targetType参数不正确");
        }

        if ($field['targetType'] == 'course'){
            $course = $this->getCourseService()->getCourse($field['targetId']);
            if (empty($course)){
                throw $this->createServiceException("课程(#{$field['targetId']})不存在，创建题目失败！");
            }
        }
        else if ($field['targetType'] == 'lesson'){
            $lesson = $this->getCourseService()->getCourseLesson($courseId, $field['targetId']);
            if (empty($lesson)) {
                throw $this->createServiceException("课时(#{$field['targetId']})不存在，创建题目失败！");
            }
        }

        $field['stem'] = $this->purifyHtml($question['stem']);
        $field['difficulty'] = (int) $question['difficulty'];

        $field['userId'] = $this->getCurrentUser()->id;
        $field['createdTime'] = time();

        return $field;
    }

    private function filterChoiceFields($question)
    {
        $field['choices'] = $question['choices'];
        $field['answers'] = explode('|', $question['answers']);

        if (!is_array($field['choices']) || count($field['choices']) < 2) {
            throw $this->createServiceException("choices参数不正确");
        }

        if (!is_array($field['answers']) || empty($field['answers'])) {
            throw $this->createServiceException("answers参数不正确");
        }
        return $field;
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    private function getQuizQuestionChoiceDao()
    {
        return $this->createDao('Quiz.QuizQuestionChoiceDao');
    }

    private function getQuizQuestionCategotyDao()
    {
        return $this->createDao('Quiz.QuizQuestionCategotyDao');
    }

    

}


class QuestionSerialize
{
    public static function serialize(array $question)
    {
        if (isset($question['answer'])) {
            $question['answer'] = json_encode($question['answer']);
        }
        return $question;
    }

    public static function unserialize(array $question = null)
    {
        if (empty($question)) {
            return null;
        }
        if(!empty($question['answer'])){
            $question['answer'] = json_decode($question['answer'],true);
        }

        return $question;
    }

    public static function unserializes(array $questions)
    {
        return array_map(function($question) {
            return QuestionSerialize::unserialize($question);
        }, $questions);
    }
}

