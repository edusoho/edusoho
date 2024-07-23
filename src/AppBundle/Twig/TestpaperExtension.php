<?php

namespace AppBundle\Twig;

use Biz\Course\Service\MemberService;
use Biz\System\Service\SettingService;
use Biz\Testpaper\Service\TestpaperService;
use Codeages\Biz\Framework\Context\Biz;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TestpaperExtension extends \Twig_Extension
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
        return [
            new \Twig_SimpleFilter('parse_exercise_range', [$this, 'parseRange']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_features', [$this, 'getFeatures']),
            new \Twig_SimpleFunction('show_answers', [$this, 'canShowAnswers']),
        ];
    }

    public function parseRange($range)
    {
        $rangeDefault = ['bankId' => 0];
        $range = empty($range) ? $rangeDefault : $range;

        if (is_array($range)) {
            return $range;
        } elseif ('course' == $range) {
            return $rangeDefault;
        }

        return $rangeDefault;
    }

    public function getFeatures()
    {
        return $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : [];
    }

    public function canShowAnswers($testpaperResult)
    {
        if (empty($testpaperResult)) {
            return false;
        }

        if (!in_array($testpaperResult['status'], ['reviewing', 'finished'])) {
            return false;
        }

        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);
        if (!$canLookTestpaper) {
            return false;
        }

        $user = $this->biz['user'];
        if ($user->isAdmin()) {
            return true;
        }

        $isCourseTeacher = $this->getCourseMemberService()->isCourseTeacher($testpaperResult['courseId'], $user['id']);
        if ($isCourseTeacher) {
            return true;
        }

        $answersShowMode = $this->getSettingService()->node('questions.testpaper_answers_show_mode', 'submitted');
        if ('submitted' == $answersShowMode) {
            return true;
        }

        if ('reviewed' == $answersShowMode && 'finished' == $testpaperResult['status']) {
            return true;
        }

        if ('hide' == $answersShowMode && 'testpaper' != $testpaperResult['type']) {
            return true;
        }

        return false;
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->biz->service('Testpaper:TestpaperService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getName()
    {
        return 'web_testpaper_twig';
    }
}
