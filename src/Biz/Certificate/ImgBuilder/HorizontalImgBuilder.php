<?php

namespace Biz\Certificate\ImgBuilder;

use Biz\Certificate\Certificate;

/**
 * Class HorizontalImgBuilder 横版， 以3600x2600 作为参照基准，不论传入的图片大小会长宽分别与3600 2600等比缩放
 */
class HorizontalImgBuilder extends ImgBuilder
{
    protected $imageX = 1;

    protected $imageY = 1;

    protected $imageXRatio = 1; //缩放基数

    protected $imageYRatio = 1; //缩放基数

    protected $DEFAULT_IMAGE_X = 3600;

    protected $DEFAULT_IMAGE_Y = 2600;

    protected function setCertificateTitle(Certificate $certificate)
    {
        $length = mb_strlen($certificate->getCertificateTitle(), 'utf-8');

        return $this->imageTtfText(
            [
                'x' => 1800 * $this->imageXRatio - $length * $this->defaultFontSize * 0.5,
                'y' => 592 * $this->imageYRatio,
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
        return $this->imageTtfText(
            [
                'x' => 592 * $this->imageXRatio,
                'y' => 920 * $this->imageYRatio,
                'fontSize' => $this->defaultFontSize * 0.5,
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
            $length = 60,
            $hans_length = 2
        );

        foreach ($contents as $key => $content) {
            $this->imageTtfText(
                [
                    'x' => 592 * $this->imageXRatio,
                    'y' => 1113 * $this->imageYRatio + $key * 112,
                    'fontSize' => $this->defaultFontSize * 0.5,
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
                'x' => 592 * $this->imageXRatio,
                'y' => 1864 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书编号：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 920 * $this->imageXRatio,
                'y' => 1864 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
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
                'x' => 592 * $this->imageXRatio,
                'y' => 2008 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '有效期至：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 912 * $this->imageXRatio,
                'y' => 2008 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
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
                'x' => 2258 * $this->imageXRatio,
                'y' => 2008 * $this->imageYRatio,
                'fontSize' => 0.4 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '发证日期：',
            ]
        );

        return $this->imageTtfText(
            [
                'x' => 2586 * $this->imageXRatio,
                'y' => 2008 * $this->imageYRatio,
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
                'dst_x' => 2500 * $this->imageXRatio,
                'dst_y' => 1550 * $this->imageYRatio,
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
        imagecopymerge($this->image, $qrCode, 420 * $this->imageXRatio, 360 * $this->imageYRatio, 0, 0, 160 * $this->imageXRatio, 160 * $this->imageXRatio, 100);

        return $this->imageTtfText(
            [
                'x' => 360 * $this->imageXRatio,
                'y' => 588 * $this->imageYRatio,
                'fontSize' => 0.25 * $this->defaultFontSize,
                'color' => 102,
                'fontWeight' => 1,
                'bottomPicUrl' => $certificate->getCertificateBasemap(),
                'txt' => '证书验证二维码',
            ]
        );
    }
}
