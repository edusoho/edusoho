<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class MarketingCourseProduct extends CourseProduct
{

    public function init(array $params)
    {
        Parent::init($params);
        $this->originPrice = $params['originPrice'];
    }
}
