<?php

namespace Biz\Certificate\Strategy;

use AppBundle\Util\CdnUrl;
use Biz\Certificate\Certificate;
use Biz\Certificate\Service\CertificateService;
use Biz\Certificate\Service\RecordService;
use Biz\Certificate\Service\TemplateService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Context\Biz;

abstract class BaseStrategy
{
    /**
     * @var Biz
     */
    protected $biz;

    public function __construct($biz)
    {
        $this->biz = $biz;
    }

    abstract public function getTargetModal();

    abstract public function count($conditions);

    abstract public function search($conditions, $orderBys, $start, $limit);

    abstract public function getTarget($targetId);

    abstract public function findTargetsByIds($targetIds);

    abstract public function findTargetsByTargetTitle($targetTitle);

    public function getCertificateImg($record)
    {
        $certificate = $this->getCertificateService()->get($record['certificateId']);
        if (empty($certificate)) {
            return '';
        }

        $template = $this->getTemplateService()->get($certificate['templateId']);

        $certificate = new Certificate();
        $certificateContent = implode('', explode("\r\n", trim($template['certificateContent'])));
        $certificate->setCertificateParams([
            'certificateTitle' => $template['certificateName'],
            'certificateQrCodeUrl' => '',
            'certificateRecipient' => $this->getRecipientContent($record['userId'], $template['recipientContent']),
            'certificateContent' => $this->getContent($record, $certificateContent),
            'certificateCode' => $record['certificateCode'],
            'certificateExpiryTime' => empty($record['expiryTime']) ? '长期有效' : date('Y-m-d', $record['expiryTime']),
            'certificateIssueTime' => date('Y-m-d', $record['issueTime']),
            'certificateStamp' => empty($template['stamp']) ? '' : $this->getWebExtension()->getFurl($template['stamp']),
            'certificateBasemap' => empty($template['basemap']) ? $this->getAssetUrl("static-dist/app/img/admin-v2/{$template['type']}_basemap.jpg") : $this->getWebExtension()->getFurl($template['basemap']),
        ]);

        return $this->getImgBuilder($template['styleType'])->getCertificateImgByBase64($certificate, 0.5);
    }

    protected function getRecipientContent($userId, $recipientContent)
    {
        $user = $this->getUserService()->getUserAndProfile($userId);
        if (strstr($recipientContent, '$name$')) {
            $recipientContent = str_replace('$name$', $user['truename'], $recipientContent);
        }

        if (strstr($recipientContent, '$username$')) {
            $recipientContent = str_replace('$username$', $user['nickname'], $recipientContent);
        }

        return $recipientContent;
    }

    protected function generateUrl($name, $parameters, $referenceType)
    {
        global $kernel;

        return $kernel->getContainer()->get('router')->generate($name, $parameters, $referenceType);
    }

    protected function getWebExtension()
    {
        global $kernel;

        return $kernel->getContainer()->get('web.twig.extension');
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }
        $path = $this->getBaseUrl()."/{$path}";

        return $path;
    }

    protected function getHttpHost()
    {
        return $this->getSchema()."://{$_SERVER['HTTP_HOST']}";
    }

    protected function getSchema()
    {
        $https = empty($_SERVER['HTTPS']) ? '' : $_SERVER['HTTPS'];
        if (!empty($https) && 'off' !== strtolower($https)) {
            return 'https';
        }

        return 'http';
    }

    protected function getCdn($type = 'default')
    {
        $cdn = new CdnUrl();

        return $cdn->get($type);
    }

    protected function getBaseUrl($type = 'default')
    {
        $cdnUrl = $this->getCdn($type);
        if (!empty($cdnUrl)) {
            return $this->getSchema().':'.$cdnUrl;
        }

        return $this->getHttpHost();
    }

    protected function getImgBuilder($type)
    {
        return $this->biz['certificate.img_builder.'.$type];
    }

    /**
     * @return RecordService
     */
    protected function getRecordService()
    {
        return $this->biz->service('Certificate:RecordService');
    }

    /**
     * @return CertificateService
     */
    protected function getCertificateService()
    {
        return $this->biz->service('Certificate:CertificateService');
    }

    /**
     * @return TemplateService
     */
    protected function getTemplateService()
    {
        return $this->biz->service('Certificate:TemplateService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
