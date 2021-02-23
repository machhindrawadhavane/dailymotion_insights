<?php
require_once 'db_connection.php';

function getVideosDaily() {
    $access_token = $page_name = $owner_id = '';
    $available_format = $custom_classification = $geoblocking = $tags = $title = $created_time = $updated_time = '';
    $created_after = date("Y-m-d", strtotime("- 7 day"));
    $created_before = date("Y-m-d", strtotime("+ 1 day"));
    $conn = createConnection();
    
    $sql5 = "SELECT st.token, st.ownerId, st.pageName FROM statuses as st";
    $token_data = $conn->query($sql5);
    $tokens = $token_data->fetch_all(MYSQLI_ASSOC);
    
    $curl = curl_init();
    
    foreach ($tokens as $token) {
        if (array_key_exists('token',$token)) {
            $access_token = $token['token'];
        }
        if (array_key_exists('pageName',$token)) {
            $page_name = $token['pageName'];
        }
        if (array_key_exists('ownerId',$token)) {
            $owner_id = $token['ownerId'];
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.dailymotion.com/videos?user={$owner_id}&created_after={$created_after}&created_before={$created_before}&fields=id%2Ctitle%2Curl%2Cviews_total%2Clikes_total%2Caudience%2Caudience_total%2Ccreated_time%2Cdescription%2Clanguage%2Cstatus%2Cupdated_time%2Cviews_last_day%2Cviews_last_hour%2Cviews_last_month%2Cviews_last_week%2Cwidth",
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
            if (array_key_exists('error',$video_data)) {
                //code for logging error into a given file 
                $error_message = $video_data['error']['message']." for ".$page_name; 
                $log_file = "error-log/errors.log"; 
                ini_set("log_errors", TRUE);  
                ini_set('error_log', $log_file); 
                error_log($error_message); 
            }
            else {
                if (array_key_exists('list',$video_data)) {
                    $sql1="UPDATE statuses SET since = '".$created_after."', until = '".$created_before."' WHERE pageName='".$page_name."'";
                    $conn->query($sql1);
                    foreach ($video_data['list'] as $data) {
                        if (array_key_exists('available_formats',$data)) {
                            $available_format = serialize($data['available_formats']);
                        }
                        if (array_key_exists('custom_classification',$data)) {
                            $custom_classification = serialize($data['custom_classification']);
                        }
                        if (array_key_exists('geoblocking',$data)) {
                            $geoblocking = serialize($data['geoblocking']);
                        }
                        if (array_key_exists('tags',$data)) {
                            $tags = serialize($data['tags']);
                        }
                        $custom_imported_date = date("Y-m-d H:i:s");
                        if (array_key_exists('created_time',$data)) {
                            $created_time = date('Y-m-d H:i:s', $data['created_time']);
                        }
                        if (array_key_exists('updated_time',$data)) {
                            $updated_time = date('Y-m-d H:i:s', $data['updated_time']);
                        }
                        if (array_key_exists('title',$data)) {
                            $title = $conn->real_escape_string($data['title']);
                        }
                        $sql = "INSERT INTO `videos_daily`(video_id,title,views_total,likes_total,audience,audience_total,created_time,description,language,status,updated_time,views_last_day,views_last_hour,views_last_month,views_last_week,page_name,custom_imported_date) VALUES ('".$data['id']."','".$title."','".$data['views_total']."','".$data['likes_total']."','".$data['audience']."','".$data['audience_total']."','".$created_time."','".$data['description']."','".$data['language']."','".$data['status']."','".$updated_time."','".$data['views_last_day']."','".$data['views_last_hour']."','".$data['views_last_month']."','".$data['views_last_week']."','".$page_name."','".$custom_imported_date."')";
                        $result = $conn->query($sql);
                    }
                    $page = 0;
                    $has_more = FALSE;
                    if (array_key_exists('page',$video_data)) {
                        $page = $video_data['page'];
                    }
                    if (array_key_exists('has_more',$video_data) && $video_data['has_more']) {
                        $has_more = $video_data['has_more'];
                        $page++;
                        while ($has_more) {
                            $has_more = get_video_insights_daily($page,$access_token,$conn, $page_name, $owner_id, $created_after, $created_before);
                            $page++;
                        }
                    }
                }
            }
        }
    }
}

//Get data of more pages
function get_video_insights_daily($page, $access_token, $conn, $page_name, $owner_id, $created_after, $created_before) {
    $has_more = FALSE;
    $available_format = $custom_classification = $geoblocking = $tags = $title = $created_time = $updated_time = '';
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.dailymotion.com/videos?user={$owner_id}&page={$page}&created_after={$created_after}8&created_before={$created_before}fields=id%2Ctitle%2Curl%2Cviews_total%2Clikes_total%2Caudience%2Caudience_total%2Ccreated_time%2Cdescription%2Clanguage%2Cstatus%2Cupdated_time%2Cviews_last_day%2Cviews_last_hour%2Cviews_last_month%2Cviews_last_week%2Cwidth",
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
    }
    else {
        if (array_key_exists('list',$video_data)) {
            foreach ($video_data['list'] as $data) {
                if (array_key_exists('available_formats',$data)) {
                    $available_format = serialize($data['available_formats']);
                }
                if (array_key_exists('custom_classification',$data)) {
                    $custom_classification = serialize($data['custom_classification']);
                }
                if (array_key_exists('geoblocking',$data)) {
                    $geoblocking = serialize($data['geoblocking']);
                }
                if (array_key_exists('tags',$data)) {
                    $tags = serialize($data['tags']);
                }
                $custom_imported_date = date("Y-m-d H:i:s");
                if (array_key_exists('created_time',$data)) {
                    $created_time = date('Y-m-d H:i:s', $data['created_time']);
                }
                if (array_key_exists('updated_time',$data)) {
                    $updated_time = date('Y-m-d H:i:s', $data['updated_time']);
                }
                if (array_key_exists('title',$data)) {
                    $title = $conn->real_escape_string($data['title']);
                }

                $sql5 = "INSERT INTO `videos_daily`(video_id,title,views_total,likes_total,audience,audience_total,created_time,description,language,status,updated_time,views_last_day,views_last_hour,views_last_month,views_last_week,page_name,custom_imported_date) VALUES ('".$data['id']."','".$title."','".$data['views_total']."','".$data['likes_total']."','".$data['audience']."','".$data['audience_total']."','".$created_time."','".$data['description']."','".$data['language']."','".$data['status']."','".$updated_time."','".$data['views_last_day']."','".$data['views_last_hour']."','".$data['views_last_month']."','".$data['views_last_week']."','".$page_name."','".$custom_imported_date."')";
                $result = $conn->query($sql5);
            }
            $has_more = $video_data['has_more'];
        }
    }
    return $has_more;
}