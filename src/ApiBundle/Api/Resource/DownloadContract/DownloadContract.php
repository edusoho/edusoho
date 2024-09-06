<?php

namespace ApiBundle\Api\Resource\DownloadContract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Contract\ContractDisplayTrait;
use ApiBundle\Api\Util\AssetHelper;
use Biz\User\UserException;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadContract extends AbstractResource
{
    use ContractDisplayTrait;

    public function get(ApiRequest $request, $id)
    {
        $signedContract = $this->getContractService()->getSignedContract($id);
        if (!$this->getCurrentUser()->hasPermission('admin_v2_contract_manage') && $signedContract['userId'] != $this->getCurrentUser()->getId()) {
            throw UserException::PERMISSION_DENIED();
        }
        $signSnapshot = $signedContract['snapshot'];
        if (!empty($signSnapshot['sign']['handSignature'])) {
            $signSnapshot['sign']['handSignature'] = AssetHelper::getFurl($signSnapshot['sign']['handSignature']);
        }
        $signSnapshot['contract']['content'] = $this->replaceContentVariable($signSnapshot['contract']['content'], $signedContract['goodsKey'], $signSnapshot['contractCode'], $signSnapshot['sign']);
        $html = $this->getHtmlByRecord($signSnapshot['contract']['content'], $signSnapshot);
        $mpdf = new Mpdf([
            'utf-8' => true,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
        ]);
        $mpdf->WriteHTML($html);
        // 获取生成的 PDF 输出
        $output = $mpdf->Output('', 'S'); // 'S' 表示将 PDF 输出为字符串
        // 创建响应对象
        $response = new Response($output);
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            rawurlencode($this->getFileName($signSnapshot)),
            'utf-8'
        );
        // 设置响应头
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Content-Length', strlen($output));

        return $response;
    }

    protected function getFileName($signSnapshot)
    {
        return $signSnapshot['contractCode'].'_'.$signSnapshot['sign']['truename'].'_'.$signSnapshot['contract']['name'].'.pdf';
    }
}
