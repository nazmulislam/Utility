<?php
declare(strict_types=1);
namespace NazmulIslam\Utility\Email;
use App\Models\App\User;
use NazmulIslam\Utility\Encryption\StringEncryption;
use App\Controllers\UserControllers\UsersController;
use App\Models\App\EmailTags;
use App\Models\App\PlatformSetting;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DynamicEmail
 *
 * @author apple
 */
class DynamicEmail {
    
    static public function getDynamicEmailContentForMessage(array $emailContent, User $user, array $additionalData = []): array
    {
        $message = isset($emailContent['message'])?$emailContent['message']:'';
        $subject = isset($emailContent['subject'])?$emailContent['subject']:'';
        
        $emailTags = self::checkEmailTags($message, $user->toArray(), $additionalData);
        $platformVariables = PlatformSetting::where('is_active',1)->get()->toArray();
        /**
        * Covert message variables to actual dynamic values.
        */
        if(isset($message) && !empty($message) && ((isset($emailTags) && count($emailTags) > 0) || (isset($platformVariables) && count($platformVariables) > 0)))
        {
            foreach($emailTags as $key => $value) {
                //do not use replace function for keys containing array
                if(isset($value) && $value!=NULL && is_string($value)){
                    $message = str_replace('[['.$key.']]', $value,$message);
                }
            }
            foreach($platformVariables as $platformVariable) {
                $key = isset($platformVariable['platform_variable_key'])?$platformVariable['platform_variable_key']:'';
                $value = isset($platformVariable['platform_variable_value'])?$platformVariable['platform_variable_value']:'';
                //do not use replace function for keys containing array
                if(isset($value) && $value!=NULL && is_string($value) && isset($key) && !empty($key)){
                    $message = str_replace('{{'.$key.'}}', $value,$message);
                }
            }
        }
        $emailTags = self::checkEmailTags($subject, UsersController::getUserFullDetail($user->id), $additionalData);
        if(isset($subject) && !empty($subject) && ((isset($emailTags) && count($emailTags) > 0) || (isset($platformVariables) && count($platformVariables) > 0)))
        {
            foreach($emailTags as $key => $value) {
                //do not use replace function for keys containing array
                if(isset($value) && $value!=NULL && is_string($value)){
                    $subject = str_replace('[['.$key.']]', $value,$subject);
                }
            }
            foreach($platformVariables as $platformVariable) {
                $key = isset($platformVariable['platform_variable_key'])?$platformVariable['platform_variable_key']:'';
                $value = isset($platformVariable['platform_variable_value'])?$platformVariable['platform_variable_value']:'';
                //do not use replace function for keys containing array
                if(isset($value) && $value!=NULL && is_string($value) && isset($key) && !empty($key)){
                    $subject = str_replace('{{'.$key.'}}', $value,$subject);
                }
            }
        }
        $emailContent['subject'] = $subject;
        $emailContent['message'] = $message;
        return $emailContent;
    }
    
    
    static public function checkEmailTags(string $message, array $user, array $additionalData = []): array
    {
        $selectedEmailTagsContents = [];
        $emailTags = EmailTags::select('name as text')->where('is_active',1)->get()->toArray();
        if(isset($message) && !empty($message) && ((isset($emailTags) && count($emailTags) > 0)))
        {
            foreach($emailTags as $emailTag) {
                //do not use replace function for keys containing array
                if(isset($emailTag['text']) && $emailTag['text']!=NULL && is_string($emailTag['text'])){
                    if(strpos($message, '[['.$emailTag['text'].']]') !== false){
                        
                        $selectedEmailTagsContents[$emailTag['text']] = self::fillEmailTagValue($user, $emailTag['text'],$additionalData);
                    }
                }
            }
        }
        return $selectedEmailTagsContents;
    }
    
    static public function fillEmailTagValue($userWithContact, $tag , array $additionalData = []): string
    {
        switch (strtolower($tag)) {
            case 'first_name': {
                    return (isset($userWithContact['first_name']) ? $userWithContact['first_name'] : ' ');
                }
                break;
            case 'user_name': {
                    return (isset($userWithContact['first_name']) ? $userWithContact['first_name'] . (isset($userWithContact['last_name']) ? ' ' . $userWithContact['last_name'] : '') : (isset($userWithContact['last_name']) ? $userWithContact['last_name'] : ''));
                }
                break;
            case 'user_email': {
                    return isset($userWithContact['username']) ? $userWithContact['username'] : '';
                }
                break;
            case 'user_password': {
                    return isset($additionalData['password']) ? $additionalData['password'] : '';
                }
                break;
            case 'user_id': {
                    $encryption = new StringEncryption;
                    return isset($userWithContact['id']) ? $encryption->encryptString($userWithContact['id']) : '';
                }
                break;
            case 'today_date': {
                    return date("d M, Y");
                }
                break;
        }
        return '';
    }
}
