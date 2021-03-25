<?php

require_once 'Dailymotion.php';

// Account settings
$apiKey        = '26bccd27f45a4c8b96ec';
$apiSecret     = '539177bad529e78fdcbadbd1450d2af9295cbf93';
$testUser      = 'newj@thenewj.com';
$testPassword  = 'Daily@2019';
$videoTestFile = 'http://stagingathena.azurewebsites.net/dummy_video/27245_TMF.mp4';

// Scopes you need to run your tests
$scopes = array(
    'userinfo',
    'feed',
    'manage_videos',
);
// Dailymotion object instanciation
$api = new Dailymotion();
$api->setGrantType(
    Dailymotion::GRANT_TYPE_PASSWORD,
    $apiKey,
    $apiSecret,
    $scopes,
    array(
        'username' => $testUser,
        'password' => $testPassword,
    )
);
