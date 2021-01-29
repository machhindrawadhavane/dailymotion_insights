<?php
require_once 'db_connection.php';

function getUsers() {
    $access_token = $page_name = $owner_id = $birthday = $created_time = '';
    $conn = createConnection();
    
    $sql5 = "SELECT st.token, st.ownerId, st.pageName FROM statuses as st";
    $token_data = $conn->query($sql5);
    $tokens = $token_data->fetch_all(MYSQLI_ASSOC);
    
    $curl = curl_init();
    
    foreach ($tokens as $token) {
        if (array_key_exists('token',$token)) {
            $access_token = $token['token'];
        }
        if (array_key_exists('ownerId',$token)) {
            $owner_id = $token['ownerId'];
        }
        if (array_key_exists('pageName',$token)) {
            $page_name = $token['pageName'];
        }
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.dailymotion.com/users?ids={$owner_id}&fields=views_total%2Cvideos_total%2Crevenues_website_last_week%2Crevenues_video_last_day%2Crevenues_paidcontent_total%2Crevenues_video_last_month%2Crevenues_paidcontent_last_day%2Crevenues_claim_total%2Crevenues_paidcontent_last_month%2Crevenues_website_total%2Cis_following%2Crevenues_video_total%2Crevenues_website_last_day%2Cactive%2Citem_type%2Caddress%2Clast_name%2Cpinterest_url%2Cchildren_total%2Cbirthday%2Creposts_total%2Ccity%2Ccountry%2Crevenues_claim_last_week%2Cfacebook_url%2Ccover_url%2Ccreated_time%2Cdescription%2Cemail%2Crevenues_paidcontent_last_week%2Cid%2Cgoogleplus_url%2Cgender%2Cfirst_name%2Cfullname%2Cfollowers_total%2Cfollowing_total%2Crevenues_video_last_week%2Cparent%2Clinkedin_url%2Climits%2Clanguage%2Crevenues_website_last_month%2Crevenues_claim_last_month%2Crevenues_claim_last_day%2Cpartner%2Cplaylists_total%2Cstatus%2Curl%2Cscreenname%2Ctwitter_url%2Cinstagram_url%2Cusername%2Cverified%2Cwebsite_url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
              "authorization: Bearer {$access_token}",
              "cache-control: no-cache",
              "postman-token: 98cf00cb-3a80-d947-2545-4337ff7bfbbb"
            ),
          ));
    
          $response = curl_exec($curl);
          $err = curl_error($curl);
    
          if ($err) {
            echo "cURL Error #:" . $err;
          } else {
            $response = json_decode($response,true);
            if (array_key_exists('error',$response)) {
                //code for logging error into a given file 
                $error_message = $response['error']['message']." for ".$page_name; 
                $log_file = "error-log/errors.log"; 
                ini_set("log_errors", TRUE);  
                ini_set('error_log', $log_file); 
                error_log($error_message); 
            }
            else if (array_key_exists('list',$response) && array_key_exists('0',$response['list'])) {
                $data = $response['list'][0];
                if (array_key_exists('limits',$data)) {
                    $limits = serialize($data['limits']);
                }
                if (array_key_exists('birthday',$data)) {
                    $birthday = date('Y-m-d H:i:s', $data['birthday']);
                }
                if (array_key_exists('created_time',$data)) {
                    $created_time = date('Y-m-d H:i:s', $data['created_time']);
                }
                $custom_imported_date = date("Y-m-d H:i:s");
                $sql1 = "SELECT id FROM users_insights WHERE user_id='".$data['id']."'";
                $user_video = $conn->query($sql1);
                if ($user_video->num_rows > 0) {
                    $sql2="UPDATE users_insights SET followers_total = '".$data['followers_total']."', following_total = '".$data['following_total']."' , playlists_total = '".$data['playlists_total']."' , reposts_total ='".$data['reposts_total']."', revenues_claim_last_day ='".$data['revenues_claim_last_day']."', revenues_claim_last_month ='".$data['revenues_claim_last_month']."', revenues_claim_last_week ='".$data['revenues_claim_last_week']."', revenues_claim_total ='".$data['revenues_claim_total']."', revenues_paidcontent_last_day ='".$data['revenues_paidcontent_last_day']."', revenues_paidcontent_last_month ='".$data['revenues_paidcontent_last_month']."', revenues_paidcontent_last_week ='".$data['revenues_paidcontent_last_week']."', revenues_paidcontent_total ='".$data['revenues_paidcontent_total']."', revenues_video_last_day ='".$data['revenues_video_last_day']."', revenues_video_last_month ='".$data['revenues_video_last_month']."', revenues_video_last_week ='".$data['revenues_video_last_week']."', revenues_video_total ='".$data['revenues_video_total']."', revenues_website_last_day ='".$data['revenues_website_last_day']."', revenues_website_last_month ='".$data['revenues_website_last_month']."', revenues_website_last_week ='".$data['revenues_website_last_week']."', revenues_website_total ='".$data['revenues_website_total']."', videos_total ='".$data['videos_total']."', views_total ='".$data['views_total']."', custom_updated_date = '".$custom_imported_date."' WHERE user_id='".$data['id']."'";
                    $update_videos = $conn->query($sql2);
                }
                else {
                    $sql3 = "INSERT INTO `users_insights`(user_id,active,address,birthday,children_total,city,country,cover_url,created_time,description,email,facebook_url,first_name,followers_total,following_total,fullname,gender,googleplus_url,instagram_url,is_following,item_type,language,last_name,limits,linkedin_url,parent,partner,pinterest_url,playlists_total,reposts_total,revenues_claim_last_day,revenues_claim_last_month,revenues_claim_last_week,revenues_claim_total,revenues_paidcontent_last_day,revenues_paidcontent_last_month,revenues_paidcontent_last_week,revenues_paidcontent_total,revenues_video_last_day,revenues_video_last_month,revenues_video_last_week,revenues_video_total,revenues_website_last_day,revenues_website_last_month,revenues_website_last_week,revenues_website_total,screenname,status,twitter_url,url,username,verified,videos_total,views_total,website_url, custom_imported_date) VALUES ('".$data['id']."','".$data['active']."','".$data['address']."','".$birthday."','".$data['children_total']."','".$data['city']."','".$data['country']."','".$data['cover_url']."','".$created_time."','".$data['description']."','".$data['email']."','".$data['facebook_url']."','".$data['first_name']."','".$data['followers_total']."','".$data['following_total']."','".$data['fullname']."','".$data['gender']."','".$data['googleplus_url']."','".$data['instagram_url']."','".$data['is_following']."','".$data['item_type']."','".$data['language']."','".$data['last_name']."','".$limits."','".$data['linkedin_url']."','".$data['parent']."','".$data['partner']."','".$data['pinterest_url']."','".$data['playlists_total']."','".$data['reposts_total']."','".$data['revenues_claim_last_day']."','".$data['revenues_claim_last_month']."','".$data['revenues_claim_last_week']."','".$data['revenues_claim_total']."','".$data['revenues_paidcontent_last_day']."','".$data['revenues_paidcontent_last_month']."','".$data['revenues_paidcontent_last_week']."','".$data['revenues_paidcontent_total']."','".$data['revenues_video_last_day']."','".$data['revenues_video_last_month']."','".$data['revenues_video_last_week']."','".$data['revenues_video_total']."','".$data['revenues_website_last_day']."','".$data['revenues_website_last_month']."','".$data['revenues_website_last_week']."','".$data['revenues_website_total']."','".$data['screenname']."','".$data['status']."','".$data['twitter_url']."','".$data['url']."','".$data['username']."','".$data['verified']."','".$data['videos_total']."','".$data['views_total']."','".$data['website_url']."','".$custom_imported_date."')";
                    $conn->query($sql3);
                }
            }
        }
    }
}
