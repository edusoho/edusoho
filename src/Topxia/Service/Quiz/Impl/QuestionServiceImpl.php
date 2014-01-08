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

    public function findQuestionsByParentIds(array $ids){
        return $this->getQuizQuestionDao()->findQuestionsByParentIds($ids);
    }

    public function findQuestionsByCourseId($courseId)
    {
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        
        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
        }
        
        $questions = ArrayToolkit::index($this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 99999),'id');

        $parentIds = array();
        foreach ($questions as $key => $question) {

            if ($question['questionType'] == 'material') {
                $parentIds[] = $question['id'];
            }
        }

        if (!empty($parentIds)) {

            $con['parentIds'] = $parentIds;
            $materialQuestions = ArrayToolkit::index($this->searchQuestion($con, array('createdTime' ,'DESC'), 0, 99999),'id');

            $questions = array_merge($questions, $materialQuestions);
        }
        
        return $questions;
    }

    public function findQuestionsTypeNumberByCourseId($courseId)
    {
        $lessons = $this->getCourseService()->getCourseLessons($courseId);

        $conditions['target']['course'] = array($courseId);
        
        $conditions['parentId'] = 0;

        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');
        }
        
        $questions = $this->searchQuestion($conditions, array('createdTime' ,'DESC'), 0, 99999);

        $nums  = array();
        foreach ($questions as $ques) {

            if (empty($nums[$ques['questionType']][$ques['difficulty']])) {
                $nums[$ques['questionType']][$ques['difficulty']] = 0;
            }

            $nums[$ques['questionType']][$ques['difficulty']]++;
        }
       
        return $nums;
    }

    public function checkQuesitonNumber($field, $courseId)
    {
        if(empty($field['isDiffculty']) || empty($field['isDiffculty']) || empty($field['questionType']) || empty($field['difficulty'])){
            $this->createNotFoundException();
        }

        $dictQuestionType = $field['questionType'];
        $dictDifficulty   = $field['difficulty'];
        $isDiffculty      = $field['isDiffculty'];
        $itemCounts       = $field['itemCounts'];

        $diff = array_diff($itemCounts, $dictQuestionType);

        if(empty($diff)){
            throw $this->createNotFoundException('参数错误');
        }

        $perventage['0'] = 10;
        $perventage['1'] = 40;
        $perventage['2'] = 50;

        if(($perventage['0'] + $perventage['1'] +$perventage['2']) != 100){
            throw $this->createNotFoundException('参数错误');
        }

        $questionNumbers = $this->getQuestionService()->findQuestionsTypeNumberByCourseId($courseId);

        $message = array();
        foreach ($itemCounts as $item) {

            list($type, $num) = $item;

            if ($num == 0)
                continue;

            if ($isDiffculty == 1 ){
                $needNums = array();
                $needNums['simple']     = (int) ($num * $perventage['0'] /100); 
                $needNums['ordinary']   = (int) ($num * $perventage['1'] /100); 
                $needNums['difficulty'] = (int) ($num * $perventage['2'] /100); 

                $otherNum = $num - ($needNums['simple'] + $needNums['ordinary'] + $needNums['difficulty']);

                if ($otherNum != 0){
                    $randNum = array_rand($needNums, 1);
                    $needNums[$randNum] = $needNums[$randNum] + $otherNum;
                }

                foreach ($needNums as $difficulty => $needNum) {
                    if(empty($questionNumbers[$type][$difficulty])) {

                        if($needNum != 0)
                            $message[] = "{$dictQuestionType[$type]}中{$dictDifficulty[$difficulty]}缺少{$needNum}题 ";
                    }
                    else if(($needNum - $questionNumbers[$type][$difficulty]) < 0) {

                        $needNum = abs($needNum - $questionNumbers[$type][$difficulty]);
                        $message[] = "{$dictQuestionType[$type]}中{$dictDifficulty[$difficulty]}缺少{$needNum}题 ";
                    }
                }
            } else {
                if(empty($questionNumbers[$type])){

                    $message[] = "{$dictQuestionType[$type]}缺少{$num}题 ";

                }else{

                    $typeSum = 0;
                    foreach ($questionNumbers[$type] as $questionNumber) {

                        $typeSum = $questionNumber + $typeSum;
                    }
                    if( ($typeSum - $num) < 0) {

                        $needNum = abs($typeSum - $num);
                        $message[] = "{$dictQuestionType[$type]}缺少{$needNum}题 ";
                    }
                }
            }
        }

        if(empty($message)){

            $message = false;
        }else{

            $message = array_merge(array('课程题库题目不足,无法生成试卷') , $message);
            $message = implode(',', $message);
        }

        return $message;
    }


    public function findRandQuestions($courseId, $testPaperId, $field){
        $testPaper = $this->getTestService()->getTestPaper($testPaperId);
        
        if(empty($field['itemCounts']) || empty($field['itemScores']) || empty($testPaper)){
            $this->createNotFoundException();
        }


        $scores = $field['itemScores'];
        $counts = $field['itemCounts'];

        $questions    = ArrayToolkit::index($this->getQuestionService()->findQuestionsByCourseId($courseId), 'id');

        $quNews = array();
        $quSons = array();

        foreach ($questions as $question) {

            if($question['parentId'] == 0) {

                $question['score'] = $scores[$question['questionType']] == 0 ? $question['score'] : 
                    (empty($scores[$question['questionType']])?$question['score']:$scores[$question['questionType']]);
                $quNews[$question['questionType']][] = $question;
            }else{

                $question['score'] = $scores['material'] == 0 ? $question['score'] : 
                    (empty($scores['material']) ? $question['score'] :$scores['material'] ) ;
                $quSons[] = $question;
            }
        }

        $seq = explode(',', $testPaper['metas']['question_type_seq']);

        $quRandoms = array();

        foreach ($seq as $type) {

            for($i = 0;$i<$counts[$type];$i++){
                $rand = array_rand($quNews[$type]);
                $quRandoms[] = $quNews[$type][$rand];
                unset($quNews[$type][$rand]);
            }   
        }

        return array_merge(ArrayToolkit::index($quRandoms, 'id'), ArrayToolkit::index($quSons, 'id'));
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

    public function findChoicesByQuestionIds(array $ids)
    {
        return $this->getQuizQuestionChoiceDao()->findChoicesByQuestionIds($ids);
    }

    public function findQuestionTargets($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course))
            return null;
        $lessons = $this->getCourseService()->getCourseLessons($courseId);

        $targets = array();

        $targets['course'.'-'.$course['id']] = '课程';

        foreach ($lessons as  $lesson) {
            $targets['lesson'.'-'.$lesson['id']] = '课时'.$lesson['number'].'-'.$lesson['title'];
        }

        return $targets;
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

    private function getTestService()
    {
        return $this->createService('Quiz.TestService');
    }

    private function getQuestionService()
    {
        return $this->createService('Quiz.QuestionService');
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
