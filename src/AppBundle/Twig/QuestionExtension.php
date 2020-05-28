<?php

namespace AppBundle\Twig;

use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\QuestionService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use Codeages\Biz\ItemBank\Item\Service\AttachmentService;
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
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('find_question_num_by_course_set_id', [$this, 'findQuestionNumsByCourseSetId']),
            new \Twig_SimpleFunction('question_html_filter', [$this, 'questionHtmlFilter']),
            new \Twig_SimpleFunction('attachment_uploader_token', [$this, 'makeAttachmentToken']),
        ];
    }

    public function findQuestionNumsByCourseSetId($courseSetId)
    {
        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes(['courseSetId' => $courseSetId, 'parentId' => 0]);
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
            $safeDomains = [];
        }

        $config = [
            'cacheDir' => $this->biz['cache_directory'].'/htmlpurifier',
            'safeIframeDomains' => $safeDomains,
        ];

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

    public function makeAttachmentToken()
    {
        $user = $this->biz['user'];
        $setting = $this->getSettingService()->get('storage', []);
        $accessKey = empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'];
        $secretKey = empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key'];

        return $this->getAttachmentService()->makeToken($user, $accessKey, $secretKey);
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->biz->service('Question:QuestionService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return AttachmentService
     */
    protected function getAttachmentService()
    {
        return $this->biz->service('ItemBank:Item:AttachmentService');
    }

    public function getName()
    {
        return 'web_question_twig';
    }
}
