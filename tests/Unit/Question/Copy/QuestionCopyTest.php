<?php

namespace Tests\Unit\Question\Copy;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\File\Dao\FileUsedDao;
use Biz\File\Service\UploadFileService;
use Biz\Question\Copy\QuestionCopy;
use Biz\Question\Service\QuestionService;

class QuestionCopyTest extends BaseTestCase
{
    public function testdDoCopy()
    {
        $source = array('id' => 1);
        $options = array('newCourseSet' => array('id' => 2));

        $biz = $this->getBiz();
        $copyObj = new QuestionCopy($biz, array());

        $this->createQuestions($source['id'], 1);

        $copyObj->doCopy($source, $options);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($source['id']);
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($options['newCourseSet']['id']);

        $this->assertEquals(count($questions), count($copyQuestions));
    }

    public function testdDoCopyNoQuestion()
    {
        $source = array('id' => 1);
        $options = array('newCourseSet' => array('id' => 2));

        $biz = $this->getBiz();
        $copyObj = new QuestionCopy($biz, array());

        $copyObj->doCopy($source, $options);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($source['id']);
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($options['newCourseSet']['id']);

        $this->assertEmpty($questions);
        $this->assertEmpty($copyQuestions);
    }

    public function testdDoCopyNoChildQuestion()
    {
        $source = array('id' => 1);
        $options = array('newCourseSet' => array('id' => 2));

        $biz = $this->getBiz();
        $copyObj = new QuestionCopy($biz, array());

        $this->createQuestions($source['id'], 0);

        $copyObj->doCopy($source, $options);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($source['id']);
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($options['newCourseSet']['id']);

        $this->assertEquals(count($questions), count($copyQuestions));
    }

    public function testDoCopyWithAttachments()
    {
        $source = array('id' => 1);
        $options = array('newCourseSet' => array('id' => 2));

        $biz = $this->getBiz();
        $copyObj = new QuestionCopy($biz, array());

        $this->createQuestions($source['id'], 1, true);

        $copyObj->doCopy($source, $options);

        $questions = $this->getQuestionService()->findQuestionsByCourseSetId($source['id']);
        $copyQuestions = $this->getQuestionService()->findQuestionsByCourseSetId($options['newCourseSet']['id']);

        $targetIds = ArrayToolkit::column($copyQuestions, 'id');
        $conditions = array(
            'type' => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds' => $targetIds,
        );

        $attachments = $this->getUploadFileService()->searchUseFiles($conditions, false);

        $this->assertEquals(1, count($attachments));
        $this->assertEquals(count($questions), count($copyQuestions));
    }

    public function testPreCopy()
    {
        $biz = $this->getBiz();
        $copyObj = new QuestionCopy($biz, array());

        $result = $copyObj->preCopy(array(), array());
        $this->assertNull($result);
    }

    private function createQuestions($courseSetId, $isCreateChild, $withAttachments = false)
    {
        $material = array(
            'type' => 'material',
            'stem' => 'test material question.',
            'content' => 'question material content',
            'courseId' => 1,
            'courseSetId' => $courseSetId,
            'lessonId' => 0,
            'answer' => array(),
            'target' => 'course-1',
        );
        $questionParent = $this->getQuestionService()->create($material);
        $attachments = array(
            'type' => 'attachment',
            'fileId' => 1,
            'targetType' => 'question.stem',
            'targetId' => $questionParent['id'],
        );
        $this->getFileUsedDao()->create($attachments);

        if ($isCreateChild) {
            $single = array(
                'type' => 'single_choice',
                'stem' => 'test material-single choice question.',
                'content' => 'question material-single_choice content',
                'courseId' => 1,
                'courseSetId' => $courseSetId,
                'lessonId' => 0,
                'choices' => array(
                    'question 1 -> choice 1',
                    'question 1 -> choice 2',
                    'question 1 -> choice 3',
                    'question 1 -> choice 4',
                ),
                'answer' => array(1),
                'target' => 'course-1',
                'parentId' => $questionParent['id'],
            );
            $this->getQuestionService()->create($single);

            $determine = array(
                'type' => 'determine',
                'stem' => 'test material-determine question.',
                'content' => 'question material-determine content',
                'courseId' => 1,
                'courseSetId' => $courseSetId,
                'lessonId' => 0,
                'answer' => array(1),
                'target' => 'course-1',
                'parentId' => $questionParent['id'],
                'metas' => array('mediaId' => 1),
            );
            $this->getQuestionService()->create($determine);
        }

        $question = array(
            'type' => 'determine',
            'stem' => 'test material-determine question.',
            'content' => 'question determine content',
            'courseId' => 1,
            'courseSetId' => $courseSetId,
            'lessonId' => 0,
            'answer' => array(1),
            'target' => 'course-1',
            'metas' => array('mediaId' => 1),
        );
        $this->getQuestionService()->create($question);

        $question = array(
            'type' => 'fill',
            'stem' => 'fill[[a|b]]',
            'content' => 'question fill content2',
            'courseId' => 1,
            'courseSetId' => $courseSetId,
            'lessonId' => 0,
            'answer' => array(array('a', 'b')),
            'target' => 'course-1',
        );
        $this->getQuestionService()->create($question);
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return FileUsedDao
     */
    protected function getFileUsedDao()
    {
        return $this->biz->dao('File:FileUsedDao');
    }
}
