<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\OTPVerificationController;
use App\Models\Area;
use App\Models\ClientUser;
use App\Models\Customer;
use App\Models\District;
use App\Models\LoginLog;
use App\Models\User;
use App\Notifications\AppEmailVerificationNotification;
use App\Utility\GetLocation;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Socialite;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v2/auth/signup",
     *     tags={"Auth"},
     *     summary="signup",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email_or_phone","password","register_by"},
     *               @OA\Property(property="email_or_phone", type="string"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="register_by", type="string")
     *     ),
     *        ),
     *    ),
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    public function signup(Request $request)
    {
        if (User::where('email', $request->email_or_phone)->orWhere('phone', $request->email_or_phone)->first() != null) {
            return response()->json([
                'result'  => false,
                'message' => translate('User already exists.'),
                'user_id' => 0,
            ], 201);
        }

        if ($request->register_by == 'email') {
            $user = new User([
                'name'              => $request->name,
                'email'             => $request->email_or_phone,
                'password'          => bcrypt($request->password),
                'verification_code' => rand(100000, 999999),
            ]);
        } else {
            $user = new User([
                'name'              => $request->name,
                'phone_no'          => $request->email_or_phone,
                'password'          => bcrypt($request->password),
                'verification_code' => rand(100000, 999999),
            ]);
        }
        //create token
        $user->createToken('tokens')->plainTextToken;

        return response()->json([
            'result'  => true,
            'message' => translate('Registration Successful. Please verify and log in to your account.'),
            'user_id' => $user->id,
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/resend_code",
     *     tags={"Auth"},
     *     summary="resend_code",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"user_id","verify_by"},
     *               @OA\Property(property="user_id", type="integer"),
     *               @OA\Property(property="verify_by", type="string")
     *     ),
     *        ),
     *    ),
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    public function resendCode(Request $request)
    {
        $user                    = User::where('id', $request->user_id)->first();
        $user->verification_code = rand(100000, 999999);

        if ($request->verify_by == 'email') {
            $user->notify(new AppEmailVerificationNotification());
        } else {
            $otpController = new OTPVerificationController();
            $otpController->send_code($user);
        }

        $user->save();

        return response()->json([
            'result'  => true,
            'message' => translate('Verification code is sent again'),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/confirm_code",
     *     tags={"Auth"},
     *     summary="confirm_code",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"user_id","verification_code"},
     *               @OA\Property(property="user_id", type="integer"),
     *               @OA\Property(property="verification_code", type="string")
     *     ),
     *        ),
     *    ),
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    public function confirmCode(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        if ($user->verification_code == $request->verification_code) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->verification_code = null;
            $user->save();
            return response()->json([
                'result'  => true,
                'message' => translate('Your account is now verified.Please login'),
            ], 200);
        } else {
            return response()->json([
                'result'  => false,
                'message' => translate('Code does not match, you can request for resending the code'),
            ], 200);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/auth/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"email_or_phone","password"},
     *               @OA\Property(property="email_or_phone", type="string"),
     *               @OA\Property(property="password", type="password")
     *     ),
     *        ),
     *    ),
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    public function login(Request $request)
    {
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->email_or_phone)
                ->orWhere('phone_no', $request->email_or_phone)
                ->orWhereRaw('LOWER(username) = ?', [strtolower($request->email_or_phone)]);
        })
        // ->where('status', 1)
            ->where('isactive', true)
            ->first();

        //Client User
        // if ($user == null) {
        //     $user = ClientUser::where(function ($query) use ($request) {
        //         $query->where('email', $request->email_or_phone)
        //             ->orWhere('phoneno', $request->email_or_phone)
        //             ->orWhere('username', $request->email_or_phone);
        //     })->first();

        //     if ($user == null) {
        //         return response()->json([
        //             'result' => false,
        //             'message' => translate('User not found or deactivated'),
        //             // 'password_is_set' => false
        //         ], 401);
        //     }
        //     // dd('ok');
        //     // ->where('status', 1)
        //     $user->where('is_active', true)
        //         ->first();

        //     // dd()
        //     if (
        //         $user->password_is_set == null
        //         || $user->password_is_set == ""
        //         || $user->password == null
        //         || $user->password == ""
        //     ) {
        //         return response()->json([
        //             'result' => true,
        //             'message' => translate('Password is not set or null'),
        //             'password_is_set' => false,
        //         ], 200);
        //     }
        // }
        // return $user->role;

        if ($user != null) {
            if ($user->role ?
                $request->password == $user->password :
                Hash::check($request->password, $user->password)
            ) {
                // dd($user);
                // if ($request->password == $user->password) {
                // $user->login_status = 1;
                // $user->save();
                $location = GetLocation::locationInfo();
                $address  = 'Post: ' . $location->zipCode . ',cityName: ' . $location->cityName . ',Region: ' . $location->regionName . ',Country: ' . $location->countryName;

                LoginLog::create([
                    // 'user_id' => $user->id,
                    "username"     => $user->username,
                    'client_id'    => $user->id,
                    'client_name'  => $user->client_name ? $user->client_name : '',
                    'login_time'   => now(),
                    "latitude"     => $location->latitude,
                    "longitude"    => $location->longitude,
                    "login_ip"     => $location->ip,
                    "city"         => $location->cityName,
                    "country"      => $location->countryName,
                    "area_address" => $address,
                    "timezone"     => $location->timezone,
                ]);
                return $this->loginSuccess($user);
            } else {
                return response()->json([
                    'result'  => false,
                    'message' => translate('Unauthorized'),
                    'user'    => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found or deactivated'),
                'user'    => null,
            ], 401);
        }
    }

    public function clientLogin(Request $request)
    {
        $user = ClientUser::where(function ($query) use ($request) {
            $query->where('email', $request->email_or_phone)
                ->orWhere('phoneno', $request->email_or_phone)
                ->orWhere('username', $request->email_or_phone);
        })->first();

        if ($user == null) {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found or deactivated'),
            ], 401);
        }

        $user->where('is_active', true)
            ->first();

        if (
            $user->password_is_set == null
            || $user->password_is_set == ""
            || $user->password == null
            || $user->password == ""
        ) {
            return response()->json([
                'result'          => true,
                'message'         => translate('Password is not set or null'),
                'password_is_set' => false,
            ], 200);
        }

        if ($user != null) {
            if ($user->role ?
                $request->password == $user->password :
                Hash::check($request->password, $user->password)
            ) {

                $location = GetLocation::locationInfo();
                $address  = 'Post: ' . $location->zipCode . ',cityName: ' . $location->cityName . ',Region: ' . $location->regionName . ',Country: ' . $location->countryName;

                LoginLog::create([
                    // 'user_id' => $user->id,
                    "username"     => $user->username,
                    'client_id'    => $user->id,
                    'client_name'  => $user->client_name ? $user->client_name : '',
                    'login_time'   => now(),
                    "latitude"     => $location->latitude,
                    "longitude"    => $location->longitude,
                    "login_ip"     => $location->ip,
                    "city"         => $location->cityName,
                    "country"      => $location->countryName,
                    "area_address" => $address,
                    "timezone"     => $location->timezone,
                ]);
                return $this->loginSuccess($user);
            } else {
                return response()->json([
                    'result'  => false,
                    'message' => translate('Unauthorized'),
                    'user'    => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found or deactivated'),
                'user'    => null,
            ], 401);
        }

    }

    public function clientAppLogin(Request $request)
    {
        //Client User
        $user = ClientUser::where(function ($query) use ($request) {
            $query->where('email', $request->email_or_phone)
                ->orWhere('phoneno', $request->email_or_phone)
                ->orWhere('username', $request->email_or_phone);
        })->first();

        // if user not register/found
        if ($user == null) {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found or deactivated'),
                // 'password_is_set' => false
            ], 401);
        }
        // dd('ok');
        // ->where('status', 1)
        $user->where('is_active', true)
            ->first();

        // dd()
        if (! $request->has("password")) {
            if (
                $user->password_is_set == null
                || $user->password_is_set == ""
                || $user->password == null
                || $user->password == ""
            ) {
                return response()->json([
                    'result'          => true,
                    'message'         => translate('Password is not set or null'),
                    'password_is_set' => false,
                ], 200);
            } else {
                return response()->json([
                    'result'          => true,
                    'message'         => translate('Password is set'),
                    'password_is_set' => true,
                ], 200);
            }
        }
        // return $user->role;

        if ($user != null) {
            if (Hash::check($request->password, $user->password)) {
                $location = GetLocation::locationInfo();
                $address  = 'Post: ' . $location->zipCode . ',cityName: ' . $location->cityName . ',Region: ' . $location->regionName . ',Country: ' . $location->countryName;

                LoginLog::create([
                    // 'user_id' => $user->id,
                    "username"     => $user->username,
                    'client_id'    => $user->id,
                    'client_name'  => $user->client_name ? $user->client_name : '',
                    'login_time'   => now(),
                    "latitude"     => $location->latitude,
                    "longitude"    => $location->longitude,
                    "login_ip"     => $location->ip,
                    "city"         => $location->cityName,
                    "country"      => $location->countryName,
                    "area_address" => $address,
                    "timezone"     => $location->timezone,
                ]);
                return $this->loginSuccess($user);
            } else {
                return response()->json([
                    'result'  => false,
                    'message' => translate('Unauthorized'),
                    'user'    => null,
                ], 401);
            }
        } else {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found or deactivated'),
                'user'    => null,
            ], 401);
        }
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Get(
     *     path="/api/v2/auth/logout",
     *     tags={"Auth"},
     *     summary="logout",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    public function logout(Request $request)
    {
        $user = request()->user();
        // return $login_id = LoginLog::where('user_id', $user->currentAccessToken()->tokenable_id)->get()->last();
        $login_id = LoginLog::where('client_id', $user->currentAccessToken()->tokenable_id)->get()->last();
        // $user->login_status = 0;
        // $user->save();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
        $login_id->update([
            "logout_time" => now(),
        ]);
        return response()->json([
            'result'  => true,
            'message' => translate('Successfully logged out'),
        ]);
    }

    public function socialLogin(Request $request)
    {
        if (! $request->provider) {
            return response()->json([
                'result'  => false,
                'message' => translate('User not found'),
                'user'    => null,
            ]);
        }

        //
        switch ($request->social_provider) {
            case 'facebook':
                $social_user = Socialite::driver('facebook')->fields([
                    'name',
                    'first_name',
                    'last_name',
                    'email',
                ]);
                break;
            case 'google':
                $social_user = Socialite::driver('google')
                    ->scopes(['profile', 'email']);
                break;
            default:
                $social_user = null;
        }
        if ($social_user == null) {
            return response()->json(['result' => false, 'message' => translate('No social provider matches'), 'user' => null]);
        }

        $social_user_details = $social_user->userFromToken($request->access_token);

        if ($social_user_details == null) {
            return response()->json(['result' => false, 'message' => translate('No social account matches'), 'user' => null]);
        }

        //

        $existingUserByProviderId = User::where('provider_id', $request->provider)->first();

        if ($existingUserByProviderId) {
            return $this->loginSuccess($existingUserByProviderId);
        } else {

            $existingUserByMail = User::where('email', $request->email)->first();
            if ($existingUserByMail) {

                return response()->json(['result' => false, 'message' => 'You can not login with this provider', 'user' => null]);
            } else {

                $user = new User([
                    'name'              => $request->name,
                    'email'             => $request->email,
                    'provider_id'       => $request->provider,
                    'email_verified_at' => Carbon::now(),
                ]);
                $user->save();
            }
        }
        return $this->loginSuccess($user);
    }

    protected function loginSuccess($user)
    {
        // return $user;

        if (strpos($user->pro_img_url, 'media') !== false) {
            $user->image = 'http://support.mediasoftbd.com' . $user->pro_img_url;
        } else {
            $user->image = $user->pro_img_url ? asset($user->pro_img_url) : null;
        }

        $token = $user->createToken('API Token')->plainTextToken;
        // return $user->role;
        if ($user->role) {
            return response()->json([
                'result'       => true,
                'message'      => 'Successfully logged in',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'expires_at'   => null,
                'user'         => [
                    'id'          => $user->id,
                    'name'        => $user->username,
                    'customer_id' => $user->client_id,
                    'email'       => $user->email,
                    'photo'       => $user->image,
                    'phone'       => $user->phone_no,
                    'user_type'   => $user->role,
                    'roles'       => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions(),
                ],
            ]);
        } else {
            //client user
            return response()->json([
                'result'       => true,
                'message'      => 'Successfully logged in',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'expires_at'   => null,
                'user'         => [
                    'id'          => $user->id,
                    'name'        => $user->username,
                    'customer_id' => $user->client_id,
                    'email'       => $user->email,
                    'photo'       => $user->image,
                    'phone'       => $user->phoneno,
                    'user_type'   => 'client',
                    'roles'       => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions(),
                ],
            ]);
        }
    }

    public function supportMobileSignup(Request $request)
    {
        // Validate user type first
        if (! $request->has('user_type') || $request->user_type !== 'support') {
            return $this->errorResponse('Invalid user type. Only support registration is allowed.', 403);
        }

        // Determine if input is email or phone
        $isEmail = $this->isEmail($request->email_or_phone);
        $isPhone = $this->isPhone($request->email_or_phone);

        // Validation rules
        $rules = [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ];

        // Add specific validation rule based on input type
        if ($isEmail) {
            $rules['email_or_phone'] = 'required|string|email|max:255|unique:supportadmin_support_user,email';
        } elseif ($isPhone) {
            $rules['email_or_phone'] = 'required|string|max:255|unique:supportadmin_support_user,phone_no';
        } else {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => translate('Please provide a valid email or phone number'),
            ], 400);
        }

        // Validation
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => translate('Validation error!'),
                'errors'  => $validator->errors(),
            ], 400);
        }

        try {
            // Create new user based on input type
            $userData = [
                'username'         => $request->username,
                'password'         => $request->password,
                'role'             => 'support',
                'isactive'         => true,
                'is_status_active' => true,
                'isTemp'           => true,
            ];

            if ($isEmail) {
                $userData['email']    = $request->email_or_phone;
                $userData['phone_no'] = null;
            } else {
                $userData['phone_no'] = $request->email_or_phone;
                $userData['email']    = null;
            }

            $user = User::create($userData);

            // Create token
            $token = $user->createToken('tokens')->plainTextToken;

            return response()->json([
                'success'    => true,
                'status'     => 201,
                'message'    => translate('Registration Successful'),
                'user_id'    => $user->id,
                'username'   => $user->username,
                'user_type'  => 'support',
                'email'      => $user->email,
                'phone_no'   => $user->phone_no,
                'token'      => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'An error occurred during registration.' . $e->getMessage(),
            ], 500);
        }
    }

    public function clientUserMobileSignup(Request $request)
    {
        // Validate user type first
        if (! $request->has('user_type') || $request->user_type !== 'client') {
            return $this->errorResponse('Invalid user type. Only client registration is allowed.', 403);
        }

        // Determine if input is email or phone
        $isEmail = $this->isEmail($request->email_or_phone);
        $isPhone = $this->isPhone($request->email_or_phone);

        // Base validation rules
        $rules = [
            'username'     => 'required|string|max:255',
            'client_id'    => 'required|exists:addusers_customer,id',
            'shop_address' => 'required|string|max:255',
            'district_id'  => 'required|exists:addusers_district,id',
            'area_id'      => 'required|exists:areas,id',
            'password'     => [
                'required',
                'string',
                'min:6',
                'confirmed',
            ],
        ];

        // Add specific validation rule based on input type
        if ($isEmail) {
            $rules['email_or_phone'] = [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('addusers_users', 'email'),
            ];
        } elseif ($isPhone) {
            $rules['email_or_phone'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('addusers_users', 'phoneno'),
            ];
        } else {
            return $this->errorResponse('Please provide a valid email or phone number', 400);
        }

        // Validation
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status'  => 400,
                'message' => translate('Validation error!'),
                'errors'  => $validator->errors(),
            ], 400);
        }

        // Fetch required relations with error handling
        $client   = Customer::findOrFail($request->client_id);
        $district = District::findOrFail($request->district_id);
        $area     = Area::findOrFail($request->area_id);

        // Check if area belongs to the selected district
        if ($area->district_id !== $district->id) {
            return $this->errorResponse('Selected area does not belong to the selected district', 400);
        }

        // Prepare user data
        $userData = [
            'username'          => strip_tags($request->username), // Sanitize input
            'client_id'         => $client->id,
            'client_name'       => $client->cusname,
            'dist_id'           => $district->id,
            'district'          => $district->dist_name,
            'area_id'           => $area->id,
            'area'              => $area->name,
            'shopname'          => strip_tags($request->shop_address), // Sanitize input
            'password'          => bcrypt($request->password),
            'is_active'         => true,
            'is_status_active'  => true,
            'is_approved'       => true,
            'isTemp'            => true,
            'user_created_time' => Carbon::now(),
        ];

        if ($isEmail) {
            $userData['email']   = strtolower($request->email_or_phone);
            $userData['phoneno'] = null;
        } else {
            $userData['phoneno'] = preg_replace('/[^0-9]/', '', $request->email_or_phone);
            $userData['email']   = null;
        }

        // Create user in a transaction
        DB::beginTransaction();
        try {
            $user = ClientUser::create($userData);

            // Create token
            $token = $user->createToken('client_user_token')->plainTextToken;

            DB::commit();

            return response()->json([
                'success' => true,
                'status'  => 201,
                'message' => translate('Registration Successful'),
                'data'    => [
                    'user_id'    => $user->id,
                    'user_type'  => 'client',
                    'username'   => $user->username,
                    'email'      => $user->email,
                    'phoneno'    => $user->phoneno,
                    'token'      => $token,
                    'token_type' => 'Bearer',
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'An error occurred during registration.' . $e->getMessage(),
            ], 500);
        }
    }

    // Helper function for error responses
    protected function errorResponse($message, $status)
    {
        return response()->json([
            'success' => false,
            'status'  => $status,
            'message' => $message,
        ], $status);
    }

    // Helper function to check if string is email
    protected function isEmail($string)
    {
        return filter_var($string, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Helper function to check if string is phone number
    protected function isPhone($string)
    {
        $cleanedNumber = preg_replace('/[^0-9]/', '', $string);
        return preg_match('/^[0-9]{10,15}$/', $cleanedNumber);
    }
}
