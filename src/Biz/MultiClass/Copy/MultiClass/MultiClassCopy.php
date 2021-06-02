<?php

namespace Biz\MultiClass\Copy\MultiClass;

use Biz\Course\Copy\AbstractEntityCopy;
use Biz\MultiClass\Dao\MultiClassDao;

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
        $newMultiClass['title'] = $multiClass['title']."(复制{$number})";
        $newMultiClass['productId'] = $config['productId'];
        $newMultiClass = $this->getMultiClassDao()->create($newMultiClass);
        $newMultiClass['number'] = $number;

        return $newMultiClass;
    }

    /**
     * @return MultiClassDao
     */
    private function getMultiClassDao()
    {
        return $this->biz->dao('MultiClass:MultiClassDao');
    }
}
