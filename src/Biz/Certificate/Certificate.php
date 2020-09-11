<?php

namespace Biz\Certificate;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\InvalidArgumentException;

class Certificate
{
    /**
     * @var string 证书标题
     */
    protected $certificateTitle = '';

    /**
     * @var string 证书二维码Url
     */
    protected $certificateQrCodeUrl = '';

    /**
     * @var string 证书授予人信息
     */
    protected $certificateRecipient = '';

    /**
     * @var string 证书正文
     */
    protected $certificateContent = '';

    /**
     * @var string 证书编码
     */
    protected $certificateCode = '';

    /**
     * @var string 证书有效期
     */
    protected $certificateDeadline = '';

    /**
     * @var string 证书发送时间
     */
    protected $certificateIssueTime = '';

    /**
     * @var string 证书印章图片链接
     */
    protected $certificateStamp = '';

    /**
     * @var string 证书底图图片链接
     */
    protected $certificateBasemap = '';

    /**
     * @var array
     */
    protected $certificate = [];

    public function setCertificateParams($params)
    {
        $fields = [
            'certificateTitle',
            'certificateQrCodeUrl',
            'certificateRecipient',
            'certificateContent',
            'certificateCode',
            'certificateExpiryTime',
            'certificateIssueTime',
            'certificateStamp',
            'certificateBasemap',
        ];
        if (!ArrayToolkit::requireds($params, $fields)) {
            throw new InvalidArgumentException('Lack of required fields');
        }
        $this->certificate = ArrayToolkit::parts($params, $fields);
        $this->certificateTitle = $params['certificateTitle'];
        $this->certificateQrCodeUrl = $params['certificateQrCodeUrl'];
        $this->certificateRecipient = $params['certificateRecipient'];
        $this->certificateContent = $params['certificateContent'];
        $this->certificateCode = $params['certificateCode'];
        $this->certificateDeadline = $params['certificateExpiryTime'];
        $this->certificateIssueTime = $params['certificateIssueTime'];
        $this->certificateStamp = $params['certificateStamp'];
        $this->certificateBasemap = $params['certificateBasemap'];
    }

    public function getCertificateParams()
    {
        return $this->certificate;
    }

    public function getCertificateTitle()
    {
        return $this->certificateTitle;
    }

    public function getCertificateQrCodeUrl()
    {
        return $this->certificateQrCodeUrl;
    }

    public function getCertificateRecipient()
    {
        return $this->certificateRecipient;
    }

    public function getCertificateContent()
    {
        return $this->certificateContent;
    }

    public function getCertificateCode()
    {
        return $this->certificateCode;
    }

    public function getCertificateDeadline()
    {
        return $this->certificateDeadline;
    }

    public function getCertificateIssueTime()
    {
        return $this->certificateIssueTime;
    }

    public function getCertificateStamp()
    {
        return $this->certificateStamp;
    }

    public function getCertificateBasemap()
    {
        return $this->certificateBasemap;
    }
}
