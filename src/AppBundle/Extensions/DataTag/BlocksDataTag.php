<?php

namespace AppBundle\Extensions\DataTag;

class BlocksDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取所有Blocks.
     *
     * 可传入的参数：
     *
     *   codes Block编码
     *
     * @param array $arguments 参数
     *
     * @return array Blocks
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['codes'])) {
            return array();
        }

        return $this->getBlockService()->getContentsByCodes($arguments['codes']);
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content:BlockService');
    }
}
