<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\EmailMailboxValidator;

use NazmulIslam\Utility\Logger\Logger;


/**
 * if response code is 250 - Email address exist
 * if response code is 550 - Email address does not exist
 */
class EmailMailboxValidator
{
    public static array $response = [];
    public const DOES_NOT_EXIST_550 = 550;
    public const DOES_EXIST_250 = 250;


    public static function checkDNSDomainExist(string $hostname): bool
    {

        if (!checkdnsrr($hostname, "MX")) {
            return false;
        }
        return true;
    }


    public static function checkIfMailboxExist(string $email): string | bool | array
    {

        $return = [];
        $mailparts = self::emailAdressSpilter($email);
        $hostname = $mailparts[1];



        // get mx addresses by getmxrr
        $b_mx_avail = getmxrr($hostname, $mx_records, $mx_weight);
        $b_server_found = 0;

        if ($b_mx_avail) {
            $return['mxrecordStatus'] = "MX RECORDS FOUND";

            // copy mx records and weight into array $mxs
            $mxs = array();

            for ($i = 0; $i < count($mx_records); $i++) {
                $mxs[$mx_weight[$i]] = $mx_records[$i];
            }

            // sort array mxs to get servers with highest prio
            ksort($mxs, SORT_NUMERIC);
            reset($mxs);
            $return['mxrecords'] = $mxs;


            foreach ($mxs as $mx_host) {

                if ($b_server_found == 0) {

                    //try connection on port 25
                    $fp = fsockopen($mx_host, 25, $errno, $errstr, 2);
                    //echo "<pre>", print_r($fp);
                    $return['connection'] = $fp;


                    if ($fp) {
                        $ms_resp = "";
                        // say HELO to mailserver
                        $ms_resp .= self::send_command($fp, "HELO microsoft.com");

                        // initialize sending mail
                        $ms_resp .= self::send_command($fp, "MAIL FROM:<support@microsoft.com>");

                        // try receipent address, will return 250 when ok..

                        $rcpt_text = self::send_command($fp, "RCPT TO:<" . $email . ">");
                        //echo $ms_resp .= $rcpt_text;
                        $return['receipientSendMessage'] = $rcpt_text;

                        //self::$response['response'] = $rcpt_text;

                        $b_server_found = 1;
                        if (substr($rcpt_text, 0, 3) ==  self::DOES_EXIST_250) {
                            $return['receipientStatusCode'] = 250;
                            //self::$response['status'] = true;
                            $b_server_found = 1;
                        } else {
                            if (substr($rcpt_text, 0, 3) !== self::DOES_EXIST_250) {
                                $return['receipientStatusCode'] = 550;
                            }
                        }

                        // quit mail server connection
                        $ms_resp .= self::send_command($fp, "QUIT");

                        fclose($fp);
                    }
                }
            }
        } else {
            $return['mxrecordStatus'] = "NO MX RECORDS FOUND";
        }

        return $return;
    }

    public static function send_command($fp, $out)
    {

        fwrite($fp, $out . "\r\n");
        return self::get_data($fp);
    }

    public static function get_data($fp)
    {
        $s = "";
        stream_set_timeout($fp, 2);

        for ($i = 0; $i < 2; $i++) {
            $s .= fgets($fp, 1024);
        }
        return $s;
    }

    public static function emailAdressSpilter(string $email): array
    {
        $data = explode('@', $email);
        return $data;
    }
}
