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
            new \Twig_SimpleFunction('question_html_filter', array($this, 'questionHtmlFilter')),
        );
    }

    public function findQuestionNumsByCourseSetId($courseSetId)
    {
        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes(array('courseSetId' => $courseSetId, 'parentId' => 0));
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        return $questionNums;
    }

    public function questionHtmlFilter($html, $allowed = '')
    {
        if (!isset($html)) {
            return '';
        }

        $html = preg_replace('/(<img .*?src=")(.*?)(".*?>)/is', '[图片]', $html);
        $security = $this->getSettingService()->get('security');

        if (!empty($security['safe_iframe_domains'])) {
            $safeDomains = $security['safe_iframe_domains'];
        } else {
            $safeDomains = array();
        }

        $config = array(
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
            'safeIframeDomains' => $safeDomains,
        );

        $this->warmUp($config['cacheDir']);
        $htmlConfig = \HTMLPurifier_Config::createDefault();
        $htmlConfig->set('Cache.SerializerPath', $config['cacheDir']);
        $htmlConfig->set('HTML.Allowed', $allowed);

        $htmlpurifier = new \HTMLPurifier($htmlConfig);

        return $htmlpurifier->purify($html);
    }

    protected function warmUp($cacheDir)
    {
        if (!@mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
            throw new ServiceException('mkdir cache dir error');
        }

        if (!is_writable($cacheDir)) {
            chmod($cacheDir, 0777);
        }
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getName()
    {
        return 'web_question_twig';
    }
}
