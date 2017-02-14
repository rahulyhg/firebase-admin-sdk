<?php

namespace FirebaseAdminSDK;

use Firebase\JWT\JWT;

class Credentials {

    const GOOGLE_TOKEN_AUDIENCE = 'https://accounts.google.com/o/oauth2/token';
    const GOOGLE_AUTH_TOKEN_HOST = 'accounts.google.com';
    const GOOGLE_AUTH_TOKEN_PATH = '/o/oauth2/token';
    const GOOGLE_AUTH_TOKEN_PORT = 443;
    const ONE_HOUR_IN_SECONDS = 60 * 60;
    const JWT_ALGORITHM = 'RS256';

    private $serviceAccountKey;

    public function __construct($serviceAccountKeyFile) {

        if (!file_exists($serviceAccountKeyFile)) {
            throw new \Exception('serviceAccountKeyFile not found');
        }

        $this->serviceAccountKey = json_decode(file_get_contents($serviceAccountKeyFile));
    }

    private function createAuthJwt_() {

        $claims = [
            'scope' => join(' ', [
                'https://www.googleapis.com/auth/firebase.database',
                'https://www.googleapis.com/auth/firebase.messaging',
                'https://www.googleapis.com/auth/identitytoolkit',
                'https://www.googleapis.com/auth/userinfo.email',
            ]),
            'aud' => self::GOOGLE_TOKEN_AUDIENCE,
            'exp' => time() + self::ONE_HOUR_IN_SECONDS,
            'iss' => $this->serviceAccountKey->client_email,
            'iat' => time(),
        ];

        return JWT::encode($claims, $this->serviceAccountKey->private_key, self::JWT_ALGORITHM);
    }

    public function getAccessToken() {

        $token = $this->createAuthJwt_();
        $postData = 'grant_type=urn%3Aietf%3Aparams%3Aoauth%3A' .
            'grant-type%3Ajwt-bearer&assertion=' .
            $token;

        $options = [
            'method' => 'POST',
            'host' => self::GOOGLE_AUTH_TOKEN_HOST,
            'port' => self::GOOGLE_AUTH_TOKEN_PORT,
            'path' => self::GOOGLE_AUTH_TOKEN_PATH,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Content-Length' => strlen($postData)
            ]
        ];

        $buf = $this->requestAccessToken($options, $postData);
        return json_decode($buf);
    }

    private function requestAccessToken($options, $postData) {

        $ch = curl_init(self::GOOGLE_TOKEN_AUDIENCE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $buf = curl_exec($ch);
        curl_close($ch);

        return $buf;
    }

}