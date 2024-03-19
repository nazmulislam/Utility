<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\File;


class File
{

    CONST RELATIVE_PATH = __DIR__ . '/../../../';

    /**
     *
     * @param type $directory
     * @param type $results
     * @return array
     */
  
    static function  getAllDirectoryDocuments($directory, &$results = []): array
    {
        if (file_exists($directory))
        {
            $files = scandir($directory);
            foreach ($files as $key => $value)
            {
                $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
                if (!is_dir($path) && $value != "." && $value != ".." && $value != '.DS_Store')
                {
                    $results[] = $path;
                } else if ($value != "." && $value != ".." && $value != '.DS_Store')
                {
                    self::getAllDirectoryDocuments($path, $results);
                }
            }
        }
        return $results;
    }

    /**
     * Deletes files inside a directory
     * @param type $target
     */
    static function deleteFiles($target)
    {
        if (is_dir($target))
        {
            $files = glob($target . '*', GLOB_MARK); //GLOB_MARK adds a slash to directories returned

            foreach ($files as $file)
            {
                self::deleteFiles($file);
            }
        } elseif (is_file($target))
        {
            unlink($target);
        }
    }

    static function deleteDirectoryWithContent(string $dir):void
    {
        if (is_dir($dir))
        {
            $objects = scandir($dir);
            foreach ($objects as $object)
            {
                if ($object != "." && $object != "..")
                {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
                        rmdir($dir . DIRECTORY_SEPARATOR . $object);
                    else
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
            rmdir($dir);
        }
    }

    static public function sanitizeDirectoryName(string $string): string
    {
        //$replacedString = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
       return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $string)); // Removes special chars.
        //        $string = strtolower($string); // Convert to lowercase
       
    }

    static public function sanitizeDocumentFileName(string $string): string
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^a-zA-Z0-9\/_|+ .-]/', '', $string); // Removes special chars.
        
       
        return $string;
    }

    /**
     * 
     * @param string $filename
     * @return type
     * @throws \Exception
     */
    static function readCsvFile(string $filepath): array
    {
        try
        {
            $csvData = [];

            /**
             * Allowed format to upload files
             */
            $allowed = array('csv');

            $file = fopen($filepath, 'r');
            /**
             * GET File extension
             */
            $ext = pathinfo($filepath, PATHINFO_EXTENSION);

            /**
             * Throw exception if file type is not expected
             */
            if (in_array($ext, $allowed))
            {

                while (($line = fgetcsv($file)) !== FALSE)
                {
                    /*
                     * $line is an array of the csv elements
                     */
                    $csvData[] = $line;
                }
                fclose($file);

                return $csvData;
            } else
            {
                throw new \Exception('only csv file allowed to upload');
            }
        } catch (\Exception $ex)
        {
            
            throw new \Exception('We have some issue in read cvs file');
        }
    }

    /**
     * 
     * @param string $filename
     * @return array
     */
    static function readPdfFile(string $filename): array
    {
        // Parse pdf file and build necessary objects.
        $parser = new \Smalot\PdfParser\Parser();

        $pdf = $parser->parseFile($filename);
        // Retrieve all details from the pdf file.
        $details = $pdf->getDetails();

        // Loop over each property to extract values (string or array).
        /**
          foreach ($details as $property => $value)

          {
          if (is_array($value))
          {
          $value = implode(', ', $value);
          }
          $properties[] =  $property . ' => ' . $value . "\n";
          }
         *
         */
        $text = $pdf->getText();
        $textToArray = [];
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $text) as $line)
        {
            if ($line != '' || !empty($line) || !isset($line))
            {
                $textToArray[] = $line;
            }
        }
        return $textToArray;
    }

    static function fileUploadOnServer($files)
    {
        $file = (isset($files['file']['tmp_name']) ? $files['file']['tmp_name'] : '');
        $file_csvfile_name = (isset($files['file']['name']) ? $files['file']['name'] : '');
        $filetype = \pathinfo($file_csvfile_name, PATHINFO_EXTENSION);
        $target_dir = PROJECT_ROOT . DS . UPLOAD_SESSION_FOLDER_PATH;
        $output_file = PROJECT_ROOT . DS . UPLOAD_SESSION_FOLDER_PATH . DS . 'output.csv';
        if (!file_exists($target_dir))
        {
            mkdir($target_dir, 0775, true);
        }
        $filename = md5(time());
        $target_file = $target_dir . DS . $filename . "." . $filetype;
        $file = $filename . "." . $filetype;
        $success = FALSE;
        if (\move_uploaded_file($files["file"]["tmp_name"], $target_file))
        {
            return ['status' => true, 'filename' => $filename, 'targetFile' => $target_file];
        } else
        {
            return ['status' => false];
        }
    }

    /**
     * Moves a file to a specific folder location
     * @param type $uploadedFile
     * @return type
     * @throws \Exception
     */
    static function moveUploadedFile(string $uploadFilename, string $uploadTempName, string $path)
    {
        $directory = __DIR__ . '/../../../' . $path;
        self::createDirectoryIfNotExists($path);

        $filename = self::checkUploadedFileName($path, $uploadFilename);

        if (!\move_uploaded_file($uploadTempName, $directory . "/" . $filename))
        {
            throw new \Exception('Eoor uploading files');
        }
        return $filename;
    }

    public static function moveExistingFile(string $uploadFilename, string $uploadTempName, string $path)
    {
        $directory = __DIR__ . '/../../../' . $path;
        self::createDirectoryIfNotExists($path);

        $filename = self::checkUploadedFileName($path, $uploadFilename);

        if (!\rename(__DIR__ . '/../../../' . $uploadTempName, $directory . "/" . $filename))
        {
            throw new \Exception('could not move file');
        }
        return $filename;
    }

    public static function createDirectoryIfNotExists(string $path): void
    {
        $directory = __DIR__ . '/../../../' . $path;
        if (!file_exists($directory))
        {
            \mkdir($directory, 0755, true);
        }
    }

    /**
     * Todo in complete, need to make dynamic and also test
     * @param array $validExtentions
     * @throws RuntimeException
     */
    static function validateExtensions(array $validExtentions = [])
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (false === $ext = array_search(
                $finfo->file($_FILES['upfile']['tmp_name']),
                $validExtentions,
                true
                ))
        {
            throw new \RuntimeException('Invalid file format.');
        }
    }

    /**
     * renames file file name already exists
     * @param string $path
     * @param string $filename
     * @return string
     */
    public static function checkUploadedFileName(string $path, string $filename): string
    {
        $count = 0;
        $directory = __DIR__ . '/../../../' . $path . '/';
        while (file_exists($directory . $filename))
        {
            $path_parts = pathinfo($directory . $filename);
            $count += 1;
            $trimmed = explode('~', $path_parts['filename']);
            $filename = $trimmed[0] . '~' . $count . '.' . $path_parts['extension'];
        }
        return $filename;
    }

    /**
     *
     * @param string $file
     * @return bool
     */
    static public function checkIfFileExists(string $file): bool
    {
        if (!file_exists(__DIR__ . '/../../../' . $file))
        {
            \NazmulIslam\Utility\Logger\Logger::debug('File path info ' . $file, []);
            return false;
        }
        return true;
    }

    /**
     *
     * @param string $file
     * @return string
     */
    public static function getFileInfo(string $file): array
    {
       
        if (self::checkIfFileExists($file))
        {

            return pathinfo(realpath(__DIR__ . '/../../../') . $file);
        } else
        {
            \NazmulIslam\Utility\Logger\Logger::debug('NO FILE INFO: ' . $file, []);
            return [];
        }
    }

    /**
     * Get the folder size
     * @param string $dir
     * @return type
     */
    public static function folderSize(string $dir)
    {

        $count_size = 0;
        $count = 0;
        $dir_array = scandir($dir);
        
        foreach ($dir_array as $key => $filename)
        {
            if ($filename != ".." && $filename != ".")
            {
                if (is_dir( $dir . "/" . $filename))
                {
                    /**
                     * Do not include relative here path here
                     */
                    $new_foldersize = self::folderSize($dir . "/" . $filename);
                    $count_size += $new_foldersize;
                } else if (is_file( $dir . "/" . $filename))
                {
                    $count_size += filesize( $dir . "/" . $filename);
                    $count++;
                }
            }
        }
        return $count_size;
    }
    
    /**
 * Converts a long string of bytes into a readable format e.g KB, MB, GB, TB, YB
 * 
 * @param {Int} num The number of bytes.
 */
    static function readableBytes($bytes) {
        $i = floor(log($bytes) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }


    static function generateGuid() { 
    $s = strtoupper(md5(uniqid((string)rand(),true))); 
    $guidText = 
        substr($s,0,8) . '-' . 
        substr($s,8,4) . '-' . 
        substr($s,12,4). '-' . 
        substr($s,16,4). '-' . 
        substr($s,20); 
    return $guidText;
}
}
