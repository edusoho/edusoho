<?php

namespace AppBundle\Common;

class CloudFileStatusToolkit
{
    const TRANSCODE_DETAULT_ERROR_MESSAGE_KEY = 'cloud_file.transcoding_tips.default_error_message';

    const PROCESS_STATUS_DEFAULT_CONVERT_STATUS = 'unknow';

    public static $errorCodeMap = array(
        '41001' => 'TRANSCODE_CLIENT_ERROR_NOT_SUPPORT_VIDEO_FORMAT',
        '41002' => 'TRANSCODE_CLIENT_ERROR_FILE_DELETED',
        '41003' => 'TRANSCODE_CLIENT_ERROR_NOT_SUPPORT_DOCUMENT_FORMAT',
        '41004' => 'TRANSCODE_CLIENT_ERROR_FILE_ENCRYPTED',
        '41005' => 'TRANSCODE_CLIENT_ERROR_FILE_TOO_LARGE',
        '41006' => 'TRANSCODE_CLIENT_ERROR_FILE_CANNOT_OPEN',
        '51001' => 'TRANSCODE_SERVER_ERROR_NETWORK_ERROR',
        '51002' => 'TRANSCODE_SERVER_ERROR_FILE_DOWNLOAD_ERROR',
        '51003' => 'TRANSCODE_SERVER_ERROR_DISK_FULL',
        '51004' => 'TRANSCODE_SERVER_ERROR_DOCUMENT_BYTE_LENGTH_TOO_LARGE',
        '51005' => 'TRANSCODE_SERVER_ERROR_CALL_LIB_FUNCTION_ERROR',
        '51006' => 'TRANSCODE_SERVER_ERROR_TRANSCODE_TIMEOUT',
        '51007' => 'TRANSCODE_SERVER_ERROR_UPLOAD_FILE_ERROR',
        '51008' => 'TRANSCODE_SERVER_ERROR_UNKNOW_ERROR',
        '51009' => 'TRANSCODE_SERVER_ERROR_NO_ENCODER',
    );

    public static $processStatusMap = array(
        'waiting' => 'waiting',   //等待转码
        'processing' => 'doing',  //正在转码
        'ok' => 'success',        //转码成功
        'error' => 'error',       //转码失败.  errorType:client 文件不支持转码; errorType:server 转码失败
        'none' => 'noneed',       //无需转码
        'unknow' => 'unknow',     //未知状态
    );

    public static $filterStatusMap = array(
        'waiting' => array('processStatus' => 'waiting'),
        'processing' => array('processStatus' => 'processing'),
        'ok' => array('processStatus' => 'ok'),
        'noneed' => array('processStatus' => 'none'),
        'error' => array('processStatus' => 'error', 'errorType' => 'server'),
        'nonsupport' => array('processStatus' => 'error', 'errorType' => 'client'),
    );

    public static function convertProcessStatus($processStatus)
    {
        if (isset(self::$processStatusMap[$processStatus])) {
            return self::$processStatusMap[$processStatus];
        }

        return self::PROCESS_STATUS_DEFAULT_CONVERT_STATUS;
    }

    public static function getTranscodeErrorMessageKeyByCode($code)
    {
        if (isset(self::$errorCodeMap[$code])) {
            return 'cloud_file.transcoding_tips.error_code_'.$code;
        }

        return self::TRANSCODE_DETAULT_ERROR_MESSAGE_KEY;
    }

    public static function getTranscodeFilterStatusCondition($filterStatus)
    {
        if (isset(self::$filterStatusMap[$filterStatus])) {
            return self::$filterStatusMap[$filterStatus];
        }

        return array();
    }
}
