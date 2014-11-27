<?php
namespace Custom\WebBundle\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ColumnController extends BaseController
{



    public function matchAction(Request $request)
    {
        $data = array();
        $queryString = $request->query->get('q');
        $callback = $request->query->get('callback');
        $columns = $this->getColumnService()->getColumnByLikeName($queryString);
        foreach ($columns as $column) {
            $data[] = array('id' => $column['id'],  'name' => $column['name'] );
        }
        return new JsonResponse($data);
    }

    private function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }

}