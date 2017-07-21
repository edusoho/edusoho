<?php

namespace Biz\Course\Component\Clones\Chain;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Component\Clones\AbstractClone;
use Biz\Question\Dao\QuestionDao;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class CourseSetQuestionClone extends AbstractClone
{
    protected function cloneEntity($source, $options)
    {
        return $this->cloneCourseSetQuestions($source, $options);
    }

    protected function getFields()
    {
        return array(
            'type',
            'stem',
            'score',
            'answer',
            'analysis',
            'metas',
            'categoryId',
            'courseId',//先保存courseId LessonId 后面再更新
            'lessonId',
            'difficulty',
            'subCount',
        );
    }

    private function cloneCourseSetQuestions($source, $options)
    {
        $newCourseSet = $options['newCourseSet'];
        $this->cloneParentQuestions($source, $newCourseSet);
        $this->cloneChildrenQuestions($source, $newCourseSet);
    }

    private function cloneParentQuestions($originalCourseSet, $newCourseSet)
    {
        $conditions = array(
            'parentId' => 0,
//            'courseId' => 0,
            'courseSetId' => $originalCourseSet['id'],
        );
        $parentQuestions = $this->getQuestionDao()->search($conditions, array(), 0, PHP_INT_MAX);

        if (empty($parentQuestions)) {
            return;
        }
        $newQuestions = array();
        foreach ($parentQuestions as $question) {
            $newQuestion = $this->filterFields($question);
//            $newQuestion['courseId'] = 0;
            $newQuestion['courseSetId'] = $newCourseSet['id'];
//            $newQuestion['lessonId'] = 0;
            $newQuestion['copyId'] = $question['id']; //暂时存储copyId，当把childrenQuestion 填写之后，再Update将copyId归零
            $newQuestion['createdUserId'] = $this->biz['user']['id'];
            $newQuestion['updatedUserId'] = $this->biz['user']['id'];
            $newQuestion['parentId'] = 0;

            $newQuestions[] = $newQuestion;
        }
        if (!empty($newQuestions)) {
            $this->getQuestionDao()->batchCreate($newQuestions);
        }
    }

    private function cloneChildrenQuestions($originalCourseSet, $newCourseSet)
    {
        $newQuestions = $this->getQuestionDao()->findQuestionsByCourseSetId($newCourseSet['id']);
        $newQuestionIds = ArrayToolkit::column($newQuestions, 'id');
        $newQuestions = ArrayToolkit::index($newQuestions, 'copyId');

        $conditions = array(
            'parentIdGT' => 0,
            'courseSetId' => $originalCourseSet['id'],
//            'courseId' => 0,
        );
        $childrenQuestions = $this->getQuestionDao()->search($conditions, array(), 0, PHP_INT_MAX);
        if (empty($childrenQuestions)) {
            return;
        }
        $newChildQuestions = array();
        foreach ($childrenQuestions as $question) {
            $newQuestion = $this->filterFields($question);
//            $newQuestion['courseId'] = 0;
            $newQuestion['courseSetId'] = $newCourseSet['id'];
//            $newQuestion['lessonId'] = 0;
            $newQuestion['createdUserId'] = $this->biz['user']['id'];
            $newQuestion['updatedUserId'] = $this->biz['user']['id'];
            $newQuestion['copyId'] = $question['id'];
            $parentQuestion = $newQuestions[$question['parentId']];
            $newQuestion['parentId'] = $parentQuestion['id'];

            $newChildQuestions[] = $newQuestion;
        }

        $this->getQuestionDao()->batchCreate($newChildQuestions);
//        copyId的刷新不在这里做，在课程复制完之后然后再操作
//        foreach ($newQuestionIds as $newQuestionId) {
//            $this->getQuestionBatchUpdateHelper()->add('id',$newQuestionId,array('copyId' => 0));
//        }
//        $this->getQuestionBatchUpdateHelper()->flush();
    }

    /**
     * @return BatchUpdateHelper
     */
    protected function getQuestionBatchUpdateHelper()
    {
        $questionDao = $this->getQuestionDao();

        $questionUpdateHelper = new BatchUpdateHelper($questionDao);

        return $questionUpdateHelper;
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('Question:QuestionDao');
    }
}
