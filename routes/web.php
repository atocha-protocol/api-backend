<?php


use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Support\Facades\Route;
//use Illuminate\Http\Request;

Route::get('/', function () {
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> "http://localhost/callback"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));
    return view('home', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

// http://localhost/callback?oauth_token=Q1aopAAAAAABbJKUAAABgS8p8Gw&oauth_verifier=mZJvId1Hu7ZkfQesDDjvTnwTkcalsaoX
Route::get('/callback', function () {
    $oauth_token = request()->get('oauth_token');
    $oauth_verifier = request()->get('oauth_verifier');

    //    oauth/access_token
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $oauth_infos = $connection->oauth('oauth/access_token', [
        'oauth_consumer_key'=> env('TWITTER_CONSUMER_KEY'),
        'oauth_token'=> $oauth_token,
        'oauth_verifier'=> $oauth_verifier,
    ]);
    // Array ( [oauth_token] => 1511200895165296648-jANgwBzreieJhF7I7j3p7ARPSa8g8h [oauth_token_secret] => 5X4fwPvH0lK4gQoTdg5CBKurK98aoTKh8viw44B0TRbr6 [user_id] => 1511200895165296648 [screen_name] => AtochaGuild )
    $oauth_token = $oauth_infos['oauth_token'];
    $oauth_token_secret = $oauth_infos['oauth_token_secret'];
    $user_id = $oauth_infos['user_id'];
    $screen_name = $oauth_infos['screen_name'];

    return view('callback', [
            'oauth_token'=>$oauth_token,
            'oauth_token_secret'=> $oauth_token_secret,
            'user_id'=>$user_id,
            'screen_name'=>$screen_name,
        ]
    );
});
