<?php

namespace Biz\Certificate\ImgBuilder;

use Biz\Certificate\Certificate;
use Codeages\Biz\Framework\Context\Biz;
use Endroid\QrCode\QrCode;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

abstract class ImgBuilder
{
    protected $fontName = 'SourceHanSerifCNBold.otf';
    protected $biz;
    protected $certificateDir;

    protected $defaultFontSize = 160; //证书标题作为标准字号

    const IMAGE_RATIO = 1;

    protected $imageX = 1;

    protected $imageY = 1;

    protected $imageXRatio = 1;

    protected $imageYRatio = 1;

    protected $DEFAULT_IMAGE_X = 1;

    protected $DEFAULT_IMAGE_Y = 1;

    protected $PERCENT = 1;

    protected $image = null;

    protected $fontPath = '';

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
        $this->certificateDir = $this->biz['topxia.upload.public_directory'].'/tmp/certificate/';
        $this->fontPath = $this->getFontPath($this->fontName);
    }

    /**
     * @param Certificate $certificate
     * @param $percent
     *
     * @return string
     */
    public function getCertificateImgByBase64(Certificate $certificate, $percent = 1)
    {
        $this->PERCENT = $percent;
        $fileUri = $this->buildImage($certificate);
        $base64 = $this->imageToBase64($fileUri);
        $this->deleteFile($fileUri);

        return $base64;
    }

    /**
     * @param Certificate $certificate
     * @param $percent
     *
     * @return mixed|string
     *
     *                      获取证书图片
     */
    public function getCertificateImgByUrl(Certificate $certificate, $percent = 1)
    {
        $this->PERCENT = $percent;
        $fileUri = $this->buildImage($certificate);
        $fileDir = explode('../web/', $fileUri);

        return $fileDir[1];
    }

    protected function buildImage(Certificate $certificate)
    {
        $this->image = $this->_imageCreateFromJpegOrPng($certificate->getCertificateBasemap());
        $this->imageX = imagesx($this->image);
        $this->imageY = imagesy($this->image);
        $this->imageXRatio = $this->imageX / $this->DEFAULT_IMAGE_X;
        $this->imageYRatio = $this->imageY / $this->DEFAULT_IMAGE_Y;
        $this->defaultFontSize = 160 * $this->imageXRatio;
        $this->image = $this->compressImg(1);
        $this->setCertificateQrCode($certificate);
        $this->setCertificateTitle($certificate);
        $this->setCertificateRecipient($certificate);
        $this->setCertificateContent($certificate);
        $this->setCertificateCode($certificate);
        $this->setCertificateDeadline($certificate);
        $this->setCertificateIssueTime($certificate);
        $this->setCertificateStamp($certificate);

        $this->image = $this->compressImg($this->PERCENT);
        $fileUri = $this->saveImageFile();
        imagejpeg($this->image, $fileUri);
        imagedestroy($this->image);

        return $fileUri;
    }

    /**
     * @return false|resource
     *
     * 高清压缩图片
     */
    public function compressImg($percent)
    {
        $new_width = $this->imageX * $percent;
        $new_height = $this->imageY * $percent;
        $image_thump = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image_thump, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->imageX, $this->imageY);

        return $image_thump;
    }

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书标题
     */
    abstract protected function setCertificateTitle(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书用户
     */
    abstract protected function setCertificateRecipient(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书正文
     */
    abstract protected function setCertificateContent(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书编码
     */
    abstract protected function setCertificateCode(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书有效期
     */
    abstract protected function setCertificateDeadline(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书发布时间
     */
    abstract protected function setCertificateIssueTime(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *               设置证书印章
     */
    abstract protected function setCertificateStamp(Certificate $certificate);

    /**
     * @param Certificate $certificate
     *
     * @return mixed
     *
     * 设置证书二维码
     */
    abstract protected function setCertificateQrCode(Certificate $certificate);

    protected function imageToBase64($fileUri)
    {
        if ($fp = fopen($fileUri, 'rb', 0)) {
            $gambar = fread($fp, filesize($fileUri));
            fclose($fp);

            return chunk_split(base64_encode($gambar));
        }

        return  '';
    }

    protected function deleteFile($file)
    {
        if (file_exists($file)) {
            @unlink($file);
        }

        return true;
    }

    protected function getFontPath($fontName)
    {
        $directory = $this->biz['root_directory'].'/web/assets/fonts/';
        $fontPath = $directory.$fontName;
        if (!is_file($fontPath)) {
            throw new NotFoundResourceException('文件不存在');
        }

        return $fontPath;
    }

    protected function parseFileUri($uri)
    {
        $parsed = [];
        if (false == stripos($uri, '://')) {
            $parsed['fullpath'] = $uri;

            return $parsed;
        }
        $parts = explode('://', $uri);
        if (empty($parts) || 2 != count($parts)) {
            throw $this->createServiceException(sprintf('解析文件URI(%s)失败！', $uri));
        }
        $parsed['access'] = $parts[0];
        $parsed['path'] = $parts[1];
        $parsed['directory'] = dirname($parsed['path']);
        $parsed['name'] = basename($parsed['path']);

        if ('public' == $parsed['access']) {
            $directory = $this->biz['topxia.upload.public_directory'];
        } else {
            $directory = $this->biz['topxia.upload.private_directory'];
        }
        $parsed['fullpath'] = $directory.'/'.$parsed['path'];

        return $parsed;
    }

    /**
     * @param $image
     * @param $setting
     *
     * @return mixed
     *               图片添加文字
     */
    protected function imageTtfText($setting)
    {
        $black = imagecolorallocate($this->image, $setting['color'], $setting['color'], $setting['color']);
        for ($i = 0; $i < $setting['fontWeight']; ++$i) {
            imagettftext(
                $this->image,
                $this->_px2pt($setting['fontSize']) * self::IMAGE_RATIO,
                0,
                $setting['x'],
                $setting['y'],
                $black,
                $this->fontPath,
                $setting['txt']
            );
        }

        return $this->image;
    }

    /**
     * @param $stampIconUri
     * @param $setting
     *
     * @return mixed
     *
     * 图片添加印章
     */
    protected function imageTtfStamp($stampIconUri, $setting)
    {
        $stamp = $this->_imageCreateFromJpegOrPng($stampIconUri);
        if (false !== stripos($stampIconUri, '.jpg') || false !== stripos($stampIconUri, '.jpeg')) {
            imagecopymerge($this->image, $stamp, $setting['dst_x'], $setting['dst_y'], $setting['src_x'], $setting['src_y'], $setting['src_w'], $setting['src_h'], $setting['pct']);
        } else {
            $black = imagecolorallocate($stamp, 0, 0, 0);
            imagecolortransparent($stamp, $black);
            imagecopy($this->image, $stamp, $setting['dst_x'], $setting['dst_y'], $setting['src_x'], $setting['src_y'], $setting['src_w'], $setting['src_h']);
        }
        imagedestroy($stamp);

        return $this->image;
    }

    /**
     * 处理正文分行，开头空两格
     * $content 原始字符串
     * $length 插入的间隔长度, 英文长度
     * $hansLength 一个汉字等于多少个英文的宽度
     * $append 需要插入的字符串
     */
    public function processContentSegmentation($content, $length, $hansLength = 2)
    {
        $content = '&*&*'.$content;
        $nstr = '';
        for ($line = 0, $len = mb_strlen($content, 'utf-8'), $i = 0; $i < $len; ++$i) {
            $v = mb_substr($content, $i, 1, 'utf-8');
            $vlen = strlen($v) > 1 ? $hansLength : 1;
            if ($line + $vlen > $length) {
                $nstr .= '&\n&';
                $line = 0;
            }
            $nstr .= $v;
            $line += $vlen;
        }
        $nstr .= '&\n&';
        $nstr = str_ireplace('&*&*', '        ', $nstr);
        $contents = explode('&\n&', $nstr);

        return array_filter($contents);
    }

    protected function buildQrCode($url)
    {
        $qrCode = new QrCode();
        $qrCode->setText($url);
        $qrCode->setSize(160 * $this->imageXRatio);
        $qrCode->setPadding(0);
        $qrCode->setForegroundColor(['r' => 153, 'g' => 153, 'b' => 153, 'a' => 1]);
        $img = $qrCode->get('jpg');

        return $img;
    }

    protected function _px2pt($px)
    {
        return $pt = $px * 3 / 4;
    }

    protected function saveImageFile()
    {
        $tmpDir = $this->certificateDir.date('Y').'/'.date('m-d').'/';

        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }
        $fileName = time().base64_encode('certificate'.uniqid()).'.png';

        return $tmpDir.$fileName;
    }

    protected function _imageCreateFromJpegOrPng($filename)
    {
        if (false !== stripos($filename, '.jpg') || false !== stripos($filename, '.jpeg')) {
            return @imagecreatefromjpeg($filename);
        }

        return @imagecreatefrompng($filename);
    }

    protected function _imageCreateFromImageOrString($file)
    {
        if (false !== stripos($file, '.jpg') || false !== stripos($file, '.jpeg')) {
            return @imagecreatefromjpeg($file);
        }
        if (false !== stripos($file, '.png')) {
            return @imagecreatefrompng($file);
        }

        return @imagecreatefromstring($file);
    }
}
