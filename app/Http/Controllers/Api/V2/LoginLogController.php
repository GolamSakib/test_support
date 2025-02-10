<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v2/login-log",
     *     tags={"Support User"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a Login Logs",
     * @OA\Response(
 *         response=200,
 *         description="Success response",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Success message", example="Data saved successfully.")
 *         )
 *     ),
     * )
     */
    public function loginLog(Request $request)
    {
        $start_time = $request->start_time ?? null;
        $end_time = $request->end_time ?? null;

        // Ensure start and end times are in the correct format, if provided
        if ($start_time && $end_time) {
            $start_time = Carbon::parse($start_time)->format('Y-m-d H:i:s');
            $end_time = Carbon::parse($end_time)->format('Y-m-d H:i:s');
        }

        $loginLogs = LoginLog::select([
                'id', 'username', 'client_name', 'login_time', 'logout_time',
                'latitude', 'longitude', 'city', 'country', 'area_address'
            ])
            ->orderBy('id', 'desc')
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('login_time', [$start_time, $end_time]);
            })
            ->get();

        $tempLogs = $loginLogs->map(function ($loginLog) {
            return [
                "id" => $loginLog->id,
                "username" => $loginLog->username,
                "client_name" => $loginLog->client_name,
                "login_time" => $loginLog->login_time,
                "logout_time" => $loginLog->logout_time,
                "latitude" => $loginLog->latitude,
                "longitude" => $loginLog->longitude,
                "city" => $loginLog->city,
                "country" => $loginLog->country,
                "area_address" => $loginLog->area_address,
            ];
        });

        // Return the response with the transformed data
        return response()->json(['data' => $tempLogs], 200);

    }

    /**
     * @OA\Get(
     *     path="/api/v2/filter-login-user",
     *     tags={"Filter Login User"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a Filter Login User",
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Get Data")
     *         )
     *     ),
     * )
     */
    public function filterloginuser(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $filterlogin = LoginLog::with('user')->get();
//        dd($filterlogin);
        if (isset($from_date) && isset($to_date)) {
            $filterlogin->whereBetween('login_time', [
                $from_date, $to_date
            ]);
        }
        $filteruser = [];
        foreach ($filterlogin as $filter) {
            array_push($filteruser, [
                "User Name" => $filter->user->username,
                "Country" => $filter->country,
                "Area Address" => $filter->area_address,
                "City" => $filter->city,
                "Login Time" => $filter->login_time,
            ]);
        }
        $filterlogin = $filteruser;
        return JsonDataResponse($filterlogin);
    }
}
