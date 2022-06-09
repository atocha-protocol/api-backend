<?php


use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
//use Illuminate\Http\Request;

Route::get('/', function () {
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> "http://localhost:6999/callback"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));
    return view('home', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

Route::get('/bind/{ato_address}', function (Request $request, $ato_address) {
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> "http://localhost:6999/callback"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));

    $request->session()->put('ato_address', $ato_address);

    return view('home', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

// http://localhost/callback?oauth_token=Q1aopAAAAAABbJKUAAABgS8p8Gw&oauth_verifier=mZJvId1Hu7ZkfQesDDjvTnwTkcalsaoX
Route::get('/callback', function (Request $request) {
    $oauth_token = request()->get('oauth_token');
    $oauth_verifier = request()->get('oauth_verifier');

    // oauth/access_token
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

    $user_show = curlTwitterData($connection->url('1.1/users/show.json', array('user_id' =>$user_id, 'screen_name'=>$screen_name)));

    $twiter_info = [
        'access_token' => $oauth_infos,
        'user_show' => $user_show,
    ];

    // ato_address,twitter_screen_name
    $db_ato_address = trim($request->session()->get('ato_address', ''));
    $db_twitter_screen_name = $oauth_infos['screen_name'];
    $db_oauth_token = $oauth_token;
    $db_oauth_token_secret = $oauth_token_secret;
    $db_profile_image_url = $user_show->profile_image_url;
    $db_profile_image_url_https = $user_show->profile_image_url_https;

    if ('' == $db_ato_address) {
        $encode_data = [
            'status' => 'failed',
            'tip' => 'Need bind address',
        ];
        return view('show_json', ['encode_data'=>$encode_data, ]);
    }

    // check user
    $bind_user_exists = DB::table('twitter_bind')
        ->where('ato_address', '=', $db_ato_address )
        ->exists();

    if($bind_user_exists) {
        DB::table('twitter_bind')->where('ato_address', '=', $db_ato_address )->update(
            [
                'twitter_screen_name' => $db_twitter_screen_name,
                'twitter_profile_image_url' => $db_profile_image_url,
                'twitter_profile_image_url_https' => $db_profile_image_url_https,
                'oauth_token' => $db_oauth_token,
                'oauth_token_secret' => $db_oauth_token_secret,
                'twitter_full_data' => json_encode($oauth_infos),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
    }else{
        // check screen_name exists.
        // check user
        $bind_twitter_exists = DB::table('twitter_bind')
            ->where('twitter_screen_name', '=', $db_twitter_screen_name )
            ->exists();

        if($bind_twitter_exists) {
            $encode_data = [
                'status' => 'failed',
                'tip' => 'twitter_screen_name exists::'.$db_twitter_screen_name,
            ];
            return view('show_json', ['encode_data'=>$encode_data, ]);
        }

        // with insert
        DB::table('twitter_bind')->insert([
            'ato_address' => $db_ato_address,
            'twitter_screen_name' => $db_twitter_screen_name,
            'twitter_profile_image_url' => $db_profile_image_url,
            'twitter_profile_image_url_https' => $db_profile_image_url_https,
            'oauth_token' => $db_oauth_token,
            'oauth_token_secret' => $db_oauth_token_secret,
            'twitter_full_data' => json_encode($oauth_infos),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    $encode_data = [
        'status' => 'success',
        'data' => [
            'ato_address' => $db_ato_address,
            'twitter_screen_name' => $db_twitter_screen_name,
        ],
    ];
    return view('show_json', ['encode_data'=>$encode_data, ]);
//
//    return view('callback', [
//            'twiter_info'=>$twiter_info,
//        ]
//    );
});


// $url="https://api.twitter.com/2/users/".$config['twitter_id']."/followers";
function curlTwitterData($url) {

    $auth='Authorization: Bearer '.env('TWITTER_BEARER_TOKEN');

    $twitter=curl_init();
    curl_setopt($twitter, CURLOPT_URL, $url);
    curl_setopt($twitter, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $auth));
    curl_setopt($twitter, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($twitter, CURLOPT_FOLLOWLOCATION, 1);

    $result=json_decode(curl_exec($twitter));
    curl_close($twitter);
    return $result;
}
