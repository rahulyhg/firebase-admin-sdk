<?php

require_once __DIR__ . '/vendor/autoload.php';

try {

    $cred = new \FirebaseAdminSDK\Credentials(__DIR__ . '/../serviceAccountKey.json');
    $accessToken = $cred->getAccessToken();

    $api = new \FirebaseAdminSDK\Client($accessToken);

    $options = [
        'email' => 'user@example.com',
        'emailVerified' => false,
        'password' => 'secretPassword',
        'displayName' => 'John Doe',
        'photoURL' => 'http://www.example.com/12345678/photo.png',
        'disabled' => false
    ];

    // create user
    $buf = $api->createUser($options);
    if (property_exists($buf, 'error')) {
        throw new \Exception($buf->error->errors[0]->message);
    }

    if (!property_exists($buf, 'email') || $buf->email != $options['email']) {
        throw new \Exception('unable to create user');
    }
    $localID = $buf->localId;

    // get user (by ID)
    $buf = $api->getUser($localID);
    if (property_exists($buf, 'error')) {
        throw new \Exception($buf->error->errors[0]->message);
    }

    if (!property_exists($buf, 'users') || count($buf->users) != 1) {
        throw new \Exception('unable to get user');
    }

    // update user
    $options['email'] = 'user_xyz@example.com';
    $buf = $api->updateUser($localID, $options);
    if (property_exists($buf, 'error')) {
        throw new \Exception($buf->error->errors[0]->message);
    }

    // get user (by email)
    $buf = $api->getUserByEmail($options['email']);
    if (property_exists($buf, 'error')) {
        throw new \Exception($buf->error->errors[0]->message);
    }
    if ($options['email'] != $buf->users[0]->email) {
        throw new \Exception('unable to change email');
    }

    // delete user
    $buf = $api->deleteUser($localID);
    if (property_exists($buf, 'error')) {
        throw new \Exception($buf->error->errors[0]->message);
    }

    exit('OK' . PHP_EOL);

} catch(\Exception $e) {
    exit(sprintf('ERROR: %s', $e->getMessage()) . PHP_EOL);
}