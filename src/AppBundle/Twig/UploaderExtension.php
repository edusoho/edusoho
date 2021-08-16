<?php

namespace AppBundle\Twig;

use AppBundle\Util\UploaderToken;

class UploaderExtension extends \Twig_Extension
{
    protected $container;

    protected $pageScripts;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('uploader_token', [$this, 'makeUpoaderToken']),
            new \Twig_SimpleFunction('uploader_process', [$this, 'getProcessMode']),
            new \Twig_SimpleFunction('uploader_accept', [$this, 'getUploadFileAccept']),
        ];
    }

    public function makeUpoaderToken($targetType, $targetId, $bucket, $ttl = 86400)
    {
        $maker = new UploaderToken();

        return $maker->make($targetType, $targetId, $bucket, $ttl);
    }

    public function getProcessMode($targetType)
    {
        $modes = [
            'course-task' => 'auto',
            'coursematerial' => 'auto',
            'materiallib' => 'auto',
            'course-activity' => 'auto',
            'headLeader' => 'none',
        ];

        if (isset($modes[$targetType])) {
            return $modes[$targetType];
        }

        return 'auto';
    }

    public function getUploadFileAccept($targetType, $only = '')
    {
        $targetAcceptTypes = [
            'course-task' => ['video', 'audio', 'flash', 'ppt', 'cloud_document'],
            'course-batch-create-lesson' => ['video', 'audio', 'ppt', 'cloud_document'],
            'course-activity' => ['video', 'audio', 'flash', 'ppt', 'document', 'all'],
            'coursematerial' => ['video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'],
            'materiallib' => ['video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'],
            'attachment' => ['video', 'audio', 'ppt', 'document', 'zip'],
        ];
        $availableAccepts = [
            'video' => [
                'extensions' => ['mp4', 'avi', 'flv', 'f4v', 'mpg', 'wmv', 'mov', 'vob', 'rmvb', 'mkv', 'm4v'],
                'mimeTypes' => ['video/mp4', 'video/mpeg', 'video/x-msvideo', 'video/quicktime', 'video/3gpp', 'video/x-m4v', 'video/x-flv', 'video/x-ms-wmv'],
            ],
            'local_video' => [
                'extensions' => ['mp4'],
                'mimeTypes' => ['video/mp4', 'video/mpeg', 'video/x-msvideo', 'video/quicktime', 'video/3gpp', 'video/x-m4v', 'video/x-flv', 'video/x-ms-wmv'],
            ],
            'audio' => [
                'extensions' => ['mp3'],
                'mimeTypes' => ['audio/mp4', 'audio/mpeg', 'audio/basic', 'audio/ac3', 'audio/ogg', 'audio/3gpp'],
            ],
            'flash' => [
                'extensions' => ['swf'],
                'mimeTypes' => ['application/x-shockwave-flash'],
            ],
            'ppt' => [
                'extensions' => ['ppt', 'pptx'],
                'mimeTypes' => ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', '.pptx', '.ppt'],
            ],
            'cloud_document' => [
                'extensions' => ['doc', 'docx', 'pdf', 'xls', 'xlsx'],
                'mimeTypes' => ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            ],
            'document' => [
                'extensions' => ['doc', 'docx', 'pdf', 'xls', 'xlsx', 'wps', 'odt', 'ppt', 'pptx'],
                'mimeTypes' => ['application/vnd.ms-excel', 'application/vnd.ms-outlook', 'application/vnd.ms-pkicertstore', 'application/vnd.ms-pkiseccat', 'application/vnd.ms-pkistl', 'application/vnd.ms-powerpoint', 'application/vnd.ms-project', 'application/vnd.ms-works', 'application/msword', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', '.pptx', '.ppt'],
            ],
            'zip' => [
                'extensions' => ['zip', 'rar', 'gz', 'tar', '7z'],
                'mimeTypes' => ['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/x-tar', 'application/x-gzip', 'application/x-7zip'],
            ],
            'image' => [
                'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp'],
                'mimeTypes' => ['image/jpg,image/jpeg,image/png,image/gif,image/bmp'],
            ],
            'text' => [
                'extensions' => ['txt', 'html', 'js', 'css'],
                'mimeTypes' => ['text/*'],
            ],
            'all' => [
                'extensions' => ['*'],
                'mimeTypes' => ['*'],
            ],
        ];

        $types = [];

        $only = explode(',', $only);

        if ($only && !empty($only[0])) {
            $types = $only;
        } elseif (isset($targetAcceptTypes[$targetType])) {
            $types = $targetAcceptTypes[$targetType];
        } else {
            $types = ['all'];
        }

        $accept = ['extensions' => [], 'mimeTypes' => []];

        foreach ($types as $type) {
            if (isset($availableAccepts[$type])) {
                $accept['extensions'] = array_merge($accept['extensions'], $availableAccepts[$type]['extensions']);
                $accept['mimeTypes'] = array_merge($accept['mimeTypes'], $availableAccepts[$type]['mimeTypes']);
            }
        }

        return $accept;
    }

    public function getName()
    {
        return 'topxia_uploader_twig';
    }
}
