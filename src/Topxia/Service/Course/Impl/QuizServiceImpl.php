<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\QuizService;
use Topxia\Common\ArrayToolkit;

class QuizServiceImpl extends BaseService implements QuizService
{

    public function createItem(array $item)
    {
        $course = $this->getCourseService()->tryManageCourse($item['courseId']);

        $item = ArrayToolkit::parts($item, array('courseId', 'lessonId', 'description', 'level', 'choices', 'answers'));

        $lesson = $this->getCourseService()->getCourseLesson($item['courseId'], $item['lessonId']);
        if (empty($lesson)) {
            throw $this->createServiceException("课时(#{$item['lessonId']})不存在，创建测验题目失败！");
        }

        $this->checkItem($item);

        $item['type'] = count($item['answers']) > 1 ? 'multiple' : 'single';
        $item['userId'] = $this->getCurrentUser()->id;
        $item['createdTime'] = time();
        return ItemSerialize::unserialize(
            $this->getCourseQuizItemDao()->addQuizItem(ItemSerialize::serialize($item))
        );
    }

    public function updateItem($id, $fields)
    {
        $item = $this->getCourseQuizItemDao()->getQuizItem($id);
        if(!$item){
            throw $this->createServiceException("问题(#{$id})不存在，更新问题失败!");
        }

        $fields = ArrayToolkit::parts($fields, array('description', 'level', 'choices', 'answers'));
        $this->checkItem($fields);
        $fields['type'] = count($fields['answers']) > 1 ? 'multiple' : 'single';
        return ItemSerialize::unserialize(
            $this->getCourseQuizItemDao()->updateQuizItem($item['id'], ItemSerialize::serialize($fields))
        );
    }

    public function deleteItem($id)
    {
        $item = $this->getCourseQuizItemDao()->getQuizItem($id);
        if(!$item){
            throw $this->createServiceException("测验问题(#{$id})不存在，删除问题失败!");
        }
        $this->getCourseService()->tryManageCourse($item['courseId']);

        $this->getCourseQuizItemDao()->deleteQuizItem($id);
    }


    public function getQuizItem($lessonQuizItemId)
    {
        $getedLessonQuizItem = $this->getCourseQuizItemDao()->getQuizItem($lessonQuizItemId);
        if(!$getedLessonQuizItem){
            return null;
        } else {
            return $getedLessonQuizItem;
        }
    }

    public function findLessonQuizItems($courseId, $lessonId)
    {
        return ItemSerialize::unserializes(
            $this->getCourseQuizItemDao()->findQuizItemsByCourseIdAndLessonId($courseId, $lessonId)
        );
    }

    public function getUserLessonQuiz($courseId, $lessonId, $userId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $getedLessonQuiz = $this->getCourseQuizDao()->getQuizByCourseIdAndLessonIdAndUserId(
            $lesson['courseId'], $lesson['id'], $userId);
        return $getedLessonQuiz ? $getedLessonQuiz : array();
    }

    public function findQuizItemsInLessonQuiz($lessonQuizId)
    {
        $quiz = $this->getCourseQuizDao()->getQuiz($lessonQuizId);
        $quizItemIds = explode("|", $quiz['itemIds']);
        // @todo HTML Purifier
        
        if(!empty($quizItemIds)){
            $quizItems = $this->getCourseQuizItemDao()->findQuizItemsByIds($quizItemIds);
            foreach ($quizItems as $key => &$item) {
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
        $itemIds = $this->getCourseQuizItemDao()->findItemIdsByCourseIdAndLessonId($lesson['courseId'], $lesson['id']);
        return $itemIds ? $itemIds : null;
    }

    public function answerLessonQuizItem($lessonQuizId, $itemId, $answerContent)
    {
        $checkResult = $this->checkQuizAndItem($lessonQuizId, $itemId);
        $answerInfo = array();
        $answersResult = $this->checkAnswerContent($checkResult['item']['answers'], $answerContent);
        if($answersResult){
            $answerInfo['isCorrect'] = 1;
        } else {
            $answerInfo['isCorrect'] = 0;
        }
        $answerInfo['createdTime'] = time();
        $answerInfo['quizId'] = $checkResult['quiz']['id'];
        $answerInfo['itemId'] = $checkResult['item']['id'];
        $answerInfo['answers'] = $answerContent;
        $answerInfo['userId'] = $this->getCurrentUser()->id;

        $itemAnswer = $this->getCourseQuizItemAnswerDao()->getAnswerByQuizIdAndItemIdAndUserId(
            $checkResult['quiz']['id'], $checkResult['item']['id'], $this->getCurrentUser()->id);
        if(!empty($itemAnswer)){
             throw $this->createServiceException("每一道题目只能答一次!");
        }
        $this->getCourseQuizItemAnswerDao()->addAnswer($answerInfo);

        return $answersResult ? 'correct' : 'wrong';
    }

    public function checkUserLessonQuizResult($quizId)
    {
        $quiz = $this->getCourseQuizDao()->getQuiz($quizId);
        $itemIds = explode("|", $quiz['itemIds']);
        $itemIdsCount = count($itemIds);
        $correctAnswersCount = $this->getCourseQuizItemAnswerDao()->getCorrectAnswersCountByUserIdAndQuizId($this->getCurrentUser()->id, $quiz['id']);
        $score = round(100*($correctAnswersCount/$itemIdsCount), 1);
        $this->getCourseQuizDao()->updateQuiz($quiz['id'], array('score'=>$score, 'endTime'=>time()));
        return array("score"=>$score, "correctCount"=>$correctAnswersCount, "wrongCount"=> ($itemIdsCount - $correctAnswersCount));
    }



    public function deleteQuiz($quizId)
    {
        $this->clearUserAnswersInQuiz($this->getCurrentUser()->id, $quizId);
        $lessonQuiz = $this->getCourseQuizDao()->getQuiz($quizId);
        if(!$lessonQuiz){
            throw $this->createServiceException("删除问题失败，本问题不存在!");
        }
        return $this->getCourseQuizDao()->deleteQuiz($quizId);
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

        return $this->getCourseQuizDao()->addQuiz($lessonQuizInfo);
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
        $quiz = $this->getCourseQuizDao()->getQuiz($quizId);
        if(empty($quiz)){
            throw $this->createServiceException("测试不存在!");
        }
        return  $this->getCourseQuizItemAnswerDao()->deleteAnswersByUserIdAndQuizId($userId, $quizId);
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
        $lessonQuizItem = $this->getCourseQuizItemDao()->getQuizItem($itemId);
        if(empty($lessonQuizItem)){
            throw $this->createServiceException("本问题不存在!");
        }

        $lessonQuiz = $this->getCourseQuizDao()->getQuiz($lessonQuizId);
        if(empty($lessonQuiz)){
            throw $this->createServiceException("本测验不存在!");
        }
        return array('quiz'=>$lessonQuiz, 'item'=>$lessonQuizItem);
    }

    private function checkItem($item)
    {
        if (!in_array($item['level'], array('low', 'normal', 'high'))) {
            throw $this->createServiceException("level参数不正确");
        }

        if (!is_array($item['choices']) || count($item['choices']) < 2) {
            throw $this->createServiceException("choices参数不正确");
        }

        if (!is_array($item['answers']) || empty($item['answers'])) {
            throw $this->createServiceException("answers参数不正确");
        }
    }


    private function getCourseQuizItemAnswerDao()
    {
        return $this->createDao('Course.CourseQuizItemAnswerDao');
    }

    private function getCourseQuizItemDao()
    {
        return $this->createDao('Course.CourseQuizItemDao');
    }

     private function getCourseQuizDao()
    {
        return $this->createDao('Course.CourseQuizDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}


class ItemSerialize
{
    public static function serialize(array $item)
    {
        if (isset($item['answers'])) {
            $item['answers'] = implode(';', $item['answers']);
        }

        if (isset($item['choices'])) {
            $item['choices'] = json_encode($item['choices']);
        }

        return $item;
    }

    public static function unserialize(array $item = null)
    {
        if (empty($item)) {
            return null;
        }

        $item['answers'] = explode(';', $item['answers']);
        $item['choices'] = json_decode($item['choices'], true);
        return $item;
    }

    public static function unserializes(array $items)
    {
        return array_map(function($item) {
            return ItemSerialize::unserialize($item);
        }, $items);
    }
}