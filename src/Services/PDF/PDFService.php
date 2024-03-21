<?php
declare(strict_types=1);

namespace App\Services\PDF;
use Slim\Psr7\Stream;
use NazmulIslam\Utility\Logger\Logger;


class PDFService
{
    CONST RELATIVE_PATH = __DIR__  . '/../../../';
    public string $filename;
    public string $filePath;
    public string $htmlReport;
    
    public function createPdf(string $filename, string $html,string $saveFolderPath): PDFService
    {
        $options = [
            'margin-top' => '20',
            'margin-left' => '10',
            'margin-right' => '10',
            'margin-bottom' => '10',
            'page-size' => 'A4',
//            'footer-spacing' => '5',
//            'footer-font-size' => 8,
//            'footer-center' => 'STRICTLY CONFIDENTIAL',
//            'footer-right' => 'Page [page] of [topage]',
//           
            
            
        ];


        $jsonEncodeddata = json_encode([
            'contents' => base64_encode($html),
            'options' => $options,
        ]);



        $pdfDataBinary = $this->makeCurlRequest($_ENV['PDF_SERVER_URL'],$jsonEncodeddata);


        $this->savePdfFile($pdfDataBinary,$filename,$saveFolderPath);


        
        return $this;
        
        
    }
    
    private function savePdfFile(string $pdfDataBinary,$filename,$tempRelativePath)
    {
        
         $file = self::RELATIVE_PATH . $tempRelativePath . DS . $filename . '.pdf';
        if (!file_exists(self::RELATIVE_PATH . $tempRelativePath))
        {
            mkdir(self::RELATIVE_PATH . $tempRelativePath, 0755, true);
        }
        
       

        $this->filePath = $file;
        $myfile = fopen($file, "w");

        fwrite($myfile, $pdfDataBinary);

        fclose($myfile);
        return $this;
    }
    
    private function makeCurlRequest(string $url,string $data)
    {
      
         $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $data,
        ]);
        $result = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
}
curl_close($ch);

if (isset($error_msg)) {
    Logger::debug('curl Error',[$error_msg]);
}
        return $result;
    }
    
    public function getFileStream(string $filename, string $folderPath ):Stream
    {
        $file = self::RELATIVE_PATH . $folderPath  . DS . $filename . '.pdf';
        $fh = fopen($file, 'rb');
        return new Stream($fh);
    }
    
    public function sanitizeFileName(string $name): string
    {
        if (strlen($name)) {
            return preg_replace('/[^A-Za-z0-9 \\._-]+/', '', $name);
        }

        return $name;
    }
    

    
 
   
 
}
