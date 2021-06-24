<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\WrongBook\Service\WrongQuestionService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;

class MeCourseWrongBookType extends AbstractResource
{
//    /**
//     * @ResponseFilter(class="ApiBundle\Api\Resource\WrongBook\WrongBookCertainTypeFilter", mode="public")
//     */
    public function search(ApiRequest $request,$id)
    {
        $courses=$this->getCourseService()->findPublishedCoursesByCourseSetId($id);
        $conditions = $request->query->all();
        $conditions=$this->prepareConditions($conditions);
        $tasks=$this->getTaskService()->searchTasks($conditions,[],0,PHP_INT_MAX);
        $coursesTitles=ArrayToolkit::columns($courses,['id','title']);
        $result['plans']=array_combine($coursesTitles[0],$coursesTitles[1]);
        echo "<pre>";
        print_r($tasks);die;

        $conditions['user_id'] = $this->getCurrentUser()->getId();
    }

    private function prepareConditions($conditions){

        if(empty($conditions['courseId'])){
            unset($conditions['courseId']);
        }
        if(!empty($conditions['courseMediaType'])){
            $conditions['type']=$conditions['courseMediaType'];
            unset($conditions['courseMediaType']);
        }
        if(!empty($conditions['courseTaskId'])){
            $conditions['id']=$conditions['courseTaskId'];
            unset($conditions['courseTaskId']);
        }
        return $conditions;
    }


    /**
     * @return WrongQuestionService
     */
    private function getWrongQuestionService()
    {
        return $this->service('WrongBook:WrongQuestionService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
