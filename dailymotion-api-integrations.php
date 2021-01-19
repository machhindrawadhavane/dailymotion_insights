<?php

    // Create connection
    function createConnection() {
        global $conn;
        $servername = "mysqlservernewjprod.mysql.database.azure.com";
    
        $username = "phantom@mysqlservernewjprod";
        $password = "Zurich$1";
        $dbname = "dailymotion_insights";
        $conn = new mysqli($servername, $username, $password, $dbname);
        print_r($conn);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    }
    createConnection();
    $client_id = "2166c1d3ad618a9ac049";
    $client_secret = "c3519ec6b1b550a010fa6f9e85803bbe6892215a";
    $username = "marathi@thenewj.com";
    $password = "Daily@2019";
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
    name=\"Content-Type\"\r\n\r\napplication/x-www-form-urlencoded\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"grant_type\"\r\n\r\npassword\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_id\"\r\n\r\n{$client_id}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"client_secret\"\r\n\r\n{$client_secret}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"username\"\r\n\r\n{$username}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"password\"\r\n\r\n{$password}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"scope\"\r\n\r\nuserinfo,manage_videos,manage_players,manage_playlists\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
    CURLOPT_HTTPHEADER => array(
        "cache-control: no-cache",
        "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
        "postman-token: eb49b08e-7285-d536-18d1-c1410e1fe4a3"
    ),
    ));

    $auth = curl_exec($curl);
    $err = curl_error($curl);

    // curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        $auth_arr = json_decode($auth,true);
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
                $uid = $token_arr['uid'];
                $access_token = $token_arr['access_token'];
                curl_setopt_array($curl, array(
                    CURLOPT_URL => "https://api.dailymotion.com/videos?user=x2bstyi&fields=id%2Ctitle%2Curl%2Cviews_total%2Clikes_total%2Cchannel%2Cadvertising_instream_blocked%2Callow_embed%2Callowed_in_playlists%2Caspect_ratio%2Caudience%2Caudience_total%2Cavailable_formats%2Cchecksum%2Ccountry%2Ccreated_time%2Ccustom_classification%2Cdescription%2Cduration%2Cembed_html%2Cembed_url%2Cencoding_progress%2Cend_time%2Cexpiry_date%2Cexpiry_date_deletion%2Cexplicit%2Cfilmstrip_60_url%2Cgeoblocking%2Cgeoloc%2Cheight%2Citem_type%2Clanguage%2Cliked_at%2Clive_ad_break_end_time%2Clive_ad_break_remaining%2Clive_airing_time%2Clive_audio_bitrate%2Clive_auto_record%2Clive_ingests%2Clive_publish_url%2Cmedia_type%2Cmode%2Conair%2Cowner%2Cpartner%2Cpreview_240p_url%2Cpreview_360p_url%2Cpreview_480p_url%2Cprivate%2Cprivate_id%2Cpublish_date%2Cpublished%2Cpublishing_progress%2Crecord_end_time%2Crecord_start_time%2Crecord_status%2Crecurrence%2Cstatus%2Ctags%2Cupdated_time%2Cverified%2Cviews_last_day%2Cviews_last_hour%2Cviews_last_month%2Cviews_last_week%2Cwidth",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "GET",
                    CURLOPT_HTTPHEADER => array(
                      "authorization: Bearer {$access_token}",
                      "cache-control: no-cache",
                      "postman-token: 91ed5381-4ade-92b7-5882-53394f3287a0"
                    ),
                  ));

                  $video_response = curl_exec($curl);
                  $video_data = json_decode($video_response,true);
                  $err = curl_error($curl);

                  if ($err) {
                    echo "cURL Error #:" . $err;
                  } else {
                    foreach ($video_data['list'] as $data) {
                        $available_format = serialize($data['available_formats']);
                        $custom_classification = serialize($data['custom_classification']);
                        $geoblocking = serialize($data['geoblocking']);
                        $tags = serialize($data['tags']);
                        $page_name = 'Marathi';
                        $imported_date = date("Y-m-d H:i:s");
                        $created_time = date('Y-m-d H:i:s', $data['created_time']);
                        $updated_time = date('Y-m-d H:i:s', $data['updated_time']);
                        $sql = "INSERT INTO `videos`(video_id,title,url,views_total,likes_total,channel,advertising_instream_blocked,allow_embed,allowed_in_playlists,aspect_ratio,audience,audience_total,available_formats,checksum,country,created_time,custom_classification,duration,embed_html,embed_url,encoding_progress,end_time,expiry_date,expiry_date_deletion,explicit,filmstrip_60_url,geoblocking,geoloc,height,item_type,language,liked_at,live_ad_break_end_time,live_ad_break_remaining,live_airing_time,live_audio_bitrate,live_auto_record,live_ingests,live_publish_url,media_type,mode,onair,owner,partner,preview_240p_url,preview_360p_url,preview_480p_url,private,private_id,publish_date,published,publishing_progress,record_end_time,record_start_time,record_status,recurrence,status,tags,updated_time,verified,views_last_day,views_last_hour,views_last_month,views_last_week,width,page_name,imported_date) 
                        VALUES ('".$data['id']."','".$data['title']."','".$data['url']."','".$data['views_total']."','".$data['likes_total']."','".$data['channel']."','".$data['advertising_instream_blocked']."','".$data['allow_embed']."','".$data['allowed_in_playlists']."','".$data['aspect_ratio']."','".$data['audience']."','".$data['audience_total']."','".$available_format."','".$data['checksum']."','".$data['country']."','".$created_time."','".$custom_classification."','".$data['duration']."','".$data['embed_html']."','".$data['embed_url']."','".$data['encoding_progress']."','".$data['end_time']."','".$data['expiry_date']."','".$data['expiry_date_deletion']."','".$data['explicit']."','".$data['filmstrip_60_url']."','".$geoblocking."','".$data['geoloc']."','".$data['height']."','".$data['item_type']."','".$data['language']."','".$data['liked_at']."','".$data['live_ad_break_end_time']."','".$data['live_ad_break_remaining']."','".$data['live_airing_time']."','".$data['live_audio_bitrate']."','".$data['live_auto_record']."','".$data['live_ingests']['Default (recommended)']."','".$data['live_publish_url']."','".$data['media_type']."','".$data['mode']."','".$data['onair']."','".$data['owner']."','".$data['partner']."','".$data['preview_240p_url']."','".$data['preview_360p_url']."','".$data['preview_480p_url']."','".$data['private']."','".$data['private_id']."','".$data['publish_date']."','".$data['published']."','".$data['publishing_progress']."','".$data['record_end_time']."','".$data['record_start_time']."','".$data['record_status']."','".$data['recurrence']."','".$data['status']."','".$tags."','".$updated_time."','".$data['verified']."','".$data['views_last_day']."','".$data['views_last_hour']."','".$data['views_last_month']."','".$data['views_last_week']."','".$data['width']."','".$page_name."','".$imported_date."')";
                        $result = $conn->query($sql);
                        print_r($result);
                    }
                  }
            }
        }
        
    }
