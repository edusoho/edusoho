<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class SaleController extends BaseController
{

	public function indexAction(Request $request){

		$conditions = $request->query->all();

        $count = $this->getOffsaleService()->searchOffsaleCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $offsales = $this->getOffsaleService()->searchOffsales($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());

        return $this->render('TopxiaAdminBundle:Sale:index.html.twig', array(
            'conditions' => $conditions,
            'offsales' => $offsales ,  
            'paginator' => $paginator
        ));

	}

    public function createAction(Request $request)
    {
        if('POST' == $request->getMethod()){
            $offsaleSetting = $request->request->all();
          

            $this->getOffsaleService()->createOffsales($offsaleSetting);
            return $this->indexAction($request);
        }

        $offsaleSetting = array(
            'id'=>0,
            'promoName'=>'',
            'promoCode'=>0,
            'prodName'=>'',

            'prodId'=>0
              );

        return $this->render('TopxiaAdminBundle:Sale:offsale-modal.html.twig',array('offsaleSetting' => $offsaleSetting));
    }
   

    private function getOffsaleService()
    {
        return $this->getServiceKernel()->createService('Sale.OffsaleService');
    }
}