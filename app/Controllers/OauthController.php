<?php

namespace App\Controllers;

use Helpers\BaseController;
use Bpjs\Core\Request;
use Helpers\Validator;
use Helpers\View;
use Helpers\CSRFToken;
use League\OAuth2\Client\Provider\GenericProvider;

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
                // Tukar authorization code jadi access token + refresh token
                $accessToken = $provider->getAccessToken('authorization_code', [
                    'code' => $request->code
                ]);

                echo "<h2>Access Token:</h2>";
                echo "<pre>" . $accessToken->getToken() . "</pre>";

                echo "<h2>Refresh Token:</h2>";
                echo "<pre>" . $accessToken->getRefreshToken() . "</pre>";

                echo "<h2>Expires:</h2>";
                echo date('Y-m-d H:i:s', $accessToken->getExpires());

                // Refresh token inilah yang nanti dipakai di PHPMailer setOAuth
            } catch (\Exception $e) {
                exit('Error saat tukar code ke token: ' . $e->getMessage());
            }
        } else {
            // Kalau belum ada code, arahkan user ke login Microsoft
            $authUrl = $provider->getAuthorizationUrl();
            header('Location: ' . $authUrl);
            exit;
        }
    }
}
