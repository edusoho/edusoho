<?php 
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class JsonCoursewareController extends BaseController
{
    public function searchAction(Request $request)
    {
        $query = $request->query->all();

        $conditions = array();

        if (!empty($query['mainKonwledgeId'])) {
            $conditions['mainKonwledgeId'] = $query['mainKonwledgeId'];
        }

        if (!empty($query['tagIds'])) {
            $conditions['tagIds'] = $query['tagIds'];
        }

        if (!empty($query['keyword'])) {
            $conditions['keyword'] = $query['keyword'];
        }
        $coursewares = $this->getCoursewareService()->searchCoursewares($conditions, array('createdTime','DESC'),0, 15);
        
        return $this->createJsonResponse($coursewares);
    }

    private function getCoursewareService()
    {
        return $this->getServiceKernel()->createService('Courseware.CoursewareService');
    }
}