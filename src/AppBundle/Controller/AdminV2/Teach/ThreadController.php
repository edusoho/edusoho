<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Course\Service\ThreadService;
use Symfony\Component\HttpFoundation\Request;

class ThreadController extends BaseController
{
    public function deleteAction(Request $request, $id)
    {
        $this->getCourseThreadService()->deleteThread($id);

        return $this->createJsonResponse(true);
    }

    public function batchDeleteAction(Request $request)
    {
        $ids = $request->request->get('ids');
        foreach ($ids ?: array() as $id) {
            $this->getCourseThreadService()->deleteThread($id);
        }

        return $this->createJsonResponse(true);
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}
