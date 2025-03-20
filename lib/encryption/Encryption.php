<?php

namespace lib\encryption;

class Encryption
{
    private static $secretKey = 'your-secret-key'; // 替换为你自己的秘钥
    private static $cipher = 'AES-256-CBC';

    /**
     * 生成安全的密码哈希
     * @param string $password 明文密码
     * @return string 加密后的哈希
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    /**
     * 验证密码是否匹配哈希
     * @param string $password 明文密码
     * @param string $hash 哈希密码
     * @return bool 是否匹配
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * AES-256-CBC 对称加密
     * @param string $data 需要加密的字符串
     * @return string 加密后的密文（Base64 编码）
     */
    public static function encrypt(string $data): string
    {
        $key = hash('sha256', self::$secretKey, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::$cipher));
        $encrypted = openssl_encrypt($data, self::$cipher, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * AES-256-CBC 对称解密
     * @param string $encryptedData 加密后的 Base64 字符串
     * @return string 解密后的字符串
     */
    public static function decrypt(string $encryptedData): string
    {
        $key = hash('sha256', self::$secretKey, true);
        $decoded = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length(self::$cipher);
        $iv = substr($decoded, 0, $ivLength);
        $encrypted = substr($decoded, $ivLength);
        return openssl_decrypt($encrypted, self::$cipher, $key, 0, $iv);
    }
}
