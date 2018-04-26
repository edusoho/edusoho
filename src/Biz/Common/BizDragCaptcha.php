<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;

class BizDragCaptcha extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    const JIGSAW_WIDTH = 40;

    const DEVIATION = 500;

    const TOKENTIMES = 5;

    const TOKENTYPE = 'drag_captcha';

    public function generate($options = array())
    {
        $size = getimagesize('/private/var/www/edusoho/web/assets/img/captcha/5.jpg');

        $default = array(
            'height' => $size[1],
            'width' => $size[0],
        );
        $options = array_merge($options, $default);
        $options = $this->setJigsawPosition($options);
        $jigsaw = $this->getJigsaw($options);

        $token = $this->getTokenService()->makeToken(self::TOKENTYPE, array(
            'times' => self::TOKENTIMES + 1,
            'duration' => 60 * 10,
            'userId' => 0,
            'data' => $options,
        ));

        return array(
            'token' => $token['token'],
            'jigsaw' => $jigsaw,
            'w' => $options,
        );
    }

    public function checkByServer($token, $jigsaw)
    {
        $token = $this->getTokenService()->verifyToken(self::TOKENTYPE, $token);
        if (!$this->validateJigsaw($token, $jigsaw)) {
            throw new \Exception();
        }

        return true;
    }

    public function check($token, $jigsaw)
    {
        // 由于前端后端都要消耗token使用次数，所以验证正确之后，必须保证剩余验证次数大于一次
        $token = $this->getTokenService()->verifyToken(self::TOKENTYPE, $token);
        if (empty($token)) {
            return self::STATUS_EXPIRED;
        }

        if ($this->validateJigsaw($token, $jigsaw)) {
            return $token['remainedTimes'] > 2 ? self::STATUS_INVALID : self::STATUS_EXPIRED;
        }

        return self::STATUS_SUCCESS;
    }

    private function validateJigsaw($token, $jigsaw)
    {
        return abs($jigsaw - $token['data']['positionX']) > self::DEVIATION;
    }

    public function getBackground($token)
    {
        $token = $this->getTokenDao()->getByToken($token);
        if (empty($token)) {
            return;
        }

        $options = $token['data'];
        $source = $this->getSource($options);
        $sub = imagecreatefrompng('/private/var/www/edusoho/web/assets/img/captcha/jigsaw-border5.png');
        imagecopyresampled($source, $sub, $options['positionX'], $options['positionY'], 0, 0, self::JIGSAW_WIDTH, self::JIGSAW_WIDTH, 80, 80);
        ob_start();
        imagepng($source);

        imagedestroy($sub);
        imagedestroy($source);

        return ob_get_clean();
    }

    private function getSource($options)
    {
        return imagecreatefromjpeg('/private/var/www/edusoho/web/assets/img/captcha/5.jpg');
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
