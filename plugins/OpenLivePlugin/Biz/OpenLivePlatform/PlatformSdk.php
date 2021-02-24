<?php
namespace OpenLivePlugin\Biz\OpenLivePlatform;

use Codeages\Biz\Framework\Service\BaseService;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
use OpenLivePlugin\Common\ArrayToolkit;
use OpenLivePlugin\Common\Qiniu\Http\Client;
use OpenLivePlugin\PluginSystem;
use ESCloud\SDK\Auth;

class PlatformSdk extends BaseService
{
    const LIVE_TIME = 600;

    protected $host = PluginSystem::OPEN_LIVE_SERVER;

    protected $defaultRequestConfig = [
        'connectTimeout' => 3,
        'timeout' => 5,
    ];

    private $prefixApiUri = '/school';

    protected $uri;

    public function getLive($id)
    {
        $this->uri = $this->prefixApiUri . '/room/' . $id . '/detail';
        $data = [];

        try {
            $this->getLogger()->info('try getLive ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播间详情失败！']];
        }
    }

    public function createLive($liveData)
    {
        $this->uri = $this->prefixApiUri . '/room/create';
        $data = ArrayToolkit::parts($liveData, [
            'title',
            'room_cover',
            'start_time',
            'end_time',
            'speaker',
            'speaker_avatar',
            'speaker_name',
            'speaker_mobile',
            'enroll_sms',
            'enroll_wechat',
            'enroll_notice_time',
            'summary',
        ]);

        try {
            $this->getLogger()->info('try createLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("createLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '创建直播失败！']];
        }
    }

    public function editLive($liveData)
    {
        $this->uri = $this->prefixApiUri . '/room/edit';
        $data = ArrayToolkit::parts($liveData, [
            'room_id',
            'title',
            'room_cover',
            'start_time',
            'end_time',
            'speaker',
            'speaker_avatar',
            'speaker_name',
            'speaker_mobile',
            'enroll_sms',
            'enroll_wechat',
            'enroll_notice_time',
            'summary',
        ]);

        try {
            $this->getLogger()->info('try editLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("editLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '修改直播失败！']];
        }
    }

    public function searchLives($conditions)
    {
        $this->uri = $this->prefixApiUri . '/room/search';
        $data = $this->filterConditions($conditions);

        try {
            $this->getLogger()->info('try searchLives ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("searchLives error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播数据失败！']];
        }
    }

    public function publishLive($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/publish';
        $data = ['room_id' => $roomId];

        try {
            $this->getLogger()->info('try publishLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("publishLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '发布失败！']];
        }
    }

    public function unpublishLive($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/unpublish';
        $data = ['room_id' => $roomId];

        try {
            $this->getLogger()->info('try unpublishLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("unpublishLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '取消发布失败！']];
        }
    }

    public function closeLive($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/close';
        $data = ['room_id' => $roomId];

        try {
            $this->getLogger()->info('try closeLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("closeLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '结束直播失败！']];
        }
    }

    public function deleteLive($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/delete';
        $data = ['room_id' => $roomId];

        try {
            $this->getLogger()->info('try deleteLive ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("deleteLive error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '删除直播失败！']];
        }
    }

    public function getTeacherEntryUrl($roomId, $userInfo)
    {
        $this->uri = $this->prefixApiUri . '/room/teacher/entry_url';
        $userInfo['room_id'] = $roomId;
        $data = ArrayToolkit::parts($userInfo, [
            'room_id',
            'id',
            'nickname',
            'role'
        ]);

        try {
            $this->getLogger()->info('try getTeacherEntryUrl ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getTeacherEntryUrl error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取老师直播地址失败！']];
        }
    }

    public function getLiveShareUrl($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/share_url';
        $data = ['room_id' => $roomId];

        try {
            $this->getLogger()->info('try getLiveShareUrl ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getLiveShareUrl error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播分享地址失败！']];
        }
    }

    public function getCashAccount()
    {
        $this->uri = $this->prefixApiUri . '/cash/account';
        $data = [];
        try {
            $this->getLogger()->info('try getCashAccount ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getCashAccount error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取学校账户失败！']];
        }
    }

    public function searchLiveOnlineNumRecords($roomId, $conditions = [])
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/total_num';
        $data = ArrayToolkit::parts($conditions, ['save_time_GT', 'save_time_LT']);

        try {
            $this->getLogger()->info('try getLiveOnlineNumRecords ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getLiveOnlineNumRecords error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播在线人数记录数据失败！']];
        }
    }

    public function getLiveVisitorReport($roomId, $conditions = [])
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/visitor_student_report';
        $data = ArrayToolkit::parts($conditions, ['student_nickname_like', 'mobile', 'offset', 'limit']);

        try {
            $this->getLogger()->info('try getLiveVisitorReport ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getLiveVisitorReport error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播观看人员统计数据失败！']];
        }
    }

    public function getMemberAnalysisData($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/member_analysis_data';
        $data = [];

        try {
            $this->getLogger()->info('try getMemberAnalysisData ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getMemberAnalysisData error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取直播人员分析数据失败！']];
        }
    }

    public function initUploadFile($fileInfo)
    {
        $this->uri = $this->prefixApiUri . '/room/img/init_upload';
        $data = ArrayToolkit::parts($fileInfo, ['name','reskey','extno']);

        try {
            $this->getLogger()->info('try initUploadFile ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("initUploadFile error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '初始化上传失败！']];
        }
    }

    public function uploadFile($params)
    {
        if (!ArrayToolkit::requireds($params, ['uploadUrl', 'token', 'key', 'file', 'fileName', 'no'], true)) {
            return [];
        }
        $fields = [
            'token' => $params['token'],
            'key' => $params['key'],
        ];

        try {
            $this->getLogger()->info('try uploadFile ');
            $response = Client::multipartPost($params['uploadUrl'], $fields, 'file', $params['fileName'], $params['file'], 'application/octet-stream');
            if (!$response->ok()) {
                throw new \Exception($response->error);
            }
            $this->uploadFileFinished($params['no']);
            return $response->json();
        } catch (\Throwable $e) {
            $this->getLogger()->error("uploadFile error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $params]);
            return ['error' => ['code' => 50020201029, 'message' => '上传文件失败！']];
        }
    }

    public function searchEnrolledRoomStudent($roomId, $conditions = [])
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/enrolled_students/search';
        $data = ArrayToolkit::parts($conditions, ['student_nickname_like', 'mobile', 'sorts', 'offset', 'limit']);

        try {
            $this->getLogger()->info('try searchEnrolledRoomStudent ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("searchEnrolledRoomStudent error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取已报名学生数据失败！']];
        }
    }

    public function searchSmsReachedRoomStudent($roomId, $conditions = [])
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/sms_reached_students/search';
        $data = ArrayToolkit::parts($conditions, ['mobile', 'offset', 'limit']);

        try {
            $this->getLogger()->info('try searchSmsReachedRoomStudent ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("searchSmsReachedRoomStudent error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取短信触达学生数据失败！']];
        }
    }

    public function saveLiveWeChatShare($roomId, $liveShareData)
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/wechat_share/save';
        $data = ArrayToolkit::parts($liveShareData, [
            'share_title',
            'share_content',
            'share_image',
        ]);

        try {
            $this->getLogger()->info('try saveLiveWeChatShare ', ['DATA' => $data]);
            return $this->request('POST', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("saveLiveWeChatShare error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '保存微信分享设置失败！']];
        }
    }

    public function getWeChatShareByLiveId($roomId)
    {
        $this->uri = $this->prefixApiUri . '/room/' . $roomId . '/wechat_share';
        $data = [];

        try {
            $this->getLogger()->info('try getWeChatShareByLiveId ', ['DATA' => $data]);
            return $this->request('GET', $data, $this->defaultRequestConfig);
        } catch (\Throwable $e) {
            $this->getLogger()->error("getWeChatShareByLiveId error:{$e->getMessage()} traceString:{$e->getTraceAsString()}", ['DATA' => $data]);
            return ['error' => ['code' => 50020201029, 'message' => '获取微信分享设置失败！']];
        }
    }

    public function handelSdkResult($result, $defaultOutput = [], $neeSuccessStatus = false)
    {
        if (empty($result['error'])) {
            if ($neeSuccessStatus) {
                $result['success'] = true;
            }

            return $result;
        }
        $errorData = $result['error'];
        if (empty($errorData['code']) || 6 == $errorData['code']) {
            $errorMsg = '系统出错，请联系管理员';
        } else {
            $errorMsg = $this->transErrorCodeToMsg($errorData['code']);
        }
        if (empty($errorMsg)) {
            $errorMsg = (isset($errorData['message']) && is_string($errorData['message'])) ? $errorData['message'] : '连接超时，数据获取失败';
        }
        $this->getLogger()->error('open-live-service sdk error', $result);

        if (!$neeSuccessStatus) {
            throw new ServiceException($errorMsg, 1);
        }

        return array_merge($defaultOutput, ['success' => false, 'errorMsg' => $errorMsg]);
    }

    private function uploadFileFinished($no)
    {
        $this->uri = $this->prefixApiUri . '/room/img/finish_upload/'.$no;
        $data = [];

        $this->getLogger()->info('try uploadFileFinished ', ['DATA' => $data]);
        $result = $this->request('POST', $data, $this->defaultRequestConfig);
        if (empty($result['success']) || true != $result['success']) {
            $this->getLogger()->info('uploadFileFinished failed', $result);
            throw new \Exception('uploadFileFinished failed');
        }
        $this->getLogger()->info('uploadFileFinished succeed');
    }

    private function filterConditions($conditions)
    {
        return ArrayToolkit::parts($conditions, [
            'title',
            'speakers',
            'speaker',
            'start_time_GT',
            'end_time_LT',
            'start_time_LT',
            'end_time_GT',
            'actual_start_time_GT',
            'actual_start_time_LT',
            'actual_end_time_LT',
            'actual_end_time_GT',
            'status',
            'charge_status',
            'is_published',
            'offset',
            'limit',
            'sorts'
        ]);
    }

    protected function request($method, $params = [], $conditions = [])
    {
        $conditions['userAgent'] = isset($conditions['userAgent']) ? $conditions['userAgent'] : '';
        $conditions['connectTimeout'] = isset($conditions['connectTimeout']) ? $conditions['connectTimeout'] : 10;
        $conditions['timeout'] = isset($conditions['timeout']) ? $conditions['timeout'] : 10;

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, $conditions['userAgent']);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $conditions['connectTimeout']);
        curl_setopt($curl, CURLOPT_TIMEOUT, $conditions['timeout']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);

        $body = '';
        if ('POST' == $method) {
            curl_setopt($curl, CURLOPT_POST, 1);
            if (!empty($params)) {
                if (version_compare(phpversion(), '5.4.0', '>=')) {
                    $body = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } else {
                    $body = json_encode($params);
                }
            }
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        } elseif ('PUT' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('DELETE' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } elseif ('PATCH' == $method) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($params));
        } else {
            if (!empty($params)) {
                $this->uri = $this->uri.(strpos($this->uri, '?') ? '&' : '?').http_build_query($params);
            }
        }
        $url = $this->host . $this->uri;
        $conditions = $this->makeAuthorization($conditions, $body);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        if (!empty($conditions['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $conditions['headers']);
        }
        $response = curl_exec($curl);
        $curlinfo = curl_getinfo($curl);

        $body = substr($response, $curlinfo['header_size']);

        curl_close($curl);

        if (empty($curlinfo['namelookup_time'])) {
            return [];
        }

        if (isset($conditions['contentType']) && 'plain' === $conditions['contentType']) {
            return $body;
        }

        $body = json_decode($body, true);
        $this->getLogger()->info('access success');

        return $body;
    }

    private function makeAuthorization($conditions, $body)
    {
        $storageSetting = $this->getSettingService()->get('storage', []);

        if (empty($storageSetting['cloud_access_key']) || empty($storageSetting['cloud_secret_key'])) {
            $this->getLogger()->error('makeAuthorization error cloud_access_key or cloud_secret_key not exist', array('DATA' => $storageSetting));
            return $conditions;
        }

        $auth = new Auth($storageSetting['cloud_access_key'], $storageSetting['cloud_secret_key']);

        $conditions['headers'][] = 'Authorization:' . $auth->makeRequestAuthorization($this->uri, $body, self::LIVE_TIME, true, null);
        $conditions['headers'][] = 'Content-Type:application/json';

        return $conditions;
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('open_live.plugin.logger');
    }

    protected function transErrorCodeToMsg($code)
    {
        $errorCodeTrans = new ErrorCodeTrans();

        return $errorCodeTrans->transCodeToMsg($code);
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}