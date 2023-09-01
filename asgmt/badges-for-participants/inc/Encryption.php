<?php

class Encryption
{

    const OPEN_SSL_METHOD = 'aes-256-cbc';
    const BASE64_ENCRYPTION_KEY = 'G1fM0aXhguJ5tVaqVMJOVHB+Jk6QFd99FgkfAcEgwjI';//base64_encode(openssl_random_pseudo_bytes(32));
    const BASE64_IV = 'xIkaHuquZFjtP4SI4mIyOg';//base64_encode(openssl_random_pseudo_bytes(openssl_cipher_iv_length(AES_256_CBC)));

    static private function base64_url_encode($input)
    {
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    static private function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-_,', '+/='));
    }


    static function encrypt_to_url_param($message)
    {
        $encrypted = openssl_encrypt($message, self::OPEN_SSL_METHOD, base64_decode(self::BASE64_ENCRYPTION_KEY), 0, base64_decode(self::BASE64_IV));
        $base64_encrypted = self::base64_url_encode($encrypted);
        return $base64_encrypted;
    }

    static function decrypt_from_url_param($base64_encrypted)
    {
        $encrypted = self::base64_url_decode($base64_encrypted);
        $decrypted = openssl_decrypt($encrypted, self::OPEN_SSL_METHOD, base64_decode(self::BASE64_ENCRYPTION_KEY), 0, base64_decode(self::BASE64_IV));
        return $decrypted;
    }
}