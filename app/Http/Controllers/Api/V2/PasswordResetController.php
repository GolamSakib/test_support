<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use App\Models\User;
use App\Models\ClientUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
use Illuminate\Validation\Rule;

use App\Notifications\PasswordResetRequest;
use App\Http\Controllers\OTPVerificationController;
use App\Notifications\AppEmailVerificationNotification;

class PasswordResetController extends Controller
{
    public function forgetRequest(Request $request)
    {
        // dd($request->all());
        $user = $this->findUser($request);

        // dd($user);

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }

        // return rand(100000, 999999);

        if ($user) {
            // $user->verification_code = rand(100000, 999999);
            // $user->save();
            if ($request->send_code_by == 'phone') {

                $otpController = new OTPVerificationController();
                $otpController->send_code($user);
            } else {
                $user->notify(new AppEmailVerificationNotification());
            }
        }

        return response()->json([
            'result' => true,
            'message' => translate('A code is sent')
        ], 200);
    }

    public function confirmReset(Request $request)
    {
        $user = User::where('verification_code', $request->verification_code)->first();

        if ($user != null) {
            $user->verification_code = null;
            $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your password is reset.Please login'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('No user is found'),
            ], 200);
        }
    }

    public function resendCode(Request $request)
    {

        if ($request->verify_by == 'email') {
            $user = User::where('email', $request->email_or_phone)->first();
        } else {
            $user = User::where('phone', $request->email_or_phone)->first();
        }


        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => translate('User is not found')
            ], 404);
        }

        $user->verification_code = rand(100000, 999999);
        $user->save();

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }



        return response()->json([
            'result' => true,
            'message' => translate('A code is sent again'),
        ], 200);
    }

    public function verifyOTP(Request $request)
    {
        // dd($request->all());
        $user = User::where('verification_code', $request->verification_code)
            ->first();

        if($user == null){
                $user = ClientUser::where('verification_code', $request->verification_code)
                ->first();
        }

        if ($user != null) {
            $user->verification_code = null;
            // $user->password = Hash::make($request->password);
            $user->save();
            return response()->json([
                'result' => true,
                'message' => translate('Your OTP verified'),
            ], 200);
        } else {
            return response()->json([
                'result' => false,
                'message' => translate('OTP verify failed'),
            ], 404);
        }
    }

    public function chanagePassword(Request $request){
        $request->validate([
            'email_or_phone' => 'required',
            'password' => 'required'
        ]);

        try {
            $user = User::where('phone_no', $request->email_or_phone)
                ->orWhere('email', $request->email_or_phone)
                ->first();


            if($user != null){
                $user->password = $request->password;
                $user->password_is_set = true;
                $user->save();

                return response()->json([
                    'result' => true,
                    'message' => translate('password updated'),
                ], 200);
            }
            // dd($request->all());

            $user = ClientUser::where('phoneno', $request->email_or_phone)
                ->orWhere('email', $request->email_or_phone)
                ->first();

            $user->password = Hash::make($request->password);
            $user->password_is_set = true;
            $user->save();

            return response()->json([
                'result' => true,
                'status' => 200,
                'message' => translate('password updated'),
            ], 200);


        } catch (\Throwable $th) {
            return response()->json([
                'result' => false,
                'status' => 505,
                'message' => translate('Password change failed'),
            ], 505);
        }

    }

    public function findUser(Request $request)
    {
        $validated = $request->validate([
            'email_or_phone' => 'required',
            'send_code_by' => ['required', Rule::in(['email', 'phone'])]
        ]);

       $field = $this->getSearchField($validated['send_code_by']);
    //    dd($field);
        return $this->findActiveUser($validated['email_or_phone'], $field);
    }

    private function getSearchField(string $sendCodeBy): array
    {
        return [
            'user_field' => $sendCodeBy === 'email' ? 'email' : 'phone_no',
            'client_field' => $sendCodeBy === 'email' ? 'email' : 'phoneno'
        ];
    }

    /**
     * Find active user in both User and ClientUser tables
     *
     * @param string $searchValue
     * @param array $fields
     * @return User|ClientUser|null
     */
    private function findActiveUser(string $searchValue, array $fields)
    {
        // Try finding in Users table
        $user = User::where($fields['user_field'], $searchValue)
            // ->where('isactive', true)
            ->first();

        if ($user) {
            $user->verification_code = rand(100000, 999999);
            $user->save();
            return $user;
        }

        // Try finding in ClientUsers table
        $clientUser =  ClientUser::where($fields['client_field'], $searchValue)
            // ->where('is_active', true)
            ->first();

        $clientUser->verification_code = rand(100000, 999999);
        $clientUser->save();
        return $clientUser;

    }

}
