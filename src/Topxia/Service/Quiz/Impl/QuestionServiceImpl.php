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
        return empty($question) ? array() : $this->getQuestionImplementor($question['questionType'])->getQuestion($question);
    }

    public function createQuestion($question)
    {
        $question = $this->filterCommonFields($question);
        $question['createdTime'] = time();
        return $this->getQuestionImplementor($question['type'])->createQuestion($question);
    }

    public function updateQuestion($id, $question)
    {
        $field = $this->filterCommonFields($question);
        return $this->getQuestionImplementor($question['type'])->updateQuestion($id, $question, $field);  
    }

    public function deleteQuestion($id)
    {
        $question = $this->getQuizQuestionDao()->getQuestion($id);
        if (empty($question)) {
            throw $this->createNotFoundException();
        }
        $this->getQuizQuestionDao()->deleteQuestion($id);

        $this->getQuizQuestionDao()->deleteQuestionsByParentId($id);

        $this->getQuizQuestionChoiceDao()->deleteChoicesByQuestionIds(array($id));
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


    public function findQuestionCountByTypeAndTypeIds($type,$ids)
    {

    }

    public function findQuestions ($ids)
    {
        $questions = QuestionSerialize::unserializes($this->findQuestionsByIds($ids));

        if (empty($questions)){
            throw $this->createNotFoundException('题目不存在！');
        }

        $choices = $this->findChoicesByQuestionIds($ids);

        $questions = ArrayToolkit::index($questions, 'id');

        if (!empty($choices)){
            foreach ($choices as $key => $value) {
                if (!array_key_exists('choices', $questions[$value['questionId']])) {
                    $questions[$value['questionId']]['choices'] = array();
                }
                // array_push($questions[$value['questionId']]['choices'], $value);
                $questions[$value['questionId']]['choices'][$value['id']] = $value;
            }

            // $choiceIndex = 65;
            // foreach ($choices as $key => $value) {
            //  $choices[$key]['choiceIndex'] = chr($choiceIndex);
            //  $choiceIndex++;
            // }

            // $question['choices'] = $choices;
        }
        return $questions;
    }



    // //TODO 
    // public function findQuestionsByCourseId($courseId)
    // {
    //     $lessons = $this->getCourseService()->getCourseLessons($courseId);
        
    //     $conditions['target']['course'] = array($courseId);
    //     if (!empty($lessons)){
    //         $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
    //     }
        
    //     $questions = ArrayToolkit::index($this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 999999),'id');

    //     $parentIds = array();
    //     foreach ($questions as $question) {

    //         if ($question['questionType'] == 'material') {

    //             $parentIds[] = $question['id'];
    //         }
    //     }

    //     if (!empty($parentIds)) {

    //         $materialQuestions = ArrayToolkit::index($this->searchQuestion(array('parentIds'=> $parentIds), array('createdTime' ,'DESC'), 0, 999999),'id');
    //         $questions = array_merge($questions, $materialQuestions);
    //     }
        
    //     return $questions;
    // }

    // //TODO 

    // public function findQuestionsTypeNumberByCourseId($courseId)
    // {
    //     $lessons = $this->getCourseService()->getCourseLessons($courseId);
        
    //     $conditions['parentId'] = 0;

    //     $conditions['target']['course'] = array($courseId);

    //     if (!empty($lessons)){
    //         $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
    //     }
        
    //     $questions = $this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 99999);

    //     $typeNums  = array();
    //     foreach ($questions as $question) {

    //         $type = $question['questionType'];

    //         $difficulty = $question['difficulty'];

    //         if (empty($typeNums[$type][$difficulty])) {

    //             $typeNums[$type][$difficulty] = 0;
    //         }

    //         $typeNums[$type][$difficulty]++;
    //     }

    //     $sum = array();
    //     foreach ($typeNums as $type => $difficultyNums) {

    //         $sum[$type] = 0;
    //         foreach ($difficultyNums as $num) {

    //             $sum[$type] = $sum[$type] + $num;
    //         }
    //     }

    //     $typeNums['sum'] = $sum;

    //     return $typeNums;
    // } 

    public function checkQuesitonNumber($field, $courseId)
    {

    }

    public function findQuestionsCountByTypeAndTypeIds($type, $ids)
    {
        return $this->getQuizQuestionDao()->findQuestionsCountByTypeAndTypeIds($type, $ids);
    }

    public function findRandQuestions($courseId, $testPaperId, $field) 
    {

    }

    // public function findRandQuestions($courseId, $testPaperId, $field){

    //     $testPaper = $this->getTestService()->getTestPaper($testPaperId);
        
    //     if(empty($field['itemCounts']) || empty($field['itemScores']) || empty($testPaper)){
    //         $this->createNotFoundException();
    //     }

    //     $scores = $field['itemScores'];
    //     $counts = $field['itemCounts'];

    //     if(empty($field['isDifficulty'])){
    //         $field['isDifficulty'] = 0;
    //     }

    //     $questions = ArrayToolkit::index($this->findQuestionsByCourseId($courseId), 'id');

    //     $quNews = array();
    //     $quSons = array();

    //     foreach ($questions as $question) {

    //         if($question['parentId'] == 0) {

    //             $question['score'] = $scores[$question['questionType']] == 0 ? $question['score'] : 
    //                 (empty($scores[$question['questionType']])?$question['score']:$scores[$question['questionType']]);
    //             $quNews[$question['questionType']][$question['difficulty']][] = $question;
    //         }else{

    //             $question['score'] = $scores['material'] == 0 ? $question['score'] : 
    //                 (empty($scores['material']) ? $question['score'] :$scores['material'] ) ;
    //             $quSons[] = $question;
    //         }
    //     }

    //     $question_type_seq = explode(',', $testPaper['metas']['question_type_seq']);

    //     $randoms = array();
    //     foreach ($question_type_seq as $type) {

    //         if($field['isDifficulty'] == 0){

    //             for($i = 0;$i<$counts[$type];$i++){

    //                 $randDifficulty = array_rand($quNews[$type]);

    //                 $randId = array_rand($quNews[$type][$randDifficulty]);

    //                 $randoms[] = $quNews[$type][$randDifficulty][$randId];

    //                 unset($quNews[$type][$randDifficulty][$randId]);

    //                 if(count($quNews[$type][$randDifficulty]) ==0){

    //                     unset($quNews[$type][$randDifficulty]);
    //                 }
    //             } 
    //         }else{

    //             $needNums = $this->getItemDifficultyNeedNums($counts[$type], $field['perventage']);

    //             foreach ($needNums as $difficulty => $needNum) {

    //                 if ($difficulty == 'otherNum') {

    //                     for($i = 0;$i<$needNum;$i++){

    //                         $randDifficulty = array_rand($quNews[$type]);

    //                         $randId = array_rand($quNews[$type][$randDifficulty]);

    //                         $randoms[] = $quNews[$type][$randDifficulty][$randId];

    //                         unset($quNews[$type][$randDifficulty][$randId]);

    //                         if(count($quNews[$type][$randDifficulty]) ==0){

    //                             unset($quNews[$type][$randDifficulty]);
    //                         }
    //                     } 

    //                     continue;
    //                 }

    //                 for($i = 0; $i<$needNum; $i++){

    //                     $randId = array_rand($quNews[$type][$difficulty]);
    //                     $randoms[] = $quNews[$type][$difficulty][$randId];
    //                     unset($quNews[$type][$difficulty][$randId]);

    //                     if(count($quNews[$type][$difficulty]) ==0){

    //                         unset($quNews[$type][$difficulty]);
    //                     }

    //                 }

    //             }

    //         }

    //     }

    //     return array_merge(ArrayToolkit::index($randoms, 'id'), ArrayToolkit::index($quSons, 'id'));
    // }


    public function getCategory($id){
        return $this->getQuizQuestionCategoryDao()->getCategory($id);
    }

    public function createCategory($category){
        $field['userId'] = $this->getCurrentUser()->id;
        $field['name'] = empty($category['name'])?'':$category['name'];
        $field['createdTime'] = time();
        $field['targetId'] = empty($category['courseId'])?'':$category['courseId'];
        $field['targetType'] = "course";
        $field['seq'] = $this->getQuizQuestionCategoryDao()->getCategorysCountByCourseId($field['targetId'])+1;

        return $this->getQuizQuestionCategoryDao()->addCategory($field);
    }

    public function updateCategory($categoryId, $category){
        $field['name'] = empty($category['name'])?'':$category['name'];
        $field['updatedTime'] = time();
        return $this->getQuizQuestionCategoryDao()->updateCategory($categoryId, $field);
    }

    public function deleteCategory($id)
    {
        $category = $this->getQuizQuestionCategoryDao()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }
        $this->getQuizQuestionCategoryDao()->deleteCategory($id);

        $categorys = $this->findCategorysByCourseIds(array($category['targetId']));
        $seq = 1;
        foreach ($categorys as $category) {
            $fields = array('seq' => $seq);
            $this->getQuizQuestionCategoryDao()->updateCategory($category['id'], $fields);
            $seq ++;
        }
    }

    public function findCategorysByCourseIds(array $id){
        return $this->getQuizQuestionCategoryDao()->findCategorysByCourseIds($id);
    }

    public function sortCategories($courseId, array $categoryIds)
    {
        $categorys = $this->findCategorysByCourseIds(array($courseId));

        if (count($categoryIds) != count($categorys)) {
            throw $this->createServiceException('categoryIds参数不正确');
        }

        $diffCategoryIds = array_diff(array_keys($categoryIds), array_keys($categorys));
        if (!empty($diffCategoryIds)) {
            throw $this->createServiceException('categoryIds参数不正确');
        }

        $categorys = ArrayToolkit::index($categorys,'id');
        $seq = 1;
        foreach ($categoryIds as $categoryId) {
            $fields = array('seq' => $seq);
            $this->getQuizQuestionCategoryDao()->updateCategory($categoryId, $fields);
            $seq ++;
        }
    }

    public function findChoicesByQuestionIds(array $ids)
    {
        return $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds($ids);
    }
  
    private function filterCommonFields($question)
    {
        if (!in_array($question['type'], array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
                throw $this->createServiceException('question type error！');
        }

        $field = array();
        $field['questionType'] = $question['type'];
        $field['stem']         = empty($question['stem']) ? '' : $this->purifyHtml($question['stem']);
        $field['difficulty']   = empty($question['difficulty']) ? 'simple': $question['difficulty'];
        $field['userId']       = $this->getCurrentUser()->id;
        $field['analysis']     = empty($question['analysis']) ? '': $question['analysis'];
        $field['score']        = empty($question['score'])? 0 : $question['score'];
        $field['categoryId']   = empty($question['categoryId']) ? 0 : (int) $question['categoryId'];
        $field['parentId'] = empty($question['parentId']) ? 0 : (int) trim($question['parentId']);
        $field['updatedTime']  = time();

        if(!empty($question['target'])){

            $target = explode('-', $question['target']);

            if (count($target) != 2){
                throw $this->createServiceException("target参数不正确");
            }

            $field['targetType'] = $target['0'];

            $field['targetId'] = (int) $target['1'];

            if (!in_array($field['targetType'], array('course','lesson'))){
                throw $this->createServiceException("targetType参数不正确");
            }
        }

        return $field;
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

    private function getQuizQuestionDao()
    {
        return $this->createDao('Quiz.QuizQuestionDao');
    }

    private function getQuizQuestionChoiceDao()
    {
        return $this->createDao('Quiz.QuizQuestionChoiceDao');
    }

    private function getQuizQuestionCategoryDao()
    {
        return $this->createDao('Quiz.QuizQuestionCategoryDao');
    }

    private function getQuestionImplementor($name)
    {
        return $this->createService('Quiz.'.preg_replace('/(?:^|_)([a-z])/e', "strtoupper('\\1')", $name).'QuestionImplementor');
    }
}
