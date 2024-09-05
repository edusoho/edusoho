<?php

namespace ApiBundle\Api\Resource\DownloadContract;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Contract\ContractDisplayTrait;
use ApiBundle\Api\Util\AssetHelper;
use Biz\User\UserException;
use Dompdf\Dompdf;
use Dompdf\Options;
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
//        $html = '
//	<body style="font-family:simsun">中文字体</body>
//';
        $options = new Options();
        $options->setDefaultFont('simsun');
        // 创建一个 DomPDF 实例
        $pdf = new DomPDF($options);
        $pdf->loadHtml($html, 'UTF-8');
        $pdf->render();
        // 获取生成的 PDF 输出
        $output = $pdf->output();
        // 创建响应对象
        $response = new response($output);
        // 设置 PDF 文件名
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'abc.pdf'
        );
        // 设置响应头
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }
}
