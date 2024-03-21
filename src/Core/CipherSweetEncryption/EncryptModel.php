<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\CipherSweetEncryption;

use NazmulIslam\Utility\Logger\Logger;
use Illuminate\Database\Capsule\Manager as DB;
use ReflectionClass;

class EncryptModel
{
    protected $modelPath;
    protected $newCipherKey;
    protected $oldCipherKey;

    public function __construct(string $modelPath, $newCipherKey, $oldCipherKey)
    {
        $this->newCipherKey = $newCipherKey;
        $this->oldCipherKey = $oldCipherKey;
        $this->modelPath    = $modelPath;
    }

    public function handle()
    {


        // try {

            $stringModelPath = $this->modelPath;

            $cipherInstance = new Methods();


            if ($cipherInstance->ensureValidInput($stringModelPath)) {

                $cipherInstance->encryptModelValues($stringModelPath, $this->newCipherKey, $this->oldCipherKey);
            }
        // } catch (\Exception $ex) {

        //     Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());
        //     \Resque_Event::trigger('onFailure', [$ex->getMessage(), (array) $ex->getTraceAsString()]);

        //     // Db::connection('app')->rollBack();
        // }
    }
}
