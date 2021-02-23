<?php
require_once 'db_connection.php';
require_once 'access-token.php';
require_once 'users-insights.php';
require_once 'videos-insights.php';
require_once 'videos-daily.php';


getAccessToken();
getUsers();
getVideosDetails();
getVideosDaily();

