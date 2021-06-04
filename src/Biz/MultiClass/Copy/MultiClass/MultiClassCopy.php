<?php

namespace Biz\MultiClass\Copy\MultiClass;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\MultiClass\Dao\MultiClassDao;
use Biz\MultiClass\Service\MultiClassProductService;

class MultiClassCopy extends AbstractEntityCopy
{
    protected function getFields()
    {
        return [
           'courseId',
       ];
    }

    protected function copyEntity($multiClass, $config = [])
    {
        $number = $this->getMultiClassDao()->count(['copyId' => $multiClass['id']]);
        $number = empty($number) ? '' : $number;
        $newMultiClass = $this->filterFields($multiClass);
        $newMultiClass['copyId'] = $multiClass['id'];
        $newMultiClass['title'] = empty($config['cloneMultiClass']) ? $multiClass['title']."(复制{$number})" : $config['cloneMultiClass']['title'];
        $newMultiClass['productId'] = empty($config['cloneMultiClass']) ? $this->getMultiClassProductService()->getDefaultProduct()['id'] : $config['cloneMultiClass']['productId'];
        $newMultiClass = $this->getMultiClassDao()->create($newMultiClass);
        $newMultiClass['number'] = $number;

        return $newMultiClass;
    }

    /**
     * @return MultiClassProductService
     */
    private function getMultiClassProductService()
    {
        return $this->biz->service('MultiClass:MultiClassProductService');
    }

    /**
     * @return MultiClassDao
     */
    private function getMultiClassDao()
    {
        return $this->biz->dao('MultiClass:MultiClassDao');
    }
}
