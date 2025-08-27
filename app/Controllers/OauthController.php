<?php

namespace App\Controllers;

use Helpers\BaseController;
use Bpjs\Core\Request;
use Helpers\Validator;
use Helpers\View;
use Helpers\CSRFToken;
use League\OAuth2\Client\Provider\GenericProvider;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\PHPMailer;

class OauthController extends BaseController
{
    // Controller logic here
    public function callback(Request $request)
    {
        $provider = new GenericProvider([
            'clientId'                => env('AUTH_CLIENT_ID'),
            'clientSecret'            => env('AUTH_CLIENT_SECRET'),
            'redirectUri'             => 'https://10.203.84.25/oauth/v1/callback',
            'urlAuthorize'            => 'https://login.microsoftonline.com/'.env('AUTH_TENANT_ID').'/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/'.env('AUTH_TENANT_ID').'/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'https://outlook.office365.com/SMTP.Send offline_access'
        ]);

        if ($request->code) {
            try {
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $request->code
                ]);
                echo "<h2>Access Token:</h2>";
                echo "<pre>" . $accessToken->getToken() . "</pre>";
                echo "<h2>Refresh Token:</h2>";
                echo "<pre>" . $accessToken->getRefreshToken() . "</pre>";
                echo "<h2>Expires:</h2>";
                echo date('Y-m-d H:i:s', $accessToken->getExpires());
            } catch (\Exception $e) {
                exit('Error saat tukar code ke token: ' . $e->getMessage());
            }
        } else {
            $authUrl = $provider->getAuthorizationUrl();
            header('Location: ' . $authUrl);
            exit;
        }
    }

    public function testOauth()
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.office365.com';
            $mail->Port       = 587;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth   = true;

            $mail->AuthType = 'XOAUTH2';
            
            $provider = new GenericProvider([
                'clientId'                => env('AUTH_CLIENT_ID'),
                'clientSecret'            => env('AUTH_CLIENT_SECRET'),
                'redirectUri'             => 'https://10.203.84.25/oauth/v1/callback',
                'urlAuthorize'            => 'https://login.microsoftonline.com/'.env('AUTH_TENANT_ID').'/oauth2/v2.0/authorize',
                'urlAccessToken'          => 'https://login.microsoftonline.com/'.env('AUTH_TENANT_ID').'/oauth2/v2.0/token',
                'urlResourceOwnerDetails' => '',
                'scopes'                  => 'https://outlook.office.com/SMTP.Send',
            ]);

            $mail->setOAuth(new OAuth([
                'provider'      => $provider,
                'clientId'      => env('AUTH_CLIENT_ID'),
                'clientSecret'  => env('AUTH_CLIENT_SECRET'),
                'refreshToken'  => '',
                'userName'      => 'ISE00010@stanley-electric.com',
            ]));

            $mail->setFrom('ISE00010@stanley-electric.com', 'Test Oauth');
            $mail->addAddress('fadli_azka_prayogi@stanley-electric.com', 'Fadli');
            $mail->isHTML(true);
            $mail->Subject = 'PHPMailer SMTP OAuth Test';
            $mail->Body    = 'This is a test email sent using PHPMailer with SMTP OAuth.';

            $mail->send();
            echo 'Message has been sent';
        } catch (\Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
