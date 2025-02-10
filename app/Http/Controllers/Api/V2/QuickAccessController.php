<?php

namespace App\Http\Controllers\Api\V2;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\SoftwareSupportPerson;

class QuickAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/v2/support-inquiry/support_executive_list",
     *     tags={"Support Inquiry"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get support executive list with average support time",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     example="John"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     example="Doe"
     *                 ),
     *                 @OA\Property(
     *                     property="username",
     *                     type="string",
     *                     example="johndoe"
     *                 ),
     *                 @OA\Property(
     *                     property="accepted_support_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="t_avg",
     *                     type="number",
     *                     format="float",
     *                     example=86400
     *                 ),
     *                 @OA\Property(
     *                     property="days",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="hours",
     *                     type="integer",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="minutes",
     *                     type="integer",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="remaining_seconds",
     *                     type="number",
     *                     format="float",
     *                     example=0
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="active_days",
     *                     type="string",
     *                     example="1, 2, 3"
     *                 ),
     *             )
     *         )
     *     )
     * )
     */
    public function bySupportExecutive(Request $request)
    {
        $user_id=$request->user_id??null;
        $start_date=$request->start_date??null;
        $end_date=$request->end_date??null;
        if($start_date){
            $start_date = Carbon::createFromFormat('Y-m-d', $start_date)->startOfDay();
        }
        if($end_date){
            $end_date = Carbon::createFromFormat('Y-m-d', $end_date)->endOfDay();
        }
        $data = User::where(function ($query) {
            $query->where('role', "Support")
                  ->orWhere('role', "support");
        })
        ->when($user_id, function ($query) use ($user_id) {
            $query->where('id', $user_id);
        })
        ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
            $query->whereHas('totalsupportsforSupportPerson', function ($query) use ($start_date, $end_date) {
                $query->whereBetween('done_time', [$start_date, $end_date]);
            });
            $query->whereHas('supportsGivenToUniqueClient', function ($query) use ($start_date, $end_date) {
                $query->whereBetween('done_time', [$start_date, $end_date]);
            });
            $query->whereHas('supportsGivenToUniqueSoftware', function ($query) use ($start_date, $end_date) {
                $query->whereBetween('done_time', [$start_date, $end_date]);
            });
        })
        ->withCount(['totalsupportsforSupportPerson as support_given' => function ($query) use ($start_date, $end_date) {
            $query->where('is_done', 1)
                  ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                      $query->whereBetween('done_time', [$start_date, $end_date]);
                  });
        }])
        ->with(['totalsupportsforSupportPerson' => function ($query) use($start_date, $end_date) {
            $query->selectRaw('accepted_support_id, SUM(EXTRACT(EPOCH FROM done_time) - EXTRACT(EPOCH FROM COALESCE(support_accepted_time, assigned_time))) as total_time_for_support')
                  ->groupBy('accepted_support_id')
                  ->where('is_done', 1)
                  ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                      $query->whereBetween('done_time', [$start_date, $end_date]);
                  });
        }])
        ->with(['supportsGivenToUniqueClient' => function ($query) use ($start_date, $end_date){
            $query->selectRaw('accepted_support_id, COUNT(DISTINCT client_id) as number_of_clients')
                  ->groupBy('accepted_support_id')
                  ->where('is_done', 1)
                  ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                      $query->whereBetween('done_time', [$start_date, $end_date]);
                  });
        }])
        ->with(['supportsGivenToUniqueSoftware' => function ($query) use ($start_date, $end_date){
            $query->selectRaw('accepted_support_id, COUNT(DISTINCT soft_id) as number_of_software')
                  ->groupBy('accepted_support_id')
                  ->where('is_done', 1)
                  ->when($start_date && $end_date, function ($query) use ($start_date, $end_date) {
                      $query->whereBetween('done_time', [$start_date, $end_date]);
                  });
        }])
        ->orderBy('support_given', 'desc')
        ->get();



        $total_supports = $data->sum('support_given');
        $total_supported_clients = $data->sum(function($user) {
            foreach($user->supportsGivenToUniqueClient as $support) {
             return $support->number_of_clients;
            }
        });
        $total_supported_software=$data->sum(function($user) {
            foreach($user->supportsGivenToUniqueSoftware as $support) {
             return $support->number_of_software;
            }
        });

    foreach ($data as $user) {
        if ($user->totalsupportsforSupportPerson !== null && $user->totalsupportsforSupportPerson->isNotEmpty()) {
            foreach ($user->totalsupportsforSupportPerson as $total_support) {
                $time_taken = $total_support->total_time_for_support;
                // Ensure that $time_taken is not null or empty
                if ($time_taken !== null && $time_taken !== '') {
                    $start = new DateTime("@0");
                    $end = new DateTime("@" . floor($time_taken));
                    $interval = $start->diff($end);
                    $formatted = $interval->format('%a days, %H:%I:%S');
                    $formattedWithMicroseconds = $formatted;
                    $user->total_time = $formattedWithMicroseconds;
                    $avg_time = $time_taken / $user->support_given;
                    $end_time_for_avg_time = new DateTime("@" . floor($avg_time));
                    $interval_for_avg_time = $start->diff($end_time_for_avg_time);
                    $formatted_for_avg_time = $interval_for_avg_time->format('%h hours, %i minutes and %s seconds');
                    $user->avg_time=$formatted_for_avg_time;
                } else {
                    $user->total_time = '0 days, 00:00:00';
                    $user->avg_time = '0 hours, 0 minutes and 0 seconds';
                }
            }
        }
        else{
            $user->total_time = '0 days, 00:00:00.000000';
            $user->avg_time = '0 hours, 0 minutes and 0 seconds';
        }
    }
    $result=[];
    $result['support_executives']=$data;
    $result['total_supports'] = $total_supports;
    $result['total_supported_clients'] = $total_supported_clients;
    $result['total_supported_software'] = $total_supported_software;


        if (!$result) {
            return JsonDataResponse($result);
        } else {
            return JsonDataResponse($result);
        }
    }
}
