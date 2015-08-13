<?php

namespace Custom\Service\Homework\Impl;

use Custom\Service\Homework\HomeworkService;
use Homework\Service\Homework\Impl\HomeworkServiceImpl as BaseHomeworkServiceImpl;
use Topxia\Service\Common\ServiceEvent;
use Topxia\Common\ArrayToolkit;

class HomeworkServiceImpl extends BaseHomeworkServiceImpl implements HomeworkService
{
    public function createCustomHomework($courseId,$lessonId,$fields)
    {
        if(empty($fields)){
            throw$this->createServiceException("内容为空，创建作业失败！");
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            throw $this->createServiceException('课程不存在，创建作业失败！');
        }

        $lesson = $this->getCourseService()->getCourseLesson($courseId,$lessonId);

        if (empty($lesson)) {
            throw $this->createServiceException('课时不存在，创建作业失败！');
        }

        $excludeIds = $fields['excludeIds'];

        if (empty($excludeIds)) {
            throw $this->createServiceException("题目不能为空，创建作业失败！");
        }

        unset($fields['excludeIds']);

        $fields = $this->filterHomeworkFields($fields,$mode = 'add');
        $fields['courseId'] = $courseId;
        $fields['lessonId'] = $lessonId;
        $excludeIds = explode(',',$excludeIds);
        $fields['itemCount'] = count($excludeIds);
//        $fields[comment] = $fields[comment];

        $fields['updatedUserId'] = 0;
        $fields['updatedTime'] = 0;
        $homework = $this->getHomeworkDao()->addHomework($fields);
        $this->addHomeworkItems($homework['id'],$excludeIds);

        $this->getLogService()->info('homework','create','创建课程{$courseId}课时{$lessonId}的作业');

        return $homework;
    }

    public function createHomeworkPairReview($homeworkResultId, array $fields){
        $homeworkResult=$this->loadHomeworkResult($homeworkResultId);
        $fields['homeworkResultId'] = $homeworkResult['id'];
        $fields['homeworkId'] = $homeworkResult['homeworkId'];
        $fields['userId'] = $this->getCurrentUser()->id;
        return $this->getReviewDao()->create($fields);
    }

    public function randomizeHomeworkResultForPairReview($homeworkId,$userId){
        $homework=$this->getHomeworkDao()->getHomework($homeworkId);
        $reviewableResults=$this->getResultDao()->findPairReviewables($homework,$userId);
        if(empty($reviewableResults)){
            return null;
        }
        $selectedId = $this->pickReviewable($reviewableResults, $homework);
        $result=$this->loadHomeworkResult($selectedId);
        $resultItems=$this->getItemSetResultByHomeworkIdAndUserId($homeworkId,$result['userId']);
        $result['items']=$resultItems;

        return $result;
    }

    public function getHomeworkResult($homeworkResultId){
        return $this->getResultDao()->getResult($homeworkResultId);
    }

    public function loadHomeworkResult($homeworkResultId){
        if(empty($homeworkResultId)){
           throw $this->createServiceException("作业答卷id为空.");  
        }
        $homeworkResult = $this -> getHomeworkResult($homeworkResultId);
        if(empty($homeworkResult)){
           throw $this->createServiceException("未能找到作业答卷！");
        }
        return $homeworkResult;
    }

    public function updateHomeworkResult($homeworkResultId, array $fields){
        return $this->getResultDao()->updateResult($homeworkResultId, $fields);
    }

    private  function pickReviewable($results, $homework) {
        $insufficient = array();
        $sufficient = array();
        foreach($results as $result){
            if($result['pairReviews']< $homework['minReviews']){
                array_push($insufficient, $result['id']);
            }else{
                array_push($sufficient, $result['id']);
            }
        }
        $ids = empty($insufficient) ? $sufficient : $insufficient;
        return empty($ids) ? null : $ids[rand(0,count($ids)-1)];
    }

    protected function getReviewDao(){
          return $this->createDao('Custom:Homework.ReviewDao');
    }

    protected function getHomeworkDao(){
          return $this->createDao('Homework:Homework.HomeworkDao');
    }

    protected function getResultDao(){
          return $this->createDao('Custom:Homework.HomeworkResultDao');
    }
}