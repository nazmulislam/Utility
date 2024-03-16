<?php
declare(strict_types=1);

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QRCodeGenerator
{

   public static function createQRCode(string $url)
   {
       $options = new QROptions(
            [
              'eccLevel' => QRCode::ECC_L,
              'outputType' => QRCode::OUTPUT_MARKUP_SVG,
              'version' => 5,
            ]
          );

        return $qrcode = (new QRCode($options))->render($url);
   }

}
