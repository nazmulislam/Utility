<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Archive;

class Unzipper
{

    public $localdir = __DIR__.'/../../../../../';
    
    public $zipfiles = [];

    public function __construct(string $path)
    {
        $this->localdir = __DIR__.'/../../../../../'.$path;
        // Read directory and pick .zip, .rar and .gz files.
//        if ($dh = opendir($this->localdir))
//        {
//            while (($file = readdir($dh)) !== FALSE)
//            {
//                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip' || pathinfo($file, PATHINFO_EXTENSION) === 'gz' || pathinfo($file, PATHINFO_EXTENSION) === 'rar'
//                )
//                {
//                    $this->zipfiles[] = $file;
//                }
//            }
//            closedir($dh);
//
//            if (!empty($this->zipfiles))
//            {
//                $GLOBALS['status'] = array('info' => '.zip or .gz or .rar files found, ready for extraction');
//            } else
//            {
//                $GLOBALS['status'] = array('info' => 'No .zip or .gz or rar files found. So only zipping functionality available.');
//            }
//        }
        
        //$this->zipfiles[] = '';


    }

    /**
     * Prepare and check zipfile for extraction.
     *
     * @param string $archive
     *   The archive name including file extension. E.g. my_archive.zip.
     * @param string $destination
     *   The relative destination path where to extract files.
     */
    public function prepareExtraction($archive, $destination = ''):Unzipper
    {
        // Determine paths.
        if (empty($destination))
        {
            $extpath = $this->localdir;
        } else
        {
            $extpath = $this->localdir . '/' . $destination;
            // Todo: move this to extraction function.
            if (!is_dir($extpath))
            {
                mkdir($extpath);
            }
        }
        // Only local existing archives are allowed to be extracted.
        if (in_array($archive, $this->zipfiles))
        {
            $this->extract($archive, $extpath);
        }

        return $this;
    }

    /**
     * Checks file extension and calls suitable extractor functions.
     *
     * @param string $archive
     *   The archive name including file extension. E.g. my_archive.zip.
     * @param string $destination
     *   The relative destination path where to extract files.
     */
    public function extract($archive, $destination):Unzipper
    {
        $ext = pathinfo($this->localdir.DIRECTORY_SEPARATOR.$archive, PATHINFO_EXTENSION);

       
        switch ($ext)
        {
            case 'zip':
                $this->extractZipArchive($archive, $destination);
                break;
            case 'gz':
                $this->extractGzipFile($archive, $destination);
                break;
            case 'rar':
                $this->extractRarArchive($archive, $destination);
                break;
        }
        return $this;
    }

    /**
     * Decompress/extract a zip archive using ZipArchive.
     *
     * @param $archive
     * @param $destination
     */
    public function extractZipArchive($archive, $destination):Unzipper
    {
        // Check if webserver supports unzipping.
        if (!class_exists('ZipArchive'))
        {
            \NazmulIslam\Utility\Logger\Logger::debug('ZipArchive does not exist');
           
        }

        $zip = new \ZipArchive;

        // Check if archive is readable.

        \NazmulIslam\Utility\Logger\Logger::debug('unzipper'.$this->localdir.DIRECTORY_SEPARATOR.$archive,[]);
        if ($zip->open($this->localdir.DIRECTORY_SEPARATOR.$archive) === TRUE)
        {
            // Check if destination is writable
            if (is_writeable(__DIR__.'/../../../../../'.$destination . '/'))
            {
                $zip->extractTo(__DIR__.'/../../../../../'.$destination);
                $zip->close();

                \NazmulIslam\Utility\Logger\Logger::debug('Files unzipped successfully');
            } else
            {
                 \NazmulIslam\Utility\Logger\Logger::debug('Error: Directory not writeable by webserver.');
                
            }
        } else
        {
            \NazmulIslam\Utility\Logger\Logger::debug('Error: Cannot read .zip archive.');
            
        }

        return $this;
    }

    /**
     * Decompress a .gz File.
     *
     * @param string $archive
     *   The archive name including file extension. E.g. my_archive.zip.
     * @param string $destination
     *   The relative destination path where to extract files.
     */
    public function extractGzipFile($archive, $destination):Unzipper
    {
        // Check if zlib is enabled
        if (!function_exists('gzopen'))
        {


            \NazmulIslam\Utility\Logger\Logger::debug('Error: Your PHP has no zlib support enabled.');
            
        }

        $filename = pathinfo($this->localdir.DIRECTORY_SEPARATOR.$archive, PATHINFO_FILENAME);
        $gzipped = gzopen($this->localdir.DIRECTORY_SEPARATOR.$archive, "rb");
        $file = fopen(__DIR__.'/../../../../../'.$destination . '/' . $filename, "w");

        while ($string = gzread($gzipped, 4096))
        {
            fwrite($file, $string, strlen($string));
        }
        gzclose($gzipped);
        fclose($file);

        // Check if file was extracted.
        if (file_exists(__DIR__.'/../../../../../'.$destination . '/' . $filename))
        {
            \NazmulIslam\Utility\Logger\Logger::debug('File unzipped successfully.');

            // If we had a tar.gz file, let's extract that tar file.
            if (pathinfo(__DIR__.'/../../../../../'.$destination . '/' . $filename, PATHINFO_EXTENSION) == 'tar')
            {
                $phar = new \PharData(__DIR__.'/../../../../../'.$destination . '/' . $filename);
                if ($phar->extractTo(__DIR__.'/../../../../../'.$destination))
                {
                    \NazmulIslam\Utility\Logger\Logger::debug('Extracted tar.gz archive successfully.');
                   
                    // Delete .tar.
                    //sunlink($destination . '/' . $filename);
                }
            }
        } else
        {
            \NazmulIslam\Utility\Logger\Logger::debug('Error unzipping file.');
           
        }
        return $this;
    }

    /**
     * Decompress/extract a Rar archive using RarArchive.
     *
     * @param string $archive
     *   The archive name including file extension. E.g. my_archive.zip.
     * @param string $destination
     *   The relative destination path where to extract files.
     */
    public function extractRarArchive($archive, $destination):Unzipper
    {
        // Check if webserver supports unzipping.
        if (!class_exists('RarArchive'))
        {
            \NazmulIslam\Utility\Logger\Logger::debug('Your PHP version does not support .rar archive functionality.');
           
            
        }
        // Check if archive is readable.
        if ($rar = \RarArchive::open($archive))
        {
            // Check if destination is writable
            if (is_writeable(__DIR__.'/../../../../../'.$destination . '/'))
            {
                $entries = $rar->getEntries();
                foreach ($entries as $entry)
                {
                    $entry->extract(__DIR__.'/../../../../../'.$destination);
                }
                $rar->close();

                \NazmulIslam\Utility\Logger\Logger::debug('Files extracted successfully.');
            } else
            {
                
                \NazmulIslam\Utility\Logger\Logger::debug('Error: Directory not writeable by webserver.');
            }
        } else
        {

            \NazmulIslam\Utility\Logger\Logger::debug('Error: Cannot read .rar archive.');
        }

        return $this;
    }

}


