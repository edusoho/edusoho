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

    protected $DEFAULT_IMAGE_X = 2600;

    protected $DEFAULT_IMAGE_Y = 3600;

    protected function setCertificateTitle(Certificate $certificate)
    {
        $length = mb_strlen($certificate->getCertificateTitle(), 'utf-8');

        return $this->imageTtfText(
            [
                'x' => 1300 * $this->imageXRatio - $length * 0.5 * $this->defaultFontSize,
                'y' => 992 * $this->imageYRatio,
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
                'x' => 1300 * $this->imageXRatio - $length * 0.25 * $this->defaultFontSize,
                'y' => 1320 * $this->imageYRatio,
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
                    'x' => 494 * $this->imageXRatio,
                    'y' => 1456 * $this->imageYRatio + $key * 112,
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
        $this->imageTtfText(
            [
                'x' => 400 * $this->imageXRatio,
                'y' => 400 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书编号：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 634 * $this->imageXRatio,
                'y' => 400 * $this->imageYRatio,
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
                'x' => 400 * $this->imageXRatio,
                'y' => 554 * $this->imageYRatio,
                'fontSize' => 0.3 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '有效期至：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 634 * $this->imageXRatio,
                'y' => 554 * $this->imageYRatio,
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
                'x' => 1258 * $this->imageXRatio,
                'y' => 2896 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '发证日期：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 1586 * $this->imageXRatio,
                'y' => 2896 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
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
                'dst_x' => 1550 * $this->imageXRatio,
                'dst_y' => 2510 * $this->imageYRatio,
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
        imagecopymerge($this->image, $qrCode, 1980 * $this->imageXRatio, 360 * $this->imageYRatio, 0, 0, 160 * $this->imageXRatio, 160 * $this->imageXRatio, 100);

        return $this->imageTtfText(
            [
                'x' => 1920 * $this->imageXRatio,
                'y' => 588 * $this->imageYRatio,
                'fontSize' => 40 / 160 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书验证二维码',
            ]
        );
    }
}
