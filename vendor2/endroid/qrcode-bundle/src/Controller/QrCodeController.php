<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Bundle\QrCodeBundle\Controller;

use Endroid\QrCode\QrCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * QR code controller.
 */
class QrCodeController extends Controller
{
    /**
     * @Route("/{text}.{extension}", name="endroid_qrcode", requirements={"text"="[\w\W]+", "extension"="jpg|png|gif"})
     */
    public function generateAction(Request $request, $text, $extension)
    {
        $qrCode = new QrCode();
        $qrCode->setText($text);

        if ($request->get('size') !== null) {
            $qrCode->setSize($request->get('size'));
        }

        if ($request->get('padding') !== null) {
            $qrCode->setPadding($request->get('padding'));
        }

        if ($request->get('error_correction_level') !== null) {
            $qrCode->setErrorCorrection($request->get('error_correction_level'));
        }

        if ($request->get('foreground_color') !== null) {
            $qrCode->setForegroundColor($request->get('foreground_color'));
        }

        if ($request->get('background_color') !== null) {
            $qrCode->setBackgroundColor($request->get('background_color'));
        }

        if ($request->get('label') !== null) {
            $qrCode->setLabel($request->get('label'));
        }

        if ($request->get('labelFontSize') !== null) {
            $qrCode->setLabelFontSize($request->get('labelFontSize'));
        }

        $qrCode = $qrCode->get($extension);

        $mime_type = 'image/' . $extension;
        if ($extension == 'jpg') {
            $mime_type = 'image/jpeg';
        }

        return new Response($qrCode, 200, array('Content-Type' => $mime_type));
    }
}
