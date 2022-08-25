<?php


use Abraham\TwitterOAuth\TwitterOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
//use Illuminate\Http\Request;
use Noweh\TwitterApi\Client;
use Tools\Tools;
use Illuminate\Support\Facades\URL;

$url = config('app.url');
URL::forceRootUrl($url);

Route::get('/', function () {
//    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
//    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> "http://localhost:6999/callback"]);
//    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));
//    return view('home', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
    echo 'api.atocha.io';
    echo '<br/>';
    echo env('APP_URL');
    echo '<br/>';
    echo route('backpack.auth.register');
});


Route::get('/bind/{ato_address}/{ref}', function (Request $request, $ato_address, $ref) {

    $connection = new TwitterOAuth(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'), env('TWITTER_ACCESS_TOKEN'), env('TWITTER_ACCESS_TOKEN_SECRET'));
//    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> env('APP_URL')."/callback_bind"]);
    $request_tokens = $connection->oauth("oauth/request_token", ["oauth_callback"=> env('APP_URL')."/callback_bind", "x_auth_access_type"=>"write"]);
    $content = $connection->url('oauth/authorize', array('oauth_token' => $request_tokens['oauth_token']));

    $request->session()->put('ato_address', $ato_address);
    $request->session()->put('bind_ato_ref', $ref);

    return view('bindredirect', ['author'=>'Kami', 'content'=> $content, 'request_tokens'=>$request_tokens]);
});

Route::get('/test_bind', function (Request $request) {
    $test_url_data = base64_encode('http://www.baidu.com');
    $request->session()->put('bind_ato_ref', $test_url_data);
    return Tools::toRefIfExists($test_url_data, $request);
});

// https://api.atocha.io/bind/5DvNVQ69obcSG5KwxSmbZYrVPkwvaGSSH6EswjLuNeEvBCBs/aHR0cHM6Ly9wbGF5LmF0b2NoYS5pby9teV9ob21l
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
        return Tools::toRefIfExists($encode_data, $request);
    }

    // Array ( [oauth_token] => 1511200895165296648-jANgwBzreieJhF7I7j3p7ARPSa8g8h [oauth_token_secret] => 5X4fwPvH0lK4gQoTdg5CBKurK98aoTKh8viw44B0TRbr6 [user_id] => 1511200895165296648 [screen_name] => AtochaGuild )
    $oauth_token = $oauth_infos['oauth_token'];
    $oauth_token_secret = $oauth_infos['oauth_token_secret'];
    $user_id = $oauth_infos['user_id'];
    $screen_name = $oauth_infos['screen_name'];

    $user_show = Tools::curlTwitterData($connection->url('1.1/users/show.json', array('user_id' =>$user_id, 'screen_name'=>$screen_name)));

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
        return Tools::toRefIfExists($encode_data, $request);
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
            return Tools::toRefIfExists($encode_data, $request);
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
            return Tools::toRefIfExists($encode_data, $request);
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
    return Tools::toRefIfExists($encode_data, $request);
});



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
        return Tools::toRefIfExists($encode_data, $request);
    }

    // ato_address,twitter_screen_name
    $db_twitter_screen_name = $oauth_infos['screen_name'];

    if ('' == $db_ato_address) {
        $encode_data = [
            'status' => 'failed',
            'tip' => 'Need bind address',
        ];
        return Tools::toRefIfExists($encode_data, $request);
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
            return Tools::toRefIfExists($encode_data, $request);
        }

    }

    $encode_data = [
        'status' => 'success',
        'data' => [
            'ato_address' => $db_ato_address,
            'twitter_screen_name' => $db_twitter_screen_name,
        ],
    ];
    return Tools::toRefIfExists($encode_data, $request);

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

Route::get('/twitter_post/{ato_address}', function (Request $request, $ato_address) {
    return Tools::accessProhibited();

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

    $twitter_full_data = $bind_user->twitter_full_data;
    $twitter_full_data_obj = json_decode($twitter_full_data);

    $settings = [
        'account_id' => $twitter_full_data_obj->user_id,
        'consumer_key' => env('TWITTER_CONSUMER_KEY'),
        'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
        'bearer_token' => env('TWITTER_BEARER_TOKEN'),
        'access_token' => $bind_user->oauth_token,
        'access_token_secret' => $bind_user->oauth_token_secret
    ];

    $client = new Client($settings);
    $result = $client->tweet()->performRequest('POST', ['text' => 'Hello Atocha.']);
    $encode_data = [
        'status' => 'success',
        'tip' => $result,
    ];
    return json_encode($encode_data);
});

Route::get('task', 'App\Http\Controllers\TaskController@index')->name('task.index');
Route::get('task/rewardList', 'App\Http\Controllers\TaskController@rewardList')->name('task.rewardList');
Route::get('task/requestList/{request_owner}', 'App\Http\Controllers\TaskController@requestList')->name('task.requestList');
Route::post('task/do', 'App\Http\Controllers\TaskController@do')->name('task.do');
Route::post('task/apply', 'App\Http\Controllers\TaskController@apply')->name('task.apply');
Route::get('task/admin', 'App\Http\Controllers\TaskController@admin')->name('task.admin');
Route::get('task/payto', 'App\Http\Controllers\TaskController@payto')->name('task.payto');
