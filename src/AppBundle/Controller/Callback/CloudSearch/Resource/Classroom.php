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
        $filteredRes = ArrayToolkit::parts($res,
            array(
                'id',
                'title',
                'price',
                'income',
                'hitNum',
                'auditorNum',
                'studentNum',
                'courseNum',
                'lessonNum',
                'threadNum',
                'noteNum',
                'postNum',
                'rating',
                'ratingNum',
                'tags',
            )
        );

        $filteredRes['about'] = $this->filterHtml($res['about']);
        $filteredRes['category'] = isset($res['category']['name']) ? $res['category']['name'] : '';
        $filteredRes['picture'] = isset($res['largePicture']) ? $this->getFileUrl($res['largePicture']) : '';
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }
}
