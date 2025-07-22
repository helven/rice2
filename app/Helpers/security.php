<?php
if(!function_exists('encrypt_str'))
{
    function encrypt_str($str, $secret='')
    {
        $key        = md5("doyouknowwhoislimpeh".$secret);
        $encrypted  = openssl_encrypt($str, "AES-128-ECB", $key);

        return $encrypted;
    }
}

if(!function_exists('decrypt_str'))
{
    function decrypt_str($str, $secret='')
    {
        $key        = md5("doyouknowwhoislimpeh".$secret);
        $decrypted  = openssl_decrypt($str, "AES-128-ECB", $key);

        return $decrypted;
    }
}