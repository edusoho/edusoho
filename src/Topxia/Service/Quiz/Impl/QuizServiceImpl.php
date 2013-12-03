<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuizService;
use Topxia\Common\ArrayToolkit;

class QuizServiceImpl extends BaseService implements QuizService
{


    public function getQuizItem($id)
    {
        return ItemSerialize::unserialize($this->getItemDao()->getQuizItem($id));
    }

    public function getUserLessonQuiz($courseId, $lessonId, $userId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $getedLessonQuiz = $this->getQuizDao()->getQuizByCourseIdAndLessonIdAndUserId(
            $lesson['courseId'], $lesson['id'], $userId);
        return $getedLessonQuiz ? $getedLessonQuiz : array();
    }

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

        //创建题目 过滤html不安全代码
        $item['description'] = $this->purifyHtml($item['description']);
        $result =  ItemSerialize::unserialize(
            $this->getItemDao()->addQuizItem(ItemSerialize::serialize($item))
        );
        $this->getCourseService()->increaseLessonQuizCount($lesson['id']);
        return $result;
    }

    public function updateItem($id, $fields)
    {
        $item = $this->getItemDao()->getQuizItem($id);
        if(!$item){
            throw $this->createServiceException("问题(#{$id})不存在，更新问题失败!");
        }

        $fields = ArrayToolkit::parts($fields, array('description', 'level', 'choices', 'answers'));
        $this->checkItem($fields);
        $fields['type'] = count($fields['answers']) > 1 ? 'multiple' : 'single';

        //更新题目 过滤html不安全代码
        $fields['description'] = $this->purifyHtml($fields['description']);
        return ItemSerialize::unserialize(
            $this->getItemDao()->updateQuizItem($item['id'], ItemSerialize::serialize($fields))
        );
    }

    public function deleteItem($id)
    {
        $item = $this->getItemDao()->getQuizItem($id);
        if(!$item){
            throw $this->createServiceException("测验问题(#{$id})不存在，删除问题失败!");
        }
        if(!$this->getCourseService()->canManageCourse($item['courseId'])){
             throw $this->createServiceException("无权限操作");
        }
        $this->getItemDao()->deleteQuizItem($id);
        $count = $this->getItemDao()->getQuizItemsCount($item['courseId'],$item['lessonId']);
        $this->getCourseService()->resetLessonQuizCount($item['lessonId'],$count);
    }

    public function getQuiz($id)
    {
        return QuizSerialize::unserialize($this->getQuizDao()->getQuiz($id));
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

        return $this->getQuizDao()->addQuiz($lessonQuizInfo);
    }

    public function deleteQuiz($quizId)
    {
        $this->clearUserAnswersInQuiz($this->getCurrentUser()->id, $quizId);
        $lessonQuiz = $this->getQuizDao()->getQuiz($quizId);
        if(!$lessonQuiz){
            throw $this->createServiceException("删除问题失败，本问题不存在!");
        }
        return $this->getQuizDao()->deleteQuiz($quizId);
    }

    public function findLessonQuizItems($courseId, $lessonId)
    {
        return ItemSerialize::unserializes(
            $this->getItemDao()->findQuizItemsByCourseIdAndLessonId($courseId, $lessonId)
        );
    }

    public function findLessonQuizItemIds($courseId, $lessonId)
    {
        $lesson = $this->checkCourseAndLesson($courseId, $lessonId);
        $itemIds = $this->getItemDao()->findItemIdsByCourseIdAndLessonId($lesson['courseId'], $lesson['id']);
        return $itemIds ? $itemIds : null;
    }

    public function findQuizItemsInLessonQuiz($lessonQuizId)
    {
        $quiz = $this->getQuizDao()->getQuiz($lessonQuizId);
        $quizItemIds = explode("|", $quiz['itemIds']);
        // @todo HTML Purifier
        
        if(!empty($quizItemIds)){
            $quizItems = $this->getItemDao()->findQuizItemsByIds($quizItemIds);
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

    public function answerQuizItem($quizId, $itemId, $answers)
    {
        $user = $this->getCurrentUser();

        $quiz = $this->getQuiz($quizId);
        $item = $this->getQuizItem($itemId);

        if (empty($quiz) or empty($item) or !in_array($item['id'], $quiz['itemIds'])) {
            throw $this->createServiceException('提交的测验数据不正确。');
        }

        if ($quiz['userId'] != $user['id']) {
            throw $this->createServiceException('无权限提交当前测试的答案。');
        }

        if (empty($answers)) {
            throw $this->createServiceException('答案不能为空，回答失败！');
        }

        // $existAnswer = $this->getItemAnswerDao()->getAnswerByQuizIdAndItemIdAndUserId($quizId, $itemId, $user['id']);
        // if(!empty($existAnswer)){
        //      throw $this->createServiceException("每一道题目只能答一次!");
        // }

        $answer = array();
        $answer['isCorrect'] = $this->isAnswersCorrect($item['answers'], $answers) ? 1 : 0;
        $answer['quizId'] = $quizId;
        $answer['itemId'] = $itemId;
        $answer['answers'] = $answers;
        $answer['userId'] = $this->getCurrentUser()->id;
        $answer['createdTime'] = time();

        $this->getItemAnswerDao()->addAnswer(AnswerSerialize::serialize($answer));

        return array('correct' => $answer['isCorrect'], 'correctAnswers' => $item['answers']);
    }

    public function submitQuizResult($quizId)
    {
        $quiz = $this->getQuiz($quizId);
        if (empty($quiz)) {
            throw $this->createServiceException("测验(#{$quizId})不存在，提交失败。");
        }

        $user = $this->getCurrentUser();
        if ($quiz['userId'] != $user['id']) {
            throw $this->createServiceException("你无权提交该测验(#{$quizId})。");
        }

        $itemCount = count($quiz['itemIds']);

        $correctCount = $this->getItemAnswerDao()->getCorrectAnswersCountByUserIdAndQuizId($user['id'], $quiz['id']);
        $score = round(100*($correctCount/$itemCount), 1);

        $this->getQuizDao()->updateQuiz($quiz['id'], array('score'=>$score, 'endTime'=>time()));
        
        return array(
            'score'=>$score,
            'itemCount' => $itemCount,
            'correctCount' => $correctCount, 
        );
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
    
    private function isAnswersCorrect($standardAnswers, $userAnswers)
    {
        $diff1 = array_diff($standardAnswers, $userAnswers);
        $diff2 = array_diff($userAnswers, $standardAnswers);
        return empty($diff1) && empty($diff2);
    }
    
    private function clearUserAnswersInQuiz($userId, $quizId)
    {
        $user = $this->getCurrentUser();
        if($user['id'] != $userId){
            throw $this->createServiceException("用户已经尚未登陆，操作失败!");
        }
        $quiz = $this->getQuizDao()->getQuiz($quizId);
        if(empty($quiz)){
            throw $this->createServiceException("测试不存在!");
        }
        return  $this->getItemAnswerDao()->deleteAnswersByUserIdAndQuizId($userId, $quizId);
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

    private function checkQuizAndItem($quizId, $itemId)
    {
        $lessonQuiz = $this->getQuizDao()->getQuiz($quizId);
        if(empty($lessonQuiz)){
            throw $this->createServiceException("本测验不存在!");
        }

        $lessonQuizItem = $this->getItemDao()->getQuizItem($itemId);
        if(empty($lessonQuizItem)){
            throw $this->createServiceException("本问题不存在!");
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

    private function getItemAnswerDao()
    {
        return $this->createDao('Course.CourseQuizItemAnswerDao');
    }

    private function getItemDao()
    {
        return $this->createDao('Course.CourseQuizItemDao');
    }

     private function getQuizDao()
    {
        return $this->createDao('Course.CourseQuizDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

}

class QuizSerialize
{
    public static function serialize(array $quiz)
    {
        if (isset($quiz['itemIds'])) {
            $quiz['itemIds'] = implode('|', $quiz['itemIds']);
        }

        return $quiz;
    }

    public static function unserialize(array $quiz = null)
    {
        if (empty($quiz)) {
            return null;
        }

        $quiz['itemIds'] = explode('|', $quiz['itemIds']);
        return $quiz;
    }

    public static function unserializes(array $quizs)
    {
        return array_map(function($quiz) {
            return ItemSerialize::unserialize($quiz);
        }, $quizs);
    }
}

class ItemSerialize
{
    public static function serialize(array $item)
    {
        if (isset($item['answers'])) {
            $item['answers'] = implode('|', $item['answers']);
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

        $item['answers'] = explode('|', $item['answers']);
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

class AnswerSerialize
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