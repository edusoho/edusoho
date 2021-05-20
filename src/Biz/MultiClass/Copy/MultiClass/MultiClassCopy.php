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
        $newMultiClass = $this->filterFields($multiClass);
        $newMultiClass['copyId'] = $multiClass['id'];
        $newMultiClass['title'] = $multiClass['title']."(复制{$config['number']})";
        $newMultiClass['productId'] = $config['productId'];
        $newMultiClass = $this->getMultiClassDao()->create($newMultiClass);

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
