<?php
session_start();
require 'autoload.php';
include_once 'configs.php';
use Abraham\TwitterOAuth\TwitterOAuth;

//Steps to authorise app and get user oauth_token and oauth_token_secret
if(!isset($_SESSION['access_token'])) {
    delete_files('./pictures2');
    $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET);
    $request_token = $connection->oauth('oauth/request_token',array('oauth_callback'=> OAUTH_CALLBACK));
    $_SESSION['oauth_token'] = $request_token['oauth_token'];
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
    $url = $connection->url('oauth/authorize',array('oauth_token' => $request_token['oauth_token']));
    #echo $url;
    header('Location: '. $url.'');
} 
//After authorization media tweet upload 
else {
    if (!file_exists('./pictures2')) {
        mkdir('./pictures2', 0777, true);
    }
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token['oauth_token'],$access_token['oauth_token_secret']);
    $user = $connection -> get("account/verify_credentials");
    
    $flag = true;
    while ($flag) {
        $random = rand(1,9);
        $media = $random.'.jpg';
        if (!file_exists('./pictures2/'.$media)) {
            $flag = false;
        }
        else {
            echo "Tweet exist ".$media."\n";
        }
    }
    $mediaUp = $connection -> upload('media/upload',['media' =>'./pictures/'.$media]);
    $tweet = $connection -> post('statuses/update',['media_ids' => $mediaUp->media_id, 'status' => 'Image Tweeting']);
    copy('./pictures/'.$media,'./pictures2/'.$media);
    echo '<img src = "./pictures/'.$media.'"alt="'.$media.'"/>';
    echo "Image posted.....";
}

function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
        
        foreach( $files as $file )
        {
            delete_files( $file );      
        }
      
        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}
