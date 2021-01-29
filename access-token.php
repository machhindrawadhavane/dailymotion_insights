<?php
require_once 'db_connection.php';
function getAccessToken() {
    $conn = createConnection();
    $sql5 = "SELECT st.apiKey, st.apiSecret, st.userName, st.password FROM statuses as st";
    $get_cred = $conn->query($sql5);
    $get_cred_arr = $get_cred->fetch_all(MYSQLI_ASSOC);
    
    foreach ($get_cred_arr as $data) {
        $client_id = $data['apiKey'];
        $client_secret = $data['apiSecret'];
        $username = $data['userName'];
        $password = $data['password'];
        $uid = 0;
        $access_token = '';
        $curl = curl_init();
        //Authentic
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.dailymotion.com/oauth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; 
            name=\"Content-Type\"\r\n\r\napplication/x-www-form-urlencoded\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"grant_type\"\r\n\r\npassword\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n{$client_id}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_secret\"\r\n\r\n{$client_secret}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n{$username}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n{$password}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"scope\"\r\n\r\nuserinfo,manage_videos,manage_players,manage_playlists,read_insights,email\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                "postman-token: eb49b08e-7285-d536-18d1-c1410e1fe4a3"
            ),
        ));
    
        $auth = curl_exec($curl);
        $err = curl_error($curl);
    
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $auth_arr = json_decode($auth,true);
            if (array_key_exists('error',$auth_arr)) {
                 //code for logging error into a given file 
                $error_message = $auth_arr['error_description']." for ".$username; 
                $log_file = "error-log/errors.log"; 
                ini_set("log_errors", TRUE);  
                ini_set('error_log', $log_file); 
                error_log($error_message); 
            }
            else {
                if (!empty($auth_arr) && array_key_exists('refresh_token',$auth_arr)) {
                    //Get Access token ussing refresh token
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.dailymotion.com/oauth/token",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"Content-Type\"\r\n\r\napplication/x-www-form-urlencoded\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"grant_type\"\r\n\r\nrefresh_token\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n{$client_id}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_secret\"\r\n\r\n{$client_secret}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"refresh_token\"\r\n\r\n{$auth_arr['refresh_token']}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                        CURLOPT_HTTPHEADER => array(
                            "cache-control: no-cache",
                            "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                            "postman-token: dd41b50e-6eda-3b67-c8a9-e209850ce310"
                        ),
                    ));
                    $token = curl_exec($curl);
                    $err = curl_error($curl);
            
                    if ($err) {
                    echo "cURL Error #:" . $err;
                    } else {
                        $token_arr = json_decode($token,true);
                        $sql6 = "Update statuses SET token = '".$token_arr['access_token']."'  WHERE userName='".$username."'";
                        $conn->query($sql6);
                        if (array_key_exists('uid',$token_arr)) {
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => "https://api.dailymotion.com/me/children",
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "GET",
                                CURLOPT_HTTPHEADER => array(
                                  "authorization: Bearer {$token_arr['access_token']}",
                                  "cache-control: no-cache",
                                  "postman-token: 3725cb8a-dfce-1ca5-3eb5-9b8879963792"
                                ),
                              ));
                              
                              $response = curl_exec($curl);
                              $err = curl_error($curl);
                              
                              if ($err) {
                                echo "cURL Error #:" . $err;
                              } else {
                                $page_data = json_decode($response,true);
                                if ($page_data['total'] > 0 && array_key_exists('list',$page_data) && array_key_exists('0',$page_data['list'])) {
                                    foreach ($page_data['list'] as $data) {
                                        $sql1 = "SELECT id FROM statuses WHERE ownerId='".$data['id']."'";
                                        $channel = $conn->query($sql1);
                                        if ($channel->num_rows > 0) {
                                            $sql2="UPDATE statuses SET token = '".$token_arr['access_token']."' WHERE ownerId='".$data['id']."'";
                                            $conn->query($sql2);
                                        }
                                        else {
                                            $sql3 = "INSERT INTO `statuses`(pageName,ownerId,apiKey,apiSecret,token,userName,password,views_total,videos_total,followers_total) VALUES ('".$data['screenname']."','".$data['id']."','".$client_id."','".$client_secret."','".$token_arr['access_token']."','".$username."','".$password."')";
                                            $conn->query($sql3);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
