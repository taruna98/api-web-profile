<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PHPMailer\PHPMailer\PHPMailer;

class MailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PHPMailer::class, function ($app) {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION');
            $mail->Port = env('MAIL_PORT');
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));

            return $mail;
        });
    }
}
