<?php

namespace FirebaseAdminSDK;

class Client {

    const FIREBASE_API_URL = 'https://www.googleapis.com/identitytoolkit/v3/relyingparty/';

    private $accessToken;

    public function __construct(\stdClass $accessToken) {

        if (strlen($accessToken->access_token) < 1 || $accessToken->token_type != 'Bearer') {
            throw new \Exception('invalid access token');
        }

        $this->accessToken = $accessToken;
    }

    public function createUser($options = []) {
        return $this->apiRequest('signupNewUser', $options);
    }

    public function updateUser($localId, $options = []) {
        return $this->apiRequest('setAccountInfo', array_merge(
            ['localId' => $localId],
            $options
        ));
    }

    public function getUser($localID) {
        return $this->apiRequest('getAccountInfo', [
            'localId' => [$localID]
        ]);
    }

    public function getUserByEmail($email) {
        return $this->apiRequest('getAccountInfo', [
            'email' => [$email]
        ]);
    }

    public function deleteUser($localId) {
        return $this->apiRequest('deleteAccount', [
            'localId' => $localId
        ]);
    }

    private function apiRequest($path, $postData) {

        $ch = curl_init(self::FIREBASE_API_URL . $path);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . sprintf('%s %s', $this->accessToken->token_type, $this->accessToken->access_token)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        $buf = curl_exec($ch);
        curl_close($ch);

        return json_decode($buf);
    }

}