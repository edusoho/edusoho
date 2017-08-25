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

    public $showTemplate = 'order/show/course-item.html.twig';

    public $targetType = self::TYPE;

    public function init(array $params)
    {
        $course = $this->getCourseService()->getCourse($params['targetId']);
        $params['title'] = $course['title'];
        $params['targetId'] = $course['id'];
        $params['courseSet'] = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $params['payablePrice'] = $params['price'] = $course['price'];
        $params['originPrice'] = $course['originPrice'];
        $params['maxRate'] = $course['maxRate'];
        $params['deducts'] = array();
        $params['backUrl'] = array('routing' => 'course_show', 'params' => array('id' => $course['id']));

        if (!empty($params['couponCode'])) {
            $params['pickedDeducts']['coupon'] = array(
                'code' => $params['couponCode'],
            );
        }

        foreach ($params as $key => $param) {
            $this->$key = $param;
        }
    }

    public function validate()
    {
        $access = $this->getCourseService()->canJoinCourse($this->targetId);

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
