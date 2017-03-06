<?php

namespace AppBundle\Common;

use xxtea;

class EncryptionToolkit
{
    public static function XXTEAEncrypt($data, $key)
    {
        $xxtea = new xxtea();

        return $xxtea::encrypt($data, $key);
    }

    public static function XXTEADecrypt($data, $key)
    {
        $xxtea = new xxtea();

        return $xxtea::decrypt($data, $key);
    }
}
