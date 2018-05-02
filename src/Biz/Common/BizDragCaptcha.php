<?php

namespace Biz\Common;

use Codeages\Biz\Framework\Context\BizAware;
use AppBundle\Common\ArrayToolkit;

class BizDragCaptcha extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    const JIGSAW_WIDTH = 40;

    const DEVIATION = 0.3;

    const SERVER_TOKENTIMES = 1;

    const TOKENTIMES = 3;

    const TOKENTYPE = 'drag_captcha';

    private $backgroundImages = array(
        '1.jpg',
        '2.jpg',
        '3.jpg',
        '4.jpg',
        '5.jpg',
        '6.jpg',
    );

    public function generate($options = array())
    {
        $bg = $this->backgroundImages[rand(0, 5)];
        $imagePath = $this->getImagePath($bg);
        $size = getimagesize($imagePath);

        $default = array(
            'height' => $size[1],
            'width' => $size[0],
            'bg' => $imagePath,
        );
        $options = array_merge($options, $default);
        $options = $this->setJigsawPosition($options);
        $jigsaw = $this->getJigsaw($options);

        $token = $this->getTokenService()->makeToken(self::TOKENTYPE, array(
            'times' => self::TOKENTIMES + self::SERVER_TOKENTIMES,
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

    public function getBackground($token)
    {
        $token = $this->getTokenDao()->getByToken($token);
        if (empty($token)) {
            return;
        }

        $options = $token['data'];
        $source = $this->getSource($options);
        $sub = imagecreatefrompng($this->getImagePath('jigsaw-border.png'));
        imagecopyresampled($source, $sub, $options['positionX'], $options['positionY'], 0, 0, self::JIGSAW_WIDTH, self::JIGSAW_WIDTH, 80, 80);
        ob_start();
        imagejpeg($source);

        imagedestroy($sub);
        imagedestroy($source);

        return ob_get_clean();
    }

    public function checkByServer($data)
    {
        if (!ArrayToolkit::requireds($data, array('drag_captcha_token', 'jigsaw'), true)) {
            throw CommonException::FORBIDDEN_DRAG_CAPTCHA_REQUIRED();
        }

        $token = $this->getTokenService()->verifyToken(self::TOKENTYPE, $data['drag_captcha_token']);

        if (empty($token)) {
            throw CommonException::FORBIDDEN_DRAG_CAPTCHA_EXPIRED();
        }

        if (!$this->validateJigsaw($token, $data['jigsaw'])) {
            throw CommonException::FORBIDDEN_DRAG_CAPTCHA_ERROR();
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

        if (!$this->validateJigsaw($token, $jigsaw)) {
            return $token['remainedTimes'] > 2 ? self::STATUS_INVALID : self::STATUS_EXPIRED;
        }

        return self::STATUS_SUCCESS;
    }

    private function validateJigsaw($token, $jigsaw)
    {
        return abs($jigsaw - $token['data']['positionX']) < self::DEVIATION;
    }

    private function getSource($options)
    {
        return imagecreatefromjpeg($options['bg']);
    }

    private function getJigsaw($options)
    {
        $source = $this->getSource($options);

        $jigsawBg = imagecreatetruecolor(self::JIGSAW_WIDTH, $options['height']);
        imagesavealpha($jigsawBg, true);
        $transColour = imagecolorallocatealpha($jigsawBg, 255, 255, 255, 127);
        imagefill($jigsawBg, 0, 0, $transColour);

        imagecopymerge($jigsawBg, $source, 0, $options['positionY'], $options['positionX'], $options['positionY'], self::JIGSAW_WIDTH, self::JIGSAW_WIDTH, 100);
        ob_start();
        imagepng($jigsawBg);
        $str = ob_get_clean();
        imagedestroy($jigsawBg);
        imagedestroy($source);

        return 'data:image/png;base64,'.base64_encode($str);
    }

    private function setJigsawPosition($options)
    {
        $rate = 100;
        $options['positionX'] = rand(self::JIGSAW_WIDTH * $rate, $rate * ($options['width'] - self::JIGSAW_WIDTH)) / $rate;
        $options['positionY'] = rand(self::JIGSAW_WIDTH * $rate, $rate * ($options['height'] - self::JIGSAW_WIDTH)) / $rate;

        return $options;
    }

    private function getImagePath($name)
    {
        $rootPath = $this->biz['root_directory'];

        return $rootPath.'web/assets/img/captcha/'.$name;
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
