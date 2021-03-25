<?php
require_once 'local_config.php';

$url = $api->uploadFile($videoTestFile);
$result = $api->post(
    '/videos',
    array(
        'url'       => $url,
        'title'     => 'Dailymotion Newj test',
        'tags'      => 'dailymotion,api,sdk,test',
        'channel'   => 'videogames',
        'published' => true,
    )
);
print($result);