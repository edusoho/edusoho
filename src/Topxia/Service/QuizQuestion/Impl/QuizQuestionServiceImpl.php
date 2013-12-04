<?php
namespace Topxia\Service\QuizQuestion\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\QuizQuestion\QuizQuestionService;
use Topxia\Common\ArrayToolkit;

class QuizQuestionServiceImpl extends BaseService implements QuizQuestionService
{


    public function getQuestion($id)
    {
        return $this->getQuizQuestionsDao()->getQuestion($id);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
    }






    private function getQuizQuestionCategotyDao()
    {
        return $this->createDao('QuizQuestion.QuizQuestionCategotyDao');
    }

    private function getQuizQuestionChoiceDao()
    {
        return $this->createDao('QuizQuestion.QuizQuestionChoiceDao');
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('QuizQuestion.QuizQuestionDao');
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
