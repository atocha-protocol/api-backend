<?php
namespace Tools;

class Tools{
    // $url="https://api.twitter.com/2/users/".$config['twitter_id']."/followers";
    public static function curlTwitterData($url) {

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

    public static function accessProhibited(): string
    {
        $encode_data = [
            'status' => 'failed',
            'tip' => "Access Prohibited",
        ];
        return json_encode($encode_data);
    }

    public static function toRefIfExists($encode_data, $request) {
        $db_bind_ato_ref = trim($request->session()->get('bind_ato_ref', ''));
        if($db_bind_ato_ref != '') {
            $db_bind_ato_ref = base64_decode($db_bind_ato_ref);
            $msg='';
            if($encode_data['status'] == 'failed') {
                $msg=urlencode($encode_data['tip']);
            }
            header("Location: $db_bind_ato_ref?status={$encode_data['status']}&msg={$msg}");
            exit;
        }
        return json_encode($encode_data);
    }
}


