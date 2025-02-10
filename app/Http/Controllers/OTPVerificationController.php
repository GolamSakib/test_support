<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OTPVerificationController extends Controller
{
    /**
     * Length of the OTP code
     */
    const OTP_LENGTH = 6;

    /**
     * OTP expiration time in minutes
     */
    const OTP_EXPIRY_MINUTES = 5;

    /**
     * Send OTP code to user
     *
     * @param User|ClientUser $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function send_code($user)
    {
        try {
            // Generate OTP
            $otp = $user->verification_code;
            // dd($otp);
            // Send SMS
            $success = $this->sendSMS($user->phone_no ?? $user->phoneno, $otp);

            if (!$success) {
                return response()->json([
                    'status' => false,
                    'message' => 'Failed to send OTP'
                ], 500);
            }

            return response()->json([
                'status' => true,
                'message' => 'OTP sent successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Send Error: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Failed to process OTP request'
            ], 500);
        }
    }



    /**
     * Send SMS using your preferred SMS gateway
     *
     * @param string $phoneNumber
     * @param string $otp
     * @return bool
     */
    // private function sendSMS(string $phoneNumber, string $otp): bool
    // {
    //     try {
    //         $message = 'Your OTP is '. $otp;
    //             //         // Move credentials to config
    //         $username = config('services.sms.username');
    //         $password = config('services.sms.password');
    //         $source = config('services.sms.source');
    //         $baseUrl = config('services.sms.base_url');
    //         // $response = Http::post('http://smppbd.rmlconnect.net/bulksms/personalizedbulksms?username=MediasoftBDENT&password=C6bQFqer&source=MEDIASOFT&destination=88'.$phoneNumber .'&message='.$message);
    //         $response = Http::post(
    //             "{$baseUrl}?username={$username}&password={$password}&source={$source}&destination=88{$phoneNumber}&message={$message}"
    //         );

    //         return $response->successful();

    //     } catch (\Exception $e) {
    //         Log::error('SMS Send Error: ' . $e->getMessage());
    //         return false;
    //     }
    // }


    private function sendSMS(string $phoneNumber, string $otp): bool
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
            $message = "Your verification code is: {$otp}";

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

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('SMS Send Error', [
                'message' => $e->getMessage(),
                'phone' => $phoneNumber ?? 'not set',
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
