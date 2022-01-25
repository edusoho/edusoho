<?php

namespace Biz\Certificate\ImgBuilder;

use Biz\Certificate\Certificate;

/**
 * Class VerticalImgBuilder  竖版， 以2600x3600 作为参照基准，不论传入的图片大小会长宽分别与2600 3600等比缩放
 */
class VerticalImgBuilder extends ImgBuilder
{
    protected $imageX = 1;

    protected $imageY = 1;

    protected $imageXRatio = 1;

    protected $imageYRatio = 1;

    protected $DEFAULT_IMAGE_X = 2480;

    protected $DEFAULT_IMAGE_Y = 3508;

    protected function setCertificateTitle(Certificate $certificate)
    {
        $length = mb_strlen($certificate->getCertificateTitle(), 'utf-8');

        return $this->imageTtfText(
            [
                'x' => 1220 * $this->imageXRatio - $length * 0.5 * $this->defaultFontSize,
                'y' => 492 * $this->imageYRatio,
                'fontSize' => $this->defaultFontSize,
                'color' => 0,
                'fontWeight' => 3,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => $certificate->getCertificateTitle(),
            ]
        );
    }

    protected function setCertificateRecipient(Certificate $certificate)
    {
        $length = mb_strlen($certificate->getCertificateRecipient(), 'utf-8');

        return $this->imageTtfText(
            [
                'x' => 502 * $this->imageXRatio,
                'y' => 1000 * $this->imageYRatio,
                'fontSize' => 0.5 * $this->defaultFontSize,
                'color' => 0,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => $certificate->getCertificateRecipient(),
            ]
        );
    }

    protected function setCertificateContent(Certificate $certificate)
    {
        $contents = $this->processContentSegmentation(
            $certificate->getCertificateContent(),
            $length = 40,
            $hans_length = 2
        );
        foreach ($contents as $key => $content) {
            $this->imageTtfText(
                [
                    'x' => 454 * $this->imageXRatio,
                    'y' => 1156 * $this->imageYRatio + $key * 112,
                    'fontSize' => 0.5 * $this->defaultFontSize,
                    'color' => 51,
                    'fontWeight' => 1,
                    'bottomPicUrl' => $certificate->getCertificateBasemap(),
                    'txt' => $content,
                ]
            );
        }
    }

    protected function setCertificateCode(Certificate $certificate)
    {
        $x = 490 * $this->imageXRatio;
        $this->imageTtfText(
            [
                'x' => $x,
                'y' => 2910 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书编号：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => $x + 234 * $this->imageXRatio,
                'y' => 2910 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 0,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => $certificate->getCertificateCode(),
            ]
        );
    }

    protected function setCertificateDeadline(Certificate $certificate)
    {
        $this->imageTtfText(
            [
                'x' => 1510 * $this->imageXRatio,
                'y' => 2796 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书有效时间：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 1844 * $this->imageXRatio,
                'y' => 2796 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 0,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => $certificate->getCertificateDeadline(),
            ]
        );
    }

    protected function setCertificateIssueTime(Certificate $certificate)
    {
        $this->imageTtfText(
            [
                'x' => 1510 * $this->imageXRatio,
                'y' => 2896 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书获取时间：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 1844 * $this->imageXRatio,
                'y' => 2896 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 0,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => $certificate->getCertificateIssueTime(),
            ]
        );
    }

    protected function setCertificateStamp(Certificate $certificate)
    {
        if (empty($certificate->getCertificateStamp())) {
            return null;
        }

        return $this->imageTtfStamp(
            $certificate->getCertificateStamp(),
            [
                'dst_x' => 1500 * $this->imageXRatio,
                'dst_y' => 2410 * $this->imageYRatio,
                'src_x' => 0,
                'src_y' => 0,
                'src_w' => 650 * $this->imageXRatio,
                'src_h' => 650 * $this->imageXRatio,
                'pct' => 100,
            ]
        );
    }

    protected function setCertificateQrCode(Certificate $certificate)
    {
        if (empty($certificate->getCertificateQrCodeUrl())) {
            return null;
        }
        $qrCode = $this->buildQrCode($certificate->getCertificateQrCodeUrl());
        $qrCode = $this->_imageCreateFromImageOrString($qrCode);
        imagecopymerge($this->image, $qrCode, 490 * $this->imageXRatio, 2580 * $this->imageYRatio, 0, 0, 258 * $this->imageXRatio, 258 * $this->imageXRatio, 100);

        return $this->imageTtfText(
            [
                'x' => 450 * $this->imageXRatio,
                'y' => 2610 * $this->imageYRatio,
                'fontSize' => 40 / 160 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '',
            ]
        );
    }
}
