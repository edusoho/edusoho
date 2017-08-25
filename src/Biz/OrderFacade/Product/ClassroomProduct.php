<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class ClassroomProduct extends Product
{
    const TYPE = 'classroom';

    public $targetType = self::TYPE;

    public function init(array $params)
    {
        $params['showTemplate'] = 'order/show/classroom-item.html.twig';

        $classroom = $this->getClassroomService()->getClassroom($params['targetId']);
        $params['title'] = $classroom['title'];
        $params['targetId'] = $classroom['id'];
        $params['price'] = $classroom['price'];
        $params['originPrice'] = $classroom['price'];
        $params['maxRate'] = $classroom['maxRate'];
        $params['deducts'] = array();
        $params['backUrl'] = array('routing' => 'classroom_show', 'params' => array('id' => $classroom['id']));
        $params['smallPicture'] = $classroom['smallPicture'];
        $params['middlePicture'] = $classroom['middlePicture'];
        $params['largePicture'] = $classroom['largePicture'];

        foreach ($params as $key => $param) {
            $this->$key = $param;
        }

    }

    public function validate()
    {
        $access = $this->getClassroomService()->canJoinClassroom($this->targetId);

        if ($access['code'] !== AccessorInterface::SUCCESS) {
            throw new InvalidArgumentException($access['msg']);
        }
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
