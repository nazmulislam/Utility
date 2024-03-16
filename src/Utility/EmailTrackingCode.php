<?php
declare(strict_types=1);

namespace NazmulIslam\Utility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Description of EmailTrackingCode
 *
 * @author nazmulislam
 */
class EmailTrackingCode
{
    static function generateUniqueTrackingCode(int $limit = 8) {
        if (!isset($limit)) {
            $limit = 8;
        }
        do {
            $trackingCode = self::uniqueCode($limit);
        } while (empty($tracking = \App\Models\App\EmailTracking::where('tracking_code', '=', $trackingCode)));

        return $trackingCode;
    }

    static function uniqueCode(int $limit=8)
    {
      return substr(base_convert(sha1(uniqid((string)mt_rand())), 16, 36), 0, $limit);
    }

    function updateTracking(Request $request, ResponseInterface $response, $args)
    {
        if(isset($args['track_code']) && !empty($args['track_code']))
        {
            $tracking = \App\Models\App\EmailTracking::where('tracking_code',$args['track_code'])->where('status',0)->first();
            $tracking->status = 1;
            $tracking->save();

         $response->withStatus(200);
        return $response;
        }
        

    }
}
