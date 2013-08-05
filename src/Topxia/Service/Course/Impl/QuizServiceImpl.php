<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\QuizService;
use Topxia\Common\ArrayToolkit;

class QuizServiceImpl extends BaseService implements QuizService
{

    public function createLessonQuizItem($courseId, $lessonId, $lessonQuizItemInfo)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $lessonQuizItemInfo['courseId'] = $lesson['courseId'];
        $lessonQuizItemInfo['lessonId'] = $lesson['id'];
        $lessonQuizItemInfo['userId'] = $this->getCurrentUser()->id;
        $lessonQuizItemInfo['createdTime'] = time(); 

        $quizItemAnswers = explode(";", $lessonQuizItemInfo['answers']);
        if(count($quizItemAnswers) > 1){
            $lessonQuizItemInfo['type'] = 'multiple';
        } else {
            $lessonQuizItemInfo['type'] = 'single';
        }

        $lessonQuizItem = $this->getLessonQuizItemDao()->addLessonQuizItem($lessonQuizItemInfo);
        $lessonQuizItem['description'] = strip_tags($lessonQuizItem['description']);
        return $lessonQuizItem;
    }

    public function getLessonQuizItem($lessonQuizItemId)
    {
        $getedLessonQuizItem = $this->getLessonQuizItemDao()->getLessonQuizItem($lessonQuizItemId);
        if(!$getedLessonQuizItem){
            return null;
        } else {
            return $getedLessonQuizItem;
        }
    }

    public function findLessonQuizItems($courseId, $lessonId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $lessonQuizItems = $this->getLessonQuizItemDao()->findLessonQuizItemsByCourseIdAndLessonId($lesson['courseId'], $lesson['id']);
        
        foreach ($lessonQuizItems as $key => &$lessonQuizItem) {
            $lessonQuizItem['description'] = strip_tags($lessonQuizItem['description']);
        }

        if(!empty($lessonQuizItems)){
            return $lessonQuizItems;
        } else {
            return null;
        }
    }

    public function getUserLessonQuiz($courseId, $lessonId, $userId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $getedLessonQuiz = $this->getLessonQuizDao()->getLessonQuizByCourseIdAndLessonIdAndUserId(
            $lesson['courseId'], $lesson['id'], $userId);
        if($getedLessonQuiz){
            return $getedLessonQuiz;
        } else {
            return array();
        }

    }

    public function findQuizItemsInLessonQuiz($lessonQuizId)
    {
        $quiz = $this->getLessonQuizDao()->getLessonQuiz($lessonQuizId);
        $quizItemIds = explode("|", $quiz['itemIds']);

        if(!empty($quizItemIds)){
            $quizItems = $this->getLessonQuizItemDao()->findLessonQuizItemsByIds($quizItemIds);
            foreach ($quizItems as $key => &$item) {
                $item['Number2Chinese'] = $this->number2Chinese($key+1);
                $item['choices'] = json_decode($item['choices']);
                $item['choices'] = $this->randomFullArray($item['choices']);
                $item['description'] = strip_tags($item['description']);
            }
            return $quizItems;
        } else {
            return array();
        }        
    }

    public function findLessonQuizItemIds($courseId, $lessonId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $lessonQuizItemIds = $this->getLessonQuizItemDao()->findItemIdsByCourseIdAndLessonId($lesson['courseId'], $lesson['id']);
        if(!empty($lessonQuizItemIds)){
            return $lessonQuizItemIds;
        } else {
            return null;
        }
    }

    public function answerLessonQuizItem($lessonQuizId, $itemId, $answerContent)
    {
        $checkResult = $this->checkQuizAndItem($lessonQuizId, $itemId);
        $lessonQuizItemAnswerInfo = array();
        $answersResult = $this->checkAnswerContent($checkResult['item']['answers'], $answerContent);
        if($answersResult){
            $lessonQuizItemAnswerInfo['isCorrect'] = 1;
        } else {
            $lessonQuizItemAnswerInfo['isCorrect'] = 0;
        }
        $lessonQuizItemAnswerInfo['createdTime'] = time();
        $lessonQuizItemAnswerInfo['quizId'] = $checkResult['quiz']['id'];
        $lessonQuizItemAnswerInfo['itemId'] = $checkResult['item']['id'];
        $lessonQuizItemAnswerInfo['answers'] = $answerContent;
        $lessonQuizItemAnswerInfo['userId'] = $this->getCurrentUser()->id;

        $itemAnswer = $this->getLessonQuizItemAnswerDao()->getLessonQuizItemAnswerByQuizIdAndItemIdAndUserId(
            $checkResult['quiz']['id'], $checkResult['item']['id'], $this->getCurrentUser()->id);
        if(!empty($itemAnswer)){
             throw $this->createServiceException("每一道题目只能答一次!");
        }

        $this->getLessonQuizItemAnswerDao()->addLessonQuizItemAnswer($lessonQuizItemAnswerInfo);
        
        if($answersResult){
            return "correct";
        } else {
            return "wrong";
        }
    }

    public function checkUserLessonQuizResult($quizId)
    {
        $quiz = $this->getLessonQuizDao()->getLessonQuiz($quizId);
        $itemIds = explode("|", $quiz['itemIds']);
        $itemIdsCount = count($itemIds);
        $correctAnswersCount = $this->getLessonQuizItemAnswerDao()->getCorrectAnswersCountByUserIdAndQuizId($this->getCurrentUser()->id, $quiz['id']);
        $score = round(100*($correctAnswersCount/$itemIdsCount), 1);
        $this->getLessonQuizDao()->updateLessonQuiz($quiz['id'], array('score'=>$score, 'endTime'=>time()));
        return array("score"=>$score, "correctCount"=>$correctAnswersCount, "wrongCount"=> ($itemIdsCount - $correctAnswersCount));
    }

    public function editLessonQuizItem($lessonQuizItemId, $fields)
    {
        $lessonQuizItem = $this->getLessonQuizItemDao()->getLessonQuizItem($lessonQuizItemId);
        if(!$lessonQuizItem){
            throw $this->createServiceException("编辑问题失败，本问题不存在!");
        }
        if(isset($fields['choices'])){
            $fields['choices'] = json_encode($fields['choices']);
        }
        $quizItemAnswers = explode(";", $fields['answers']);
        if(count($quizItemAnswers) > 1){
            $fields['type'] = 'multiple';
        } else {
            $fields['type'] = 'single';
        }
        return $this->getLessonQuizItemDao()->updateLessonQuizItem($lessonQuizItem['id'], $fields);
    }

    public function deleteLessonQuizItem($id)
    {
        $lessonQuizItem = $this->getLessonQuizItemDao()->getLessonQuizItem($id);
        if(!$lessonQuizItem){
            throw $this->createServiceException("删除问题失败，本问题不存在!");
        }
        return $this->getLessonQuizItemDao()->deleteLessonQuizItem($id);
    }

    public function deleteLessonQuiz($quizId)
    {
        $this->clearUserAnswersInQuiz($this->getCurrentUser()->id, $quizId);
        $lessonQuiz = $this->getLessonQuizDao()->getLessonQuiz($quizId);
        if(!$lessonQuiz){
            throw $this->createServiceException("删除问题失败，本问题不存在!");
        }
        return $this->getLessonQuizDao()->deleteLessonQuiz($quizId);
    }

    public function createLessonQuiz($courseId, $lessonId, $itemIds)
    {
        
        $randIds = array();
        $lessonQuizInfo = array();
        $lesson = $this->checkForCreateQuiz($courseId, $lessonId, $itemIds);
        $lessonQuizInfo['itemIds'] = '|';
        $keys = array_rand($itemIds, count($itemIds));
        if(is_array($keys)){
            foreach ($keys as $key => $value) {
                array_push($randIds, $itemIds[$value]);
            }
        } else {
            array_push($randIds, $itemIds[$keys]);
        }

        foreach (array_values($randIds) as $key => $value) {
            $lessonQuizInfo['itemIds'] = $lessonQuizInfo['itemIds'].$value.'|';
        }
        $lessonQuizInfo['itemIds'] = substr($lessonQuizInfo['itemIds'], 1);
        $lessonQuizInfo['itemIds'] = substr($lessonQuizInfo['itemIds'], 0, strlen($lessonQuizInfo['itemIds'])-1);

        $lessonQuizInfo['startTime'] = time();
        $lessonQuizInfo['courseId'] = $lesson['courseId'];
        $lessonQuizInfo['lessonId'] = $lesson['id'];
        $lessonQuizInfo['userId'] = $this->getCurrentUser()->id;
        $lessonQuizInfo['answerIds'] = '';
        $lessonQuizInfo['score'] = 0;
        $lessonQuizInfo['endTime'] = 0;

        return $this->getLessonQuizDao()->addLessonQuiz($lessonQuizInfo);
    }

    private function checkForCreateQuiz($courseId, $lessonId, $itemIds)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if(empty($course)){
            throw $this->createServiceException("创建问题失败，本课程不存在!");
        }

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if(empty($lesson)){
            throw $this->createServiceException("创建问题失败，本课时不存在!");
        }

        if(count(array_unique($itemIds)) != count($itemIds)){
            throw $this->createServiceException("出现重复的问题Id!");
        }
        return $lesson;
    }
    
    private  function randomFullArray($input)
    {
        if (!is_array($input)) return $input;
        $input = array_values($input);
        $keys = range(0, count($input)-1);
        shuffle($keys);
        $result = array();
        foreach ($keys as $key) {
            $result[$key] = $input[$key];
        }
        return $result;
    }
    
    private function checkAnswerContent($standardAnswers, $userAnswers)
    {
        $standardAnswersArray = explode(";", $standardAnswers);
        $userAnswersArray = explode(";", $userAnswers);
        $arrayDiff1 = array_diff($standardAnswersArray, $userAnswersArray);
        $arrayDiff2 = array_diff($userAnswersArray, $standardAnswersArray);
        if (empty($arrayDiff1) && empty($arrayDiff2)) {
            return true;
        } else {
            return false;
        }
    }
    
    private function clearUserAnswersInQuiz($userId, $quizId)
    {
        $user = $this->getCurrentUser();
        if($user['id'] != $userId){
            throw $this->createServiceException("用户已经尚未登陆，操作失败!");
        }
        $quiz = $this->getLessonQuizDao()->getLessonQuiz($quizId);
        if(empty($quiz)){
            throw $this->createServiceException("测试不存在!");
        }
        return  $this->getLessonQuizItemAnswerDao()->deleteLessonQuizItemAnswersByUserIdAndQuizId($userId, $quizId);
    }

    private function checkCourseAndLesson($courseId, $lessonId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if(empty($course)){
            throw $this->createServiceException("操作失败，本课程不存在!");
        }
        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if(empty($lesson)){
            throw $this->createServiceException("操作失败，本课时不存在!");
        }
        return $lesson;
    }

    private function checkQuizAndItem($lessonQuizId, $itemId)
    {
        $lessonQuizItem = $this->getLessonQuizItemDao()->getLessonQuizItem($itemId);
        if(empty($lessonQuizItem)){
            throw $this->createServiceException("本问题不存在!");
        }

        $lessonQuiz = $this->getLessonQuizDao()->getLessonQuiz($lessonQuizId);
        if(empty($lessonQuiz)){
            throw $this->createServiceException("本测验不存在!");
        }
        return array('quiz'=>$lessonQuiz, 'item'=>$lessonQuizItem);
    }

    private function number2Chinese($num, $m = 1) 
    {
        $numbers = array(0 => "零","一","二","三","四","五","六","七","八","九");
         // array(0=>"零","壹","贰","叁","肆","伍","陆","柒","捌","玖");
        $unit1 = array(0 => "","十","百","千");
         // var $unit1 = array(1=>"拾","佰","仟");
        $unit2 = array(0 => "","万","亿");
      
        if(!is_numeric($num)){
            exit("Number Error!");
        }

        $num = trim(strval($num));
        $zs = null;
        $xs = null;
        $chn = null;
        $len = strlen($num);
        $i = strpos($num,".");

        if(is_numeric($i)){
           $zs = $i == 0?"0":substr($num,0,$i);
           if($i == 0){
            $zs = "0";
            $xs = substr($num,1);
           }else if($i == $len - 1){
            $zs = substr($num,0,$i);
            ;
           }else{
            $zs = substr($num,0,$i);
            $xs = substr($num,$i + 1);
           }
          } else{
           $zs = $num;
        }
      
        if($zs){
            $i = 0;
            $len = strlen($zs);
            while($i < $len && $zs[$i] == "0"){
                $i++;
            }
            if($i){
                $zs = substr($zs,$i);
            }
        }
      
        if($xs){
            $i = strlen($xs) - 1;
            while($i && $xs[$i] == "0"){
                $i--;
            }
            if($i > -1){
                $xs = substr($xs,0,$i + 1);
            }
        }
      
        if($zs){
            $len = strlen($zs);
            $i = $len;
            $parts = array();
            while($i > 4){
                $i -= 4;
                $parts[] = substr($zs,$i,4);
            }
            $parts[] = substr($zs,0,$i);
            $chn = '';
            $l = 0;
            foreach($parts as $part){
                if($part == "0000"){ continue; }
                $t = '';
                for($i = 0,$j = strlen($part);$i < $j;$i++){
                    $p = 0;
                    while($i + $p < $j && $part[$i + $p] == "0"){
                        $p++;
                    }
                    if($i + $p == $j){ continue; }
                    if($p){ $i += $p - 1; }
                    $t .= $numbers[$part[$i]];
                    if($part[$i]){
                        $t .= $unit1[$j - $i - 1];
                    }
                }
        
                if(!isset($unit2[$l])){
                    if($l % 2){
                        $unit2[$l] = $unit2[$l - 1] . $unit2[1];
                    } else {
                        $unit2[$l] = $unit2[$l - 1] . $unit2[2];
                    }
                }
                $t .= $unit2[$l];
                $chn = $t . $chn;
                $l++;
            }
        } else {
            $chn = "零";
        }
      
        if($xs){
           $chn .= "点";
            for($i = 0,$j = strlen($xs);$i < $j;$i++){
                $chn .= $numbers[$xs[$i]];
            }
        }
      
        return $chn;
    }

    private function getLessonQuizItemAnswerDao()
    {
        return $this->createDao('Course.LessonQuizItemAnswerDao');
    }

    private function getLessonQuizItemDao()
    {
        return $this->createDao('Course.LessonQuizItemDao');
    }

     private function getLessonQuizDao()
    {
        return $this->createDao('Course.LessonQuizDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}