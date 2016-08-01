<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\QrCodeBundle\Twig\Extension;

use Endroid\QrCode\QrCode;
use Symfony\Component\DependencyInjection\Container;
use Twig_Extension;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QrCodeExtension extends Twig_Extension implements ContainerAwareInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('qrcode_url', array($this, 'qrcodeUrlFunction')),
            new \Twig_SimpleFunction('qrcode_data_uri', array($this, 'qrcodeDataUriFunction')),
        );
    }

    /**
     * Creates the QR code URL corresponding to the given message.
     *
     * @param        $text
     * @param int    $size
     * @param int    $padding
     * @param string $extension
     * @param string $errorCorrectionLevel
     * @param array  $foregroundColor
     * @param array  $backgroundColor
     * @param string $label
     * @param string $labelFontSize
     * @param string $labelFontPath
     *
     * @return mixed
     */
    public function qrcodeUrlFunction($text, $size = null, $padding = null, $extension = null, $errorCorrectionLevel = null, array $foregroundColor = null, array $backgroundColor = null, $label = null, $labelFontSize = null, $labelFontPath = null)
    {
        $endroidQrCodeSetting = $this->container->getParameter('endroid_qr_code');

        $params = array_merge($endroidQrCodeSetting, array('text' => $text));

        if ($size !== null) {
            $params['size'] = $size;
        }

        if ($padding !== null) {
            $params['padding'] = $padding;
        }

        if ($extension !== null) {
            $params['extension'] = $extension;
        }

        if ($errorCorrectionLevel !== null) {
            $params['error_correction_level'] = $errorCorrectionLevel;
        }

        if ($foregroundColor !== null) {
            $params['foreground_color'] = $foregroundColor;
        }

        if ($backgroundColor !== null) {
            $params['background_color'] = $backgroundColor;
        }

        if ($label !== null) {
            $params['label'] = $label;
        }

        if ($labelFontSize !== null) {
            $params['label_font_size'] = $labelFontSize;
        }

        if ($labelFontPath !== null) {
            $params['label_font_path'] = $labelFontPath;
        }

        return $this->container->get('router')->generate('endroid_qrcode', $params, true);
    }

    /**
     * Creates the QR code data corresponding to the given message.
     *
     * @param        $text
     * @param int    $size
     * @param int    $padding
     * @param string $extension
     * @param mixed  $errorCorrectionLevel
     * @param array  $foregroundColor
     * @param array  $backgroundColor
     * @param string $label
     * @param string $labelFontSize
     * @param string $labelFontPath
     *
     * @return string
     */
    public function qrcodeDataUriFunction($text, $size = null, $padding = null, $extension = null, $errorCorrectionLevel = null, array $foregroundColor = null, array $backgroundColor = null, $label = null, $labelFontSize = null, $labelFontPath = null)
    {
        if ($size === null && $this->container->hasParameter('endroid_qrcode.size')) {
            $size = $this->container->getParameter('endroid_qrcode.size');
        }

        if ($padding === null && $this->container->hasParameter('endroid_qrcode.padding')) {
            $padding = $this->container->getParameter('endroid_qrcode.padding');
        }

        if ($extension === null && $this->container->hasParameter('endroid_qrcode.extension')) {
            $extension = $this->container->getParameter('endroid_qrcode.extension');
        }

        if ($errorCorrectionLevel === null && $this->container->hasParameter('endroid_qrcode.error_correction_level')) {
            $errorCorrectionLevel = $this->container->getParameter('endroid_qrcode.error_correction_level');
        }

        if ($foregroundColor === null && $this->container->hasParameter('endroid_qrcode.foreground_color')) {
            $foregroundColor = $this->container->getParameter('endroid_qrcode.foreground_color');
        }

        if ($backgroundColor === null && $this->container->hasParameter('endroid_qrcode.background_color')) {
            $backgroundColor = $this->container->getParameter('endroid_qrcode.background_color');
        }

        if ($label === null && $this->container->hasParameter('endroid_qrcode.label')) {
            $label = $this->container->getParameter('endroid_qrcode.label');
        }

        if ($labelFontSize === null && $this->container->hasParameter('endroid_qrcode.label_font_size')) {
            $labelFontSize = $this->container->getParameter('endroid_qrcode.label_font_size');
        }

        if ($labelFontPath === null && $this->container->hasParameter('endroid_qrcode.label_font_path')) {
            $labelFontPath = $this->container->getParameter('endroid_qrcode.label_font_path');
        }

        $qrCode = new QrCode();
        $qrCode->setText($text);

        if ($size !== null) {
            $qrCode->setSize($size);
        }

        if ($padding !== null) {
            $qrCode->setPadding($padding);
        }

        if ($extension !== null) {
            $qrCode->setExtension($extension);
        }

        if ($errorCorrectionLevel !== null) {
            $qrCode->setErrorCorrection($errorCorrectionLevel);
        }

        if ($foregroundColor !== null) {
            $qrCode->setForegroundColor($foregroundColor);
        }

        if ($backgroundColor !== null) {
            $qrCode->setBackgroundColor($backgroundColor);
        }

        if ($label != null) {
            $qrCode->setLabel($label);
        }

        if ($labelFontSize != null) {
            $qrCode->setLabelFontSize($labelFontSize);
        }

        if ($labelFontPath != null) {
            $qrCode->setLabelFontPath($labelFontPath);
        }

        return $qrCode->getDataUri();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'endroid_qrcode';
    }
}
