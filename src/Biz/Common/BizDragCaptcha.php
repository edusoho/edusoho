<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;

class BizDragCaptcha extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    private $tokenType = 'drag_captcha';

    public function generate($options = array())
    {
        $default = array(
            'height' => 240,
            'width' => 480,
            'jigsaw' => 80,
        );
        $options = array_merge($options, $default);
        $options = $this->setJigsawPosition($options);
        $jigsaw = $this->getJigsaw($options);

        $token = $this->getTokenService()->makeToken($this->tokenType, array(
            'times' => 10,
            'duration' => 60 * 30,
            'userId' => 0,
            'data' => $options,
        ));

        return array(
            'token' => $token['token'],
            'jigsaw' => $jigsaw,
        );
    }

    public function check($captchaId, $phrase)
    {
        $token = $this->getTokenService()->verifyToken($this->tokenType, $captchaId);

        if (empty($token)) {
            return self::STATUS_INVALID;
        }

        $remainedTimes = $token['remainedTimes'];

        if (0 == $remainedTimes) {
            return self::STATUS_EXPIRED;
        }

        // return $token['data']['phrase'] == $phrase ? self::STATUS_SUCCESS : self::STATUS_INVALID;
    }

    public function getBackground($token)
    {
        $token = $this->getTokenDao()->getByToken($token);
        if (empty($token)) {
            return;
        }
        $options = $token['data'];
        $source = $this->getSource($options);
        $backgroundJigsaw = imagecreate($options['jigsaw'], $options['jigsaw']);
        $white = imagecolorallocate($backgroundJigsaw, 255, 255, 255);
        imagefill($backgroundJigsaw, 0, 0, $white);
        $sub = imagecreate($options['jigsaw'], $options['height']);
        $white = imagecolorallocatealpha($sub, 255, 255, 255, 80);
        // $image = imagecreatefrompng('/private/var/www/edusoho/src/Biz/Common/CaptchaImages/3.png');
        // $ptimage = imagecreatefrompng('/private/var/www/edusoho/src/Biz/Common/CaptchaImages/pt.png');
        //$size = getimagesize('/private/var/www/edusoho/src/Biz/Common/CaptchaImages/3.png');
        imagecopymerge($source, $sub, $options['positionX'], $options['positionY'], 0, 0, $options['jigsaw'], $options['jigsaw'], 90);

        ob_start();
        imagepng($source);

        return ob_get_clean();
    }

    private function getSource($options)
    {
        $bg = imagecreate($options['width'], $options['height']);
        $red = imagecolorallocate($bg, 255, 0, 0);
        imagefill($bg, 0, 0, $red);

        return $bg;
    }

    private function getJigsaw($options)
    {
        $source = $this->getSource($options);
        $sub = imagecreate($options['jigsaw'], $options['height']);
        $white = imagecolorallocatealpha($sub, 255, 255, 255, 0);
        imagecolortransparent($sub, $white);
        imagecopymerge($sub, $source, 0, $options['positionY'], $options['positionX'], $options['positionY'], $options['jigsaw'], $options['jigsaw'], 100);
        ob_start();
        imagepng($sub);
        $str = ob_get_clean();

        return 'data:image/png;base64,'.base64_encode($str);
    }

    private function setJigsawPosition($options)
    {
        $options['positionX'] = rand($options['jigsaw'], $options['width'] - $options['jigsaw']);
        $options['positionY'] = rand($options['jigsaw'], $options['height'] - $options['jigsaw']);

        return $options;
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    private function getTokenService()
    {
        return $this->biz->service('User:TokenService');
    }

    private function getTokenDao()
    {
        return $this->biz->dao('User:TokenDao');
    }
}
