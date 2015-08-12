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
            $this->createServiceException("内容为空，创建作业失败！");
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
            $this->createServiceException("题目不能为空，创建作业失败！");
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

    public function randomizeHomeworkResultForPairReview($homeworkId,$userId){
        $homework=$this->getHomeworkDao()->getHomework($homeworkId);
        $reviewableResultIds=$this->getResultDao()->findPairReviewableIds($homework,$userId);
        if(empty($reviewableResultIds)){
            return null;
        }
        $selectedId=$reviewableResultIds[rand(0, count($reviewableResultIds)-1)];
        $result=$this->getResultDao()->getResult($selectedId);
        $resultItems=$this->getItemSetResultByHomeworkIdAndUserId($homeworkId,$result['userId']);
        $result['items']=$resultItems;

        return $result;
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