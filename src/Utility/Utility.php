<?php

declare(strict_types=1);

namespace NazmulIslam\Utility;

use App\Core\CipherSweetEncryption\EncryptModel;
use App\Core\CipherSweetEncryption\Methods;
use App\Models\App\ApprovalProcess;
use App\Models\App\ApprovalProcessDetail;
use App\Models\App\ApprovalProcessHrDepartmentJobRoleModuleDetail;
use App\Models\App\Role;
use App\Models\App\RolePolicy;
use Illuminate\Database\Capsule\Manager as DB;
use NazmulIslam\Utility\Queue\Queue;
use App\Values\ConstantValues;
use NazmulIslam\Utility\Email\DynamicEmail;
use App\Models\App\User;
use App\Models\Master\MasterProductPolicy;
use NazmulIslam\Utility\Logger\Logger;
use App\Models\Master\Tenant;
use NazmulIslam\Utility\Queue\QueueRabbitMQ;
use Microsoft\Graph\Generated\Models\Approval;
use ParagonIE\ConstantTime\Hex;
use PHPMailer\PHPMailer\PHPMailer;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Description of Utility
 *
 * @author nazmulislam
 */
class Utility
{
    static $sort_by_param;

    static function sendWebsocketNotification(array $data)
    {
        $socketComponent = new \NazmulIslam\Utility\WebSocket\WebSocket();
        $socketComponent->sendNotifications([$data]);
    }




    /**
     *
     * @param string $file
     * @param array $data
     * @return string
     * @throws \Exception
     */


    public static function emailTemplate(string $file, array $data = []): string
    {
        if (empty($file)) {
            throw new \Exception('Param $path cannot be empty');
        }

        if (strpos($file, '.php') === FALSE) {
            $file = $file . '.php';
        }

        if (!\file_exists(__DIR__ . '/../Templates/Email/' . $file)) {
            throw new \Exception('The Template Path: ' . $file . ' does not exist');
        }
        \ob_start();
        include(__DIR__ . '/../Templates/Email/' . $file);

        return ob_get_clean();
    }

    public static function pdfTemplate(string $file, array $data = []): string
    {
        if (empty($file)) {
            throw new \Exception('Param $path cannot be empty');
        }

        if (strpos($file, '.php') === FALSE) {
            $file = $file . '.php';
        }

        if (!\file_exists(__DIR__ . '/../Templates/Pdf/' . $file)) {
            throw new \Exception('The Template Path: ' . $file . ' does not exist');
        }
        \ob_start();
        include(__DIR__ . '/../Templates/Pdf/' . $file);

        return ob_get_clean();
    }

    static public function getEmailContentMessageForIdentifier(string $identifier, array $emailObjects): string
    {
        $emailTemplates = \App\Models\App\EmailTemplates::select('id', 'template')->where('identifier', $identifier)->first();
        if (isset($emailTemplates) && !empty($emailTemplates)) {
            /**
             * Covert message variables to actual dynamic values.
             */
            $template = isset($emailTemplates->template) ? $emailTemplates->template : '';
            if (isset($template) && !empty($template) && isset($emailObjects) && count($emailObjects) > 0) {
                foreach ($emailObjects as $key => $value) {
                    //do not use replace function for keys containing array
                    if (isset($value) && $value != NULL && is_string($value)) {
                        $template = str_replace('{{' . $key . '}}', $value, $template);
                    }
                }
                return $template;
            }
        }
        return '';
    }




    /**
     * @Description : Convert Number into words
     * @param $num
     * @return string
     */
    function numberToWords($num)
    {
        $ones = [
            0 => "ZERO",
            1 => "ONE",
            2 => "TWO",
            3 => "THREE",
            4 => "FOUR",
            5 => "FIVE",
            6 => "SIX",
            7 => "SEVEN",
            8 => "EIGHT",
            9 => "NINE",
            10 => "TEN",
            11 => "ELEVEN",
            12 => "TWELVE",
            13 => "THIRTEEN",
            14 => "FOURTEEN",
            15 => "FIFTEEN",
            16 => "SIXTEEN",
            17 => "SEVENTEEN",
            18 => "EIGHTEEN",
            19 => "NINETEEN",
            "014" => "FOURTEEN"
        ];
        $tens = [
            0 => "ZERO",
            1 => "TEN",
            2 => "TWENTY",
            3 => "THIRTY",
            4 => "FORTY",
            5 => "FIFTY",
            6 => "SIXTY",
            7 => "SEVENTY",
            8 => "EIGHTY",
            9 => "NINETY"
        ];
        $hundreds = [
            "HUNDRED",
            "THOUSAND",
            "MILLION",
            "BILLION",
            "TRILLION",
            "QUARDRILLION"
        ]; /* limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {

            while (substr($i, 0, 1) == "0")
                $i = substr($i, 1, 5);
            if ($i < 20) {
                /* echo "getting:".$i; */
                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0")
                    $rettxt .= $tens[substr($i, 0, 1)];
                if (substr($i, 1, 1) != "0")
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
            } else {
                if (substr($i, 0, 1) != "0")
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                if (substr($i, 1, 1) != "0")
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                if (substr($i, 2, 1) != "0")
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " and ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt;
    }

    public static function getAlphanumericString(string $data): string
    {
        return preg_replace('/[^a-zA-Z0-9]+/', '', strtolower($data));
    }

    /**
     * convert bytes to readable format
     * @param type $bytes
     * @param type $precision
     * @return type
     */
    static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    static function uniqueCode(int $limit = 8)
    {
        return substr(base_convert(sha1(uniqid((string)mt_rand())), 16, 36), 0, $limit);
    }


    function json_validator($data = NULL)
    {
        if (!empty($data)) {
            json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    public static function getArray($eloquent): array
    {
        return (isset($eloquent) && !empty($eloquent)) ? $eloquent->toArray() : [];
    }

    /**
     *  @date input date
     *  @dateFormat pass date format
     */
    static function setDateTimeAsPerTimeZone(\DateTime $date, \DateTimeZone $timeZone, string $dateFormat = 'Y-m-d H:i:s')
    {
        return $date->setTimezone($timeZone)->format($dateFormat);
    }




    static public function sendTestEmailInBackground($message)
    {

        Queue::addToQueue([
            'to' => ConstantValues::NO_REPLY_EMAIL,
            'subject' => 'Test Email',
            'message' => $message
        ], $queue = 'Email', '\\App\\Jobs\\SendEmailJob', $_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], $database = 0);

        /**
         * @todo needs to be uncommented after old queue removed
         */
        // $jobArray = array(
        //     'class' => '\\App\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => ConstantValues::NO_REPLY_EMAIL,
        //         'subject' => 'Test Email',
        //         'message' => $message
        //     ]
        // );
        // QueueRabbitMQ::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);
    }

    static public function sendTestEmail($message)
    {
        \App\Services\Email\SendEmailService::sendEmail([
            'to' => ConstantValues::NO_REPLY_EMAIL,
            'subject' => 'Test Email',
            'message' => $message
        ], new PHPMailer());
    }

    static public function stringReplace(string $search, string $replace, string $subject): string
    {
        return str_replace($search,  $replace, $subject);
    }

    static public function sendEmailToUser(User $userModel, array $emailData, array $additionalData = []): void
    {

        $emailObjects = DynamicEmail::getDynamicEmailContentForMessage($emailData, $userModel, $additionalData);
        Queue::addToQueue([
            'to' => isset($userModel->username) ? $userModel->username : '',
            'subject' => isset($emailObjects['subject']) ? $emailObjects['subject'] : '',
            'message' => isset($emailObjects['message']) ? $emailObjects['message'] : '',
        ], $queue = 'Email', '\\App\\Jobs\\SendEmailJob', $_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], $database = 0);

        /**
         * @todo needs to be uncommented after old queue removed
         */
        // $jobArray = array(
        //     'class' => '\\App\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => isset($userModel->username) ? $userModel->username : '',
        //         'subject' => isset($emailObjects['subject']) ? $emailObjects['subject'] : '',
        //         'message' => isset($emailObjects['message']) ? $emailObjects['message'] : '',
        //     ]
        // );
        // QueueRabbitMQ::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);
    }

    static public function createGuidForTableIds($model, $field): string
    {


        $guid = self::getGUID();


        while (!!$model->where($field, $guid)->first()) {


            $guid =  self::getGUID();
        }

        return $guid;
    }

    static public function getGUID(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    static public function getEmployeeNumber(): string
    {
        return sprintf(
            '%04X%04X%04X%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535)
        );
    }

    function create_guid()
    { // Create GUID (Globally Unique Identifier)
        $guid = '';
        $namespace = rand(11111, 99999);
        $uid = uniqid('', true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash,  0,  8) . '-' .
            substr($hash,  8,  4) . '-' .
            substr($hash, 12,  4) . '-' .
            substr($hash, 16,  4) . '-' .
            substr($hash, 20, 12);
        return $guid;
    }

    static function getTenantName($host)
    {

        // extract username
        if (!empty($host)) {
            $hostInfo = explode('.', $host);
            return array_shift($hostInfo);
        }
    }


    static function setSaasDBHostname(string|null $tenant): string
    {

        if (isset($tenant) && !empty($tenant)) {
            $tenant = Tenant::select(['*'])->where('tenant_account_name', $tenant)->first();
            if (isset($tenant->tenant_db_name) && !empty($tenant->tenant_db_name)) {
                // Logger::debug('using dynamic database '.$tenant->tenant_db_name);
                return $tenant->tenant_db_name;
            } else {
                //Logger::debug('using default database '.$_ENV['DB_NAME']);
                return $_ENV['DB_NAME'];
            }
        }
        //Logger::debug('using default database '.$_ENV['DB_NAME']);
        return $_ENV['DB_NAME'];
    }


    static function getRealIPAddr()
    {
        //check ip from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        //to check ip is pass from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    static function getTenantFromGlobals(): string
    {

        if (strtolower(php_sapi_name()) === 'cli') {
            $dbName = DB::connection('app')->getDatabaseName();

            $tenantName = explode('_', $dbName);

            return isset($tenantName[1]) ? $tenantName[1] : '';
        } else {
            return isset($GLOBALS['TENANT'])  ? $GLOBALS['TENANT'] : '';
        }
    }

    static function getCountryFullName($countryCode)
    {
        $countryNames = array(
            "AF" => "Afghanistan",
            "AX" => "Åland Islands",
            "AL" => "Albania",
            "DZ" => "Algeria",
            "AS" => "American Samoa",
            "AD" => "Andorra",
            "AO" => "Angola",
            "AI" => "Anguilla",
            "AQ" => "Antarctica",
            "AG" => "Antigua and Barbuda",
            "AR" => "Argentina",
            "AM" => "Armenia",
            "AW" => "Aruba",
            "AU" => "Australia",
            "AT" => "Austria",
            "AZ" => "Azerbaijan",
            "BS" => "Bahamas",
            "BH" => "Bahrain",
            "BD" => "Bangladesh",
            "BB" => "Barbados",
            "BY" => "Belarus",
            "BE" => "Belgium",
            "BZ" => "Belize",
            "BJ" => "Benin",
            "BM" => "Bermuda",
            "BT" => "Bhutan",
            "BO" => "Bolivia",
            "BA" => "Bosnia and Herzegovina",
            "BW" => "Botswana",
            "BV" => "Bouvet Island",
            "BR" => "Brazil",
            "IO" => "British Indian Ocean Territory",
            "BN" => "Brunei Darussalam",
            "BG" => "Bulgaria",
            "BF" => "Burkina Faso",
            "BI" => "Burundi",
            "KH" => "Cambodia",
            "CM" => "Cameroon",
            "CA" => "Canada",
            "CV" => "Cape Verde",
            "KY" => "Cayman Islands",
            "CF" => "Central African Republic",
            "TD" => "Chad",
            "CL" => "Chile",
            "CN" => "China",
            "CX" => "Christmas Island",
            "CC" => "Cocos (Keeling) Islands",
            "CO" => "Colombia",
            "KM" => "Comoros",
            "CG" => "Congo",
            "CD" => "Congo, Democratic Republic of the",
            "CK" => "Cook Islands",
            "CR" => "Costa Rica",
            "CI" => "Côte d'Ivoire",
            "HR" => "Croatia",
            "CU" => "Cuba",
            "CY" => "Cyprus",
            "CZ" => "Czech Republic",
            "DK" => "Denmark",
            "DJ" => "Djibouti",
            "DM" => "Dominica",
            "DO" => "Dominican Republic",
            "EC" => "Ecuador",
            "EG" => "Egypt",
            "SV" => "El Salvador",
            "GQ" => "Equatorial Guinea",
            "ER" => "Eritrea",
            "EE" => "Estonia",
            "ET" => "Ethiopia",
            "FK" => "Falkland Islands (Malvinas)",
            "FO" => "Faroe Islands",
            "FJ" => "Fiji",
            "FI" => "Finland",
            "FR" => "France",
            "GF" => "French Guiana",
            "PF" => "French Polynesia",
            "TF" => "French Southern Territories",
            "GA" => "Gabon",
            "GM" => "Gambia",
            "GE" => "Georgia",
            "DE" => "Germany",
            "GH" => "Ghana",
            "GI" => "Gibraltar",
            "GR" => "Greece",
            "GL" => "Greenland",
            "GD" => "Grenada",
            "GP" => "Guadeloupe",
            "GU" => "Guam",
            "GT" => "Guatemala",
            "GG" => "Guernsey",
            "GN" => "Guinea",
            "GW" => "Guinea-Bissau",
            "GY" => "Guyana",
            "HT" => "Haiti",
            "HM" => "Heard Island and McDonald Islands",
            "VA" => "Holy See (Vatican City State)",
            "HN" => "Honduras",
            "HK" => "Hong Kong",
            "HU" => "Hungary",
            "IS" => "Iceland",
            "IN" => "India",
            "ID" => "Indonesia",
            "IR" => "Iran, Islamic Republic of",
            "IQ" => "Iraq",
            "IE" => "Ireland",
            "IM" => "Isle of Man",
            "IL" => "Israel",
            "IT" => "Italy",
            "JM" => "Jamaica",
            "JP" => "Japan",
            "JE" => "Jersey",
            "JO" => "Jordan",
            "KZ" => "Kazakhstan",
            "KE" => "Kenya",
            "KI" => "Kiribati",
            "KP" => "Korea, Democratic People's Republic of",
            "KR" => "Korea, Republic of",
            "KW" => "Kuwait",
            "KG" => "Kyrgyzstan",
            "LA" => "Lao People's Democratic Republic",
            "LV" => "Latvia",
            "LB" => "Lebanon",
            "LS" => "Lesotho",
            "LR" => "Liberia",
            "LY" => "Libyan Arab Jamahiriya",
            "LI" => "Liechtenstein",
            "LT" => "Lithuania",
            "LU" => "Luxembourg",
            "MO" => "Macao",
            "MK" => "Macedonia, the former Yugoslav Republic of",
            "MG" => "Madagascar",
            "MW" => "Malawi",
            "MY" => "Malaysia",
            "MV" => "Maldives",
            "ML" => "Mali",
            "MT" => "Malta",
            "MH" => "Marshall Islands",
            "MQ" => "Martinique",
            "MR" => "Mauritania",
            "MU" => "Mauritius",
            "YT" => "Mayotte",
            "MX" => "Mexico",
            "FM" => "Micronesia, Federated States of",
            "MD" => "Moldova, Republic of",
            "MC" => "Monaco",
            "MN" => "Mongolia",
            "ME" => "Montenegro",
            "MS" => "Montserrat",
            "MA" => "Morocco",
            "MZ" => "Mozambique",
            "MM" => "Myanmar",
            "NA" => "Namibia",
            "NR" => "Nauru",
            "NP" => "Nepal",
            "NL" => "Netherlands",
            "AN" => "Netherlands Antilles",
            "NC" => "New Caledonia",
            "NZ" => "New Zealand",
            "NI" => "Nicaragua",
            "NE" => "Niger",
            "NG" => "Nigeria",
            "NU" => "Niue",
            "NF" => "Norfolk Island",
            "MP" => "Northern Mariana Islands",
            "NO" => "Norway",
            "OM" => "Oman",
            "PK" => "Pakistan",
            "PW" => "Palau",
            "PS" => "Palestinian Territory, Occupied",
            "PA" => "Panama",
            "PG" => "Papua New Guinea",
            "PY" => "Paraguay",
            "PE" => "Peru",
            "PH" => "Philippines",
            "PN" => "Pitcairn",
            "PL" => "Poland",
            "PT" => "Portugal",
            "PR" => "Puerto Rico",
            "QA" => "Qatar",
            "RE" => "Réunion",
            "RO" => "Romania",
            "RU" => "Russian Federation",
            "RW" => "Rwanda",
            "SH" => "Saint Helena",
            "KN" => "Saint Kitts and Nevis",
            "LC" => "Saint Lucia",
            "PM" => "Saint Pierre and Miquelon",
            "VC" => "Saint Vincent and the Grenadines",
            "WS" => "Samoa",
            "SM" => "San Marino",
            "ST" => "Sao Tome and Principe",
            "SA" => "Saudi Arabia",
            "SN" => "Senegal",
            "RS" => "Serbia",
            "SC" => "Seychelles",
            "SL" => "Sierra Leone",
            "SG" => "Singapore",
            "SK" => "Slovakia",
            "SI" => "Slovenia",
            "SB" => "Solomon Islands",
            "SO" => "Somalia",
            "ZA" => "South Africa",
            "GS" => "South Georgia and the South Sandwich Islands",
            "ES" => "Spain",
            "LK" => "Sri Lanka",
            "SD" => "Sudan",
            "SR" => "Suriname",
            "SJ" => "Svalbard and Jan Mayen",
            "SZ" => "Swaziland",
            "SE" => "Sweden",
            "CH" => "Switzerland",
            "SY" => "Syrian Arab Republic",
            "TW" => "Taiwan, Province of China",
            "TJ" => "Tajikistan",
            "TZ" => "Tanzania, United Republic of",
            "TH" => "Thailand",
            "TL" => "Timor-Leste",
            "TG" => "Togo",
            "TK" => "Tokelau",
            "TO" => "Tonga",
            "TT" => "Trinidad and Tobago",
            "TN" => "Tunisia",
            "TR" => "Turkey",
            "TM" => "Turkmenistan",
            "TC" => "Turks and Caicos Islands",
            "TV" => "Tuvalu",
            "UG" => "Uganda",
            "UA" => "Ukraine",
            "AE" => "United Arab Emirates",
            "GB" => "United Kingdom",
            "US" => "United States",
            "UM" => "United States Minor Outlying Islands",
            "UY" => "Uruguay",
            "UZ" => "Uzbekistan",
            "VU" => "Vanuatu",
            "VE" => "Venezuela",
            "VN" => "Viet Nam",
            "VG" => "Virgin Islands, British",
            "VI" => "Virgin Islands, U.S.",
            "WF" => "Wallis and Futuna",
            "EH" => "Western Sahara",
            "YE" => "Yemen",
            "ZM" => "Zambia",
            "ZW" => "Zimbabwe"
        );

        if (array_key_exists($countryCode, $countryNames)) {
            return $countryNames[$countryCode];
        } else {
            return "Unknown";
        }
    }

    static function tenantEncryptionKey($tenantName = null): null|string
    {
        $encryptionKey = null;

        $tenant = \App\Models\Master\Tenant::where('tenant_account_name', $tenantName ?? $GLOBALS['TENANT'])->first();


        if ($tenant != null && $tenant->tenant_configuration != null) {

            $tenantConfigArray = json_decode($tenant->tenant_configuration, true);

            if (array_key_exists('hasOwnEncryptionKey', $tenantConfigArray)) {


                if (array_key_exists('encryptionKey', $tenantConfigArray)) {
                    $encryptionKey = $tenantConfigArray['encryptionKey'];
                }
            }
        }


        return $encryptionKey;
    }

    // FUNCTIONS FOR ENCRYPTION API CALLS 
    public function generateRandomHexKeys(Request $request, Response $response)
    {
        $keyLength = 32;
        $bytes = random_bytes($keyLength);
        $data = ['unique_encryption_key' => Hex::encode($bytes)];
        $response->getBody()->write((string)json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    public function updateEncryptionKey(Request $request, Response $response)
    {
        $tenant = \App\Models\Master\Tenant::where('tenant_account_name', $GLOBALS['TENANT'])->first();

        $checkAllowed = false;

        $tenantConfigArray = [];

        if ($tenant != null && $tenant->tenant_configuration != null) {

            $tenantConfigArray = json_decode($tenant->tenant_configuration, true);

            if (array_key_exists('hasOwnEncryptionKey', $tenantConfigArray)) {
                $checkAllowed = true;
            }
        }

        if (!$checkAllowed) {
            $response->getBody()->write((string)json_encode(['success' => false, 'message' => 'You are not allowed to have your own ecryption key']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $body = $request->getParsedBody();
        $queryParams = $request->getQueryParams();
        $input = array_merge($body ?? [], $queryParams ?? []);
        $newKey = $input['newKey'];
        Logger::debug('request all', [$input]);


        if ($newKey == null) {
            $response->getBody()->write((string)json_encode(['success' => false, 'message' => 'Encryption key not provided']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        if (!$this->validateRandomHexString($newKey)) {

            $response->getBody()->write((string)json_encode(['success' => false, 'message' => 'Encryption key is not valid']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        if ($this::tenantEncryptionKey() === null || $this::tenantEncryptionKey() === "") {

            $response->getBody()->write((string)json_encode(['success' => false, 'message' => 'You do not own a seperate ']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }

        $stringModels = $this->fetchAllEncryptedModels();
        $cipherSweetMethods = new Methods();
        $cipherSweetMethods->backupBlindIndexes();
        $cipherSweetMethods->backupOldEncryptedData($stringModels);

        DB::connection('app')->beginTransaction();

        try {

            $this->updateModels($this::tenantEncryptionKey(), $newKey);
            DB::connection('app')->commit();

            $tenantConfigArray['encryptionKey'] = $newKey;

            \App\Models\Master\Tenant::where('tenant_account_name', $GLOBALS['TENANT'])->update([
                'tenant_configuration' => json_encode($tenantConfigArray)
            ]);

            $response->getBody()->write((string)json_encode(['success' => true, 'message' => 'key updated successfully']));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (\Exception $ex) {
            Logger::error('Cauaght Exception error  on ' . gethostname() . ' ' . $ex->getMessage(), (array) $ex->getTraceAsString());

            Db::connection('app')->rollBack();

            $response->getBody()->write((string)json_encode(['success' => false, 'message' => 'failed to updated the key']));

            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
        // if (is_array($data)) {
        //     $response->getBody()->write((string)json_encode($data));
        // }

        // return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    private function validateRandomHexString($value)
    {
        // Check if the length is 64 characters (32 bytes * 2 characters per byte)
        if (strlen($value) !== 64) {
            return false;
        }

        // Check if the value is a valid hexadecimal string
        if (!ctype_xdigit($value)) {
            return false;
        }

        return true;
    }

    private function fetchAllEncryptedModels()
    {
        return DB::connection('app')->table('blind_indexes')
            ->distinct('indexable_type')
            ->pluck('indexable_type')
            ->toArray();
    }

    private function updateModels($oldKey, $newKey)
    {
        $systemModels = DB::connection('app')
            ->table('encryption_models')
            ->select(['model_name', 'model_path', 'encryption_model_id'])
            ->get();

        $modelsEncrypted = [];
        foreach ($systemModels ?? [] as $model) {


            if ((new $model->model_path)->count()) {

                $modelsEncrypted[] = $model->model_path;

                $tableName = (new $model->model_path)->getTable(); // Get the table name associated with the model

                // Get all column names for the table
                $columns = DB::connection('app')->select("SHOW COLUMNS FROM $tableName");

                // Extract column names from the result
                $columnNames = array_column($columns, 'Field');

                $columnsToEncrypt = DB::connection('app')
                    ->table('encryption_model_column_details')
                    ->where('encryption_model_id', $model->encryption_model_id)
                    ->pluck('column_name')
                    ->toArray();

                // Find the columns that are the same in both arrays
                $commonColumns = array_intersect($columnNames, $columnsToEncrypt);

                if (count($commonColumns)) {

                    $encryptModels = new EncryptModel($model->model_path, $newKey, $oldKey);
                    $encryptModels->handle();

                    DB::connection('app')
                        ->table('encryption_model_column_details')
                        ->where('encryption_model_id', $model->encryption_model_id)
                        ->update(['is_encrypted' => 1]);


                    DB::connection('app')
                        ->table('encryption_models')
                        ->where('model_path', $model->model_path)
                        ->update(['is_encrypted' => 1]);
                }

                $jsonEncodedModels = json_encode($modelsEncrypted);

                DB::connection('app')->table('encryption_history')
                    ->where('old_env_key', $oldKey)
                    ->update(['model_names' => $jsonEncodedModels]);
            }
        }
    }

    public static function getApplicantRoleId(): int|null
    {
        try {

            $tenant = Tenant::with('TenantSubscription')->where('tenant_account_name', $GLOBALS['TENANT'])->firstOrFail();
    
            if (!$tenant || !$tenant->TenantSubscription) {
                return null;
            }
    
            $productIds = $tenant->TenantSubscription->tenantSubscriptionProductIds();
    
            if (empty($productIds)) {
                return null;
            }
    
            $applicantPolicy = MasterProductPolicy::whereIn('product_id', $productIds)
                ->where('master_product_policy_identifier', ConstantValues::HR_APPLICANT_POLICY_IDENTIFIER)
                ->first();
    
            if (!$applicantPolicy) {
                return null;
            }
    
            $rolePolicyRoleIds = RolePolicy::where('master_product_policy_id', $applicantPolicy->master_product_policy_id)->pluck('role_id')->toArray();
    
            if (empty($rolePolicyRoleIds)) {
                return null;
            }
    
            $applicantPolicyRole = Role::whereIn('role_id', $rolePolicyRoleIds)->first();
    
            return $applicantPolicyRole ? $applicantPolicyRole->role_id : null;
        } catch (\Exception $e) {

            return null;
        }
    }

    public static function isUserApplicant($userId): bool
    {
        $user = User::select('role_id')->whereUserId($userId)->first();
        $policyIds = RolePolicy::whereRoleId($user->role_id)->pluck('master_product_policy_id')->toArray();
        $checkApplicantPolicy =  MasterProductPolicy::whereIn('master_product_policy_id', $policyIds)
        ->where('master_product_policy_identifier', ConstantValues::HR_APPLICANT_POLICY_IDENTIFIER)
        ->exists();
        return $checkApplicantPolicy;
    }
    
    public static function setModelInApprovalProcess($approvalProcessId,$modelPrimaryKey,$modelClass) {
        if($approvalProcessId !== null){

            ApprovalProcess::where('approval_process_id', $approvalProcessId)->update([
                'model_id' => $modelPrimaryKey,
                'model_class' => $modelClass,
            ]);
        }
    }

    public static function getAllKeysFromEmail(string $body, array $replacements)
    {
        if(isset($body) && $body !== ""){

            $result = preg_replace_callback('/\[(.*?)\]/', function ($matches) use ($replacements) {
                $word = $matches[1];
    
                // Check if a replacement exists for the word
                if (array_key_exists($word, $replacements)) {
                    // return '<'.$replacements[$word].'>';
                    return $replacements[$word];
                }
    
                // If no replacement found, keep the original word
                return $word;
            }, $body);
    
            $text = nl2br($result);
    
            return $text;
        } else {
            return $body;
        }
    }

    public static function createProcessAndReturnId($type, $modelId, $modelClass, $jobRoleId = null)
    {
        $query = ApprovalProcessHrDepartmentJobRoleModuleDetail::query();

        $query->where('type', $type);

        if ($jobRoleId !== null) {
            $query->where('department_job_role_id', $jobRoleId);
        }

        $checkProcess = $query->first();

        if ($checkProcess != null && $checkProcess->approval_process_id) {

            $approvalProcess = ApprovalProcess::where('approval_process_id', $checkProcess->approval_process_id)->first();
            $approvalProcessDetails = ApprovalProcessDetail::where('approval_process_id', $checkProcess->approval_process_id)->get();

            if ($approvalProcess != null && $approvalProcessDetails->isNotEmpty()) {

                $clonedProcess = $approvalProcess->replicate();
                $clonedProcess->model_id = $modelId;
                $clonedProcess->model_class = $modelClass;
                $clonedProcess->is_template = 'NO'; 
                $clonedProcess->save();

                foreach ($approvalProcessDetails as $detail) {
                    $clonedDetail = $detail->replicate();
                    $clonedDetail->approval_process_id = $clonedProcess->approval_process_id;
                    $clonedDetail->save();
                }

                return $clonedProcess->approval_process_id;
            }
        }

        return null;
    }
    
    
}
