<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EncryptionManagerController extends AbstractController
{
    public function __construct()
    {

    }

    /**
     * decrypt encrypted string :)
     * @param $string
     * @return false|string
     */
    public function decrypt($string, $key)
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
     * @return false|string
     */
    public function encrypt($string, $key)
    {
        $ciphering = "AES-128-CTR";
        $options = 0;
        $encryption_iv = '1234567891011121';
        $encryption_key = "GudaIsStrong" . $key;
        return openssl_encrypt($string, $ciphering,
            $encryption_key, $options, $encryption_iv);

    }
}
