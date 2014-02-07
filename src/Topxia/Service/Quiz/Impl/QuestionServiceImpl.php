<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionService;
use Topxia\Common\ArrayToolkit;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    public function getQuestion($id)
    {
        $question = $this->getQuizQuestionDao()->getQuestion($id);
        return empty($question) ? null : $this->getQuestionImplementor($question['type'])->getQuestion($question);
    }

    public function createQuestion($question)
    {
        $this->checkQuestionType($question['type']);
        $question['createdTime'] = time();

        $question = $this->getQuestionImplementor($question['type'])->createQuestion($question);

        if ($question['parentId'] > 0) {
            $subCount = $this->getQuizQuestionDao()->findQuestionsCountByParentId($question['parentId']);
            $this->getQuizQuestionDao()->updateQuestion($question['parentId'], array('subCount' => $subCount));
            $parent = $this->getQuizQuestionDao()->getQuestion($question['parentId']);
            $this->getQuizQuestionDao()->updateQuestion($question['id'], array(
                'targetType' => $parent['targetType'],
                'targetId' => $parent['targetId']
            ));
        }

        return $question;
    }

    public function updateQuestion($id, $question)
    {
        $this->checkQuestionType($question['type']);
        return $this->getQuestionImplementor($question['type'])->updateQuestion($id, $question);  
    }

    public function deleteQuestion($id)
    {
        $question = $this->getQuizQuestionDao()->getQuestion($id);
        if (empty($question)) {
            throw $this->createNotFoundException();
        }
        $this->getQuizQuestionDao()->deleteQuestion($id);

        $this->getQuizQuestionDao()->deleteQuestionsByParentId($id);

        if ($question['parentId']) {
            $subCount = $this->getQuizQuestionDao()->findQuestionsCountByParentId($question['parentId']);
            $this->getQuizQuestionDao()->updateQuestion($question['parentId'], array('subCount' => $subCount));
        }

    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit)
    {
        return $this->getQuizQuestionDao()->searchQuestion($conditions, $orderBy, $start, $limit);
    }

    public function searchQuestionCount(array $conditions)
    {
        return $this->getQuizQuestionDao()->searchQuestionCount($conditions);
    }

    public function findQuestionsByIds(array $ids)
    {
        return $this->getQuizQuestionDao()->findQuestionsByIds($ids);
    }

    public function findQuestionsByParentIds(array $ids)
    {
        return $this->getQuizQuestionDao()->findQuestionsByParentIds($ids);
    }

    public function findQuestionsByTypeAndTypeIds($type,$ids)
    {               
        return $this->getQuizQuestionDao()->findQuestionsByTypeAndTypeIds($type,$ids);
    }

    public function findQuestions ($ids)
    {
        $questions = QuestionSerialize::unserializes($this->findQuestionsByIds($ids));

        if (empty($questions)){
            throw $this->createNotFoundException('题目不存在！');
        }

        $questions = ArrayToolkit::index($questions, 'id');

        return $questions;
    }

    public function findQuestionsCountByTypeAndTypeIds($type, $ids)
    {
        return $this->getQuizQuestionDao()->findQuestionsCountByTypeAndTypeIds($type, $ids);
    }

    public function favoriteQuestion($questionId, $targetType, $targetId, $userId)
    {
        $favorite = array(
            'questionId' => $questionId,
            'targetType' => $targetType,
            'targetId' => $targetId,
            'userId' => $userId,
            'createdTime' => time()
        );

        $favoriteBack = $this->getQuestionFavoriteDao()->getFavoriteByQuestionIdAndTargetAndUserId($favorite);

        if (!$favoriteBack) {
            return $this->getQuestionFavoriteDao()->addFavorite($favorite);
        }

        return $favoriteBack;
    }

    public function unFavoriteQuestion ($questionId, $targetType, $targetId, $userId)
    {
        $favorite = array(
            'questionId' => $questionId,
            'targetType' => $targetType,
            'targetId' => $targetId,
            'userId' => $userId
        );

        return $this->getQuestionFavoriteDao()->deleteFavorite($favorite);
    }

    public function statQuestionTimes ($answers)
    {
        // $answers = ArrayToolkit::index($answers, 'questionId');
        // $ids = ArrayToolkit::column($answers, 'questionId');
        $ids = array_keys($answers);
        $rightIds = array();
        foreach ($answers as $questionId => $answer) {
            if ($answer['status'] == 'right'){
                array_push($rightIds, $questionId);
            }
        }
        $this->getQuizQuestionDao()->updateQuestionCountByIds($ids, 'finishedTimes');
        $this->getQuizQuestionDao()->updateQuestionCountByIds($rightIds, 'passedTimes');
    }


    private function checkCategoryFields($category)
    {
        $target = explode('-', $category['target']);

        if (count($target) != 2){
            throw $this->createServiceException("target参数不正确");
        }

        $field['targetType'] = $target['0'];

        $field['targetId'] = (int) $target['1'];

        if (!in_array($field['targetType'], array('course','lesson'))){
            throw $this->createServiceException("targetType参数不正确");
        }
        
        $field['name'] = empty($category['name'])?' ':$category['name'];

        return $field;
    }

    private function checkQuestionType($type)
    {
        if (!in_array($type, array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
                throw $this->createServiceException('question type error！');
        }
    }

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    private function getQuizQuestionCategoryDao()
    {
        return $this->createDao('Quiz.QuizQuestionCategoryDao');
    }

    private function getQuestionFavoriteDao()
    {
        return $this->createDao('Quiz.QuestionFavoriteDao');
    }

    private function getQuestionImplementor($name)
    {
        return $this->createService('Quiz.'.preg_replace('/(?:^|_)([a-z])/e', "strtoupper('\\1')", $name).'QuestionImplementor');
    }
}
