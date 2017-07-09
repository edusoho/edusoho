<?php

namespace AppBundle\Controller\Export;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;
use AppBundle\Common\ExportHelp;

class ExportController extends BaseController
{
    public function exportAction(Request $request, $name)
    {
        $name = sprintf($name.'-(%s).csv', date('Y-n-d'));

        return ExportHelp::exportCsv($request, $name);
    }

    public function preExportAction(Request $request, $name)
    {
        //todo ,导出预备
        $export = $this->getExport($request, $name);
        return $this->createJsonResponse($export->getPreResult());
    }

    private function getExport($request, $name)
    {
        $map = array(
            'invite-records' => 'Biz\Export\inviteRecordsExport',
        );
        try {
            return new $map[$name]($this->getBiz(), $request);
        } catch (\Exception $e) {
            var_dump(123);
            var_dump($e->getMessage());
        }
    }

    private function createExportAction($type)
    {
        //todo 不同的导出有不同的类，实现 exportAbstract 类
    }

    private function getExportContent()
    {
        // todo 调用实现类的方法，返回表格正文
    }
}
