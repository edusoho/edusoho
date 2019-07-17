<?php
namespace PhpOffice\Common\Adapter\Zip;

use PclZip;

class PclZipAdapter implements ZipInterface
{
    /**
     * @var PclZip
     */
    protected $oPclZip;

    /**
     * @var string
     */
    protected $tmpDir;

    public function open($filename)
    {
        $this->oPclZip = new PclZip($filename);
        $this->tmpDir = sys_get_temp_dir();
        return $this;
    }

    public function close()
    {
        return $this;
    }

    public function addFromString($localname, $contents)
    {
        $pathData = pathinfo($localname);

        $hFile = fopen($this->tmpDir.'/'.$pathData['basename'], "wb");
        fwrite($hFile, $contents);
        fclose($hFile);

        $res = $this->oPclZip->add($this->tmpDir.'/'.$pathData['basename'], PCLZIP_OPT_REMOVE_PATH, $this->tmpDir, PCLZIP_OPT_ADD_PATH, $pathData['dirname']);
        if ($res == 0) {
            throw new \Exception("Error zipping files : " . $this->oPclZip->errorInfo(true));
        }
        unlink($this->tmpDir.'/'.$pathData['basename']);
        return $this;
    }
}
