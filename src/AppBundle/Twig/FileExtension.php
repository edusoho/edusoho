<?php

namespace AppBundle\Twig;

use Biz\File\Service\UploadFileService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileExtension extends \Twig_Extension
{
    protected $biz;

    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($container, $biz)
    {
        $this->container = $container;
        $this->biz = $biz;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('get_file_by_file_id', [$this, 'getFileByFileId']),
            new \Twig_SimpleFunction('get_file_ext_icon_Path', [$this, 'getFileExtIconPath']),
        ];
    }

    public function getFileByFileId($fileId)
    {
        return $this->getUploadFileService()->getFile($fileId);
    }

    public function getFileExtIconPath($fileExt)
    {
        $iconPaths = [
            'pdf' => 'assets/img/file/pdf.png',
            'ppt' => 'assets/img/file/ppt.png',
            'pptx' => 'assets/img/file/ppt.png',
            'docx' => 'assets/img/file/word.png',
            'doc' => 'assets/img/file/word.png',
            'txt' => 'assets/img/file/txt.png',
            'zip' => 'assets/img/file/zip.png',
            'jpeg' => 'assets/img/file/image.png',
            'png' => 'assets/img/file/image.png',
            'jpg' => 'assets/img/file/image.png',
            'xlsx' => 'assets/img/file/xlsx.png',
            'csv' => 'assets/img/file/xlsx.png',
            'xls' => 'assets/img/file/xlsx.png',
            'mp3' => 'assets/img/file/audio.png',
            'mp4' => 'assets/img/file/shipin.png',
            'mov' => 'assets/img/file/shipin.png',
            'rmvb' => 'assets/img/file/shipin.png',
            'flv' => 'assets/img/file/shipin.png',
            'avi' => 'assets/img/file/shipin.png',
            'file' => 'assets/img/file/file.png',
        ];

        return isset($iconPaths[$fileExt]) ? $iconPaths[$fileExt] : $iconPaths['file'];
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->biz->service('File:UploadFileService');
    }
}
