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
        return array(
            new \Twig_SimpleFilter('parse_exercise_range', array($this, 'parseRange')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('get_features', array($this, 'getFeatures')),
            new \Twig_SimpleFunction('show_answers', array($this, 'canShowAnswers')),
            new \Twig_SimpleFunction('get_testpaper', array($this, 'getTestPaper')),
        );
    }

    public function parseRange($range)
    {
        $rangeDefault = array('bankId' => 0);
        $range = empty($range) ? $rangeDefault : $range;

        if (is_array($range)) {
            return $range;
        } elseif ('course' == $range) {
            return $rangeDefault;
        }

        return $rangeDefault;
    }

    public function getTestPaper($id)
    {
        return $this->getTestpaperService()->getTestpaper($id);
    }

    public function getFeatures()
    {
        return $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();
    }

    public function canShowAnswers($testpaperResult)
    {
        if (empty($testpaperResult)) {
            return false;
        }

        if (!in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
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
