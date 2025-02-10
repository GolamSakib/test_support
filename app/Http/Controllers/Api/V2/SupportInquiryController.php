<?php

namespace App\Http\Controllers\Api\V2;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\SoftwareSupportPerson;
use Illuminate\Support\Facades\Auth;

class SupportInquiryController extends Controller
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
    public function support_executive_list()
    {
        // $data = DB::table('abc')
        //     ->select(
        //         'u.id',
        //         'u.first_name',
        //         'u.last_name',
        //         'u.username',
        //         'abc.accepted_support_id',
        //         DB::raw('AVG(abc.avg_t) AS t_avg'),
        //         DB::raw('FLOOR(AVG(abc.avg_t) / (60 * 60 * 24)) AS days'),
        //         DB::raw('FLOOR((AVG(abc.avg_t) % (60 * 60 * 24)) / (60 * 60)) AS hours'),
        //         DB::raw('FLOOR((AVG(abc.avg_t) % (60 * 60)) / 60) AS minutes'),
        //         DB::raw('AVG(abc.avg_t) % 60 AS remaining_seconds'),
        //         DB::raw('SUM(abc.count) AS total'),
        //         DB::raw('GROUP_CONCAT(abc.count ORDER BY abc.count ASC SEPARATOR ", ") AS active_days')
        //     )
        //     ->join('supportadmin_support_user as u', 'u.id', '=', 'abc.accepted_support_id')
        //     ->from(function ($subquery) {
        //         $subquery->select(
        //             'foo.accepted_support_id',
        //             DB::raw('SUM(foo.completed_time - foo.accpt_asgn_time) AS avg_t'),
        //             DB::raw('COUNT(foo.order_count) AS count')
        //         )
        //             ->from(function ($nestedSubquery) {
        //                 $nestedSubquery->select(
        //                     's.accepted_support_id',
        //                     DB::raw('(CASE WHEN s.accepted_time IS NOT NULL THEN s.accepted_time ELSE s.assigned_time END) AS accpt_asgn_time'),
        //                     's.completed_time',
        //                     DB::raw('COUNT(*) as order_count')
        //                 )
        //                     ->from('supports as s')
        //                     ->groupBy(DB::raw('DAY(s.completed_time), s.accepted_support_id, s.accepted_time, s.assigned_time, s.completed_time'));
        //             }, 'foo')
        //             ->whereRaw('MONTH(foo.completed_time) = MONTH(NOW())')
        //             ->groupBy('foo.accepted_support_id', DB::raw('DAY(foo.completed_time)'));
        //     }, 'abc')
        //     ->groupBy('abc.accepted_support_id')
        //     ->get();
        $data = Support::
        with('accepted_support')
        ->select('accepted_support_id',
        DB::raw('COUNT(id) as total_supports'),
        DB::raw('
            SUM(
                CASE
                    WHEN assigned_time IS NOT NULL THEN
                        EXTRACT(EPOCH FROM (done_time - assigned_time))
                    ELSE
                        EXTRACT(EPOCH FROM (done_time - support_accepted_time))
                END
            ) as total_taken_time
        '),
        DB::raw('
            COUNT(DISTINCT EXTRACT(DAY FROM done_time)) as active_days
        ')
    )
    ->whereRaw('EXTRACT(MONTH FROM COALESCE(done_time)) = EXTRACT(MONTH FROM CURRENT_DATE)')
    ->whereRaw('EXTRACT(YEAR FROM COALESCE(done_time)) = EXTRACT(YEAR FROM CURRENT_DATE)')
    ->groupBy('accepted_support_id')
    ->get();

    foreach($data as $key => $value){
       $avg_time=$value->total_taken_time/$value->total_supports;
       $start = new DateTime("@0");
       $end = new DateTime("@" . floor($avg_time));
       $interval = $start->diff($end);
       $formatted = $interval->format('%a days, %H Hours:%I Minutes:%S Seconds');;
       $value->avg_time=$formatted;
    }




        if (!$data) {
            return JsonDataResponse($data);
        } else {
            return JsonDataResponse($data);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/v2/support-inquiry/support_for_clients",
     *     tags={"Support Inquiry"},
     *     security={{"bearerAuth": {}}},
     *     summary="support inquiry for clients",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function support_for_clients()
    {
        $data = DB::table('addusers_customer as c')
        ->select(
            'c.cusname',
            'abc.client_id',
            'abc.accepted_support_id as support_person_id',
            DB::raw('AVG(abc.sum_time) AS t_avg'),
            DB::raw('FLOOR(AVG(abc.sum_time) / (60 * 60 * 24)) AS days'),
            DB::raw('FLOOR((AVG(abc.sum_time) % (60 * 60 * 24)) / (60 * 60)) AS hours'),
            DB::raw('FLOOR((AVG(abc.sum_time) % (60 * 60)) / 60) AS minutes'),
            DB::raw('AVG(abc.sum_time) % 60 AS remaining_seconds'),
            DB::raw('SUM(abc.total) AS total'),
            DB::raw('STRING_AGG(abc.total::text, \', \' ORDER BY abc.total ASC) AS active_days'),
            DB::raw('STRING_AGG(abc.username::text, \', \' ORDER BY abc.username ASC) AS support_person')
        )
        ->join(DB::raw('(
                SELECT
                    ab.client_id,
                    ab.username,
                    ab.accepted_support_id,
                    SUM(EXTRACT(EPOCH FROM (ab.done_time - ab.accpt_asgn_time))) AS sum_time,
                    SUM(ab.order_count) AS total
                FROM (
                    SELECT
                        s.client_id,
                        ssu.username,
                        s.accepted_support_id,
                        CASE
                            WHEN s.support_accepted_time IS NOT NULL THEN s.support_accepted_time
                            ELSE s.assigned_time
                        END AS accpt_asgn_time,
                        s.done_time,
                        COUNT(*) AS order_count
                    FROM
                        supportadmin_problems s
                    LEFT JOIN
                        supportadmin_support_user ssu ON s.accepted_support_id = ssu.id
                    WHERE
                        EXTRACT(MONTH FROM s.done_time) = EXTRACT(MONTH FROM CURRENT_TIMESTAMP)
                        AND EXTRACT(YEAR FROM s.done_time) = EXTRACT(YEAR FROM CURRENT_TIMESTAMP)
                    GROUP BY
                        EXTRACT(DAY FROM s.done_time),
                        s.client_id,
                        ssu.username,
                        s.accepted_support_id,
                        s.support_accepted_time,
                        s.assigned_time,
                        s.done_time
                ) ab
                GROUP BY
                    ab.client_id, ab.username,ab.accepted_support_id
            ) abc'), function ($join) {
                $join->on(DB::raw('abc.client_id::numeric'), '=', 'c.id');
        })
        ->groupBy('abc.client_id', 'abc.accepted_support_id', 'c.cusname')
        ->get();

        foreach($data as $key=>$val){
            $start = new DateTime("@0");
            $end = new DateTime("@" . floor($val->t_avg));
            $interval = $start->diff($end);
            $formatted = $interval->format('%h hours, %i minutes and %s seconds');
            $val->t_avg=$formatted;
        }



        if (!$data) {
            return JsonDataResponse($data);
        } else {
            return JsonDataResponse($data);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/support-inquiry/most-hungry-software",
     *     tags={"Support Inquiry"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get information about the most hungry software",
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(
     *                     property="software_name",
     *                     type="string",
     *                     example="Software A"
     *                 ),
     *                 @OA\Property(
     *                     property="total_support_persons",
     *                     type="integer",
     *                     example=5
     *                 ),
     *                 @OA\Property(
     *                     property="total_clients",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="total_supports",
     *                     type="integer",
     *                     example=20
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *         )
     *     )
     * )
     */
    public function mostHungrySoftware()
    {
        // $data = DB::table(function ($query) {
        //     $query->select('s2.soft_name', 's.id', 's.soft_id', 's.accepted_support_id', 's.client_id')
        //         ->from('supportadmin_problems as s')
        //         ->join('client_support_admin_softwarelistall as s2',DB::raw('s2.id::text'), '=',  's.soft_id');
        // }, 'abc')
        //     ->select('abc.soft_id', 'abc.soft_name', DB::raw('COUNT(abc.accepted_support_id) as total_support_persons'))
        //     ->selectRaw('COUNT(abc.client_id) as total_clients')
        //     ->selectRaw('COUNT(abc.id) as total_supports')
        //     ->groupBy('abc.soft_name','abc.soft_id','abc.client_id','abc.accepted_support_id')
        //     ->get();
        $data = Support::
        with('software')->select('soft_id')
        ->selectRaw('COUNT(id) as total_supports')
        ->selectRaw('COUNT(DISTINCT accepted_support_id) as total_support_persons')
        ->selectRaw('COUNT(DISTINCT client_id) as total_clients')
        ->groupBy('soft_id')
        ->orderBy('total_supports','DESC')
        ->get();

        if (!$data) {
            return JsonDataResponse($data);
        } else {
            return JsonDataResponse($data);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function clientSupportInfo(Request $request){
        $day_type = $request->input('day_type', null);

        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        } else {
            $query = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', DB::raw('software.id::text'))
                ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
                ->leftJoin('addusers_users as client_users', 'client_users.id', 'supports.client_user_id')
                ->select(
                    'customers.cusname as client_name',
                    'customers.id as customer_id',
                    DB::raw("string_agg(DISTINCT users.username, ', ') as support_persons")
                )
                ->where('is_done', 1);

            if ($day_type) {
                $now = Carbon::now();
                if ($day_type == '7days') {
                    $query->where('supports.done_time', '>=', $now->subDays(7));
                } elseif ($day_type == '30days') {
                    $query->where('supports.done_time', '>=', $now->subDays(30));
                } elseif ($day_type == '1year') {
                    dd($now->subYear());
                    $query->where('supports.done_time', '>=', $now->subYear());
                }
            }

            $data = $query
                ->groupBy('customers.cusname', 'customers.id')
                ->orderBy('customers.cusname', 'ASC')
                ->get();

            return JsonDataResponse($data);
        }

    }
}
