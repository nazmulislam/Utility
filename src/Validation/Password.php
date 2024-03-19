<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Validation;

/**
 * Description of Password
 *
 * @author nazmulislam
 */
class Password
{
    static function hash(string $passwordText)
    {
        $options = ['memory_cost' => 1 << 11, 'time_cost' => 4, 'threads' => 2];
        return  \password_hash($passwordText, PASSWORD_ARGON2I, $options);
    }

    static function generatePassword(int $length = 9)
    {
        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 5)), 0, $length);
    }


    /**
     *
     * @param string $inputPassword
     * @param string $storedPassword
     * @return bool
     */
    static function validatePassword(string $inputPassword, string $storedPassword): bool
    {
        return \password_verify(trim($inputPassword), $storedPassword);
    }
}
