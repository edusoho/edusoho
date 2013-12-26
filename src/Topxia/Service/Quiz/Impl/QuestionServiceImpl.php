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
        $field = $this->filterCommonFields($question);
        $field['createdTime'] = time();
        $field['updatedTime'] = time();
        return $this->getQuestionImplementor($question['type'])->createQuestion($question, $field);
    }

    public function updateQuestion($id, $question)
    {
        $field = $this->filterCommonFields($question);
        $field['updatedTime'] = time();
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

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao()->searchQuestion($conditions, $orderBy, $start, $limit);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao()->searchQuestionCount($conditions);
    }

    public function findQuestionsByIds(array $ids){
        return $this->getQuizQuestionDao()->findQuestionsByIds($ids);
    }

    public function findQuestionsForTestPaper($field, $courseId)
    {
        $itemCounts = $field['itemCounts'];
        $itemScores = $field['itemScores'];

        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
        }
        
        $questions = ArrayToolkit::index($this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 99999),'id');

        $parentIds = array();
        foreach ($questions as $key => $question) {

            $questions[$key]['score'] = $itemScores[$question['questionType']]==0 ? $question['score'] : $itemScores[$question['questionType']];
            if ($question['parentId'] != 0) {
                continue;
            }

            if ($question['questionType'] == 'material') {
                $parentIds[] = $question['id'];
            }
        }

        if (!empty($parentIds)) {
            $con['parentIds'] = $parentIds;

            $materialQuestions = ArrayToolkit::index($this->searchQuestion($con, array('createdTime' ,'DESC'), 0, 99999),'id');

            foreach ($materialQuestions as $key => $question) {
                $materialQuestions[$key]['score'] = $itemScores['material']==0 ? $question['score'] : $itemScores['material'];
            }
            $questions = array_merge($questions, $materialQuestions);
        }
        
        $questions['data'] = $data;
        return $questions;
    }

    public function getQuestionsNumberByCourseId($courseId)
    {
        $lessons = $this->getCourseService()->getCourseLessons($courseId);

        $conditions['target']['course'] = array($courseId);
        
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
        }
        
        $questions = $this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 99999);

        $data = $parentIds = array();
        foreach ($questions as $question) {

            if ($question['parentId'] != 0) {
                continue;
            }

            if (empty($data[$question['questionType']][$question['difficulty']])) {
                $data[$question['questionType']][$question['difficulty']] = 1;
            }

            $data[$question['questionType']][$question['difficulty']]++;
        }
       
        return $data;
    }

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

    public function findChoicesByQuestionIds(array $id)
    {
        return $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds($id);
    }

    private function filterCommonFields($question)
    {
        if (!in_array($question['type'], array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
                throw $this->createServiceException('type error！');
        }
        if (!ArrayToolkit::requireds($question, array('difficulty'))) {
                throw $this->createServiceException('缺少必要字段difficulty, 创建课程失败！');
        }

        $field = array();
        $field['questionType'] = $question['type'];
        $field['stem']         = empty($question['stem'])?'':$question['stem'];
        $field['stem']         = $this->purifyHtml($question['stem']);
        $field['difficulty']   = empty($question['difficulty']) ? ' ': $question['difficulty'];
        $field['userId']       = $this->getCurrentUser()->id;

        $field['analysis']   = empty($question['analysis'])?'':$question['analysis'];
        $field['score']      = empty($question['score'])?'':$question['score'];
        $field['categoryId'] = (int) $question['categoryId'];

        if(!empty($question['target'])){
            $target = explode('-', $question['target']);
            if (count($target) != 2){
                throw $this->createServiceException("target参数不正确");
            }
            $field['targetType'] = $target['0'];
            $field['targetId'] = $target['1'];
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
        $field['targetId'] = $target['1'];
        if (!in_array($field['targetType'], array('course','lesson'))){
            throw $this->createServiceException("targetType参数不正确");
        }
        
        $field['name'] = empty($category['name'])?' ':$category['name'];
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

    private function getQuizQuestionCategoryDao()
    {
        return $this->createDao('Quiz.QuizQuestionCategoryDao');
    }

    private function getQuestionImplementor($name)
    {
        
        return $this->createService('Quiz.'.preg_replace('/(?:^|_)([a-z])/e', "strtoupper('\\1')", $name).'QuestionImplementor');
    }
}
