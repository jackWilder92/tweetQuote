<?php
session_start();                        //Start session
require 'autoload.php';                 //Twitter library
include_once 'configs.php';             //Add consumer Data
use Abraham\TwitterOAuth\TwitterOAuth;  //Calling namespace of twitter library by Abraham

//Steps to authorise app and get user oauth_token and oauth_token_secret
if(!isset($_SESSION['access_token'])) {                                 //Check app is authorized by user
    delete_files('./pictures2');                                        //Delete last session data
    $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET);       //Create connection with twitter OAuth api
    $request_token = $connection->oauth('oauth/request_token',array('oauth_callback'=> OAUTH_CALLBACK)); //Request for user token
    $_SESSION['oauth_token'] = $request_token['oauth_token'];           //Get user Oauth_Token
    $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret']; //Get user OAuth_Token_secret
    $url = $connection->url('oauth/authorize',array('oauth_token' => $request_token['oauth_token'])); //Authorise user
    #echo $url;
    header('Location: '. $url.''); //redirect
} 
//After authorization media tweet upload 
else {                                              //When application is authorized by user
    if (!file_exists('./pictures2')) {
        mkdir('./pictures2', 0777, true);           //Make Directory if not exist
    }
    $access_token = $_SESSION['access_token'];
    $connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,$access_token['oauth_token'],$access_token['oauth_token_secret']);
    $user = $connection -> get("account/verify_credentials");
    
    $countall = glob('./pictures/*.jpg');   
    $countpost = glob('./pictures2/*.jpg');
    
    $flag = true;
    while ($flag) {
        $random = rand(1,count($countall));     
        $media = $random.'.jpg';                        //Find random image from directory
        if((count($countall)==count($countpost))){      //Check for unique images avilability
            echo "No unique pitures available";
            break;
        }
        if (!file_exists('./pictures2/'.$media)) {      //If picture is unique
            $mediaUp = $connection -> upload('media/upload',['media' =>'./pictures/'.$media]);  //Upload media
            $tweet = $connection -> post('statuses/update',['media_ids' => $mediaUp->media_id, 'status' => 'Image Tweeting']); //post on twitter
            copy('./pictures/'.$media,'./pictures2/'.$media);       
            echo '<img src = "./pictures/'.$media.'"alt="'.$media.'"/>';
            echo "Image posted.....";
            $flag = false;
        }
    }
}

//Function for deleting images
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
