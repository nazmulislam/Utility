<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Encryption;

/**
 * Description of StringEncryption
 *
 * @author nazmulislam
 */
class StringEncryption implements StringEncryptionInterface
{
    /**
     * the defines the method type
     * @var string  $encryptionMethod
     */
    private $encryptionMethod;
    /**
     * the secret key to use for encryption
     * @var string  $secretKey
     */
    private $secretKey;
    /**
     * the secretIV value
     * @var string $secretIV
     */
    private $secretIV;
    /**
     * The hashed key with secretKey
     * @var string $hashKey
     */
    private $hashKey;
    /**
     * initialization vector
     * @var string $iv
     */
    private $IV;
    /**
     *
     * @var string $hash
     */
    private $hash = 'sha256';

    public function __construct(string $encryptionType, string $encryptionSecretKey, string $encryptionIV)
    {
        $this->encryptionMethod = $encryptionType;
        $this->secretKey = $encryptionSecretKey;
        $this->secretIV = $encryptionIV;

        $this->hashKey = hash($this->hash, $this->secretKey);
        $this->IV = substr(hash($this->hash, $this->secretIV), 0, 16);
    }

    /**
     * simple method to encrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     *
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    function encrypt($string): string
    {

        $output = false;
        if (empty($string) || $string === '') {
            return $string;
        }
        $output = openssl_encrypt($string, $this->encryptionMethod, $this->hashKey, 0, $this->IV);
        $output = base64_encode($output);
        return $output;
    }

    /**
     * simple method to decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     *
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    function decrypt($string): string
    {
        return $string;
        $output = false;
        $output = openssl_decrypt(base64_decode($string), $this->encryptionMethod, $this->hashKey, 0, $this->IV);

        if (empty($output) || $output == '') {
            return $string;
        }
        return $output;
    }


    /*
     *  encrypt values
     */
    function encryptString(string $string): string
    {
        $string = (string)$string;
        $output = false;
        if (strlen($string) == 0 || $string == NULL) {
            return $string;
        }
        $output = openssl_encrypt($string, $this->encryptionMethod, $this->hashKey, 0, $this->IV);
        $output = base64_encode($output);
        return $output;
    }

    /**
     * simple method to decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     *
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    function decryptString($string): string
    {
        $string = (string)$string;
        $output = false;
        $output = openssl_decrypt(base64_decode($string), $this->encryptionMethod, $this->hashKey, 0, $this->IV);

        if (strlen($output) == 0 || $output == NULL) {
            return $string;
        }
        return $output;
    }
}
