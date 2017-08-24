<?php

namespace Biz\OrderFacade\Product;

use Biz\Accessor\AccessorInterface;
use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;

class ClassroomProduct extends Product
{
    const TYPE = 'classroom';

    public $type = self::TYPE;

    public function init(array $params)
    {
    }

    public function validate()
    {
        $access = $this->getClassroomService()->canJoinClassroom($this->id);

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
