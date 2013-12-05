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
        $course = $this->getCourseService()->tryManageCourse($question['courseId']);
        
        if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
            $type = 'choice';
        }

        if($type == 'choice'){

            if (!ArrayToolkit::requireds($question, array('target', 'difficulty', 'stem', 'choices', 'answers', 'type'))) {
                throw $this->createServiceException('缺少必要字段，创建课程失败！');
            }

            $field = $this->filterQuestionFields($question);
            
            $field['userId'] = $this->getCurrentUser()->id;
            $field['createdTime'] = time();
            $field['stem'] = $this->purifyHtml($field['stem']);
            
            //--------------------answer  choice
            $result =  QuestionSerialize::unserialize(
                $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($field))
            );

            $choiceIds = array();
            foreach $field['choice'] as $key => $item) {
                $choice['content'] = $item;
                $choice = $this->getQuizQuestionChoiceDao()->getQuestionChoice($choice);
                if(in_array($key, $field['answers'])){
                    $choiceIds[] = ArrayToolkit::column($choice,'id');
                }
            }
            $field['answers'] =  $choiceIds;

            $choiceField = array('quesitonId' => $result['id']); 
            foreach ($variable as $choiceId) {
                $this->getQuizQuestionChoiceDao()->updateQuestionChoice($choiceId , $choiceField);
            }

        }

        return $result;   
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
    }

    private function filterQuestionFields($question)
    {
        $fields = array();
        $fields['target'] = explode('-', $question['target']);
        $fields['questionType'] = $question['type'];

        if($type =="choice"){
            $fields['stem'] = $this->purifyHtml($question['stem']);
            $fields['difficulty'] = $question['difficulty'];//--------------------------------
            $fields['answers'] = explode('|', $question['answers']);
            $fields['choices'] = $question['choices'];
        }

        if (!in_array($fields['questionType'], array('course','lesson'))){
            throw $this->createServiceException("questionType参数不正确");
        }

        if (!is_array($fields['choices']) || count($fields['choices']) < 2) {
            throw $this->createServiceException("choices参数不正确");
        }

        if (!is_array($fields['answers']) || empty($fields['answers'])) {
            throw $this->createServiceException("answers参数不正确");
        }

        if($field['targetType'] == 'course'){
            $course = $this->getCourseService()->getCourse($question['targetId']);
            if(empty($course)){
                throw $this->createServiceException("课程(#{$courseId})不存在，创建测验题目失败！");
            }
        }esle if($field['targetType'] == 'lesson'){
            $lesson = $this->getCourseService()->getCourseLesson($courseId, $question['targetId']);
            if (empty($lesson)) {
                throw $this->createServiceException("课时(#{$question['targetId']})不存在，创建测验题目失败！");
            }
        }




        return $fields;
    }



    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getQuizQuestionCategotyDao()
    {
        return $this->createDao('Quiz.QuizQuestionCategotyDao');
    }

    private function getQuizQuestionChoiceDao()
    {
        return $this->createDao('Quiz.QuizQuestionChoiceDao');
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    

}


class QuestionSerialize
{
    public static function serialize(array $question)
    {
        if (isset($question['answers'])) {
            $question['answers'] = implode('|', $question['answers']);
        }

        return $question;
    }

    public static function unserialize(array $question = null)
    {
        if (empty($question)) {
            return null;
        }

        $question['answers'] = explode('|', $question['answers']);
        return $question;
    }

    public static function unserializes(array $questions)
    {
        return array_map(function($question) {
            return ItemSerialize::unserialize($question);
        }, $questions);
    }
}
