<?php

namespace Custom\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Topxia\WebBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class GroupController extends BaseController
{
    public function signSetAction(Request $request)
    {   
        $vipLevels=$this->getLevelService()->searchLevels(array(),0,1000); 

        if($request->getMethod()=="POST"){

            $set=$this->getSettingService()->get('group',array());

            $data=$request->request->all();

            $set['daySign']=$data['daySign'];
            
            $this->getSettingService()->set('group',$set);

            foreach ($data['reward'] as $key => $value) {
         
                $this->getLevelService()->updateLevel($key,array('signReward'=>$value[0]));
            }

        }
        
        $vipLevels=$this->getLevelService()->searchLevels(array(),0,1000);
        
        return $this->render('CustomAdminBundle:Group:set.html.twig', array(
            'vipLevels'=>$vipLevels,
            ));
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
