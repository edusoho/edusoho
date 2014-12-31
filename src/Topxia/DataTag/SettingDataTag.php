<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class SettingDataTag  implements DataTag  
{
    /**
     *  根据key获取后台设置的内容，
     * @param  array $arguments 参数
     * @return array 
     */
    
    public function getData(array $arguments)
    {
          $defaultSetting = $this->getSettingService()->get('default', array());

        if (isset($defaultSetting['courseShareContent'])){
            $courseShareContent = $defaultSetting['courseShareContent'];
        } else {
            $courseShareContent = "";
        }

        $valuesToBeReplace = array('{{course}}');
        $valuesToReplace = array($course['title']);
        $courseShareContent = str_replace($valuesToBeReplace, $valuesToReplace, $courseShareContent);
        return $courseShareContent;
    }
        private function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }


}
