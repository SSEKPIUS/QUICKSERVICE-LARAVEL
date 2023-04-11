<?php

namespace App\Utilities;

use PhpParser\Node\Stmt\TryCatch;

class encryptDecrypt
{
    protected $out;
    public function __construct()
    {
        $this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
    }

    function encryptor($action, $string)
    {
        $output = null;
        try {
            // get the local secret key
            $secret_key = env('JWT_SECRET');
            $encrypt_method = "AES-256-CBC";
            $secret_iv  = 'SecretIV@123GKrQp';
            // hash
            $key = hash('sha256', $secret_key);
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', $secret_iv), 0, 16);
            //do the encryption given text/string/number
            if ($action == 'encrypt') {
                $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
                $output = base64_encode($output);
            } elseif ($action == 'decrypt') {
                //decrypt the given text/string/number
                $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
            }
        } catch (\Throwable $th) {
            $this->out->writeln($th);
        }
        return $output;
    }   
}