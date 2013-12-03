<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuizService;
use Topxia\Common\ArrayToolkit;

class QuizServiceImpl extends BaseService implements QuizService
{


    public function getQuestion($id)
    {
        return $this->getQuestionsDao()->getQuestion($id);
    }

    public function findLessonsByCourseId($courseId)
    {
        return $this->getLessonDao()->findLessonsByCourseId($courseId);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuestionsDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestions(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuestionsDao() -> searchQuestions($conditions, $orderBy, $start, $limit);
    }







    private function getLessonDao()
    {
        return $this-> createdao('Course.LessonDao');
    }

    private function getQuestionCategotyDao()
    {
        return $this->createDao('Quiz.QuestionCategotyDao');
    }

    private function getQuestionChoiceDao()
    {
        return $this->createDao('Quiz.QuestionChoiceDao');
    }

    private function getQuestionsDao()
    {
        return $this->createDao('Quiz.QuestionsDao');
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
