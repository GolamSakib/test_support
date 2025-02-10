<?php

namespace App\Utility;

use Illuminate\Http\Request;

class GetLocation
{
    public static function locationInfo()
    {
        $request=request();
        $ipaddress = $request->ip();
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
        $ipaddress='103.248.13.235';
//        $ch = curl_init();
//
//        $url = 'https://api.ipgeolocation.io/ipgeo?ip=' . $ipaddress . '&apiKey=70e8b805837a402588e345bf056c3343';
//
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//            'Content-Type: application/json',
//            'Accept: application/json',
//            'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']
//        ));
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//
//        $json = curl_exec($ch);
//        $error = curl_error($ch);
        $json = \Location::get($ipaddress);
        return $json;
    }

}
