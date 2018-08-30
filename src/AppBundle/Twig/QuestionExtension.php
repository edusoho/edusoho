<?php

namespace AppBundle\Twig;

use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\QuestionService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionExtension extends \Twig_Extension
{
    /**
     * @var Biz
     */
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('find_question_num_by_course_set_id', array($this, 'findQuestionNumsByCourseSetId')),
        );
    }

    public function findQuestionNumsByCourseSetId($courseSetId)
    {
        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes(array('courseSetId' => $courseSetId, 'parentId' => 0));
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        return $questionNums;
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    public function getName()
    {
        return 'web_question_twig';
    }
}
