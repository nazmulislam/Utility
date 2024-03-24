<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Services\Email;

use NazmulIslam\Utility\Logger\Logger;
use PHPMailer\PHPMailer\PHPMailer;


class SendEmailService
{

    public static int $smtpPort = 25;
    public static string $smtpHost;
    public static string $smtpUsername = APP_SMTP_USERNAME;
    public static string $smtpPassword = APP_SMTP_PASSWORD;
    public static string $fromEmail = APP_EMAIL_FROM_EMAIL;
    public static string $fromName = APP_EMAIL_FROM_NAME;
    public static string $subject;
    public static string $htmlMessage;
    public static string $smtpSecure = 'tls';
    public static $autoTLS = APP_EMAIL_AUTOTLS;
    public static $isExchange = false;
    public static ?PHPMailer $email;
    public static array $data;
    public static int $debug = DEBUG;
    public static string $mailService = MAIL_SERVICE;
    public static $appSmtpPort = APP_SMTP_PORT;

    /**
     *
     * @param array $data
     */
    public static function sendEmail(array $data)
    {

        try {


            self::$email = new PHPMailer(true);
            self::$data = $data;
            self::setVariables();

            return self::prepareEmail();
        } catch (\Exception $e) {
            Logger::error('email sending error - exception', [$e->getMessage(), [$e->getTraceAsString()]]);
            echo $e->getMessage(); //Boring error messages from anything else!
            echo $e->getTraceAsString();
            return false;
        }
    }

    public static function setVariables(): void
    {
        self::$smtpPort =  self::$mailService === 'production' ? intval(APP_SMTP_PORT) :  intval(MAILCATCHER_SMTP_PORT);
        self::$smtpHost = self::$mailService === 'production' ?  APP_SMTP_HOST :  MAILCATCHER_SMTP_HOST;

        self::$subject = isset(self::$data['subject']) ? self::$data['subject'] : '';

        if (empty(self::$data['message']) || !isset(self::$data['message'])) {
            throw new \Exception(' self::$data[message] is empty or null or missing, a message needs to be set');
        }
        /**
         * @todo need to sanitize string, need check for UT8 characterset, also htmlentities(string,flags,character-set,double_encode)
         */
        self::$htmlMessage = self::$data['message'];
        if (isset(self::$data['tracking_code']) && !empty(self::$data['tracking_code'])) {
            $baseUrl = self::$data['httpScheme'] . '://' . self::$data['httpHost'];
            self::$htmlMessage .= '<img style="display:none;"src="' . $baseUrl . '/email/track/' . self::$data['tracking_code'] . '" >';
        }
    }

    private static function setSMTPOptions(): void
    {
        if (self::$mailService === 'production') {
            if (intval(APP_EMAIL_SMTP_SECURE_REQUIRED) === 1) {
                /**
                 * $smtpSecure = For Mail servers such as Adit, which required SMTP secure true.
                 *
                 */
                (self::$smtpSecure == '' || self::$smtpSecure == false) ? self::$email->SMTPSecure = false : self::$email->SMTPSecure = true;
            }
            /**
             * $autoTLS = Required for servers for which required auto TLS.
             */
            (self::$autoTLS == '' || self::$autoTLS == false) ? self::$email->SMTPAutoTLS = false : self::$email->SMTPAutoTLS = true;
        }
    }
    public static function prepareEmail(): bool
    {

        self::setSMTPConfiguration();

        self::setToAddress();
        self::setCcAddress();
        self::setBccAddress();


        self::setAttachmentsAsFile();
        self::setAttachmentAsString();



        if (self::$email->Send()) {
            self::$email->ClearAllRecipients();
            self::$email->ClearAttachments();
            self::$email->clearAddresses();
            //unset(self::$email);
            return true;
        } else {
            self::$email->ClearAllRecipients();
            self::$email->ClearAttachments();
            self::$email->clearAddresses();
            return false;
        }
    }

    private static function setSMTPConfiguration(): void
    {
        if (!empty(self::$debug) && intval(self::$debug) == 1) {
            //self::$data['to'] = $_ENV['DEBUG_TO_EMAIL'];
            self::$email->SMTPDebug = 3;
        }
        self::$email->IsSMTP();
        self::$email->Host = self::$smtpHost; // SMTP server
        self::$email->CharSet = "UTF-8";
        self::$email->isHTML(true);

        self::$mailService === 'production' ? self::$email->SMTPAuth = true : '';

        self::setSMTPOptions();

        self::$email->Port = self::$smtpPort;                   // set the SMTP port for the GMAIL server
        self::$email->Username = self::$smtpUsername;  // SMTP username
        self::$email->Password = self::$smtpPassword;
        self::$email->SetFrom(self::$fromEmail, self::$fromName);
        self::$email->AddReplyTo(self::$fromEmail, self::$fromName);

        // optional items
        self::$email->Subject = self::$subject;
        self::$email->MsgHTML(self::$htmlMessage);
    }

    private static function setAttachmentAsString(): void
    {
        if (!empty(self::$data['addStringAttachment']) && isset(self::$data['addStringAttachment'])) {
            if (count(self::$data['addStringAttachment']) > 0) {

                for ($i = 0; $i < count(self::$data['addStringAttachment']); $i++) {

                    self::$email->addStringAttachment(self::$data['addStringAttachment'][$i]['content'], self::$data['addStringAttachment'][$i]['filename']);
                }
            }
        }
    }

    private static function setAttachmentsAsFile(): void
    {
        if (!empty(self::$data['attachments']) && isset(self::$data['attachments'])) {
            if (is_array(self::$data['attachments'])) {
                if (count(self::$data['attachments']) > 0) {

                    for ($i = 0; $i < count(self::$data['attachments']); $i++) {
                        self::$email->AddAttachment(self::$data['attachments'][$i]);
                    }
                    //TODO save to server
                    // added for saving to sever
                }
            } else {
                self::$email->AddAttachment(self::$data['attachments']);
            }
        }
    }

    private static function setBccAddress(): void
    {
        $bcc = '';
        if (isset(self::$data['bcc']) && strpos(self::$data['bcc'], ',') !== false) {
            $bcc_emails = explode(',', self::$data['bcc']);
            $bcc = $bcc_emails;
            for ($i = 0; $i < count($bcc_emails); $i++) {
                if (!empty($bcc_emails[$i])) {
                    self::$email->AddBCC($bcc_emails[$i], $bcc_emails[$i]);
                }
            }
        } else if (isset($bcc) && !empty($bcc)) {
            self::$email->AddBCC($bcc);
        }
    }

    private static function setCcAddress(): void
    {
        if (isset(self::$data['cc']) && strpos(self::$data['cc'], ',') !== false) {
            $cc_emails = explode(',', self::$data['cc']);
            for ($i = 0; $i < count($cc_emails); $i++) {
                if (!empty($cc_emails[$i])) {
                    self::$email->AddCC($cc_emails[$i], $cc_emails[$i]);
                }
            }
        } else if (isset(self::$data['cc']) && !empty(self::$data['cc'])) {
            self::$email->AddCC(self::$data['cc'], self::$data['cc']);
        }
    }


    /**
     * 
     * @todo refactoring function
     */
    private static function setToAddress(): void
    {
        if (isset(self::$data['to']) && is_array(self::$data['to'])) {
            for ($i = 0; $i < count(self::$data['to']); $i++) {
                if (!empty(self::$data['to'][$i])) {
                    self::$email->AddAddress(self::$data['to'][$i]);
                }
            }
        } else if (isset(self::$data['to']) && strpos(self::$data['to'], ',') !== false) {
            $to_emails = explode(',', self::$data['to']);
            for ($i = 0; $i < count($to_emails); $i++) {
                if (!empty($to_emails[$i])) {
                    self::$email->AddAddress($to_emails[$i]);
                }
            }
        } else {

            self::$email->AddAddress(self::$data['to'], self::$data['to']);
        }
    }
}
