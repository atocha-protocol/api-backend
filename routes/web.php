<?php


use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
//use Illuminate\Http\Request;

Route::get('/', function () {
//    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
//    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> "http://localhost:6999/callback"]);
//    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));
//    return view('home', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
    echo 'api.atocha.io';
});

Route::get('/bind/{ato_address}/{ref?}', function (Request $request, $ato_address, $ref) {
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> env('APP_URL')."/callback_bind"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));

    $request->session()->put('ato_address', $ato_address);
    $request->session()->put('bind_ato_ref', $ref);

    return view('bindredirect', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

Route::get('/unbind/{ato_address}/{ref}', function (Request $request, $ato_address, $ref) {
    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> env('APP_URL')."/callback_unbind"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));

    $request->session()->put('ato_address', $ato_address);
    $request->session()->put('bind_ato_ref', $ref);

    return view('bindredirect', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

// http://localhost/callback?oauth_token=Q1aopAAAAAABbJKUAAABgS8p8Gw&oauth_verifier=mZJvId1Hu7ZkfQesDDjvTnwTkcalsaoX
Route::get('/callback_bind', function (Request $request) {
    $oauth_token = request()->get('oauth_token');
    $oauth_verifier = request()->get('oauth_verifier');
    $db_ato_address = trim($request->session()->get('ato_address', ''));

    //
    try{
        $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
        $oauth_infos = $connection->oauth('oauth/access_token', [
            'oauth_consumer_key'=> env('TWITTER_CONSUMER_KEY'),
            'oauth_token'=> $oauth_token,
            'oauth_verifier'=> $oauth_verifier,
        ]);
    }catch (Exception $exc){
        $encode_data = [
            'status' => 'failed',
            'tip' => $exc->getMessage(),
        ];
        return toRefIfExists($encode_data, $request);
    }

    // Array ( [oauth_token] => 1511200895165296648-jANgwBzreieJhF7I7j3p7ARPSa8g8h [oauth_token_secret] => 5X4fwPvH0lK4gQoTdg5CBKurK98aoTKh8viw44B0TRbr6 [user_id] => 1511200895165296648 [screen_name] => AtochaGuild )
    $oauth_token = $oauth_infos['oauth_token'];
    $oauth_token_secret = $oauth_infos['oauth_token_secret'];
    $user_id = $oauth_infos['user_id'];
    $screen_name = $oauth_infos['screen_name'];

    $user_show = curlTwitterData($connection->url('1.1/users/show.json', array('user_id' =>$user_id, 'screen_name'=>$screen_name)));

    // ato_address,twitter_screen_name
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
        return toRefIfExists($encode_data, $request);
    }

    // check user
    $bind_user_exists = DB::table('twitter_bind')
        ->where('ato_address', '=', $db_ato_address )
        ->exists();

    // check user
    if($bind_user_exists) {
        try{
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
        }catch (Exception $exc) {
            $encode_data = [
                'status' => 'failed',
                'tip' => "Duplicate entry '{$db_twitter_screen_name}' for key 'twitter_bind",
            ];
            return toRefIfExists($encode_data, $request);
        }
    }else{
        // check screen_name exists.
        // check user
        $bind_twitter_exists = DB::table('twitter_bind')
            ->where('twitter_screen_name', '=', $db_twitter_screen_name )
            ->exists();

        if($bind_twitter_exists) {
            $encode_data = [
                'status' => 'failed',
                'tip' => "{$db_twitter_screen_name} is already bound.",
            ];
            return toRefIfExists($encode_data, $request);
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


    return toRefIfExists($encode_data, $request);

});

function toRefIfExists($encode_data, $request) {
    $db_bind_ato_ref = trim($request->session()->get('bind_ato_ref', ''));
    if($db_bind_ato_ref != '') {
        header("Location: $db_bind_ato_ref?status={$encode_data->status}");
        exit;
    }
    return json_encode($encode_data);
}

Route::get('/callback_unbind', function (Request $request) {
    $oauth_token = request()->get('oauth_token');
    $oauth_verifier = request()->get('oauth_verifier');
    $db_ato_address = trim($request->session()->get('ato_address', ''));

    // oauth/access_token
    $connection = null;
    $oauth_infos = [];
    try{
        $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
        $oauth_infos = $connection->oauth('oauth/access_token', [
            'oauth_consumer_key'=> env('TWITTER_CONSUMER_KEY'),
            'oauth_token'=> $oauth_token,
            'oauth_verifier'=> $oauth_verifier,
        ]);
    }catch (Exception $exc){
        $encode_data = [
            'status' => 'failed',
            'tip' => $exc->getMessage(),
        ];
        return json_encode($encode_data);
    }

    // ato_address,twitter_screen_name
    $db_twitter_screen_name = $oauth_infos['screen_name'];

    if ('' == $db_ato_address) {
        $encode_data = [
            'status' => 'failed',
            'tip' => 'Need bind address',
        ];
        return json_encode($encode_data);
    }

    // check user
    $bind_user_exists = DB::table('twitter_bind')
        ->where('ato_address', '=', $db_ato_address )
        ->where('twitter_screen_name', '=', $db_twitter_screen_name)
        ->exists();

    // check user
    if($bind_user_exists) {


        DB::table('twitter_bind')
            ->where('ato_address', '=', $db_ato_address )
            ->where('twitter_screen_name', '=', $db_twitter_screen_name)
            ->update(
            [
                'twitter_screen_name' => null,
                'twitter_profile_image_url' => null,
                'twitter_profile_image_url_https' => null,
                'oauth_token' => null,
                'oauth_token_secret' => null,
                'twitter_full_data' => null,
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
                'tip' => "No binding found, {$db_ato_address} & {$db_twitter_screen_name}",
            ];
            return json_encode($encode_data);
        }

    }

    $encode_data = [
        'status' => 'success',
        'data' => [
            'ato_address' => $db_ato_address,
            'twitter_screen_name' => $db_twitter_screen_name,
        ],
    ];
    return json_encode($encode_data);

});

Route::get('/twitter_bind/{ato_address}', function (Request $request, $ato_address) {
    $bind_user = DB::table('twitter_bind')
        ->where('ato_address', '=', $ato_address )
        ->first();
    if(!$bind_user || !$bind_user->twitter_screen_name) {
        $encode_data = [
            'status' => 'failed',
            'tip' => "No binding found, {$ato_address}",
        ];
        return json_encode($encode_data);
    }
    $encode_data = [
        'status' => 'success',
        'data' => [
            'ato_address' => $ato_address,
            'twitter_screen_name' => $bind_user->twitter_screen_name,
            'twitter_profile_image_url_https' => $bind_user->twitter_profile_image_url_https,
        ],
    ];
    return json_encode($encode_data);
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


