<?php

namespace Custom\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\AdminBundle\Controller\CoinController;
 
class CoinAdminController extends CoinController
{
    public function ConsumptionfullsettingsAction(Request $request)
    {
        $postedParams = $request->request->all();

        $coinSettingsPosted = $this->getSettingService()->get('coin',array());

        $coinSettingsSaved = $coinSettingsPosted;
        $default = array(
          'coin_consume_range_and_present' => array(array(0,0))
        );
        $coinSettingsPosted = array_merge($default, $coinSettingsPosted);
      
        if ($request->getMethod() == 'POST') {
        $i=0;
        foreach ($postedParams as $key => $value) {
            if (!is_numeric($value)){
              $this->setFlashMessage('danger', '错误，填入的必须为数字！');
              return $this->settingsRenderedPage($coinSettingsSaved);
            }
            $tmpArray[$i]=$value;
          $i+=1;
        }
       
        for ($i=0; $i<count($tmpArray)/2 ; $i+=1) { 
          $oneRangePresent[0] = $tmpArray[2*$i];
          $oneRangePresent[1] = $tmpArray[2*$i+1];
          $coinConsumeRangeAndPresent[$i] = $oneRangePresent;
        }
        $coinSettingsPosted['coin_consume_range_and_present'] =  $coinConsumeRangeAndPresent;    
     
        $this->getSettingService()->set('coin', $coinSettingsPosted);
        $this->getLogService()->info('system', 'update_settings', "更新Coin消费满设置", $coinSettingsPosted);
        $this->setFlashMessage('success', '消费满设置已保存！'); 
        }

        return $this->render('CustomAdminBundle:Coin:coin-consumptionfull-settings.html.twig',array(
        'range_number' => count($coinSettingsPosted['coin_consume_range_and_present']),
        'coin_consume_range_and_present' => $coinSettingsPosted['coin_consume_range_and_present']        
      ));
    }

}
