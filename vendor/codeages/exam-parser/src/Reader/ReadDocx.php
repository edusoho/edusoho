<?php

namespace ExamParser\Reader;

use ExamParser\Exception\ExamException;
use ZipArchive;
use DOMDocument;
use Rhumsaa\Uuid\Uuid;

class ReadDocx
{
    /**
     * Word2007+ 文档中正文的XML文件相对路径地址
     */
    const DOCUMENT_XML_PATH = 'word/document.xml';

    /**
     * Word2007+ 文档中正文资源文件的XML文件相对路径地址
     */
    const DOCUMENT_RELS_XML_PATH = 'word/_rels/document.xml.rels';

    /**
     * Word2007+ 文件主目录
     */
    const DOCUMENT_PREFIX = 'word/';

    /**
     * Word中厘米和EMU的换算比例
     */
    const CM_EMU = 360000;

    /**
     * 电脑屏幕72ppi，厘米和像素的换算规则
     */
    const CM_PX = 25;

    protected $resourceTmpPath = '/tmp';

    /**
     * @var string
     *             docx文件地址
     */
    protected $docxPath;

    /**
     * @var DOMDocument
     *                  文档主体xml
     */
    protected $docXml;

    /**
     * @var DOMDocument
     *                  文档资源xml
     */
    protected $relsXml;

    /**
     * @var string
     *             解析后的文档text
     */
    protected $documentText = '';

    public function __construct($docxPath, $options = array())
    {
        $this->docxPath = $docxPath;
        if (isset($options['resourceTmpPath'])) {
            $this->resourceTmpPath = $options['resourceTmpPath'];
        }
    }

    public function read()
    {
        $this->readZip();
        $this->convertImage();

        return $this->getDocumentText();
    }

    public function getDocxPath()
    {
        return $this->docxPath;
    }

    public function getDocumentText()
    {
        return $this->documentText;
    }

    protected function readZip()
    {
        $this->docXml = $this->loadXml(self::DOCUMENT_XML_PATH);
        $this->relsXml = $this->loadXml(self::DOCUMENT_RELS_XML_PATH);
    }

    protected function convertImage()
    {
        $rels = $this->getRels();
        $imagesList = $this->docXml->getElementsByTagName('drawing');

        foreach ($imagesList as $key => $imageXml) {
            $img = $imageXml->getElementsByTagName('blip')->item(0);
            if (empty($img)) {
                continue;
            }
            $imageId = $img->getAttribute('r:embed');
            $imageExtend = $imageXml->getElementsByTagName('extent')->item(0);
            $cx = (int) ($imageExtend->getAttribute('cx') / self::CM_EMU * self::CM_PX);
            $cy = (int) ($imageExtend->getAttribute('cy') / self::CM_EMU * self::CM_PX);
            $htmlCx = "width=\"{$cx}\"";
            $htmlCy = "height=\"{$cy}\"";

            if (isset($rels[$imageId])) {
                $file = $this->getZipResource($rels[$imageId]);
                if ($file) {
                    $ext = pathinfo($rels[$imageId], PATHINFO_EXTENSION);
                    $path = $this->resourceTmpPath.'/'.Uuid::uuid4().'.'.$ext;
                    file_put_contents($path, $file);
                    $imageXml->nodeValue = sprintf('<img src="%s" %s %s>', $path, $htmlCx, $htmlCy);
                }
            }
        }
        $this->docXml->saveXML();
        $paragraphList = $this->docXml->getElementsByTagName('p');
        $text = '';
        foreach ($paragraphList as $paragraph) {
            $text .= $paragraph->textContent.PHP_EOL;
        }

        $this->documentText = $text;
    }

    protected function loadXml($xmlPath)
    {
        $path = $this->getDocxPath();
        $zip = new ZipArchive();
        if (true === $zip->open($path)) {
            if (false !== ($index = $zip->locateName($xmlPath))) {
                $xml = $zip->getFromIndex($index);
            }
            $zip->close();
        } else {
            throw new ExamException('file format is invalid');
        }
        $docXml = new DOMDocument();
        $docXml->encoding = mb_detect_encoding($xml);
        $docXml->preserveWhiteSpace = false; //default true
        $docXml->formatOutput = true; //default true
        $docXml->loadXML($xml);

        return $docXml;
    }

    /**
     * @return array
     *               获取word资源列表
     */
    protected function getRels()
    {
        $relsList = $this->relsXml->getElementsByTagName('Relationship');
        $rels = array();
        foreach ($relsList as $relXml) {
            $rels[$relXml->getAttribute('Id')] = $relXml->getAttribute('Target');
        }

        return $rels;
    }

    /**
     * @param $filename
     *
     * @return false|string|null
     *
     * @throws ExamException
     */
    protected function getZipResource($filename)
    {
        $filename = self::DOCUMENT_PREFIX.$filename;
        $path = $this->docxPath;
        $zip = new ZipArchive();
        $file = null;
        if (true === $zip->open($path)) {
            if (false !== ($index = $zip->locateName($filename))) {
                $file = $zip->getFromIndex($index);
            }
            $zip->close();
        } else {
            throw new ExamException('file format is invalid');
        }

        return $file;
    }
}
