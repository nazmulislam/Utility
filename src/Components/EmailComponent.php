<?php

namespace NazmulIslam\Utility\Components;


use NazmulIslam\Utility\Core\Interfaces\ComponentInterface;
use NazmulIslam\Utility\Core\Traits\CollectionTraits;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\Email;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\EmailTemplate;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\EmailRecipientStatic;
use NazmulIslam\Utility\Models\NazmulIslam\Utility\EmailRecipientUser;
use NazmulIslam\Utility\Services\Email\SendEmailService;
use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Respect\Validation\Validator as v;
use PHPMailer\PHPMailer\PHPMailer;
/**
 * Class EmailComponent. This class encompasses one NazmulIslam\Utility\Models\NazmulIslam\Utility\Email
 * and performs methods related to that object such as CRUD and sending an email.
 * @package NazmulIslam\Utility\Core\Components
 */
class EmailComponent extends BaseComponent implements ComponentInterface
{
    use CollectionTraits;

    /**
     * The object that is manipulated by the methods of this component
     * @var Email
     */
    private $email;

    /**
     * EmailComponent constructor. Takes 4 arguments 1. Email Object, 2. email_template_identifier, 3. id 4. null.
     * Passing null create a new Email Eloquent Object
     * @param $id : Email | Integer | Identifier | null
     */
    public function __construct($id = null) {
        if($id instanceof Email) {
            $this->email = $id;
        } else if($id !== null  && is_string($id) && !intval($id)) {
            //identifier passed
            $this->email = Email::where('email_template_identifier', $id)->first();
        } else if($id !== null) {
            //finds by id
            $this->email = Email::find($id);
        } else {
            //creates new entity
            $this->email = new Email();
        }
    }

    /**
     * @return Email
     * Gets the Email object
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * Creates an email. Takes a key value array. The values must have the same key as the column
     * name in the emails table.
     * @param $input
     * @return bool
     */
    public function create(array $input): bool
    {
        $this->email->fill($input);
        $toTypes = $this->getUserEmailEnvRecipient($input['recipient_to'], EmailRecipientStatic::TYPE_TO_ENV, EMailRecipientUser::TYPE_TO, EmailRecipientStatic::TYPE_TO_EMAIL_ADDRESS);
        $ccTypes = $this->getUserEmailEnvRecipient($input['recipient_cc'], EmailRecipientStatic::TYPE_CC_ENV, EMailRecipientUser::TYPE_CC, EmailRecipientStatic::TYPE_CC_EMAIL_ADDRESS);
        $bccTypes = $this->getUserEmailEnvRecipient($input['recipient_bcc'], EmailRecipientStatic::TYPE_BCC_ENV, EMailRecipientUser::TYPE_BCC, EmailRecipientStatic::TYPE_BCC_EMAIL_ADDRESS);
        $recipients = array_merge($toTypes['users'], $ccTypes['users'], $bccTypes['users']);
        $staticRecipients = array_merge($toTypes['env'], $toTypes['emails'],$ccTypes['env'], $ccTypes['emails'],$bccTypes['env'], $bccTypes['emails']);
        try {
            DB::connection('app')->beginTransaction();
            $this->email->save();
            if(count($recipients) > 0) {
                $this->email->emailRecipientUser()->createMany($recipients);
            }
            if(count($staticRecipients) > 0) {
                $this->email->emailRecipientStatic()->createMany($staticRecipients);
            }
            DB::connection('app')->commit();
            return true;
        } catch(\Exception $ex) {
           
            return false;
        }
    }

    /**
     * Deletes an email.
     * Deletes any related email_recipient_users and email_recipient_statics via foreign key
     * constraint On Delete CASCADE.
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $this->email->delete();
            return true;
        } catch(\Exception $ex) {
            return false;
        }
    }

    /**
     * Updates an email and recipients. Takes a key value array. The values must have the same key as the column
     * name in the emails table with the exception of recipient_to, recipient_cc and recipient_bcc
     * which keys are as named. To save a email_recipient_users the value in recipient arrays must be a ibfmembers.id.
     * To save a ENV variable as a recipient the value in the recipient arrays must be an ENV name in the .env file.
     * To save a email address the recipient array value must be a valid email address.
     * @param $input
     * @return bool
     */
    public function update(array $input): bool
    {
        $toTypes = $this->getUserEmailEnvRecipient($input['recipient_to'], EmailRecipientStatic::TYPE_TO_ENV, EMailRecipientUser::TYPE_TO, EmailRecipientStatic::TYPE_TO_EMAIL_ADDRESS);
        $ccTypes = $this->getUserEmailEnvRecipient($input['recipient_cc'], EmailRecipientStatic::TYPE_CC_ENV, EMailRecipientUser::TYPE_CC, EmailRecipientStatic::TYPE_CC_EMAIL_ADDRESS);
        $bccTypes = $this->getUserEmailEnvRecipient($input['recipient_bcc'], EmailRecipientStatic::TYPE_BCC_ENV, EMailRecipientUser::TYPE_BCC, EmailRecipientStatic::TYPE_BCC_EMAIL_ADDRESS);
        $recipients = array_merge($toTypes['users'], $ccTypes['users'], $bccTypes['users']);
        $staticRecipients = array_merge($toTypes['env'], $toTypes['emails'],$ccTypes['env'], $ccTypes['emails'],$bccTypes['env'], $bccTypes['emails']);
        try {
            DB::connection('app')->beginTransaction();
            $this->email->update($input);
            //Deletes any recipients then creates the new ones
            $this->email->emailRecipientUser()->delete();
            if(count($recipients) > 0) {
                $this->email->emailRecipientUser()->createMany($recipients);
            }
            $this->email->emailRecipientStatic()->delete();
            if(count($staticRecipients) > 0) {
                $this->email->emailRecipientStatic()->createMany($staticRecipients);
            }
            DB::connection('app')->commit();
            return true;
        } catch(\Exception $ex) {
            return false;
        }
    }

    /**
     * formats email for a sending service interface
     * @param $data || values to replace the emails tags with
     * @param array $extraData
     * @return array
     * @throws Exception
     */
    private function formatEmailToSend($data, $extraData = []) {
        if(!$this->email)
        {
            $this->email = new Email();
            if(!$this->email->emailTemplate)
            {
                $this->email->emailTemplate = new EmailTemplate();
            }
        }
        $emailTemplateComponent = new EmailTemplateComponent($this->email->emailTemplate);
        //gets all the recipients - users, email addresses and ENV variables
        $to = [];
        $this->getUserEmailsFromRecipients('to', $to);
        $this->getEmailsFromEnv('toEnv', $to);
        $this->getStaticEmailsFromRecipients('toEmailAddress', $to);
        $cc = [];
        $this->getUserEmailsFromRecipients('cc', $cc);
        $this->getEmailsFromEnv('ccEnv', $cc);
        $this->getStaticEmailsFromRecipients('ccEmailAddress', $cc);
        $bcc = [];
        $this->getUserEmailsFromRecipients('bcc', $bcc);
        $this->getEmailsFromEnv('bccEnv', $bcc);
        $this->getStaticEmailsFromRecipients('bccEmailAddress', $bcc);

        //Overrides subject
        $subject = $this->email->subject;
        if(isset($extraData['subject'])) {
            $subject = $extraData['subject'];
            unset($extraData['subject']);
        } else {
            //Replaces ENV{{}} variables in the subject
            $consts = get_defined_constants();
            $matches = null;
            preg_match_all("/ENV{{.[^}]+/",$subject, $matches);
            foreach($matches[0] as $match) {
                $trimmedString = $match;
                $trimmedString = str_replace('ENV{{', '', $trimmedString);
                if(isset($_ENV[$trimmedString])) {
                    $subject = str_replace($match, $_ENV[$trimmedString], $subject);
                } elseif(isset($consts[$trimmedString])) {
                    $subject = str_replace($match, $consts[$trimmedString], $subject);
                } else {
                    throw new Exception("No variable found in ENV or defined constants");
                }
            }
            $subject = str_replace('}}', '', $subject);
        }

        //Appends Dynamic Recipients
        $this->appendRecipients($extraData, 'to', $to);
        $this->appendRecipients($extraData, 'cc', $cc);
        $this->appendRecipients($extraData, 'bcc', $bcc);
        if(!defined('PLATFORM_SUPPORT_EMAIL'))
        {
            define('PLATFORM_SUPPORT_EMAIL','support@ethixbase.com') ;
        }

        $formattedEmail = [
            'name' => $this->email->name,
            'subject' => $subject,
            'message' => $emailTemplateComponent->replaceTemplateTags($data),
            'to' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'from_email'=> (isset($extraData['from_email']) && !empty($extraData['from_email']))?$extraData['from_email']:PLATFORM_SUPPORT_EMAIL
        ];

        $formattedEmail = array_merge($extraData, $formattedEmail);
        return $formattedEmail;
    }

    /**
     * Gets an array of email addresses from users
     * @param $collections
     * @return array
     */
    private function getUserEmailsFromRecipients($recipientType, &$recipientArray) {
        $recipients = $this->email->{$recipientType}()->with('user')->has('user')->get();
        foreach($recipients as $r) {
            $recipientArray[] = $r->user->email;
        }
        return $recipientArray;
    }

    /**
     * Gets the emails ENV values defined in email_recipient_statics
     * @param $recipientType
     * @param $recipientArray
     */
    private function getEmailsFromEnv($recipientType, &$recipientArray) {
        $envRecipients = $this->email->{$recipientType}()->get();
        foreach ($envRecipients as $envName) {
            if(isset($_ENV[$envName->email_address_or_env_name])) {
                $recipientArray[] = $_ENV[$envName->email_address_or_env_name];
            }
        }
    }

    /**
     * Gets the emails email address values defined in email_recipient_statics
     * @param $recipientType
     * @param $recipientArray
     */
    private function getStaticEmailsFromRecipients($recipientType, &$recipientArray) {
        $recipients = $this->email->{$recipientType}()->get();
        foreach($recipients as $r) {
            $recipientArray[] = $r->email_address_or_env_name;
        }
    }

    /**
     * Appends any recipients passed into the extraData to the recipient Array
     * @param $extraData || key value array
     * @param $key || key of recipients [to, cc, bcc]
     * @param $recipientArray || array of recipients
     */
    private function appendRecipients($extraData, $key, &$recipientArray) {
        if(isset($extraData[$key])) {
            if(is_array($extraData[$key])) {
                $recipientArray = array_merge($extraData[$key], $recipientArray);
                $recipientArray = implode(', ',$recipientArray);
            } else if(is_string($extraData[$key])) {
                $recipientArray = implode(', ',$recipientArray) .','. $extraData[$key];
            }
            unset($extraData[$key]);
        } else {
            $recipientArray = implode(', ',$recipientArray);
        }
    }

    /**
     * Takes template data and extra data such as [attachments, to, subject] and sends the email.
     * @param $templateData
     * @param array $extras
     * @return bool
     * @throws Exception
     */
    public function send($templateData, $extras = []) {
        $emailData = $this->formatEmailToSend($templateData, $extras);
        //If Application Environment is defined then use Email Service with Global Constants.
        //Else use email service with environmental variables
        try {
             $result = SendEmailService::sendEmail($emailData, new PHPMailer());
                return $result;
        } catch (Exception $ex) {
            return false;
        }
    }

    //gets whether the recipient is a User, Email Address or Env Variable

    /**
     * checks whether the recipient received in a recipient_to | recipient_cc | recipient_bcc
     * array is a email address, ENV, or user id.
     * @param $recipients
     * @param $typeEnv
     * @param $typeUser
     * @param $typeStatic
     * @return array || Key Value pair with the keys being [users, env, emails] and the values arrays of
     * key value pairs
     */
    private function getUserEmailEnvRecipient($recipients, $typeEnv, $typeUser, $typeStatic) {
        $recipArr = [
            'users' => [],
            'env' => [],
            'emails' => []
        ];

        foreach($recipients as $r) {
            if(isset($_ENV[$r])) {
                $recipArr['env'][] = [
                    'email_address_or_env_name' => $r,
                    'type' => $typeEnv
                ];
            }
            if(is_numeric($r)) {
                $recipArr['users'][] = [
                    'user_id' => $r,
                    'type' => $typeUser
                ];
            }
            if(v::email()->validate($r)) {
                $recipArr['emails'][] = [
                    'email_address_or_env_name' => $r,
                    'type' => $typeStatic
                ];
            }
            //If $r isn't any of these things then it will not be saved
        }

        return $recipArr;
    }

}
