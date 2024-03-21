<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Core\CipherSweetEncryption;

use NazmulIslam\Utility\Logger\Logger;
use Illuminate\Database\Capsule\Manager as DB;
use ReflectionClass;

class WriteModelEncryptionLogic
{
    protected $modelPath;
    protected $fields;

    public function __construct(string $modelPath, array $fields)
    {
        $this->modelPath  = $modelPath;
        $this->fields     = $fields;
    }

    public function handle()
    {


        DB::connection('app')->beginTransaction();

        try {

            // THIS FUNCTION WRITES TO THE FILE
            $this->checkAndUpdateModel($this->modelPath, $this->fields);
            DB::connection('app')->commit();

            return true;
        } catch (\Exception $ex) {

            Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());
            \Resque_Event::trigger('onFailure', [$ex->getMessage(), (array) $ex->getTraceAsString()]);

            Db::connection('app')->rollBack();
        }
    }

    private function checkAndUpdateModel($modelPath, $fields)
    {
        $fileUpdated = false;
        // Use reflection to get the content of the model class file
        $reflectionClass = new ReflectionClass($modelPath);
        $modelFile = $reflectionClass->getFileName();
        $modelName = $reflectionClass->getShortName();

        if ($modelFile) {

            $methods = $reflectionClass->getMethods();

            $fileContent = file_get_contents($modelFile);

            if ($this->checkBootMethod($methods)) {

                // REMOVE CONFIGURE METHOD IF IT EXISTS
                $fileContent = preg_replace('/public static function configureCipherSweet\(EncryptedRow \$encryptedRow\): void\s*\{.*?\}/s', '', $fileContent);

                // Remove Use Trait if it exists
                $stringToRemove = 'use UsesCipherSweetTrait;';

                // Remove the string if it's present in any line
                $fileContent = str_replace($stringToRemove, '', $fileContent);

                // if ($reflectionClass->hasMethod('configureCipherSweet')) {

                //     $method = $reflectionClass->getMethod('configureCipherSweet');
                //     $startLine = $method->getStartLine() - 1; // Adjust for zero-based index
                //     $endLine = $method->getEndLine();

                //     // Read the entire file into an array of lines
                //     $fileLines = file($reflectionClass->getFileName());

                //     // Remove the lines corresponding to the method
                //     array_splice($fileLines, $startLine, $endLine - $startLine + 1);

                //     $fileContent = implode('', $fileLines);
                // }

                $cipherSweetFunction = "";

                $this->checkOrAddUseStatements($fileContent);
                $this->checkOrAddCipherSweetInstance($fileContent);
                $this->checkOrAddUsesCipherSweetTrait($fileContent, $modelName);
                $this->generateConfigureCipherSweetFunction($fields, $cipherSweetFunction);
                $this->addConfigurationFunction($cipherSweetFunction, $fileContent);
                $this->checkOrReplaceBootMethod($fileContent);
                $this->removeUseTraitDuplications($fileContent);

                if (false === file_put_contents($modelFile, $fileContent)) {
                    $fileUpdated = false;
                } else {
                    $fileUpdated = true;
                }
            }

            Logger::debug('CHECK FUNCTION END', [true]);
        }
        return $fileUpdated;
    }

    private function checkBootMethod($methods): bool
    {
        // Check if the class has a "boot" method
        $hasBootMethod = false;
        foreach ($methods as $method) {
            if ($method->name === 'boot') {
                $hasBootMethod = true;
                break;
            }
        }

        return $hasBootMethod;
    }

    private function checkOrAddCipherSweetInstance(&$fileContent)
    {
        // Check if the class declaration exists
        if (preg_match('/class\s+(\w+)\s+extends\s+Model/i', $fileContent, $matches)) {
            $className = $matches[1];

            // If the class does not implement CipherSweetEncrypted, attach it right after extends Model

            if (strpos($fileContent, 'implements CipherSweetEncrypted') === false) {
                // Add the "implements CipherSweetEncrypted" clause right after extends Model
                $fileContent = preg_replace(
                    '/class\s+' . $className . '\s+extends\s+Model/i',
                    "class $className extends Model implements CipherSweetEncrypted",
                    $fileContent
                );
            }
        }
    }

    private function checkOrAddUsesCipherSweetTrait(&$fileContent, $modelName)
    {

        $traitToAdd = "use UsesCipherSweetTrait;";

        $pattern = "/class\s+$modelName\s+extends\s+Model\s+implements\s+CipherSweetEncrypted\s*\{/";

        if (preg_match($pattern, $fileContent, $matches)) {
            $classStartIndex = strpos($fileContent, $matches[0]);
            $classEndIndex = $classStartIndex + strlen($matches[0]);

            $modifiedContent = substr($fileContent, 0, $classEndIndex + 1) . "\n    $traitToAdd\n" . substr($fileContent, $classEndIndex + 1);
            // Update the $fileContent with the modified content
            $fileContent = $modifiedContent;
        }
    }





    private function checkOrAddUseStatements(&$fileContent)
    {
        // Check if the necessary use statements exist
        $useStatements = [
            'use NazmulIslam\Utility\Core\Traits\UsesCipherSweetTrait;',
            'use Spatie\LaravelCipherSweet\Contracts\CipherSweetEncrypted;',
            'use ParagonIE\CipherSweet\BlindIndex;',
            'use ParagonIE\CipherSweet\EncryptedRow;',
        ];

        foreach ($useStatements as $useStatement) {
            if (strpos($fileContent, $useStatement) === false) {
                // Add the use statement right after the namespace declaration
                $fileContent = preg_replace(
                    '/^namespace .*;/m',
                    "$0\n\n$useStatement",
                    $fileContent
                );
            }
        }
    }

    private function generateConfigureCipherSweetFunction($fields, &$configureCipherSweetMethod)
    {
        // Generate the configureCipherSweet method dynamically
        $configureCipherSweetMethod = "public static function configureCipherSweet(EncryptedRow \$encryptedRow): void\n";
        $configureCipherSweetMethod .= "{\n";
        $configureCipherSweetMethod .= "    \$encryptedRow\n";
        $totalItems = count($fields);
        $currentIndex = 0;
        foreach ($fields as $fieldName) {
            $currentIndex++;
            if ($currentIndex === $totalItems) {
                $configureCipherSweetMethod .= "        ->addField('{$fieldName}')\n";
                $configureCipherSweetMethod .= "        ->addBlindIndex('{$fieldName}', new BlindIndex('{$fieldName}_index'));\n";
            } else {
                $configureCipherSweetMethod .= "        ->addField('{$fieldName}')\n";
                $configureCipherSweetMethod .= "        ->addBlindIndex('{$fieldName}', new BlindIndex('{$fieldName}_index'))\n";
            }
        }
        $configureCipherSweetMethod .= "}\n";
    }

    private function addConfigurationFunction($func, &$fileContent)
    {
        $pattern = '/public\s+static\s+function\s+boot\(\)\s*\{/';

        if (preg_match($pattern, $fileContent, $matches)) {
            $bootFunctionIndex = strpos($fileContent, $matches[0]);
            $modifiedContent = substr_replace($fileContent, $func . "\n\n", $bootFunctionIndex, 0);
            // Update the $fileContent with the modified content
            $fileContent = $modifiedContent;
        }
    }

    private function checkOrReplaceBootMethod(&$fileContent)
    {
        // Define a regular expression pattern to match the boot method
        $pattern = '/public static function boot\(\).*?{\s*(.*?)\s*}/s';

        // Check if the boot method exists in the file
        if (preg_match($pattern, $fileContent, $matches)) {
            // Check if static::bootUsesCipherSweet(); is NOT present in the boot method
            if (strpos($matches[1], 'static::bootUsesCipherSweet();') === false) {
                // Find the position of the line 'static::setEventDispatcher(new Dispatcher());' in the boot method
                $dispatcherPosition = strpos($matches[1], 'static::setEventDispatcher(new Dispatcher());');

                // Construct the new boot method with the added line
                $newBootMethod = $matches[1];
                if ($dispatcherPosition !== false) {
                    // Insert the line after 'static::setEventDispatcher(new Dispatcher());'
                    $newBootMethod = substr_replace(
                        $newBootMethod,
                        "    static::bootUsesCipherSweet();\n",
                        $dispatcherPosition + strlen('static::setEventDispatcher(new Dispatcher());'),
                        0
                    );
                }

                // Replace the boot method in the file content
                $fileContent = preg_replace($pattern, "public static function boot()\n{\n" . $newBootMethod . "\n}", $fileContent);
            }
        }
    }

    private function removeUseTraitDuplications(&$fileContent)
    {
        // Split the input string into an array of lines
        $lines = explode("\n", $fileContent);

        // Initialize an array to keep track of unique "use UsesCipherSweetTrait;" lines
        $uniqueLines = array();

        foreach ($lines as $line) {
            // Check if the line contains "use UsesCipherSweetTrait;"
            if (strpos($line, 'use UsesCipherSweetTrait;') !== false) {
                // If the line is not already in the uniqueLines array, add it
                if (!in_array($line, $uniqueLines)) {
                    $uniqueLines[] = $line;
                }
            } else {
                // For lines that don't contain "use UsesCipherSweetTrait;", keep them as is
                $uniqueLines[] = $line;
            }
        }

        // Combine the uniqueLines array into a string with line breaks
        $fileContent = implode("\n", $uniqueLines);
    }
}
