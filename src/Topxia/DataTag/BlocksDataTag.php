<?php

namespace Topxia\DataTag;
use Topxia\DataTag\DataTag; 

class BlocksDataTag extends BaseDataTag implements DataTag
{
	/**
     * 获取所有Blocks
     * 
     * 可传入的参数：
     *
     *   codes Block编码
     * 
     * @param  array $arguments 参数
     * @return array Blocks
     */

	public function getData(array $arguments)
    {  
    	if(empty($arguments)) {
    		return array();
    	}else {

        $BlockContents = $this->getBlockService()->getContentsByCodes($arguments['codes']);

        return $BlockContents;
    	}
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

}


