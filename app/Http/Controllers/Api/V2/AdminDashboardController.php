<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Api\V2\Controller;
use App\Models\Support;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/admin-dashboard",
     *     summary="Admin Dashboard",
     *     tags={"Admin"},
     * security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function adminDashboard(Request $request)
    {
        $user = Auth::user();
        if (
            strtolower($user->role) != 'admin' &&
            strtolower($user->role) != 'super admin' &&
            strtolower($user->role) != 'super_admin'
        ) {
            return JsonDataResponse($data = null);
        }

        $date = now()->toDateString();
        // $date = "2021-05-23 17:26:46.138 +0600";
        $month = \Carbon\Carbon::parse($date)->month;
        $year  = \Carbon\Carbon::parse($date)->year;

        $todays_support = DB::table('supportadmin_problems AS s')
            ->select(
                'u.id AS support_people_id',
                'u.username as name',
                'c.id AS client_id',
                'c.cusname AS client_name',
                's2.soft_name AS software_name',
                's.p_description AS problem_title',
                's.comment AS comment',
                's.shop_name',
                // 'sh.address AS shop_address',
                DB::raw("CASE
                WHEN s.is_done = true THEN 'done'
                WHEN s.is_processing = true THEN 'processing'
                WHEN s.is_pending = true THEN 'pending'
                ELSE 'unknown'
                END AS status")
            )
            ->leftJoin('addusers_customer AS c', DB::raw('c.id::text'), '=', 's.client_id')
            ->leftJoin('supportadmin_support_user AS u', 'u.id', '=', 's.accepted_support_id')
        // ->leftJoin('shops AS sh', 'sh.id', '=', 's.shop_id')
            ->leftJoin('client_support_admin_softwarelistall AS s2', DB::raw('s2.id::text'), '=', 's.soft_id')
            ->whereDate('s.assigned_time', $date)
            ->orWhereDate('s.support_accepted_time', $date)
            ->get();

        $top_by_support_current_month = DB::table(function ($query) use ($month, $year) {
            $query->select(
                'u.username AS name',
                's.accepted_support_id',
                's.done_time AS completed_time',
                DB::raw("CASE
                    WHEN s.support_accepted_time IS NOT NULL THEN s.support_accepted_time
                    ELSE s.assigned_time
                END AS accpt_asgn_time")
            )
                ->from('supportadmin_problems AS s')
                ->leftJoin('supportadmin_support_user AS u', 's.accepted_support_id', '=', 'u.id')
                ->where(DB::raw('EXTRACT(MONTH FROM s.done_time)'), '=', $month)
                ->where(DB::raw('EXTRACT(YEAR FROM s.done_time)'), '=', $year)
                ->groupBy(
                    'u.username',
                    's.accepted_support_id',
                    's.done_time',
                    's.support_accepted_time',
                    's.assigned_time'
                );
        }, 'ab')
            ->select(
                'ab.accepted_support_id',
                'ab.name',
                DB::raw('COUNT(ab.accepted_support_id) AS total_supports'),
                DB::raw("TO_CHAR(AVG(EXTRACT(EPOCH FROM (ab.completed_time - ab.accpt_asgn_time))) * INTERVAL '1 second', 'HH24:MI:SS') AS avg_time")
            )
            ->groupBy('ab.name', 'ab.accepted_support_id')
            ->get();

        if (
            $top_by_support_current_month->isEmpty() ||
            $top_by_support_current_month->first()->accepted_support_id === null
        ) {
            $top_by_support_current_month = [];
        }

        // return $top_by_support_current_month = DB::table(function ($query) use($month,$year) {
        //     $query->select(
        //         'u.username AS name',
        //         's.accepted_support_id',
        //         's.done_time AS completed_time',
        //         DB::raw("CASE
        //             WHEN s.support_accepted_time IS NOT NULL THEN s.support_accepted_time
        //             ELSE s.assigned_time
        //         END AS accpt_asgn_time")
        //     )
        //     ->from('supportadmin_problems AS s')
        //     ->leftJoin('supportadmin_support_user AS u', 's.accepted_support_id', '=', 'u.id')
        //     ->where(DB::raw('EXTRACT(MONTH FROM s.done_time)'), '=', $month)
        //     ->where(DB::raw('EXTRACT(YEAR FROM s.done_time)'), '=', 2024)
        //     ->groupBy(
        //         'u.username',
        //         's.accepted_support_id',
        //         's.done_time',
        //         's.support_accepted_time',
        //         's.assigned_time'
        //     );
        // }, 'ab')
        // ->select(
        //     'ab.accepted_support_id',
        //     'ab.name',
        //     DB::raw('COUNT(ab.accepted_support_id) AS total_supports'),
        //     DB::raw("TO_CHAR(AVG(EXTRACT(EPOCH FROM (ab.completed_time - ab.accpt_asgn_time))) * INTERVAL '1 second', 'HH24:MI:SS') AS avg_time")
        // )
        // ->groupBy('ab.name', 'ab.accepted_support_id')
        // ->get();
        // return $top_by_support_current_month;

        // $top_by_support_current_month = DB::table(function ($query) {
        //     $subquery = DB::table('supports AS s')
        //         ->leftJoin('users AS u', 's.accepted_support_id', '=', 'u.id')
        //         ->whereMonth('s.completed_time', '=', DB::raw('MONTH(CURRENT_DATE())'))
        //         ->groupBy('s.accepted_support_id', 's.completed_time', 'accpt_asgn_time')
        //         ->select([
        //             'u.name AS name',
        //             's.accepted_support_id AS accepted_support_id',
        //             's.completed_time AS completed_time',
        //             DB::raw("CASE
        //             WHEN s.accepted_time IS NOT NULL THEN s.accepted_time
        //             ELSE s.assigned_time
        //             END AS accpt_asgn_time")
        //         ]);

        //     $query->select([
        //         'ab.accepted_support_id',
        //         'ab.name',
        //         DB::raw('COUNT(ab.accepted_support_id) AS total_supports'),
        //         DB::raw("TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(ab.completed_time, ab.accpt_asgn_time)))), '%H') AS avg_time_hour"),
        //         DB::raw("TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(ab.completed_time, ab.accpt_asgn_time)))), '%i') AS avg_time_minute"),
        //         DB::raw("TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(TIMEDIFF(ab.completed_time, ab.accpt_asgn_time)))), '%s') AS avg_time_second"),
        //     ])
        //     ->fromSub($subquery, 'ab')
        //     ->groupBy('ab.name', 'ab.accepted_support_id');
        //     })
        //     ->get();

        // dd($top_by_support_current_month);

        $software_wise_clients = DB::table('addusers_softwarelist AS cs')
            ->leftJoin('addusers_customer AS customer',
                DB::raw('customer.id::text'),
                '=',
                'cs.client_id'
            )
            ->select(
                'cs.software_id',
                'cs.software_name',
                DB::raw('COUNT(cs.software_id) as total_client')
            )
            ->where('customer.is_active', true)
            ->groupBy('cs.software_id', 'cs.software_name')
            ->orderBy('total_client', 'DESC')
            ->get();

        // dd($software_wise_clients);
        $start_date = $request->start_time;
        $end_date   = $request->end_time;
        // $start_date = '2023-01-01';
        // $end_date = '2023-12-31';

        // Default date range for the last 12 months
        $default_start_date = now()->subMonths(12)->startOfMonth()->toDateString();
        $default_end_date   = now()->endOfMonth()->toDateString();

        $support_chart_by_month = DB::table(DB::raw('(
            SELECT EXTRACT(YEAR FROM s.done_time) AS year,
                EXTRACT(MONTH FROM s.done_time) AS month,
                COUNT(*) AS support_given_in_single_month
            FROM supportadmin_problems s
             WHERE s.done_time >= :start_date
              AND s.done_time <= :end_date
            GROUP BY EXTRACT(YEAR FROM s.done_time), EXTRACT(MONTH FROM s.done_time)
        ) abc'))
            ->select('abc.year', 'abc.month', 'abc.support_given_in_single_month')
            ->selectRaw("
            CASE
                WHEN abc.month = 1 THEN 'January'
                WHEN abc.month = 2 THEN 'February'
                WHEN abc.month = 3 THEN 'March'
                WHEN abc.month = 4 THEN 'April'
                WHEN abc.month = 5 THEN 'May'
                WHEN abc.month = 6 THEN 'June'
                WHEN abc.month = 7 THEN 'July'
                WHEN abc.month = 8 THEN 'August'
                WHEN abc.month = 9 THEN 'September'
                WHEN abc.month = 10 THEN 'October'
                WHEN abc.month = 11 THEN 'November'
                WHEN abc.month = 12 THEN 'December'
                ELSE 'unknown'
            END AS month_name")
            ->setBindings([
                'start_date' => $start_date ? $start_date : $default_start_date,
                'end_date'   => $end_date ? $end_date : $default_end_date,
            ])
            ->orderBy('abc.year')
            ->orderBy('abc.month')
            ->get();

        $fromDate = now()->subDays(365)->format('Y-m-d');

        // $subquery = DB::table('addusers_softwarelist as cs')
        //     ->select(
        //         DB::raw('COUNT(*) as total_sale'),
        //         // 'cs.sale_by as seller_id',
        //         'cs.sell_by_id as seller_id',
        //         'u.username as name',
        //         'cs.client_id as customer_id',
        //         'cs.software_name',
        //         'c.cusname as customer_name'

        //     )
        //     ->where('cs.agreement_date', '>=', DB::raw("'$fromDate'"))
        //     // ->where('cs.created_at', '>=', DB::raw("'$fromDate'"))
        //     ->leftJoin('addusers_customer as c', DB::raw('c.id::text'), '=', 'cs.client_id')
        //     // ->leftJoin('customers as c', 'c.customer_id', '=', 'cs.customer_id')
        //     ->leftJoin('supportadmin_support_user as u', DB::raw('u.id::text'), '=', 'cs.sell_by_id')
        //     // ->leftJoin('users as u', 'u.id', '=', 'cs.sale_by')
        //     ->groupBy(
        //         // 'cs.sale_by',
        //         'cs.sell_by_id',
        //         'cs.client_id',
        //         // 'cs.customer_id',
        //         'cs.software_name',
        //         'c.cusname',
        //         'u.username'
        //     );
        //     // ->get();

        // return $sales_information = DB::query()
        //     ->fromSub($subquery, 'foo')
        //     ->select(
        //         DB::raw('COUNT(*) AS total_sale_amount'),
        //         'foo.name',
        //         'foo.seller_id',
        //         // DB::raw("GROUP_CONCAT(DISTINCT foo.customer_name SEPARATOR ', ') AS customers"),
        //         // DB::raw("GROUP_CONCAT(DISTINCT foo.software_name SEPARATOR ', ') AS softwares")
        //         DB::raw("STRING_AGG(DISTINCT foo.customer_name, ', ') AS customers"),
        //         DB::raw("STRING_AGG(DISTINCT foo.software_name, ', ') AS softwares")
        //     )
        //     ->groupBy('foo.seller_id','foo.name')
        //     ->get();

        // return $sales_information_distinct = DB::table('addusers_softwarelist as cs')
        //     ->leftJoin('addusers_customer as c', DB::raw('c.id::text'), '=', 'cs.client_id')
        //     ->leftJoin('supportadmin_support_user as u', 'u.id', '=', DB::raw('jsonb_array_elements_text(cs.sell_by_id)::int'))
        //     // ->leftJoin('users as u2', 'u2.id', '=', 'cs.lead_by')
        //     // ->orderBy('cs.sale_by')
        //     // ->select(
        //     //     'u.name as name',
        //     //     'cs.sale_by as seller_id',
        //     //     'cs.lead_by',
        //     //     'u2.name as lead_by_name',
        //     //     'cs.customer_id',
        //     //     'cs.software_name',
        //     //     'c.name as customer_name'
        //     // )
        //       ->select(
        //               DB::raw('jsonb_array_elements_text(cs.sell_by_id)::int as sell_by_id'),
        //             'cs.client_id',
        //             'c.cusname as customer_name',
        //             'u.username as seller_name'
        //         )
        //     ->distinct()
        //     ->get();

        $support_tracker['total_support'] = Support::where('is_done', 1)->count();
        $support_tracker['processing']    = Support::where('is_processing', 1)
            ->where('requested_time', '>=', Carbon::now()->subDays(7))
            ->count();
        $support_tracker['pending'] = Support::where('is_pending', 1)
            ->where('requested_time', '>=', Carbon::now()->subDays(7))
            ->count();
        $support_tracker['done'] = Support::where('is_done', 1)
            ->where('requested_time', '>=', Carbon::now()->subDays(7))
            ->count();

        $data                                 = [];
        $data['todays_support']               = $todays_support;
        $data['top_by_support_current_month'] = $top_by_support_current_month;
        $data['software_wise_clients']        = $software_wise_clients;
        $data['support_chart_by_month']       = $support_chart_by_month;
        // $data['sales_information_aggregate'] = $sales_information;
        // $data['sales_information_distinct'] = $sales_information_distinct;
        $data['suppoert_tracker'] = $support_tracker;
        return JsonDataResponse($data);
    }

    public function homeDashboard(Request $request)
    {
        $start_time = null;
        $end_time   = null;
        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = $request->start_time;
            $end_time   = $request->end_time;
        }
        $user = Auth::user();
        if (! $user) {
            return JsonDataResponse($user);
        } else {
            $data  = [];
            $query = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
            // ->leftJoin('shops', 'shops.id', 'supports.shop_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', DB::raw('software.id::text'))
                ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
                ->leftJoin('addusers_users as client_users', 'client_users.id', 'supports.client_user_id')
                ->select(
                    'supports.id as id',
                    'customers.cusname as client_name',
                    'customers.id as customer_id',
                    'client_users.username as client_user_name',
                    'client_users.phoneno as client_user_phone',
                    'client_users.shopname as shop_address',
                    'customers.accountant_phone_no as client_phone',
                    'software.soft_name as software_name',
                    'users.username as support_person',
                    'users.id as support_person_id',
                    'supports.p_description as description',
                    'supports.prob_file_url as attachment',
                    'supports.support_accepted_time as accepted_time',
                    'supports.requested_time as requested_time',
                    'supports.done_time as completed_time',
                    'supports.is_processing'
                );

            if ($start_time && $end_time) {
                $query->whereBetween('supports.requested_time', [$start_time, $end_time]);
            }

            // Processing Data
            $data['processing_table'] = (clone $query)
                ->where('is_processing', 1)
                ->orderBy('supports.requested_time', 'DESC')
                ->get();
            // Log::info('Processing table size: ' . strlen(json_encode($data['processing_table'])) . ' bytes');

            // Solved Data
            $data['solved_table'] = (clone $query)
                ->where('is_done', 1)
                ->orderBy('supports.requested_time', 'DESC')
                ->get();

            // Canceled Data
            $data['cancel_table'] = (clone $query)
                ->where('is_transfer', 1)
                ->get();

            // Canceled Data
            // $data['pending_table'] = (clone $query)
            //     ->where('is_pending', 1)
            //     ->orderBy('supports.requested_time', 'DESC')

            //     ->get();

            // Pending Data
            $data['pending_table'] = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', DB::raw('software.id::text'))
                ->where('supports.is_pending', '=', 1)
                ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('supports.requested_time', [$start_time, $end_time]);
                })
                ->orderBy('supports.requested_time', 'DESC')
                ->get();

            $data['solved']     = $data['solved_table']->count();
            $data['processing'] = $data['processing_table']->count();
            $data['pending']    = $data['pending_table']->count();
            $data['cancel']     = $data['cancel_table']->count();

            // Cache::forget('home_dashboard_' . $user->id);
            $cachedData = Cache::remember('home_dashboard_' . $user->id . '_' . $start_time . '_' . $end_time, 60, function () use ($data) {
                return $data;
            });

            return JsonDataResponse($cachedData);
        }
    }

    public function reviewManager()
    {
        $data['client_rating_list'] = DB::table('supportadmin_problems as supports')
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
            ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
            ->leftJoin('addusers_users as clientUsers', 'clientUsers.id', 'supports.client_user_id')
            ->select(
                'supports.id as id',
                'customers.cusname as client_name',
                'customers.id AS customer_id',
                'clientUsers.username as client_username',
                'clientUsers.shopname as shop_address',
                'clientUsers.phoneno as phone_number',
                'supports.rating',
                'supports.rating_comment',
            )
            ->whereNotNull('rating')
            ->whereNotNull('rating_comment')
            ->where('is_done', true)
            ->get();

        $data['support_rating_list'] = DB::table('supportadmin_problems as supports')
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
            ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
            ->leftJoin('addusers_users as clientUsers', 'clientUsers.id', 'supports.client_user_id')
            ->select(
                'users.id as supportperson_id',
                'users.username as supportperson_name',
                DB::raw('COUNT(DISTINCT supports.client_id) as total_clients'),
                DB::raw('COUNT(CAST(supports.rating AS NUMERIC)) as total_ratings'),
                DB::raw('ROUND(SUM(CAST(supports.rating AS NUMERIC)) /COUNT(CAST(supports.rating AS NUMERIC)), 2) as average_rating')
            )
            ->whereNotNull('supports.rating')
            ->whereNotNull('supports.rating_comment')
            ->where('is_done', true)
            ->groupBy('users.id')
            ->get();

        return JsonDataResponse($data);
    }

    public function homeDashboardWithPagination(Request $request)
    {
        // dd($request->all());
        // Validate request parameters
        $request->validate([
            'start_time' => 'nullable|date',
            'end_time'   => 'nullable|date',
            'per_page'   => 'nullable|integer|min:1|max:100',
            'page'       => 'nullable|integer|min:1',
        ]);

        $user = Auth::user();
        if (! $user) {
            return JsonDataResponse(null, 'Unauthorized', 401);
        }

        $perPage   = $request->input('per_page', 15);
        $startTime = $request->input('start_time');
        $endTime   = $request->input('end_time');

        // Generate cache key including pagination parameters
        $cacheKey = sprintf(
            'home_dashboard_%d_%s_%s_page_%d_perpage_%d',
            $user->id,
            $startTime ?? 'all',
            $endTime ?? 'all',
            $request->input('page', 1),
            $perPage
        );

        return Cache::remember($cacheKey, 60, function () use ($request, $startTime, $endTime, $perPage) {
            // Base query builder with common joins and selections
            $baseQuery = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', DB::raw('software.id::text'))
                ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
                ->leftJoin('addusers_users as client_users', 'client_users.id', 'supports.client_user_id')
                ->select([
                    'supports.id',
                    'customers.cusname as client_name',
                    'customers.id as customer_id',
                    'client_users.username as client_user_name',
                    'client_users.phoneno as client_user_phone',
                    'client_users.shopname as shop_address',
                    'customers.accountant_phone_no as client_phone',
                    'software.soft_name as software_name',
                    'users.username as support_person',
                    'users.id as support_person_id',
                    'supports.p_description as description',
                    'supports.prob_file_url as attachment',
                    'supports.support_accepted_time as accepted_time',
                    'supports.requested_time',
                    'supports.done_time as completed_time',
                    'supports.is_processing',
                ])
                ->when($startTime && $endTime, function ($query) use ($startTime, $endTime) {
                    return $query->whereBetween('supports.requested_time', [$startTime, $endTime]);
                });

            // Get counts using a single query with CASE statements
            $counts = DB::table('supportadmin_problems')
                ->select([
                    DB::raw('COUNT(CASE WHEN is_done = true THEN 1 END) as solved'),
                    DB::raw('COUNT(CASE WHEN is_processing = true THEN 1 END) as processing'),
                    DB::raw('COUNT(CASE WHEN is_pending = true THEN 1 END) as pending'),
                    DB::raw('COUNT(CASE WHEN is_transfer = true THEN 1 END) as cancel'),
                ])
                ->when($startTime && $endTime, function ($query) use ($startTime, $endTime) {
                    return $query->whereBetween('requested_time', [$startTime, $endTime]);
                })
                ->first();

            // Get paginated data for each status
            $data = [
                'processing_table' => (clone $baseQuery)
                    ->where('is_processing', 1)
                    ->orderBy('supports.requested_time', 'DESC')
                    ->paginate($perPage),

                'solved_table'     => (clone $baseQuery)
                    ->where('is_done', 1)
                    ->orderBy('supports.requested_time', 'DESC')
                    ->paginate($perPage),

                'cancel_table'     => (clone $baseQuery)
                    ->where('is_transfer', 1)
                    ->orderBy('supports.requested_time', 'DESC')
                    ->paginate($perPage),

                'pending_table'    => (clone $baseQuery)
                    ->where('is_pending', 1)
                    ->orderBy('supports.requested_time', 'DESC')
                    ->paginate($perPage),

                // Add counts to response
                'solved'           => $counts->solved,
                'processing'       => $counts->processing,
                'pending'          => $counts->pending,
                'cancel'           => $counts->cancel,
            ];

            // return $data;
            return JsonDataResponse($data);
        });
    }
}
