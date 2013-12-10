<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionService;
use Topxia\Common\ArrayToolkit;

class QuestionServiceImpl extends BaseService implements QuestionService
{
    public function addQuestion($courseId, $question)
    {
        $questionField = $this->checkCommonFields($courseId, $question);
        $questionField['createdTime'] = time();
        switch ($question['type']) {
            case 'choice':
                if (!empty($question['parentId'])){
                    $questionField['parentId'] = (int) trim($question['parentId']);
                }
                if (!ArrayToolkit::requireds($question, array('choices'))) {
                    throw $this->createServiceException('缺少必要字段,choices，创建课程失败！');
                }
                $choiceField = $this->filterChoiceFields($question);
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
                );
                $choices = array();
                foreach ($choiceField['choices'] as $key => $content) {
                    $choice['questionId'] = $questionResult['id'];
                    $choice['content'] = $content;
                    $choiceResult = $this->getQuizQuestionChoiceDao()->addChoice($choice);
                    if (in_array($key, $choiceField['answers'])){
                        $choices[] = $choiceResult;
                    }
                }
                $questionField = array();
                $questionField['answer'] =  ArrayToolkit::column($choices,'id');
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($questionResult['id'], QuestionSerialize::serialize($questionField))
                );
                break;
            case 'essay':
            case 'determine':
                if (!empty($question['parentId'])){
                    $questionField['parentId'] = (int) trim($question['parentId']);
                }
                if (empty($question['answers'])){
                    throw $this->createServiceException('缺少必要字段,answers，创建课程失败！');
                }
                $questionField['answer'] = $question['answers'];
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
                );
                break;
            case 'fill':
                if (!empty($question['parentId'])){
                    $questionField['parentId'] = (int) trim($question['parentId']);
                }
                preg_match_all('/\[\[(.*?)\]\]/', $questionField['stem'], $answer);//全部取出
                $questionField['stem']  = preg_replace('/\[\[([a-zA-Z0-9\x7f-\xff]+)\]\]/', '(____)', $questionField['stem']);//替换
                if (count($answer['1']) == 0){
                    throw $this->createServiceException('该问题没有答案或答案格式不正确！');
                }
                //$aa    =    preg_replace('/\(\_\_\_\_\)/', '---------', $aaaaa);
                $questionField['answer'] = $answer;
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
                );
                break;
            case 'material':
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->addQuestion(QuestionSerialize::serialize($questionField))
                );
                break;
        }
            
        return $questionResult;   
    }

    public function updateQuestion($courseId, $question)
    {
        $id = $question['id'];
        $questionField = $this->checkCommonFields($courseId, $question);
        $questionField['updatedTime'] = time();

        switch ($question['type']) {
            case 'choice':
               if (!ArrayToolkit::requireds($question, array('choices'))) {
                    throw $this->createServiceException('缺少必要字段,choices，创建课程失败！');
                }
                $choiceField = $this->filterChoiceFields($question);
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($questionField))
                );
                $this->getQuizQuestionChoiceDao()->deleteChoicesByQuestionIds(array($id));
                $choices = array();
                foreach ($choiceField['choices'] as $key => $content) {
                    $choice['questionId'] = $questionResult['id'];
                    $choice['content'] = $content;
                    $choiceResult = $this->getQuizQuestionChoiceDao()->addChoice($choice);
                    if (in_array($key, $choiceField['answers'])){
                        $choices[] = $choiceResult;
                    }
                }
                $questionField = array();
                $questionField['answer'] =  ArrayToolkit::column($choices,'id');
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($questionResult['id'], QuestionSerialize::serialize($questionField))
                );
                break;
            case 'essay':
            case 'determine':
                if(empty($question['answers'])){
                    throw $this->createServiceException('缺少必要字段,answers，创建课程失败！');
                }
                $questionField['answer'] = $question['answers'];
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($questionField))
                );
                break;
            case 'fill':
                preg_match_all('/\[\[(.*?)\]\]/', $questionField['stem'], $answer);
                $questionField['stem']  = preg_replace('/\[\[([a-zA-Z0-9\x7f-\xff]+)\]\]/', '(____)', $questionField['stem']);
                if(count($answer['1']) == 0){
                    throw $this->createServiceException('该问题没有答案或答案格式不正确！');
                }
                $questionField['answer'] = $answer;
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($questionField))
                );
                break;
            case 'material':
                $questionResult =  QuestionSerialize::unserialize(
                    $this->getQuizQuestionDao()->updateQuestion($id, QuestionSerialize::serialize($questionField))
                );
                break;
        }
        return $questionResult;   
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

    public function createCategory($courseId, $category){
        // $field = $this->checkCategoryFields($courseId, $category);
        $field['userId'] = $this->getCurrentUser()->id;
        $field['name'] = $category['name'];
        $field['createdTime'] = time();
        $field['targetId'] = $courseId;
        $field['targetType'] = "course";
        return $this->getQuizQuestionCategoryDao() -> addCategory($field);
    }

    public function editCategory($courseId,$category){
        // $field = $this->checkCategoryFields($courseId, $category);
        $field['name'] = $category['name'];
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

    public function getQuestionTarget($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)){
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

    public function sortCourseItems($courseId, array $itemIds)
    {
        $items = $this->getCourseItems($courseId);
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

    public function getCategory($id){
        return $this->getQuizQuestionCategoryDao()->getCategory($id);
    }

    public function getQuestion($id)
    {
        return QuestionSerialize::unserialize($this->getQuizQuestionDao()->getQuestion($id));
    }

    public function findChoicesByQuestionIds(array $id)
    {
        return $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds($id);
    }

    public function searchQuestionCount(array $conditions){
        return $this->getQuizQuestionDao() -> searchQuestionCount($conditions);
    }

    public function searchQuestion(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionDao() -> searchQuestion($conditions, $orderBy, $start, $limit);
    }

    public function searchCategoryCount(array $conditions){
        return $this->getQuizQuestionCategoryDao() -> searchCategoryCount($conditions);
    }

    public function findCategoryByCourseIds(array $id){
        return $this->getQuizQuestionCategoryDao() -> findCategoryByCourseIds($id);
    }

    public function searchCategory(array $conditions, array $orderBy, $start, $limit){
        return $this->getQuizQuestionCategoryDao() -> searchCategory($conditions, $orderBy, $start, $limit);
    }

    private function checkCommonFields($courseId,$question)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        if (!in_array($question['type'], array('choice', 'fill', 'material', 'essay', 'determine'))) {
            $question['type'] = 'choice';
        }
        if (!ArrayToolkit::requireds($question, array('target', 'difficulty', 'stem', 'type'))) {
                throw $this->createServiceException('缺少必要字段,target, difficulty, stem, type，创建课程失败！');
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

    private function checkCategoryFields($courseId, $category)
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

