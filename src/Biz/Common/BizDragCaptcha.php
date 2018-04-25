<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;

class BizDragCaptcha extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    const JIGSAW_WIDTH = 50;

    private $tokenType = 'drag_captcha';

    public function generate($options = array())
    {
        $size = getimagesize('/private/var/www/edusoho/web/assets/img/captcha/2.png');

        $default = array(
            'height' => $size[1],
            'width' => $size[0],
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
        $sub = imagecreatefrompng('/private/var/www/edusoho/web/assets/img/captcha/jigsaw-border.png');
        imagecopyresampled($source, $sub, $options['positionX'], $options['positionY'], 0, 0, self::JIGSAW_WIDTH, self::JIGSAW_WIDTH, 80, 80);
        ob_start();
        imagepng($source);

        imagedestroy($sub);
        imagedestroy($source);

        return ob_get_clean();
    }

    private function getSource($options)
    {
        return imagecreatefrompng('/private/var/www/edusoho/web/assets/img/captcha/2.png');
    }

    private function getJigsaw($options)
    {
        $source = $this->getSource($options);

        $jigsawBg = imagecreate(self::JIGSAW_WIDTH, $options['height']);
        $white = imagecolorallocatealpha($jigsawBg, 255, 255, 255, 0);
        imagecolortransparent($jigsawBg, $white);

        imagecopymerge($jigsawBg, $source, 0, $options['positionY'], $options['positionX'], $options['positionY'], self::JIGSAW_WIDTH, self::JIGSAW_WIDTH, 100);
        ob_start();
        imagepng($jigsawBg);
        $str = ob_get_clean();
        imagedestroy($jigsawBg);

        return 'data:image/png;base64,'.base64_encode($str);
    }

    private function setJigsawPosition($options)
    {
        $options['positionX'] = rand(self::JIGSAW_WIDTH, $options['width'] - self::JIGSAW_WIDTH);
        $options['positionY'] = rand(self::JIGSAW_WIDTH, $options['height'] - self::JIGSAW_WIDTH);

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
