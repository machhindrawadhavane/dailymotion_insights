<?php
    date_default_timezone_set('Asia/Kolkata');  
    function createConnection() {    
        global $conn;
        $servername = "mysqlservernewjprod.mysql.database.azure.com";
        $username = "phantom@mysqlservernewjprod";
        $password = "Zurich$1";
        $dbname = "dailymotion_insights";
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }