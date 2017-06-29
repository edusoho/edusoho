<?php

namespace AppBundle\Controller\Callback\CloudSearch\Resource;

use AppBundle\Controller\Callback\CloudSearch\BaseProvider;

/**
 * 兼容模式，对应course_task.
 */
class Lesson extends BaseProvider
{
    public function filter($res)
    {
        $filteredRes = array();

        $filteredRes['id'] = $res['id'];
        $filteredRes['title'] = $res['title'];
        $filteredRes['summary'] = '';
        $filteredRes['content'] = isset($res['activity']['content']) ? $this->filterHtml($res['activity']['content']) : '';
        $filteredRes['type'] = $res['type'];
        $filteredRes['mediaId'] = isset($res['activity']['mediaId']) ? $res['activity']['mediaId'] : '';
        $filteredRes['courseId'] = $res['fromCourseSetId'];
        $filteredRes['chapterId'] = $res['categoryId'];
        $filteredRes['number'] = $res['number'];
        $filteredRes['free'] = $res['isFree'];
        $filteredRes['learnedNum'] = '';
        $filteredRes['viewedNum'] = '';
        $filteredRes['createdTime'] = date('c', $res['createdTime']);
        $filteredRes['updatedTime'] = date('c', $res['updatedTime']);

        return $filteredRes;
    }
}
