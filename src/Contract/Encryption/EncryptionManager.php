<?php

namespace App\Contract\Encryption;

class EncryptionManager
{

    /**
     * decrypt encrypted string :)
     * @param $string
     * @param $key
     * @return false|string
     */
    public static function decrypt($string, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = "GudaIsStrong" . $key;
        return openssl_decrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);
    }

    /**
     * encrypt string
     * @param $string
     * @param $key
     * @return false|string
     */
    public static function encrypt($string, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = "GudaIsStrong" . $key;
        return openssl_encrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);

    }
}
