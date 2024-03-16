<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Encryption;
/**
 *
 * @author nazmulislam
 */
interface StringEncryptionInterface
{
    /**
     *
     */
   public function encrypt(string $string);

   /**
    * 
    */
   public function decrypt(string $string);

}
