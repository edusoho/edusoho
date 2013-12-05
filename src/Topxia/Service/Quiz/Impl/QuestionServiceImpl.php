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

    public function addQuestion($type,$question)
    {
        if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
            $type = 'choice';
        }
        if($type == 'choice'){
            if (!ArrayToolkit::requireds($course, array('target','difficulty','stem'))) {
                throw $this->createServiceException('缺少必要字段，创建课程失败！');
            }
        }

        return $this->getQuizQuestionsDao()->getQuestion($id);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
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


class QuizSerialize
{
    public static function serialize(array $item)
    {
        if (isset($item['answers'])) {
            $item['answers'] = implode('|', $item['answers']);
        }

        return $item;
    }

    public static function unserialize(array $item = null)
    {
        if (empty($item)) {
            return null;
        }

        $item['answers'] = explode('|', $item['answers']);
        return $item;
    }

    public static function unserializes(array $items)
    {
        return array_map(function($item) {
            return ItemSerialize::unserialize($item);
        }, $items);
    }
}
