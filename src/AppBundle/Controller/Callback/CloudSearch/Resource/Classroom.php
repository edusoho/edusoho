<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;
use AppBundle\Common\ArrayToolkit;

/**
 * 单个班级资源(对应classroom表).
 */
class Classroom extends BaseProvider
{
    public function filter($res)
    {
        $filteredRes = array();
        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['about'] = $this->filterHtml($res['about']);
        $filteredRes['price'] = $res['price'];
        $filteredRes['income'] = $res['income'];
        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['picture'] = isset($res['largePicture']) ? $this->getFileUrl($res['largePicture']) : '';
        $filteredRes['hitNum'] = $res['hitNum'];
        $filteredRes['auditorNum'] = $res['auditorNum'];
        $filteredRes['studentNum'] = $res['studentNum'];
        $filteredRes['courseNum'] = $res['courseNum'];
        $filteredRes['lessonNum'] = $res['lessonNum'];
        $filteredRes['threadNum'] = $res['threadNum'];
        $filteredRes['noteNum'] = $res['noteNum'];
        $filteredRes['postNum'] = $res['postNum'];
        $filteredRes['rating'] = $res['rating'];
        $filteredRes['ratingNum'] = $res['ratingNum'];
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }
}
