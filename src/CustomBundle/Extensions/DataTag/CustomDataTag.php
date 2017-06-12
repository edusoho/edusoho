<?php
namespace  CustomBundle\Extensions\DataTag;

use \AppBundle\Extensions\DataTag\BaseDataTag;
use AppBundle\Extensions\DataTag\DataTag;

class CustomDataTag  extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        // TODO: Implement getData() method.
        return array(1,3);
    }

}