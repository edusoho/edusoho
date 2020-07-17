<?php

namespace AppBundle\Twig;

use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ItemBankExerciseExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Biz
     */
    protected $biz;

    public function __construct(ContainerInterface $container, Biz $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('is_exercise_member_expired', [$this, 'isMemberExpired']),
        ];
    }

    public function isMemberExpired($exercise, $member)
    {
        if (empty($exercise) || empty($member)) {
            return false;
        }

        return !$this->getExerciseMemberService()->isMemberNonExpired($exercise, $member);
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }

    public function getName()
    {
        return 'topxia_item_bank_exercise_twig';
    }
}
