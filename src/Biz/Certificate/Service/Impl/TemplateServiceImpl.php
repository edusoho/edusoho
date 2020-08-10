<?php

namespace Biz\Certificate\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Certificate\Dao\TemplateDao;
use Biz\Certificate\Service\TemplateService;
use Biz\Certificate\TemplateException;
use Biz\Common\CommonException;
use Biz\Content\Service\FileService;
use Biz\File\UploadFileException;
use Biz\System\Service\LogService;

class TemplateServiceImpl extends BaseService implements TemplateService
{
    public function get($id)
    {
        return $this->getTemplateDao()->get($id);
    }

    public function create($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['name', 'targetType'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $fields = $this->filterTemplateFields($fields);
        $fields['createdUserId'] = $this->getCurrentUser()->getId();

        $template = $this->getTemplateDao()->create($fields);

        $this->getLogService()->info(
            'certificate_template',
            'create',
            "证书模板创建, 模板 #{$template['id']} 名称 : 《{$template['name']}》"
        );

        return $template;
    }

    public function update($id, $fields)
    {
        $template = $this->get($id);
        if (empty($template) || 1 == $template['dropped']) {
            $this->createNewException(TemplateException::NOTFOUND_TEMPLATE());
        }

        $fields = $this->filterTemplateFields($fields);

        return $this->getTemplateDao()->update($id, $fields);
    }

    public function updateBaseMap($id, $fileUri)
    {
        $template = $this->get($id);
        if (empty($template) || 1 == $template['dropped']) {
            $this->createNewException(TemplateException::NOTFOUND_TEMPLATE());
        }

        return $this->getTemplateDao()->update($id, ['basemap' => $fileUri]);
    }

    public function updateStamp($id, $fileUri)
    {
        $template = $this->get($id);
        if (empty($template) || 1 == $template['dropped']) {
            $this->createNewException(TemplateException::NOTFOUND_TEMPLATE());
        }

        return $this->getTemplateDao()->update($id, ['stamp' => $fileUri]);
    }

    public function count($conditions)
    {
        return $this->getTemplateDao()->count($conditions);
    }

    public function search($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getTemplateDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function dropTemplate($id)
    {
        $template = $this->get($id);
        if (empty($template) || 1 == $template['dropped']) {
            $this->createNewException(TemplateException::NOTFOUND_TEMPLATE());
        }

        return $this->getTemplateDao()->update($id, ['dropped' => 1]);
    }

    protected function filterTemplateFields($fields)
    {
        return ArrayToolkit::parts(
            $fields,
            [
                'name',
                'targetType',
                'styleType',
                'certificateName',
                'recipientContent',
                'certificateContent',
                'qrCodeSet',
                'createdUserId',
            ]
        );
    }

    /**
     * @return TemplateDao
     */
    protected function getTemplateDao()
    {
        return $this->createDao('Certificate:TemplateDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return FileService
     */
    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
