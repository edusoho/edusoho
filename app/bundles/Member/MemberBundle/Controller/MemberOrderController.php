<?php
namespace Member\MemberBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class MemberOrderController extends BaseController
{

    public function buyAction(Request $request)
    {
        $levels = $this->getLevelService()->findEnabledLevels();
        return $this->render('MemberBundle:MemberOrder:buy.html.twig', array(
            'levels' => $this->makeLevelRadios($levels),
        ));
    }

    private function makeLevelRadios($levels)
    {
        $radios = array();
        foreach ($levels as $level) {
            $radios[$level['id']] = $level['name'];
        }
        return $radios;
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Member:Member.LevelService');
    }

}