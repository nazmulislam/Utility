<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Console\Traits;

use NazmulIslam\Utility\File\File;
use NazmulIslam\Utility\Text\Text;
use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as DB;

trait CommonTraits
{
    public $relativePathToUtility = __DIR__ . '/../../../';
    public $relativePathToApplication = __DIR__ . '/../../../../../../';
    public array $findText = [
        'Sample',
        'SAMPLE',
        'sample',
        'GUID',
        '[DomainFolder]',
        '[ClassName]',
        '[instanceName]',
        '[primary_key_id]',
        '[parameterId]',
        '[ModelName]',
        '[title_field]',
        '[CONSTANTS]',
        '[UCFirstWithSpace]',
        '[LowerCaseWithSpace]',
        '[table_name]',
        '[RouteGroup]',
        '[snake_case]',
    ];
    public $replaceText = [];

    public function setReplaceText(string $domainName): void
    {
        $this->replaceText  = [
            Text::pascalCase($domainName),
            strtoupper(Text::snakeCase($domainName)),
            Text::camelCase($domainName),
            strtolower(Text::snakeCase($domainName)) . '_guid',
            Text::pascalCase($domainName), // Domain Folder
            Text::pascalCase($domainName), // Classname
            Text::camelCase($domainName), // instanceName
            strtolower(Text::snakeCase($domainName)) . '_id', // Primary key id,
            Text::camelCase($domainName) . 'Id', //ParameterId
            Text::pascalCase($domainName), // Model name
            strtolower(Text::snakeCase($domainName)) . '_title', // tilte_field
            strtoupper(Text::snakeCase($domainName)), // Contstants
            $this->firstWordUpperCaseWithSpace($domainName), // [UCFirstWithSpace]
            $this->lowerCaseWordsWithSpace($domainName), //[LowerCaseWithSpace]
            strtolower(Text::snakeCase($domainName)), // table name
            strtolower(Text::hyphenCase($domainName)), // Route
            strtolower(Text::snakeCase($domainName)), // snake_case,
        ];
    }

    static function copyTemplateFolder(string $src, string $dst, string|null $searchText = null, string|null $modifier = null)
    {

        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        if (!file_exists($dst)) {
            mkdir($dst, 0755, true);
        }


        // Loop through the files in source directory
        foreach (scandir($src) as $file) {

            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {

                    // Recursively calling custom copy function
                    // for sub directory 
                    self::copyTemplateFolder(src: $src . '/' . $file, dst: $dst . '/' . $file, searchText: $searchText, modifier: $modifier);
                } else {
                    $newFile = $file;
                    if (isset($modifier) && isset($searchText)) {
                        $newFile = str_replace($searchText, $modifier, $file);
                    }
                    copy($src . '/' . $file, $dst . '/' . $newFile);
                }
            }
        }

        closedir($dir);
    }

    public function firstWordUpperCaseWithSpace(string $domainName): string
    {
        (array) $tempWords = explode('_', strtolower(Text::snakeCase($domainName)));
        $newWord = '';
        for ($i = 0; $i < count($tempWords); $i++) {
            $newWord .= ucfirst($tempWords[$i]) . ' ';
        }
        return trim($newWord);
    }

    public function lowerCaseWordsWithSpace(string $domainName): string
    {
        (array) $tempWords = explode('_', strtolower(Text::snakeCase($domainName)));
        $newWord = '';
        for ($i = 0; $i < count($tempWords); $i++) {
            $newWord .= $tempWords[$i] . ' ';
        }
        return trim($newWord);
    }

    public function copyModelTemplateFiles(string $domainName): int
    {
        $ucFirstDomainName = Text::pascalCase($domainName);

        if (!file_exists($this->relativePathToApplication . 'src/Models/App/' . $ucFirstDomainName . '.php')) {

            copy($this->relativePathToUtility . 'CodeTemplates/Model/Sample.php', $this->relativePathToApplication . 'src/Models/App/' . $ucFirstDomainName . '.php');
            //read the entire string
            $fileContent = file_get_contents($this->relativePathToApplication . 'src/Models/App/' . $ucFirstDomainName . '.php');

            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($this->relativePathToApplication . 'src/Models/App/' . $ucFirstDomainName . '.php', $str);

            return 0;
        } else {

            return 1;
        }
    }

    public function copyRouteTemplateFiles(string $domainName): int
    {

        $sampleSource = $this->relativePathToUtility . 'CodeTemplates/Route/sample.php';
        $destinationPath = $this->relativePathToApplication . 'src/routes/app/' . Text::camelCase($domainName) . '/' . strtolower(Text::pascalCase($domainName)) . '.php';
        if (!file_exists($destinationPath)) {

            if (!file_exists($this->relativePathToApplication . 'src/routes/app/' . Text::camelCase($domainName))) {
                mkdir($this->relativePathToApplication . 'src/routes/app/' . Text::camelCase($domainName), 0755, true);
            }
            copy($sampleSource, $destinationPath);
            //read the entire string
            $fileContent = file_get_contents($destinationPath);


            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($destinationPath, $str);

            $routeFileContent = file_get_contents($this->relativePathToApplication . 'src/config/routes.php');

            $routeFileName = Text::camelCase($domainName) . '/' . strtolower(Text::pascalCase($domainName)) . '.php';
            //
            $find = [
                '/** INSERT AFTER HOOK */',
            ];
            $text = [
                '/** INSERT AFTER HOOK */' . PHP_EOL . 'require_once(__DIR__ . \'/../routes/app/' . $routeFileName . '\');',
            ];
            $new_contents = str_replace($find, $text, $routeFileContent);
            file_put_contents($this->relativePathToApplication . 'src/config/routes.php', $new_contents);

            return 0;
        } else {

            return 1;
        }
    }

    public function copyPhinxTemplateFiles(string $domainName): int
    {
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $day = $now->format('d');
        $hour = $now->format('H');
        $minute = $now->format('i');
        $second = $now->format('s');

        $phinx_file_name = $year . $month . $day . $hour . $minute . $second . '_add_table_' . \strtolower(Text::snakeCase($domainName));

        $sampleSource = $this->relativePathToUtility . 'CodeTemplates/Phinx/PHINX_FILE_NAME.php';
        $destinationPath = $this->relativePathToApplication . 'database/migrations/' . $phinx_file_name . '.php';
        if (!file_exists($destinationPath)) {

            if (!file_exists($this->relativePathToApplication . 'database/migrations/' . $phinx_file_name)) {
            }
            copy($sampleSource, $destinationPath);
            //read the entire string
            $fileContent = file_get_contents($destinationPath);

            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($destinationPath, $str);

            return 0;
        } else {

            return 1;
        }
    }

    public function copyRbacModuleTemplateFiles(string $domainName): int
    {

        $ucFirstDomainName = Text::pascalCase($domainName);
        $sampleSource = $this->relativePathToUtility . 'CodeTemplates/Values/RbacValues.php';
        $destinationPath = $this->relativePathToApplication . 'src/Values/Modules/' . $ucFirstDomainName . 'Values.php';
        if (!file_exists($destinationPath)) {


            copy($sampleSource, $destinationPath);
            //read the entire string
            $fileContent = file_get_contents($destinationPath);

            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($destinationPath, $str);

            //Logic

            $moduleValueFileContent = file_get_contents($this->relativePathToApplication . 'src/Values/ModuleValues.php');

            $find = [
                'namespace App\Values;',
                'const MODULES = ['
            ];
            $text = [
                'namespace App\Values;' . PHP_EOL . 'use App\Values\Modules\\' . $ucFirstDomainName . 'Values;',
                'const MODULES = [' . PHP_EOL . '...[' . $ucFirstDomainName . 'Values::MODULE],'
            ];
            $new_contents = str_replace($find, $text, $moduleValueFileContent);
            file_put_contents($this->relativePathToApplication . 'src/Values/ModuleValues.php', $new_contents);
            return 0;
        } else {

            return 1;
        }
    }

    public function copyUnitTestTemplateFiles(string $domainName): int
    {
        $ucFirstDomainName = Text::pascalCase($domainName);

        $this->copyTemplateFolder($this->relativePathToUtility . 'CodeTemplates/UnitTest/Domain', $this->relativePathToApplication . 'tests/Api/Domain/' . $ucFirstDomainName, 'Sample', $ucFirstDomainName);

        $files = File::getAllDirectoryDocuments($this->relativePathToApplication . 'tests/Api/Domain/' . $ucFirstDomainName);
        if (isset($files) && count($files) > 0) {
            foreach ($files as $filePath) {

                //read the entire string
                $fileContent = file_get_contents($filePath);

                $str = str_replace($this->findText, $this->replaceText, $fileContent);

                //write the entire string
                file_put_contents($filePath, $str);
            }
        }

        // copy smoke test
        $this->copyTemplateFolder($this->relativePathToUtility . 'CodeTemplates/UnitTest/Smoke', $this->relativePathToApplication . 'tests/Api/Smoke/', 'Sample', $ucFirstDomainName);

        $file = $this->relativePathToApplication . 'tests/Api/Smoke/' . $ucFirstDomainName . 'SmokeTest.php';
        if (file_exists($file)) {
            //read the entire string
            $fileContent = file_get_contents($file);

            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($file, $str);
        }


        return 0;
    }

    public function copySeedTemplateFiles(string $domainName): int
    {
        $ucFirstDomainName = Text::pascalCase($domainName);



        // copy smoke test
        $this->copyTemplateFolder($this->relativePathToUtility . 'CodeTemplates/Seed', $this->relativePathToApplication . 'database/seeds/', 'Sample', $ucFirstDomainName);

        if (file_exists($this->relativePathToApplication . 'database/seeds/' . $ucFirstDomainName . 'Seeder.php')) {
            $file = $this->relativePathToApplication . 'database/seeds/' . $ucFirstDomainName . 'Seeder.php';
            //read the entire string
            $fileContent = file_get_contents($file);

            $str = str_replace($this->findText, $this->replaceText, $fileContent);

            //write the entire string
            file_put_contents($file, $str);
        }


        return 0;
    }

    public function copyDomainTemplateFiles(string $domainName): int
    {
        $ucFirstDomainName = Text::pascalCase($domainName);

        if (!file_exists($this->relativePathToApplication . 'src/Domain/' . $ucFirstDomainName)) {

            mkdir($this->relativePathToApplication . 'src/Domain/' . $ucFirstDomainName, 0755, true);
            $this->copyTemplateFolder($this->relativePathToUtility . 'CodeTemplates/Domain', $this->relativePathToApplication . 'src/Domain/' . $ucFirstDomainName, 'Sample', $ucFirstDomainName);

            $files = File::getAllDirectoryDocuments($this->relativePathToApplication . 'src/Domain/' . $ucFirstDomainName);
            if (isset($files) && count($files) > 0) {
                foreach ($files as $filePath) {

                    //read the entire string
                    $fileContent = file_get_contents($filePath);

                    $str = str_replace($this->findText, $this->replaceText, $fileContent);

                    //write the entire string
                    file_put_contents($filePath, $str);
                }
            }


            return 0;
        } else {

            return 1;
        }
    }




    public function setupTenantDB()
    {
        $db = new DB;

        //=======================================



        $db->addConnection([
            'driver' => 'mysql',
            'host' => DB_HOST_NAME,
            'database' => DB_NAME,
            'username' => DB_USERNAME,
            'password' => DB_PASSWORD,
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix' => '',
            'options' => [
                // Turn on persistent connections
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
                // Enable exceptions
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                // Emulate prepared statements
                \PDO::ATTR_EMULATE_PREPARES => true,
                // Set default fetch mode to array
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                // Set character set
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ],
        ], 'app');

        /**
         * The setAsglobal and bootEloquent is required here do not move as the Utility::setSaasDBHostname, will not be able to find connection
         */
        $db->setAsGlobal();

        // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
        $db->bootEloquent();
    }
}
