<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;

class OpenCourseLesson extends BaseProvider
{
    public function filter($res)
    {
        $filteredRes = array();
        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['summary'] = empty($res['summary']) ? '' : $res['summary'];
        $filteredRes['content'] = empty($res['content']) ? '' : $res['content'];
        $filteredRes['type'] = $res['type'];
        $filteredRes['mediaId'] = $res['mediaId'];
        $filteredRes['courseId'] = $res['courseId'];
        $filteredRes['chapterId'] = $res['chapterId'];
        $filteredRes['number'] = $res['number'];
        $filteredRes['free'] = '0';
        $filteredRes['learnedNum'] = '0';
        $filteredRes['viewedNum'] = '0';
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }
}
