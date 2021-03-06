<?php

require_once 'db_connection.php';
$conn = createConnection();
$available_format = $custom_classification = $geoblocking = $tags = $created_time = $updated_time = $title = $views_total = $likes_total = $audience_total = $views_last_day = $audience_total = $views_last_day = $views_last_hour = $views_last_month = $views_last_week = '';
$sql5 = "SELECT st.token, st.ownerId, st.pageName, st.since, st.until FROM statuses as st";
$token_data = $conn->query($sql5);
$tokens = $token_data->fetch_all(MYSQLI_ASSOC);
$curl = curl_init();
foreach ($tokens as $token) {
    $access_token = $token['token'];
    $page_name = $token['pageName'];
    $owner_id = $token['ownerId'];
    $since = $token['since'];
    $until = $token['until'];
    if ($since == '0000-00-00 00:00:00' && $until == '0000-00-00 00:00:00') {
        $since = date("Y-m-d", strtotime('2019-10-01'));
        $until = date("Y-m-d", strtotime("+1 Month", strtotime($since)));
    }
    else {
        $since = date("Y-m-d", strtotime($token['until']));
        $until = date("Y-m-d", strtotime("+1 Month", strtotime($token['until'])));;
    }
    $date_now = date("Y-m-d"); // this format is string comparable

    if ($date_now > $since || $date_now > $until) {
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.dailymotion.com/videos?user={$owner_id}&created_after={$since}&created_before={$until}&fields=id%2Ctitle%2Curl%2Cviews_total%2Clikes_total%2Cchannel%2Cadvertising_instream_blocked%2Callow_embed%2Callowed_in_playlists%2Caspect_ratio%2Caudience%2Caudience_total%2Cavailable_formats%2Cchecksum%2Ccountry%2Ccreated_time%2Ccustom_classification%2Cdescription%2Cduration%2Cembed_html%2Cembed_url%2Cencoding_progress%2Cend_time%2Cexpiry_date%2Cexpiry_date_deletion%2Cexplicit%2Cfilmstrip_60_url%2Cgeoblocking%2Cgeoloc%2Cheight%2Citem_type%2Clanguage%2Cliked_at%2Clive_ad_break_end_time%2Clive_ad_break_remaining%2Clive_airing_time%2Clive_audio_bitrate%2Clive_auto_record%2Clive_ingests%2Clive_publish_url%2Cmedia_type%2Cmode%2Conair%2Cowner%2Cpartner%2Cpreview_240p_url%2Cpreview_360p_url%2Cpreview_480p_url%2Cprivate%2Cprivate_id%2Cpublish_date%2Cpublished%2Cpublishing_progress%2Crecord_end_time%2Crecord_start_time%2Crecord_status%2Crecurrence%2Cstatus%2Ctags%2Cupdated_time%2Cverified%2Cviews_last_day%2Cviews_last_hour%2Cviews_last_month%2Cviews_last_week%2Cwidth",
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
            if (array_key_exists('list',$video_data)) {
                $sql6="UPDATE statuses SET since = '".$since."', until = '".$until."' WHERE pageName='".$page_name."'";
                $conn->query($sql6);
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
                    if ($data['title']) {
                        $title = $conn->real_escape_string($data['title']);
                    }
                    $sql1 = "SELECT id FROM videos_insights WHERE video_id='".$data['id']."'";
                    $get_video = $conn->query($sql1);
                    $views_total = array_key_exists('views_total',$data) ? $data['views_total'] : 0;
                    $likes_total = array_key_exists('likes_total',$data) ? $data['likes_total'] : 0;
                    $audience_total = array_key_exists('audience_total',$data) ? $data['audience_total'] : 0;
                    $views_last_day = array_key_exists('views_last_day',$data) ? $data['views_last_day'] : 0;
                    $views_last_hour = array_key_exists('views_last_hour',$data) ? $data['views_last_hour'] : 0;
                    $views_last_month = array_key_exists('views_last_month',$data) ? $data['views_last_month'] : 0;
                    $views_last_week = array_key_exists('views_last_week',$data) ? $data['views_last_week'] : 0;
                    if ($get_video->num_rows > 0) {
                        $sql2="UPDATE videos_insights SET views_total = '".$views_total."', likes_total = '".$likes_total."' , audience_total = '".$audience_total."' , views_last_day ='".$views_last_day."', views_last_hour ='".$views_last_hour."', views_last_month ='".$views_last_month."', views_last_week ='".$views_last_week."', custom_updated_date ='".$custom_imported_date."' WHERE video_id='".$data['id']."'";
                        $update_videos = $conn->query($sql2);
                    }
                    else {
                        $sql = "INSERT INTO `videos_insights`(video_id,title,url,views_total,likes_total,channel,advertising_instream_blocked,allow_embed,allowed_in_playlists,aspect_ratio,audience,audience_total,available_formats,checksum,country,created_time,custom_classification,duration,embed_html,embed_url,encoding_progress,end_time,expiry_date,expiry_date_deletion,explicit,filmstrip_60_url,geoblocking,geoloc,height,item_type,language,liked_at,live_ad_break_end_time,live_ad_break_remaining,live_airing_time,live_audio_bitrate,live_auto_record,live_ingests,live_publish_url,media_type,mode,onair,owner,partner,preview_240p_url,preview_360p_url,preview_480p_url,private,private_id,publish_date,published,publishing_progress,record_end_time,record_start_time,record_status,recurrence,status,tags,updated_time,verified,views_last_day,views_last_hour,views_last_month,views_last_week,width,page_name,custom_imported_date) 
                        VALUES ('".$data['id']."','".$title."','".$data['url']."','".$data['views_total']."','".$data['likes_total']."','".$data['channel']."','".$data['advertising_instream_blocked']."','".$data['allow_embed']."','".$data['allowed_in_playlists']."','".$data['aspect_ratio']."','".$data['audience']."','".$data['audience_total']."','".$available_format."','".$data['checksum']."','".$data['country']."','".$created_time."','".$custom_classification."','".$data['duration']."','".$data['embed_html']."','".$data['embed_url']."','".$data['encoding_progress']."','".$data['end_time']."','".$data['expiry_date']."','".$data['expiry_date_deletion']."','".$data['explicit']."','".$data['filmstrip_60_url']."','".$geoblocking."','".$data['geoloc']."','".$data['height']."','".$data['item_type']."','".$data['language']."','".$data['liked_at']."','".$data['live_ad_break_end_time']."','".$data['live_ad_break_remaining']."','".$data['live_airing_time']."','".$data['live_audio_bitrate']."','".$data['live_auto_record']."','".$data['live_ingests']['Default (recommended)']."','".$data['live_publish_url']."','".$data['media_type']."','".$data['mode']."','".$data['onair']."','".$data['owner']."','".$data['partner']."','".$data['preview_240p_url']."','".$data['preview_360p_url']."','".$data['preview_480p_url']."','".$data['private']."','".$data['private_id']."','".$data['publish_date']."','".$data['published']."','".$data['publishing_progress']."','".$data['record_end_time']."','".$data['record_start_time']."','".$data['record_status']."','".$data['recurrence']."','".$data['status']."','".$tags."','".$updated_time."','".$data['verified']."','".$data['views_last_day']."','".$data['views_last_hour']."','".$data['views_last_month']."','".$data['views_last_week']."','".$data['width']."','".$page_name."','".$custom_imported_date."')";
                        $result = $conn->query($sql);
                    }
                }
                $page = 0;
                $has_more = FALSE;
                $page = $video_data['page'];
                if (array_key_exists('has_more',$video_data) && $video_data['has_more']) {
                    $has_more = $video_data['has_more'];
                    $page++;
                    while ($has_more) {
                        $has_more = get_allvideo_insights($page,$access_token,$conn, $page_name, $owner_id, $since, $until);
                        $page++;
                    }
                }
            }
        }
    }
    else {
        // print('not run');
    }
}

//Get more pages
function get_allvideo_insights($page, $access_token, $conn, $page_name, $owner_id, $since, $until) {
    $available_format = $custom_classification = $geoblocking = $tags = $created_time = $updated_time = $title = $views_total = $likes_total = $audience_total = $views_last_day = $audience_total = $views_last_day = $views_last_hour = $views_last_month = $views_last_week = '';
    $has_more = FALSE;
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.dailymotion.com/videos?user={$owner_id}&page={$page}&created_after={$since}&created_before={$until}&fields=id%2Ctitle%2Curl%2Cviews_total%2Clikes_total%2Cchannel%2Cadvertising_instream_blocked%2Callow_embed%2Callowed_in_playlists%2Caspect_ratio%2Caudience%2Caudience_total%2Cavailable_formats%2Cchecksum%2Ccountry%2Ccreated_time%2Ccustom_classification%2Cdescription%2Cduration%2Cembed_html%2Cembed_url%2Cencoding_progress%2Cend_time%2Cexpiry_date%2Cexpiry_date_deletion%2Cexplicit%2Cfilmstrip_60_url%2Cgeoblocking%2Cgeoloc%2Cheight%2Citem_type%2Clanguage%2Cliked_at%2Clive_ad_break_end_time%2Clive_ad_break_remaining%2Clive_airing_time%2Clive_audio_bitrate%2Clive_auto_record%2Clive_ingests%2Clive_publish_url%2Cmedia_type%2Cmode%2Conair%2Cowner%2Cpartner%2Cpreview_240p_url%2Cpreview_360p_url%2Cpreview_480p_url%2Cprivate%2Cprivate_id%2Cpublish_date%2Cpublished%2Cpublishing_progress%2Crecord_end_time%2Crecord_start_time%2Crecord_status%2Crecurrence%2Cstatus%2Ctags%2Cupdated_time%2Cverified%2Cviews_last_day%2Cviews_last_hour%2Cviews_last_month%2Cviews_last_week%2Cwidth",
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
                if ($data['title']) {
                    $title = $conn->real_escape_string($data['title']);
                }
                $views_total = array_key_exists('views_total',$data) ? $data['views_total'] : 0;
                $likes_total = array_key_exists('likes_total',$data) ? $data['likes_total'] : 0;
                $audience_total = array_key_exists('audience_total',$data) ? $data['audience_total'] : 0;
                $views_last_day = array_key_exists('views_last_day',$data) ? $data['views_last_day'] : 0;
                $views_last_hour = array_key_exists('views_last_hour',$data) ? $data['views_last_hour'] : 0;
                $views_last_month = array_key_exists('views_last_month',$data) ? $data['views_last_month'] : 0;
                $views_last_week = array_key_exists('views_last_week',$data) ? $data['views_last_week'] : 0;
                $sql3 = "SELECT id FROM videos_insights WHERE video_id='".$data['id']."'";
                $get_video = $conn->query($sql3);
                if ($get_video->num_rows > 0) {
                    $sql4="UPDATE videos_insights SET views_total = '".$views_total."', likes_total = '".$likes_total."' , audience_total = '".$audience_total."' , views_last_day ='".$views_last_day."', views_last_hour ='".$views_last_hour."', views_last_month ='".$views_last_month."', views_last_week ='".$views_last_week."', custom_updated_date ='".$custom_imported_date."' WHERE video_id='".$data['id']."'";
                    $update_videos = $conn->query($sql4);
                }
                else {
                    $sql5 = "INSERT INTO `videos_insights`(video_id,title,url,views_total,likes_total,channel,advertising_instream_blocked,allow_embed,allowed_in_playlists,aspect_ratio,audience,audience_total,available_formats,checksum,country,created_time,custom_classification,duration,embed_html,embed_url,encoding_progress,end_time,expiry_date,expiry_date_deletion,explicit,filmstrip_60_url,geoblocking,geoloc,height,item_type,language,liked_at,live_ad_break_end_time,live_ad_break_remaining,live_airing_time,live_audio_bitrate,live_auto_record,live_ingests,live_publish_url,media_type,mode,onair,owner,partner,preview_240p_url,preview_360p_url,preview_480p_url,private,private_id,publish_date,published,publishing_progress,record_end_time,record_start_time,record_status,recurrence,status,tags,updated_time,verified,views_last_day,views_last_hour,views_last_month,views_last_week,width,page_name,custom_imported_date) 
                    VALUES ('".$data['id']."','".$title."','".$data['url']."','".$data['views_total']."','".$data['likes_total']."','".$data['channel']."','".$data['advertising_instream_blocked']."','".$data['allow_embed']."','".$data['allowed_in_playlists']."','".$data['aspect_ratio']."','".$data['audience']."','".$data['audience_total']."','".$available_format."','".$data['checksum']."','".$data['country']."','".$created_time."','".$custom_classification."','".$data['duration']."','".$data['embed_html']."','".$data['embed_url']."','".$data['encoding_progress']."','".$data['end_time']."','".$data['expiry_date']."','".$data['expiry_date_deletion']."','".$data['explicit']."','".$data['filmstrip_60_url']."','".$geoblocking."','".$data['geoloc']."','".$data['height']."','".$data['item_type']."','".$data['language']."','".$data['liked_at']."','".$data['live_ad_break_end_time']."','".$data['live_ad_break_remaining']."','".$data['live_airing_time']."','".$data['live_audio_bitrate']."','".$data['live_auto_record']."','".$data['live_ingests']['Default (recommended)']."','".$data['live_publish_url']."','".$data['media_type']."','".$data['mode']."','".$data['onair']."','".$data['owner']."','".$data['partner']."','".$data['preview_240p_url']."','".$data['preview_360p_url']."','".$data['preview_480p_url']."','".$data['private']."','".$data['private_id']."','".$data['publish_date']."','".$data['published']."','".$data['publishing_progress']."','".$data['record_end_time']."','".$data['record_start_time']."','".$data['record_status']."','".$data['recurrence']."','".$data['status']."','".$tags."','".$updated_time."','".$data['verified']."','".$data['views_last_day']."','".$data['views_last_hour']."','".$data['views_last_month']."','".$data['views_last_week']."','".$data['width']."','".$page_name."','".$custom_imported_date."')";
                    $result = $conn->query($sql5);
                }
            }
            $has_more = $video_data['has_more'];
        }
    }
    return $has_more;
}