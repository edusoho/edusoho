<?php

namespace ExamParser\Reader;

use ZipArchive;
use DOMDocument;
use Rhumsaa\Uuid\Uuid;

class ReadDocx
{
    const DOCUMENT_XML_PATH = 'word/document.xml';

    const DOCUMENT_RELS_XML_PATH = 'word/_rels/document.xml.rels';

    const DOCUMENT_PREFIX = 'word/';

    const CM_EMU = 360000;

    const CM_PX = 25; //电脑屏幕72ppi，厘米和像素的换算规则

    protected $resourceTmpPath = '/tmp';

    /**
     * @var string
     */
    protected $docxPath;

    /**
     * @var DOMDocument
     */
    protected $docXml;

    /**
     * @var DOMDocument
     */
    protected $relsXml;

    public function __construct($docxPath, $options = array())
    {
        $this->docxPath = $docxPath;
        if (isset($options['resourceTmpPath'])) {
            $this->resourceTmpPath = $options['resourceTmpPath'];
        }
        $this->readZip();
    }

    public function getDocxPath()
    {
        return $this->docxPath;
    }

    protected function readZip()
    {
        $path = $this->docxPath;
        $zip = new ZipArchive();
        if (true === $zip->open($path)) {
            if (false !== ($index = $zip->locateName(self::DOCUMENT_XML_PATH))) {
                $xml = $zip->getFromIndex($index);
            }
            $zip->close();
        } else {
            die('non zip file');
        }

        if (true === $zip->open($path)) {
            if (false !== ($index = $zip->locateName(self::DOCUMENT_RELS_XML_PATH))) {
                $xmlRels = $zip->getFromIndex($index);
            }
            $zip->close();
        } else {
            die('non zip file');
        }

        $docXml = new DOMDocument();
        $docXml->encoding = mb_detect_encoding($xml);
        $docXml->preserveWhiteSpace = false; //default true
        $docXml->formatOutput = true; //default true
        $docXml->loadXML($xml);

        $this->docXml = $docXml;

        $relsXml = new DOMDocument();
        $relsXml->encoding = mb_detect_encoding($xmlRels);
        $relsXml->preserveWhiteSpace = false;
        $relsXml->formatOutput = true;
        $relsXml->loadXML($xmlRels);

        $this->relsXml = $relsXml;
    }

    public function convertImage()
    {
        $relsList = $this->relsXml->getElementsByTagName('Relationship');
        $rels = array();
        foreach ($relsList as $relXml) {
            $rels[$relXml->getAttribute('Id')] = $relXml->getAttribute('Target');
        }

        $imagesList = $this->docXml->getElementsByTagName('drawing');

        foreach ($imagesList as $key => $imageXml) {
            $imageId = $imageXml->getElementsByTagName('blip')->item(0)->getAttribute('r:embed');
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
                    $imageXml->textContent = sprintf('<img src="%s" %s %s>', $path, $htmlCx, $htmlCy);
                    // $imageXml->textContent = '1234';
                }
            }
        }
        $this->docXml->saveXML();
        $paragraphList = $this->docXml->getElementsByTagName('p');
        $text = '';
        foreach ($paragraphList as $paragraph) {
//            $text .= '<p>'.$paragraph->textContent.'</p>'.PHP_EOL;
            $text .= $paragraph->textContent.PHP_EOL;
        }

        return $text;
    }

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
            die('non zip file');
        }

        return $file;
    }
}
