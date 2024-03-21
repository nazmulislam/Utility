<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Components;

use NazmulIslam\Utility\Domain\User\UserRepository;
use NazmulIslam\Utility\Domain\User\UserService;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\User;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\UserBruteForceAttempt;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\UserLockedAccount;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\UserLoginHistory;
use NazmulIslam\Utility\Utility\Email\EmailTemplate;
use Carbon\Carbon;
use NazmulIslam\Utility\Utility\GEOIP\GEOIP;
use NazmulIslam\Utility\Utility\Queue\Queue;

class LoginActivityComponent extends BaseComponent
{
    public int $userId;
    public string $userGuid;
    private int $score = 0;
    private ?string $countryCode;
    private ?array $browser = [];
    private ?string $ipAddress;
    private User $user;
    const LOCKED_SUSPICIOUS_LOGIN = 2;
    const SUSPECTED_SUSPICIOUS_LOGIN = 1;
    const VALID_LOGIN = 0;

    public function __construct()
    {
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function setUserGuid(string $userGuid)
    {
        $this->userGuid = $userGuid;
    }

    /**
     * Checks if the login isn't suspicous by adding up scores and returning a value
     * returns 0 if not suspicious
     * return 1 if suspicous but account not locked
     * returns 2 if susipicioous and account locked immediately
     */
    public function isLoginSafe(UserService $userService, UserRepository $userRepository): int
    {
        $this->ipAddress = $_SERVER['REMOTE_ADDR'];
        $this->user = $userService->getUserByGuid(guid: $this->userGuid, fields: ['user.username', 'user.first_name', 'user.last_name'], userRepository: $userRepository);
        $this->setUserId($this->user->user_id);

        //If users has never logged in before then skip the suspicious login logic
        $hasLoggedInBefore = UserLoginHistory::where('user_id', $this->userId)->first();


        if (!$hasLoggedInBefore) {
            return true;
        }

        $this->calculateScore();
        return $this->processScore();
        //Score

    }

    private function processScore(): int
    {
        //         $token = uniqid();
        //         $lockedAccount = new UserLockedAccount();
        //            $lockedAccount->token = $token;
        //            $lockedAccount->user_id = $this->userId;
        //            $lockedAccount->save();
        //        $this->sendSuspicousLockedEmail($token);

        return self::LOCKED_SUSPICIOUS_LOGIN;

        if ($this->score >= 60) {
            //lock account
            $token = uniqid();
            $lockedAccount = new UserLockedAccount();
            $lockedAccount->token = $token;
            $lockedAccount->user_id = $this->userId;
            $lockedAccount->save();


            $this->sendSuspicousLockedEmail(token: $token);

            return self::LOCKED_SUSPICIOUS_LOGIN;
            //send locked account email
        } else if ($this->score >= 15 && $this->score < 60) {
            $this->sendSuspiciousWarningEmail();

            return self::SUSPECTED_SUSPICIOUS_LOGIN;
        }
        return self::VALID_LOGIN;
    }

    private function sendSuspiciousWarningEmail(): void
    {
        $templateData = [];
        $time = Carbon::now();
        $templateData['date'] = $time->toDayDateTimeString();
        $templateData['first_name'] = $this->user->first_name;
        $templateData['user_name'] = $this->user->user_name;

        $templateData['country'] = GEOIP::getCountryFromIPAddress(ipAddress: $this->ipAddress);
        $templateData['browser'] = $this->browser;
        $templateData['ip_address'] = $this->ipAddress;
        $templateData['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;
        $template  = EmailTemplate::emailTemplate('User/suspicious_login_warning', $templateData);


        Queue::addToQueue([
            'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailJob',
            'args' => [
                'to' => $this->user->username,
                'subject' => 'Suspicous Login',
                'message' => $template,
            ]

        ], RABBITMQ_QUEUE_NAME);
    }

    private function sendSuspicousLockedEmail(string $token): void
    {
        $this->user->failed_logins_data = json_encode([
            'datetime' => strtotime("now"),
            'ipaddress' => $this->ipAddress,
            'browser' =>  $this->browser,
            'duration' => 'N/A',
            'status' => 'Permanent lock',
        ]);
        $this->user->failed_login_dates = $this->user->failed_login_dates . ',' . strtotime("now");
        $this->user->save();



        $templateData = [];
        $time = Carbon::now();
        $templateData['date'] = $time->toDayDateTimeString();
        $templateData['first_name'] = $this->user->first_name;
        $templateData['user_name'] = $this->user->username;
        $templateData['unlockLink'] = $GLOBALS['TENANT_INFO']['TENANT_HOST'] . '/unlock-account/' . $token;
        $templateData['tracking_code'] = $token;
        $templateData['browser'] = $this->browser;
        $templateData['ip_address'] = $this->ipAddress;
        $templateData['country'] = GEOIP::getCountryFromIPAddress(ipAddress: $this->ipAddress);
        $templateData['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;
        $template  = EmailTemplate::emailTemplate('User/suspicious_login_block.php', $templateData);


        Queue::addToQueue([
            'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailJob',
            'args' => [
                'to' => $this->user->username,
                'subject' => 'Suspicous Login - Locked',
                'message' => $template,
            ]

        ], RABBITMQ_QUEUE_NAME);
    }

    /**
     * Checks the database for users allowed ip addresses
     */
    private function isSameIp()
    {
        $userIps = UserLoginHistory::where([['ip_address', '=', $this->ipAddress], ['user_id', '=', $this->userId]])->first();
        if ($userIps !== null) {
            return true;
        } else {
            return false;
        }
    }

    private function isSameBrowser()
    {
        $loginStats = UserLoginHistory::where([['browser', '=', $this->browser], ['user_id', '=', $this->userId]])->orderBy('user_login_history_id', 'desc')->first();
        if ($loginStats !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the first part of the subnet is found e.g. (86).10.23.1
     * @param $this->ipAddress
     * @return bool
     */
    private function isSameSubnet()
    {
        $ipParts = explode(".", $this->ipAddress);
        $query = $ipParts[0] . '%';
        $userIps = UserLoginHistory::where([['ip_address', 'LIKE', $query], ['user_id', '=', $this->userId]])->get();
        if (count($userIps) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function isSameCountry()
    {

        if ($this->countryCode != null) {
            $userIp = UserLoginHistory::where([['country_name', '=', $this->countryCode], ['user_id', '=', $this->userId]])->get();
            if (count($userIp) == 0) {
                return false;
            } else {
                return true;
            }
        }
        if (IS_DEVELOPMENT) {
            return true;
        }
        return false;
    }

    /**
     * @TODO use user_brute_force_attempt for failed login
     */
    private function failedLoginAttempts($attempts)
    {
        $failedAttempts = UserBruteForceAttempt::where([['user_id', '=', $this->userId], ['failed_login_count', '>=', $attempts]])->first();

        if ($failedAttempts === null) {
            return true;
        } else {
            return false;
        }
    }

    private function getCountry()
    {
        $curlUrl = 'https://www.iplocate.io/api/lookup/' . $this->ipAddress;
        $ch = curl_init();
        //step2
        curl_setopt($ch, CURLOPT_URL, $curlUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //step3
        $result = json_decode(curl_exec($ch), true);
        //step4
        curl_close($ch);
        return $result['country'];
    }

    private function calculateScore(): void
    {

        if (!$this->isSameIp($this->ipAddress)) {
            $this->score += 5;
        }

        if (!$this->isSameSubnet($this->ipAddress)) {
            $this->score += 10;
        }
        $this->countryCode = $this->getCountry($this->ipAddress);
        if (!$this->isSameCountry()) {
            $this->score += 100;
        }
        if (!$this->failedLoginAttempts(5)) {
            $this->score += 50;
        }
        $this->browser = \NazmulIslam\Utility\Utility\Browser\Browser::getBrowserFromUserAgent($_SERVER);
        if (!$this->isSameBrowser($this->browser['browser'])) {
            $this->score += 10;
        }
        \NazmulIslam\Utility\Utility\Logger\Logger::debug('score', [$this->score]);
    }
}
