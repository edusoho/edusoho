<?php

namespace AppBundle\Twig;

use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QuestionBankExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('find_accessible_question_banks', array($this, 'findAccessibleQuestionBanks')),
        );
    }

    public function findAccessibleQuestionBanks()
    {
        return $this->getQuestionBankService()->findAllAccessibleQuestionBanks();
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }
}
