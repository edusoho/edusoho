<?php

namespace Topxia\DataTag;

use Topxia\DataTag\DataTag;

class LatestCourseQuestionsAndPostsDataTag extends CourseBaseDataTag implements DataTag  
{
    
    /**
     * 获取最新发表的课程问答列表
     *
     * 可传入的参数：
     *   courseId 可选 课程ID
     *   count 必需 课程话题数量，取值不能超过100
     * 
     * @param  array $arguments 参数
     * @return array 课程话题问答
     */

    public function getData(array $arguments)
    {
        $this->checkCount($arguments);

        $conditions = array('type' => 'question');
    	$questions = $this->getThreadService()->searchThreads($conditions, 'created', 0, $arguments['count']);
        $posts = $this->getThreadService()->

        return $questionsAndPosts;
    }


}
