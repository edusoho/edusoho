<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Controller\Course\CourseBaseController;
use Biz\MultiClass\Service\MultiClassService;
use Symfony\Component\HttpFoundation\Request;

class MultiClassController extends CourseBaseController
{
    public function teachingAction(Request $request)
    {
        $conditions = [];

        $paginator = new Paginator(
            $request,
            $this->getMultiClassService()->countMultiClass($conditions),
            20
        );

        $multiClasses = $this->getMultiClassService()->searchMultiClass(
            $conditions,
            ['createdTime' => 'desc'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->createService('MultiClass:MultiClassService');
    }
}