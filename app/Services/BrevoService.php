<?php

namespace App\Services;

use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;
use GuzzleHttp\Client;

class BrevoService
{
    protected $apiInstance;

    public function __construct()
    {
        $config = Configuration::getDefaultConfiguration()->setApiKey('api-key', env('BREVO_API_KEY'));
        $this->apiInstance = new TransactionalEmailsApi(
            new Client(),
            $config
        );
    }

    public function sendEmail($to, $subject, $body)
    {
        $sendSmtpEmail = new SendSmtpEmail([
            'subject'       => $subject,
            'sender'        => ['name' => 'Kretech ID', 'email' => 'Kretech_ID@example.com'],
            'to'            => [['email' => $to]],
            'htmlContent'   => $body
        ]);

        try {
            $result = $this->apiInstance->sendTransacEmail($sendSmtpEmail);
            return $result;
        } catch (\Exception $e) {
            return 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ' . $e->getMessage();
        }
    }
}
