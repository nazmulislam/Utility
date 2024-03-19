<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Excel;

use NazmulIslam\Utility\Logger\Logger;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Phpfastcache\Helper\Psr16Adapter;



class CSVToArray
{

    static $fileType = '';
    static $targetFile = '';
    static $additionalData = [];

    const MULTI_SELECT_SEPRATOR = "/";

    static $rowCount = 0;

    static function processExcelFile(string $filetype, string $target_file, string $output_file, array $additionalData = [])
    {
        
       
        self::$fileType = $filetype;
        self::$targetFile = $target_file;
        self::$additionalData = $additionalData;
        self::$rowCount = 2;
        return self::convertCSVToArrayRoot(self::convertXLStoCSV($target_file, $output_file));
    }

    static function convertXLStoCSV(string $infile, string $outfile)
    {
 
        
        $defaultDriver = 'Files';
$Psr16Adapter = new Psr16Adapter($defaultDriver);


\PhpOffice\PhpSpreadsheet\Settings::setCache($Psr16Adapter);
        $objPHPExcel = IOFactory::load($infile);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        $lastColumn = $objWorksheet->getHighestColumn();
        $lastColumn++;
        $lastRow = $objWorksheet->getHighestRow();

        for ($column = 'A'; $column != $lastColumn; $column++) {
            for ($row = 1; $row <= $lastRow; $row++) {
                $cell = $objWorksheet->getCell($column . $row);
            
                $cellValue = $cell->getValue();
                if (isset($cellValue) && $cellValue !== NULL && isset($cell) ) {
                   // $value = str_replace("\n", " ", $cellValue);
                    $objWorksheet->setCellValue($column . $row, str_replace("\n", " ", strval($cellValue)));
                }
            }
        }
        $objWriter = IOFactory::createWriter($objPHPExcel, 'Csv');
        $objWriter->setDelimiter(',')->setEnclosure('"')->setLineEnding("\r\n")->setSheetIndex(0)->save($outfile);
        return $outfile;
        
    }

    static function convertCSVToArrayRoot(string $outfile): array
    {
        $csvData = array_map('str_getcsv', file($outfile));
        $dataWithCols = $csvData[0];
        unset($csvData[0]);
        $dataToProcess = array_values($csvData);
        array_walk($dataToProcess, function(&$csvRow) use ($dataToProcess, $dataWithCols)
        {
            $csvRow = array_combine($dataWithCols, $csvRow);
        });
        return $dataToProcess;
    }

    /**
     * Takes a CSV file and converts it to an array
     * @param string $filename
     * @param string $delimiter
     * @return array
     */
    static function csvFileToArray(string $filename = '', string $delimiter = ','): array
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 10000, $delimiter)) !== FALSE)
            {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    static function getHeadercolumnsFromCSV(string $filename = ''): array
    {

        $csvData = array_map('str_getcsv', file($filename));
       return $csvData[0];

    }

}
