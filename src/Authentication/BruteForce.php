<?php
declare(strict_types=1);

namespace  NazmulIslam\Utility\Authentication;

use NazmulIslam\Utility\Models\NazmulIslam\Utility\User;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\UserBruteForce;
use NazmulIslam\Utility\Browser\Browser;
use NazmulIslam\Utility\Components\LoginActivityComponent;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\PlatformSetting;
use NazmulIslam\Utility\GEOIP\GEOIP;
use NazmulIslam\Utility\Logger\Logger;
use NazmulIslam\Utility\Queue\Queue;

/**
 * Class to handle authentication activities
 * Class Authentication
 * @package NazmulIslam\Utility\Domain\Authentication
 */
class BruteForce
{
    private $userBruteForce;
    private $user;
    private $password;

    public function setUser(User $user):void
    {
        $this->user = $user;
    }
    public function setPassword(string $passwordText):void
    {
        $this->password = $passwordText;
    }
    function checkIfUserPermanentBlock($failed_logins_dates, $permanentLockMessage) :array {
        if(is_array($failed_logins_dates) && count($failed_logins_dates) > 1)
        {
            $output = [
                    'status' => false,
                    'error' => [
                        'message' => $permanentLockMessage,
                        'permenant_block' => true
                    ],
                ];
            return $output;
        }
        return [];
    }
    
    function checkIfUserTemporarilyBlock(array $failed_logins_dates, int $lockDuration, int $loginAttemptCount, string $temporaryLockMessage) :array 
    {
        
        if(is_array($failed_logins_dates) && count($failed_logins_dates) == 1)
        {
            
            $fail_date = (isset($failed_logins_dates[0]) && !empty($failed_logins_dates[0]))? date('Y-m-d H:i:s', intval($failed_logins_dates[0])):null;
            
            if(isset($fail_date)) {
                
                $newTime = date("Y-m-d H:i:s",strtotime($fail_date." +". intval($lockDuration) ." minutes"));
                $nowTime = date("Y-m-d H:i:s");
                
                if ($nowTime > $newTime && intval($this->user['failed_login_count']) >= $loginAttemptCount) {
                    
                    $this->user['failed_login_count'] = null;
                    $this->user->save();
                }
                else if(intval($this->user['failed_login_count']) >= $loginAttemptCount) {
                    
                    $output = [
                            'status' => false,
                            'error' => [
                                'message' => $temporaryLockMessage,
                            'lock' => [
                                'temporary_block' => true,
                                'expires_in' => $newTime,
                                'try_again_in' => $lockDuration
                            ]
                            ],
                           
                                
                        ];
                    return $output;
                }
            }
        }
        return [];
    }
    
    function checkIfUserBlockStatus($lockDuration, $loginAttemptCount, $temporaryLockMessage, $permanentLockMessage) :array 
    {
        $failed_logins_dates = explode(',', $this->user['failed_login_dates']);
        $output = $this->checkIfUserPermanentBlock($failed_logins_dates, $permanentLockMessage);
        if(!empty($output)) {
            return $output;
        }
        else {
            $output = $this->checkIfUserTemporarilyBlock($failed_logins_dates, $lockDuration, $loginAttemptCount, $temporaryLockMessage);
            if(!empty($output)) {
                return $output;
            }
        }
        return [];
    }
    
    function userForgotPasswordAction() 
    {
        $this->user->password = \NazmulIslam\Utility\Validation\Password::hash(trim($this->password));
        $this->user->update_password = 0;
        $this->user->save();
        $output = [
                        'status' => true,
                        'message' => "Password reset successfully",
                        'error' => [
                            'message' => "",
                        ],
                    ];
        return $output;
    }

    function checkIfUserIsValid() :array {
        //  $isMatched = $this->password === $this->user->password;
        $isMatched = \NazmulIslam\Utility\Validation\Password::validatePassword($this->password, $this->user->password);
        if ($isMatched)
        {
        
//                The username and password is correct. But the login could be suspicious.
//                So checks for a suspicious login first
//                Checks the login was not suspicious
                 $loginActivityComponent = new LoginActivityComponent();
                 $loginActivityComponent->setUserId(userId:(isset($this->user->user_id) ? intval($this->user->user_id) : 0));
                //  $suspicious = $loginActivityComponent->isLoginSafe();
//                 if ($suspicious == 2) {
//                     $output = [
//                         'status' => false,
//                         'error' => [
//                             'message' => "Suspicious Login. Account locked. Please see your email to unlock your account.",
//                         ],
//                     ];
//                     return $output;
//                 }
         
            $this->user['failed_login_count'] = null;
            $this->user['failed_logins_data'] = null;
            $this->user['failed_login_dates'] = null;
            $this->user->save();
            $output = [
                    'status' => true,
                    'error' => [
                        'message' => "",
                    ],
                ];
            return $output;
        }
        return [];
    }
    
    
    function temporarilyBlockUserAction($browser,$currentTime,$ipaddress, $country, $lockDuration, $temporaryLockMessage) {

        $issuedAt = time();
        $seconds = 60;
        $minutes = $lockDuration;
        //Time until access token expires 15 minutes
        $expireTime = $issuedAt + ($minutes * $seconds);
        $failedLoginData = json_encode([
            'datetime' => $currentTime,
            'ipaddress' => $ipaddress,
            'browser' =>  $browser,
            'duration' => $lockDuration,
            'status' => 'Temporary lock',
            "country" => $country
        ]);
        //  $newTime = date("Y-m-d H:i:s", strtotime(" +" . $lockDuration+1 . " minutes"));
        $this->user['failed_login_dates'] = $currentTime;
        $this->user['failed_logins_data'] = $failedLoginData;
        if(isset($this->userBruteForce) || !empty($this->userBruteForce)){
            $this->userBruteForce->failed_login_dates = $currentTime;
            $this->userBruteForce->failed_logins_data = $failedLoginData;
            $this->userBruteForce->save();
        }
        else{
            UserBruteForce::create([
                'user_id' => $this->user['user_id'],
                'failed_login_dates' => $currentTime,
                'failed_logins_data' => $failedLoginData,
            ]);
        }


        $this->user->save();
       $this->sendEmailForTemporaryLock($lockDuration);
        $output = [
                      'status' => false,
                      'error' => [
                          'message' => $temporaryLockMessage,
                            'lock' => [
                                'temporary_block' => true,
                                'expires_in' =>  $expireTime,
                                'try_again_in'=> $lockDuration,
                            ]
                      ],
                  ];
        return $output;
    }
    
    function permanentBlockUserAction($browser,$currentTime,$ipaddress,$country, $permanentLockMessage) {
        $failedLoginData= json_encode([
            'datetime' => $currentTime,
            'ipaddress' => $ipaddress,
            'browser' =>  $browser,
            'duration' => 'N/A',
            'status' => 'Permanent lock',
            '$country' => $country
        ]);
        $this->user['failed_logins_data'] = $failedLoginData;
        $this->user['failed_login_dates'] = $this->user['failed_login_dates'] .','.$currentTime;

        if (isset($this->userBruteForce) || !empty($this->userBruteForce)) {
            $this->userBruteForce->failed_login_dates = $this->user['failed_login_dates'] . ',' . $currentTime;
            $this->userBruteForce->failed_logins_data = $failedLoginData;
            $this->userBruteForce->save();

        } else {
            UserBruteForce::create([
                'user_id' => $this->user['user_id'],
                'failed_login_dates' => $this->user['failed_login_dates'] . ',' . $currentTime,
                'failed_logins_data' => $failedLoginData,
            ]);
        }

        $this->user->save();
        $this->sendEmailForPermanentLock();
        /*$_SESSION['BRUTE_FORCE_PREVENTION'] = [
            'ipAddress'=> $ipaddress,
            'browser'=> $browser,
            'sessionId'=> session_id(),
            'expiresAt' => $newTime = date("Y-m-d H:i:s",strtotime('+10 minutes')),

        ];*/
        $output = [
                      'status' => false,
                      'error' => [
                          'message' => $permanentLockMessage,
                      ],
                  ];
        return $output;
    }

    function inValidUserAction(array $serverVariables, $lockDuration, $loginAttemptCount, $temporaryLockMessage, $permanentLockMessage, $isBruteForceEnabled, $isPermanentBruteForceLock, $loginPermanentAttemptCount) :array {
        if (intval($isBruteForceEnabled) === 1) {
        $browser = isset($_SERVER['HTTP_USER_AGENT']) ? Browser::getBrowserFromUserAgent($_SERVER['HTTP_USER_AGENT']) : null;
        $this->user['failed_login_count'] = intval($this->user['failed_login_count']) + 1;
        $failed_login_count = intval($this->user['failed_login_count']);
        $ipaddress = isset($serverVariables['REMOTE_ADDR'])?$serverVariables['REMOTE_ADDR']:'';
        
        if ($failed_login_count >= $loginAttemptCount) {
           $country =  GEOIP::getCountryFromIPAddress(ipAddress: $ipaddress);
 
           $currentTime = strtotime("now");
          
                if ((isset($this->user['failed_login_dates']) && !empty($this->user['failed_login_dates']))) {
                    if(intval($isPermanentBruteForceLock) === 1){
                  
                            return $this->permanentBlockUserAction($browser, $currentTime, $ipaddress, $country, $permanentLockMessage);
                  
                        
                    }
                } else {
                    return $this->temporarilyBlockUserAction($browser, $currentTime, $ipaddress, $country, $lockDuration, $temporaryLockMessage);
                }
        }
       }
        $this->user->save();
        $output = [
                'status' => false,
                'error' => ['message' => "The email and/or password you entered is incorrect."],
            ];
        return $output;
    }

    private function sendEmailForTemporaryLock($duration): void
    {
        // For user
        $templateDataUser = [];
        $templateDataUser['user_name'] = $this->user->first_name . " " . $this->user->last_name;
        $templateDataUser['email'] = $this->user->email;
        $templateDataUser['duration'] = $duration;
        $templateDataUser['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;
        $templateUser  = \NazmulIslam\Utility\Utility::emailTemplate('User/user-temp-lock', $templateDataUser);

        \NazmulIslam\Utility\Queue\Queue::addToQueue([
            'to' => $this->user->username,
            'subject' => $this->user->first_name . " " . $this->user->last_name .' is temporarily locked out ',
            'message' => $templateUser,
        ], $queue = 'Email', '\\NazmulIslam\Utility\\Jobs\\SendEmailJob', $_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], $database = 0);

        /**
         * @todo needs to be uncommented after old queue removed
         */
        // $jobArray = array(
        //     'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => $this->user->username,
        //         'subject' => $this->user->first_name . " " . $this->user->last_name .' is temporarily locked out ',
        //         'message' => $templateUser,
        //     ]
        // );
        // Queue::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);

        // For Admin
        $platformSettingStudioEmail = PlatformSetting::select('platform_variable_value')->where('platform_variable_key', 'studio_email_address')->first();
        $StudioEmail =  isset($platformSettingStudioEmail) && isset($platformSettingStudioEmail->platform_variable_value) ? json_decode($platformSettingStudioEmail->platform_variable_value) : '';

        $templateDataAdmin = [];
        $templateDataAdmin['user_name'] = $this->user->first_name . " " . $this->user->last_name;
        $templateDataAdmin['email'] = $this->user->email;
        $templateDataAdmin['duration'] = $duration;
        $templateDataAdmin['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;
        $templateAdmin  = \NazmulIslam\Utility\Utility::emailTemplate('Admin/admin-temp-lock', $templateDataAdmin);

        \NazmulIslam\Utility\Queue\Queue::addToQueue([
            'to' => $StudioEmail,
            'subject' => $this->user->first_name . " " . $this->user->last_name . ' is temporarily locked out ',
            'message' => $templateAdmin,
        ], $queue = 'Email',
            '\\NazmulIslam\Utility\\Jobs\\SendEmailJob',
            $_ENV['REDIS_HOST'],
            $_ENV['REDIS_PORT'],
            $database = 0
        );

        /**
         * @todo needs to be uncommented after old queue removed
         */
        // $jobArray = array(
        //     'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => $StudioEmail,
        //         'subject' => $this->user->first_name . " " . $this->user->last_name . ' is temporarily locked out ',
        //         'message' => $templateAdmin,
        //     ]
        // );
        // Queue::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);
    }


    private function sendEmailForPermanentLock(): void
    {
        // For user
        $TemplateData = [];
        $TemplateData['user_name'] = $this->user->first_name . " " . $this->user->last_name;
        $TemplateData['email'] = $this->user->email;
        $TemplateData['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;

        $template  = \NazmulIslam\Utility\Utility::emailTemplate('User/user-permanent-lock', $TemplateData);

        \NazmulIslam\Utility\Queue\Queue::addToQueue([
            'to' => $this->user->username,
            'subject' => $this->user->first_name . " " . $this->user->last_name . ' is permanently locked out ',
            'message' => $template,
        ], $queue = 'Email', '\\NazmulIslam\Utility\\Jobs\\SendEmailJob', $_ENV['REDIS_HOST'], $_ENV['REDIS_PORT'], $database = 0);

        // $jobArray = array(
        //     'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => $this->user->username,
        //         'subject' => $this->user->first_name . " " . $this->user->last_name . ' is permanently locked out ',
        //         'message' => $template,
        //     ]
        // );
        // Queue::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);

        // For Admin
        $platformSettingStudioEmail = PlatformSetting::select('platform_variable_value')->where('platform_variable_key', 'studio_email_address')->first();
        $StudioEmail =  isset($platformSettingStudioEmail) && isset($platformSettingStudioEmail->platform_variable_value) ? json_decode($platformSettingStudioEmail->platform_variable_value) : '';

        $TemplateData = [];
        $TemplateData['user_name'] = $this->user->first_name . " " . $this->user->last_name;
        $TemplateData['email'] = $this->user->email;
        $TemplateData['TENANT_INFO'] =  $GLOBALS['TENANT_INFO'] ?? null;
        $template  = \NazmulIslam\Utility\Utility::emailTemplate('Admin/admin-permanent-lock', $TemplateData);


        \NazmulIslam\Utility\Queue\Queue::addToQueue(
            [
                'to' => $StudioEmail,
                'subject' => $this->user->first_name . " " . $this->user->last_name . ' is permanently locked out',
                'message' => $template,
            ],
            $queue = 'Email',
            '\\NazmulIslam\Utility\\Jobs\\SendEmailJob',
            $_ENV['REDIS_HOST'],
            $_ENV['REDIS_PORT'],
            $database = 0
        );

        /**
         * @todo needs to be uncommented after old queue removed
         */
        // $jobArray = array(
        //     'class' => '\\NazmulIslam\Utility\\Jobs\\SendEmailRabbitMQJob',
        //     'args' => [
        //         'to' => $StudioEmail,
        //         'subject' => $this->user->first_name . " " . $this->user->last_name . ' is permanently locked out',
        //         'message' => $template,
        //     ]
        // );
        // Queue::addToQueue(args: $jobArray, queue: 'email_queue', deliveryMode: 2);
    }



    function authuticate(array $serverVariables,int $lockDuration, int $loginAttemptCount, string $temporaryLockMessage, string $permanentLockMessage, int $isBruteForceEnabled, int $isPermanentBruteForceLock , int $loginPermanentAttemptCount) : array
    {   
        $this->userBruteForce =   UserBruteForce::where('user_id', intval($this->user['user_id']))->first();
        if((isset($this->user['failed_login_dates']) && !empty($this->user['failed_login_dates']))) {
            $output = $this->checkIfUserBlockStatus($lockDuration, $loginAttemptCount, $temporaryLockMessage, $permanentLockMessage);
            if(!empty($output)) {
                return $output;
            }
        }    

        $output = $this->checkIfUserIsValid();
        if(!empty($output)) {
            return $output;
        }
        return $this->inValidUserAction(serverVariables:$serverVariables, lockDuration: $lockDuration, loginAttemptCount: $loginAttemptCount, temporaryLockMessage: $temporaryLockMessage, permanentLockMessage: $permanentLockMessage, isBruteForceEnabled: $isBruteForceEnabled, isPermanentBruteForceLock: $isPermanentBruteForceLock, loginPermanentAttemptCount: $loginPermanentAttemptCount );
    }
}
