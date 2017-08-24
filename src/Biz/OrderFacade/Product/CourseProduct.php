<?php

namespace Biz\OrderFacade\Product;

use AppBundle\Common\ArrayToolkit;

class CourseProduct extends Product
{
    const TYPE = 'course';
    private $params = array();

    public $type = self::TYPE;

    public function init(array $params)
    {
        $params['showTemplate'] = 'order/show/course-item.html.twig';
        
        $course = $this->getCourseService()->getCourse($params['targetId']);
        $params['title'] = $course['title'];
        $params['id'] = $course['id'];
        $params['type'] = 'course';
        $params['courseSet'] = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $params['price'] = $course['price'];
        $params['originPrice'] = $course['originPrice'];
        $params['maxRate'] = $course['maxRate'];
        $params['deducts'] = array();
        $params['backUrl'] = array('routing'=>'course_show', 'params' => array('id' => $course['id']));
        foreach($params as $key => $param)
        {
            $this->$key = $param;
        }

    }

    public function validate()
    {

    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}

