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

    public function courseColumnAction(){
   
        $columnCount = $this->getColumnService()->getAllColumnCount();
        $columns = $this->getColumnService()->findAllColumns(0,$columnCount);

        return $this->render('TopxiaWebBundle:Column:course-column.html.twig',array(
            'columns'=>$columns
        ));
    }
    public function allColumnAction(){
   // array_chunk
        $columnCount = $this->getColumnService()->getAllColumnCount();
        $columns = $this->getColumnService()->findAllColumns(0,$columnCount);
        $columns = array_chunk($columns,4);
      
        return $this->render('TopxiaWebBundle:Column:column-header.html.twig',array(
            'columns'=>$columns
        ));
    }

    

    public function indexAction()
    {
        return $this->render('TopxiaWebBundle:Column:index.html.twig');   
    }

    private function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }

}