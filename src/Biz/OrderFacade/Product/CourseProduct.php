<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

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
        $params['backUrl'] = array('routing' => 'course_show', 'params' => array('id' => $course['id']));
        $params['pickedDeducts']['coupon']['code'] =  empty($params['couponCode']) ? '' : $params['couponCode'];

        foreach ($params as $key => $param) {
            $this->$key = $param;
        }
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->id);

        if ($access['code'] !== AccessorInterface::SUCCESS) {
            throw new InvalidArgumentException($access['msg']);
        }
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
