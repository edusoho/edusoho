<?php

namespace AppBundle\Common;

class ImgConverToData
{
    public $imgSrc;
    public $imgData;
    public $imgMime;

    public function getImgDir($source)
    {
        $this->imgSrc = $source;
    }

    public function img2Data()
    {
        $this->_imgMime($this->imgSrc);

        return $this->imgData = fread(fopen($this->imgSrc, 'rb'), filesize($this->imgSrc));
    }

    public function data2Img()
    {
        header("content-type:$this->imgMime");

        return $this->imgData;
    }

    public function _imgMime($imgSrc)
    {
        $info = getimagesize($imgSrc);

        return $this->imgMime = $info['mime'];
    }
}
