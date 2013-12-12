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
        return $this->getQuestionImplementor($question['questionType'])->getQuestion($question);
    }

    public function createQuestion($question)
    {
        $field = $this->filterCommonFields($question);
        $field['createdTime'] = time();
        return $this->getQuestionImplementor($question['type'])->createQuestion($question, $field);
    }

    public function updateQuestion($question)
    {
        $field = $this->filterCommonFields($question);
        $field['updatedTime'] = time();
        return $this->getQuestionImplementor($question['type'])->updateQuestion($question, $field);  
    }

    public function deleteQuestion($id)
    {
        $question = $this->getQuizQuestionDao()->getQuestion($id);
        if (empty($question)) {
            throw $this->createNotFoundException();
        }
        $this->getQuizQuestionDao()->deleteQuestion($id);
        $this->getQuizQuestionDao()->deleteQuestionByParentId($id);
        $this->getQuizQuestionChoiceDao()->deleteChoicesByQuestionIds(array($id));
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
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
        return $this->getQuizQuestionCategoryDao() -> addCategory($field);
    }

    public function editCategory($category){
        $field['name'] = empty($category['name'])?'':$category['name'];
        $field['updatedTime'] = time();
        return $this->getQuizQuestionCategoryDao()->updateCategory($category['id'], $field);
    }

    public function deleteCategory($id)
    {
        $category = $this->getQuizQuestionCategoryDao()->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }
        $this->getQuizQuestionCategoryDao()->deleteCategory($id);
    }


    public function findCategoryByCourseIds(array $id){
        return $this->getQuizQuestionCategoryDao() -> findCategoryByCourseIds($id);
    }

    public function sortCourseItems($ss, array $itemIds)
    {
        $items = $this->getCourseItems($ss);
        $existedItemIds = array_keys($items);

        if (count($itemIds) != count($existedItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $diffItemIds = array_diff($itemIds, array_keys($items));
        if (!empty($diffItemIds)) {
            throw $this->createServiceException('itemdIds参数不正确');
        }

        $lessonId = $chapterId = $seq = 0;
        $currentChapter = array('id' => 0);

        foreach ($itemIds as $itemId) {
            $seq ++;
            list($type, ) = explode('-', $itemId);
            switch ($type) {
                case 'lesson':
                    $lessonId ++;
                    $item = $items[$itemId];
                    $fields = array('number' => $lessonId, 'seq' => $seq, 'chapterId' => $currentChapter['id']);
                    if ($fields['number'] != $item['number'] or $fields['seq'] != $item['seq'] or $fields['chapterId'] != $item['chapterId']) {
                        $this->getLessonDao()->updateLesson($item['id'], $fields);
                    }
                    break;
                case 'chapter':
                    $chapterId ++;
                    $item = $currentChapter = $items[$itemId];
                    $fields = array('number' => $chapterId, 'seq' => $seq);
                    if ($fields['number'] != $item['number'] or $fields['seq'] != $item['seq']) {
                        $this->getChapterDao()->updateChapter($item['id'], $fields);
                    }
                    break;
            }
        }
    }

    public function findChoicesByQuestionIds(array $id)
    {
        return $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds($id);
    }

    private function filterCommonFields($question)
    {
        if (!in_array($question['type'], array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
            $question['type'] = 'choice';
        }
        if (!ArrayToolkit::requireds($question, array('difficulty', 'stem'))) {
                throw $this->createServiceException('缺少必要字段difficulty, stem, 创建课程失败！');
        }

        $field = array();
        $field['questionType'] = $question['type'];
        $field['stem'] = $this->purifyHtml($question['stem']);
        $field['difficulty'] = empty($question['difficulty']) ?  ' ': $question['difficulty'];
        $field['userId'] = $this->getCurrentUser()->id;

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
