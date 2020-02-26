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
        return array();
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('uploader_token', array($this, 'makeUpoaderToken')),
            new \Twig_SimpleFunction('uploader_process', array($this, 'getProcessMode')),
            new \Twig_SimpleFunction('uploader_accept', array($this, 'getUploadFileAccept')),
        );
    }

    public function makeUpoaderToken($targetType, $targetId, $bucket, $ttl = 86400)
    {
        $maker = new UploaderToken();

        return $maker->make($targetType, $targetId, $bucket, $ttl);
    }

    public function getProcessMode($targetType)
    {
        $modes = array(
            'course-task' => 'auto',
            'coursematerial' => 'auto',
            'materiallib' => 'auto',
            'course-activity' => 'auto',
            'headLeader' => 'none',
        );

        if (isset($modes[$targetType])) {
            return $modes[$targetType];
        }

        return 'auto';
    }

    public function getUploadFileAccept($targetType, $only = '')
    {
        $targetAcceptTypes = array(
            'course-task' => array('video', 'audio', 'flash', 'ppt', 'cloud_document'),
            'course-activity' => array('video', 'audio', 'flash', 'ppt', 'document', 'all'),
            'coursematerial' => array('video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'),
            'materiallib' => array('video', 'audio', 'flash', 'ppt', 'document', 'zip', 'image', 'text'),
            'attachment' => array('video', 'audio', 'ppt', 'document', 'zip'),
        );
        $availableAccepts = array(
            'video' => array(
                'extensions' => array('mp4', 'avi', 'flv', 'f4v', 'mpg', 'wmv', 'mov', 'vob', 'rmvb', 'mkv', 'm4v'),
                'mimeTypes' => array('video/mp4', 'video/mpeg', 'video/x-msvideo', 'video/quicktime', 'video/3gpp', 'video/x-m4v', 'video/x-flv', 'video/x-ms-wmv'),
            ),
            'local_video' => array(
                'extensions' => array('mp4'),
                'mimeTypes' => array('video/mp4', 'video/mpeg', 'video/x-msvideo', 'video/quicktime', 'video/3gpp', 'video/x-m4v', 'video/x-flv', 'video/x-ms-wmv'),
            ),
            'audio' => array(
                'extensions' => array('mp3'),
                'mimeTypes' => array('audio/mp4', 'audio/mpeg', 'audio/basic', 'audio/ac3', 'audio/ogg', 'audio/3gpp'),
            ),
            'flash' => array(
                'extensions' => array('swf'),
                'mimeTypes' => array('application/x-shockwave-flash'),
            ),
            'ppt' => array(
                'extensions' => array('ppt', 'pptx'),
                'mimeTypes' => array('application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'),
            ),
            'cloud_document' => array(
                'extensions' => array('doc', 'docx', 'pdf', 'xls', 'xlsx'),
                'mimeTypes' => array('application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ),
            'document' => array(
                'extensions' => array('doc', 'docx', 'pdf', 'xls', 'xlsx', 'wps', 'odt'),
                'mimeTypes' => array('application/vnd.ms-excel', 'application/vnd.ms-outlook', 'application/vnd.ms-pkicertstore', 'application/vnd.ms-pkiseccat', 'application/vnd.ms-pkistl', 'application/vnd.ms-powerpoint', 'application/vnd.ms-project', 'application/vnd.ms-works', 'application/msword', 'application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ),
            'zip' => array(
                'extensions' => array('zip', 'rar', 'gz', 'tar', '7z'),
                'mimeTypes' => array('application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed', 'application/x-tar', 'application/x-gzip', 'application/x-7zip'),
            ),
            'image' => array(
                'extensions' => array('jpg', 'jpeg', 'png', 'gif', 'bmp'),
                'mimeTypes' => array('image/jpg,image/jpeg,image/png,image/gif,image/bmp'),
            ),
            'text' => array(
                'extensions' => array('txt', 'html', 'js', 'css'),
                'mimeTypes' => array('text/*'),
            ),
            'all' => array(
                'extensions' => array('*'),
                'mimeTypes' => array('*'),
            ),
        );

        $types = array();

        $only = explode(',', $only);

        if ($only && !empty($only[0])) {
            $types = $only;
        } elseif (isset($targetAcceptTypes[$targetType])) {
            $types = $targetAcceptTypes[$targetType];
        } else {
            $types = array('all');
        }

        $accept = array('extensions' => array(), 'mimeTypes' => array());

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
