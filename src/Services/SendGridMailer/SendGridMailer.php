<?php

declare(strict_types=1);

namespace NazmulIslam\Utility\Services\SendGridMailer;
use SendGrid;

class SendGridMailer
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function sendEmail($fromEmail, $fromName, $toEmail, $toName, $subject, $textContent, $htmlContent)
    {
        // Create SendGrid client
        $sendgrid = new SendGrid($this->apiKey);

        // Create an email object
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom($fromEmail, $fromName);
        $email->setSubject($subject);
        $email->addTo($toEmail, $toName);
        $email->addContent("text/plain", $textContent);
        $email->addContent("text/html", $htmlContent);

        try {
            // Send the email
            $response = $sendgrid->send($email);
            return $response->statusCode();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

