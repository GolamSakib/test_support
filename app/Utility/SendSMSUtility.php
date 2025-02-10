<?php

namespace App\Utility;

use Twilio\Rest\Client;
use App\Utility\MimoUtility;
use App\Models\OtpConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSMSUtility
{
    // public static function sendSMS($to, $from, $text)
    // {
    //         $token = env("SSL_SMS_API_TOKEN"); //put ssl provided api_token here
    //         $sid = env("SSL_SMS_SID"); // put ssl provided sid here

    //         $params = [
    //             "api_token" => $token,
    //             "sid" => $sid,
    //             "msisdn" => $to,
    //             "sms" => $text,
    //             "csms_id" => date('dmYhhmi') . rand(10000, 99999)
    //         ];

    //         $url = env("SSL_SMS_URL");
    //         $params = json_encode($params);

    //         $ch = curl_init(); // Initialize cURL
    //         curl_setopt($ch, CURLOPT_URL, $url);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    //         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    //         curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    //         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    //             'Content-Type: application/json',
    //             'Content-Length: ' . strlen($params),
    //             'accept:application/json'
    //         ));
    //         $response = curl_exec($ch);
    //         curl_close($ch);
    //         return $response;
    // }

    public static function sendSMS(string $phoneNumber, string $text): void
    {
        try {
            // Remove any spaces or special characters from phone number
            $cleanPhoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

            // Ensure phone number starts with '88' for Bangladesh
            $formattedPhone = str_starts_with($cleanPhoneNumber, '88')
                ? $cleanPhoneNumber
                : '88' . $cleanPhoneNumber;

            // Move credentials to config
            $username = config('services.sms.username');
            $password = config('services.sms.password');
            $source = config('services.sms.source');
            $baseUrl = config('services.sms.base_url');

            // Build the SMS message
            $message = $text;

            // Construct URL with query parameters
            $url = "{$baseUrl}?" . http_build_query([
                'username' => $username,
                'password' => $password,
                'source' => $source,
                'destination' => $formattedPhone,
                'message' => $message
            ]);

            $response = Http::get($url);

            // Log the response for debugging
            Log::info('SMS Gateway Response', [
                'phone' => $formattedPhone,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
        }
        catch (\Exception $e) {
            Log::error('SMS Send Error', [
                'message' => $e->getMessage(),
                'phone' => $phoneNumber ?? 'not set',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public static function sendSMSToMany($to, $from, $text)
    {
        $ch = curl_init();

        try {
            $url = 'https://smppbd.rmlconnect.net/bulksms/personalizedbulksms?username=MediasoftBDENT&password=C6bQFqer&type=0&destination='.$to.'&source='.$from.'&message='.$text;
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: '.$_SERVER['HTTP_USER_AGENT']
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            // Execute cURL request
            $response = curl_exec($ch);

            // Check if any error occurred during the request
            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
                
            }

            // Optionally, you can also check the HTTP response code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode !== 200) {
                throw new Exception("HTTP request failed with status code " . $httpCode);
            }

            return $response;
        } catch (\Exception $e) {
            // Handle the exception, you can log it or return an error message
            return 'Error: ' . $e->getMessage();
        } finally {
            // Always close the cURL session
            curl_close($ch);
        }
    }

}
