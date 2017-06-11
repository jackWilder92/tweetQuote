<?php
session_start();
require 'autoload.php';
include_once 'configs.php';
use Abraham\TwitterOAuth\TwitterOAuth;

//First visit
if (!file_exists('./pictures2')) {
        mkdir('./pictures2', 0777, true);
}

//Steps to authorise app and get user oauth_token and oauth_token_secret
if(!isset($_SESSION['access_token'])) {
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
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token['oauth_token'],$access_token['oauth_token_secret']);
    $user = $connection -> get("account/verify_credentials");
    
    $random = rand(1,8);
    #$post = $connection -> post('statuses/update',array('status' => 'tweet it'));
    $media = $random.'.jpg';
    $mediaUp = $connection -> upload('media/upload',['media' =>'./pictures/'.$media]);
    #print_r($mediaUp);
    $tweet = $connection -> post('statuses/update',['media_ids' => $mediaUp->media_id, 'status' => 'Image Tweeting']);
    echo '<img src = "./pictures/'.$media.'"alt="'.$media.'"/>';
    echo "Image posted.....";
}
