<?php

namespace Custom\Service\Homework\Impl;

use Homework\Service\Homework\Impl\HomeworkServiceImpl as BaseHomeworkServiceImpl;
use Symfony\Component\Validator\Constraints\Date;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseHomeworkServiceImpl
{


    protected function getReviewDao()
    {
        return $this->createDao('Custom:Homework.ReviewDao');
    }

    protected function getReviewItemDao()
    {
        return $this->createDao('Custom:Homework.ReviewItemDao');
    }

    protected function getHomeworkDao()
    {
        return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getResultDao()
    {
        return $this->createDao('Custom:Homework.ResultDao');
    }

    private function getResultItemDao()
    {
        return $this->createDao('Custom:Homework.ResultItemDao');
    }

    private function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

    private function getQuestionService()
    {
        return $this->createService('Question.QuestionService');
    }

    private function getHomeworkItemDao()
    {
        return $this->createDao('Homework:Homework.HomeworkItemDao');
    }

    private function getUserService(){
        return $this->createService('User.UserService');
    }

    private function filterHomeworkFields($fields, $mode)
    {
        $fields['description'] = $fields['description'];

        if ($mode == 'add') {
            $fields['createdUserId'] = $this->getCurrentUser()->id;
            $fields['createdTime'] = time();
        }

        if ($mode == 'edit') {
            $fields['updatedUserId'] = $this->getCurrentUser()->id;
            $fields['updatedTime'] = time();
        }

        return $fields;
    }

    private function addItemResult($id, $userId, $items)
    {

        $homeworkResult = $this->getResultByHomeworkIdAndUserId($id, $userId);
        $homeworkItems = $this->findItemsByHomeworkId($id);
        $itemResult = array();
        $homeworkitemResult = array();

        foreach ($homeworkItems as $key => $homeworkItem) {
            if (!empty($items[$homeworkItem['questionId']])) {
                if (!empty($items[$homeworkItem['questionId']]['answer'])) {
                    $answer = $items[$homeworkItem['questionId']]['answer'];
                    $result = $this->getQuestionService()->judgeQuestion($homeworkItem['questionId'], $answer);
                    $status = $result['status'];
                } else {
                    $answer = null;
                    $status = "noAnswer";
                }

            } else {
                $answer = null;
                $status = "noAnswer";
            }

            $existing=$this->getResultItemDao()->getItemResultByResultIdAndQuestionId($homeworkResult['id'],$homeworkItem['questionId']);
            if(empty($existing)){
                $itemResult['itemId'] = $homeworkItem['id'];
                $itemResult['homeworkId'] = $homeworkItem['homeworkId'];
                $itemResult['homeworkResultId'] = $homeworkResult['id'];
                $itemResult['questionId'] = $homeworkItem['questionId'];
                $itemResult['userId'] = $userId;
                $itemResult['status'] = $status;
                $itemResult['answer'] = $answer;
                $this->getResultItemDao()->addItemResult($itemResult);                
            }else{
                $this->getResultItemDao()->updateItemResult($existing['id'],array(
                    'answer'=>$answer,
                    'status'=>$status
                ));
            }
        }
    }

    private function addHomeworkItems($homeworkId, $excludeIds)
    {
        $homeworkItems = array();
        $index = 1;

        $fullScore=0;
        foreach ($excludeIds as $key => $excludeId) {
            $question = $this->getQuestionService()->getQuestion($excludeId);
            if(!empty($question)){                
                $items['seq'] = $index++;
                $items['questionId'] = $excludeId;
                $items['homeworkId'] = $homeworkId;
                $items['parentId'] = 0;
                $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);
            }

            $questions = $this->getQuestionService()->findQuestionsByParentId($excludeId);
            if (!empty($questions)) {
                $i = 1;
                foreach ($questions as $key => $question) {
                    $items['seq'] = $i++;
                    $items['questionId'] = $question['id'];
                    $items['homeworkId'] = $homeworkId;
                    $items['parentId'] = $question['parentId'];
                    $homeworkItems[] = $this->getHomeworkItemDao()->addItem($items);
                    $fullScore += $question['score'];
                }
            }else{
                if(!empty($question)){
                    $fullScore +=$question['score'];    
                }
            }
        }

        $this->getHomeworkDao()->updateHomework($homeworkId,array('fullScore'=>$fullScore));
    }

    public function getIndexedReviewItems($homeworkResultId){
        $indexed=array();
        $reviews=$this->getReviewDao()->findReviewsByResultId($homeworkResultId);
        if(!empty($reviews)){
            $reviews=$this->loadReviewAssociations($reviews);
            $indexedReviews=ArrayToolkit::index($reviews, 'id');
            
            $items=$this->getReviewItemDao()->findItemsByResultId($homeworkResultId);

            foreach($items as $item){
                $item['homeworkReview'] = $indexedReviews[$item['homeworkReviewId']];
                if(!array_key_exists($item['homeworkItemResultId'],  $indexed)){
                    $indexed[$item['homeworkItemResultId']] = array();
                }
                if(!array_key_exists($item['homeworkReview']['category'], $indexed[$item['homeworkItemResultId']])){
                    $indexed[$item['homeworkItemResultId']][$item['homeworkReview']['category']] = array();
                }
                array_push($indexed[$item['homeworkItemResultId']][$item['homeworkReview']['category']], $item);
            }
        }
        
        return $indexed;
    }

    private function loadReviewAssociations($reviews){
        $userIds=ArrayToolKit::column($reviews, "userId");
        $users=$this->getUserService()->findUsersByIds($userIds);
        $indexedUsers=ArrayToolkit::index($users, 'id');
        foreach($reviews as $i=>$review){
            $review['user'] = $indexedUsers[$review['userId']];
            $reviews[$i] = $review;
        }
        return $reviews;
    }
}