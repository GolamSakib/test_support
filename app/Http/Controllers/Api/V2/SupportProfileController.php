<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Complain;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\License;
use App\Models\Payment;
use App\Models\SoftwareSupportPerson;
use App\Models\Support;
use App\Models\Training;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupportProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/support-profile-list",
     *     tags={"Support Profile"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a specific profilelist",
     *@OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Show All Data.")
     *         )
     *     ),
     *
     * )
     */

    public function index(Request $request)
    {
        $profilelist = User::where('user_type', 'support')->get(['id', 'name', 'username', 'first_name', 'last_name', 'email', 'phone_no', 'designation', 'photo', 'user_type', 'address', 'status']);
        return response()->json(['data' => $profilelist], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/support-profile-individual",
     *     tags={"Support Profile"},
     *     summary="Get support profile details for an individual",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="User ID",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */

    public function SupportProfileDetails(Request $request)
    {
        if (!$request->has('id')) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        $supportId = $request['id'];
        $cacheKey = 'support_profile_individual_' . $supportId;

        try {
            $cachedData = Cache::remember($cacheKey, 86400, function () use ($supportId) {
                $support_profile = User::select('id', 'username', 'email', 'phone_no', 'designation', 'pro_img_url')
                    ->with([
                        'customerCountforSupportPerson.support_generated_by_client',
                        'acceptedSupports' => function ($query) {
                            $query->with([
                                'customer:id,cusname',
                                'software:id,soft_name',
                            ])
                                ->select(
                                    'id',
                                    'client_id',
                                    'soft_id',
                                    'soft_name',
                                    'p_description',
                                    'shop_name',
                                    'client_name',
                                    'comment',
                                    'accepted_support_id',
                                    'is_done',
                                    'is_pending',
                                    'is_helped',
                                    'helped_by_id',
                                    'requested_time',
                                    'done_time'
                                );
                        },
                    ])
                    ->findOrFail($supportId);

// Process image URL
                $support_profile->image = strpos($support_profile->pro_img_url, 'media') !== false
                ? 'http://support.mediasoftbd.com' . $support_profile->pro_img_url
                : asset($support_profile->pro_img_url);

// Get all supports in a single collection to avoid multiple queries
                $support = $support_profile->acceptedSupports;

// Calculate support metrics
                $total_support_generated_by_client = $support_profile->customerCountforSupportPerson
                    ->sum(function ($customer) {
                        return $customer->support_generated_by_client->count();
                    });

                $completed_count = $support->where('is_done', 1)->count();
                $accepted_count = $support->count();

// Calculate capacities
                $support_profile->solving_capacity = $accepted_count > 0
                ? (int) (($completed_count / $accepted_count) * 100)
                : 0;

                $support_profile->response_capacity = $total_support_generated_by_client > 0
                ? (int) (($accepted_count / $total_support_generated_by_client) * 100)
                : 0;
                $support_profile->client_number = SoftwareSupportPerson::where('support_person_id', $supportId)
                    ->distinct('client_id')
                    ->count();
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;
                $pending_support_data = $support
                    ->where('is_pending', 1)
                    ->filter(function ($item) use ($currentMonth, $currentYear) {
                        return Carbon::parse($item->requested_time)->month === $currentMonth
                        && Carbon::parse($item->requested_time)->year === $currentYear;
                    })
                    ->map(function ($support) {
                        $support->client_name = $support->customer->cusname;
                        return $support;
                    })
                    ->values();
                $support_profile->pending_support_data = $pending_support_data;
                $support_profile->pending_support = $pending_support_data->count();
$solved_support_data = $support
    ->where('is_done', 1)
    ->whereNotNull('done_time') // Add this line to filter out null done_time
    ->filter(function ($item) use ($currentMonth, $currentYear) {
        return Carbon::parse($item->done_time)->month === $currentMonth
        && Carbon::parse($item->done_time)->year === $currentYear;
    })
    ->map(function ($support) {
        $support->client_name = $support->customer->cusname;
        return $support;
    })
    ->sortByDesc('done_time')
    ->values();


                $support_profile->solved_support_data = $solved_support_data;
                $support_profile->solved = $solved_support_data->count();
                $support_profile->help_given = $support->where('is_helped', 1)->where('helped_by_id', $supportId)->count();
                $support_profile->help_wanted = $support->whereNotNull('helped_by_id')->count();

                $support_profile->most_supported_client = $support_profile->acceptedSupports
                    ->groupBy('client_id')
                    ->map(function ($group) use ($supportId) {
                        $firstSupport = $group->first();
                        return [
                            'accepted_support_id' => $supportId,
                            'client_id' => $firstSupport->client_id,
                            'total_support_given' => $group->count(),
                            'cusname' => $firstSupport->customer->cusname,
                        ];
                    })
                    ->sortByDesc('total_support_given')
                    ->take(5)
                    ->values();

                $support_profile->most_supported_software = $support_profile->acceptedSupports
                    ->whereNotNull('soft_id')
                    ->groupBy('soft_id')
                    ->map(function ($group) {
                        $firstSupport = $group->first();
                        return [
                            'soft_id' => $firstSupport->soft_id,
                            'soft_name' => $firstSupport->soft_name ?? null,
                            'total_support_to_software' => $group->count(),
                        ];
                    })
                    ->sortByDesc('total_support_to_software')
                    ->take(5)
                    ->values();

                $support_profile->most_recent_support = $support
                    ->where('is_done', 1)
                    ->whereNotNull('done_time')
                    ->sortByDesc('done_time')
                    ->take(5)
                    ->map(function ($support) {
                        return [
                            'client_id' => $support->client_id,
                            'accepted_support_id' => $support->accepted_support_id,
                            'soft_id' => $support->soft_id,
                            'soft_name' => $support->soft_name,
                            'p_description' => $support->p_description,
                            'client_name' => $support->client_name,
                            'shop_name' => $support->shop_name,
                            'comment' => $support->comment,
                            'shop_name' => $support->shop_name,
                            'done_time' => $support->done_time,
                        ];
                    })
                    ->values();

                return $support_profile;
            });

            return response()->json([
                'success' => true,
                'status' => 'success',
                'data' => $cachedData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'An error occurred while fetching the support profile',
                'data' => null,
            ], 500);
        }
    }

    public function ClientsBySupportPerson()
    {
        $support_person = Auth::user();
        $clients = DB::table(function ($query) {
            $query->select('customer.*')
                ->from('addusers_supportperson as supportPerson')
                ->leftJoin('addusers_customer as customer', DB::raw('customer.id::text'), 'supportPerson.client_id')
                ->where('customer.is_active', true)
                ->where('customer.is_registered', true)
                ->groupBy('supportPerson.client_id', 'customer.id');
        }, 'subquery')
            ->orderBy('cusname', 'asc')
            ->get();

        if (!$clients) {
            return JsonDataResponse($clients);
        } else {
            return JsonDataResponse($clients);
        }
    }
    public function ClientsBySupportPersonForApp()
    {
        $support_person = Auth::user();

        $clients = DB::table('addusers_supportperson as supportPerson')
            ->where('supportPerson.support_person_id', $support_person->id)
            ->where('customer.is_active', true)
            ->leftJoin('addusers_customer as customer', DB::raw('customer.id::text'), 'supportPerson.client_id')
            ->select('customer.*')
            ->whereNotNull('customer.id')
            ->whereNotNull('customer.cusname')
            ->distinct('supportPerson.client_id')
            ->get();

        return JsonDataResponse($clients);
    }

public function ClientsBySupportPersonForAppPayroll($username)
{
    $supportPerson = User::where('username', $username)->first();

    if (!$supportPerson) {
        return response()->json([
            'success' => false,
            'status' => 404,
            'message' => 'Support person not found',
            'data' => null
        ], 404);
    }

    $clients = DB::table('addusers_supportperson as supportPerson')
        ->where('supportPerson.support_person_id', $supportPerson->id)
        ->where('customer.is_active', true)
        ->leftJoin('addusers_customer as customer', DB::raw('customer.id::text'), 'supportPerson.client_id')
        ->select('customer.*')
        ->whereNotNull('customer.id')
        ->whereNotNull('customer.cusname')
        ->distinct('supportPerson.client_id')
        ->get();

    return response()->json([
        'success' => true,
        'status' => 200,
        'message' => 'Clients retrieved successfully',
        'data' => $clients
    ], 200);
}

    public function support_billing_info($id, Request $request)
    {
        $cacheKey = 'support_billing_info_' . $id;

        return Cache::remember($cacheKey, 86400, function () use ($id) {
            $data = array();
            $support_id = SoftwareSupportPerson::with('customer')
                ->where('support_person_id', $id)
                ->where('client_id', '!=', null)
                ->groupBy('client_id', 'id')
                ->get();

            foreach ($support_id as $c_id) {
                if (!is_numeric($c_id->client_id)) {
                    continue;
                }

                $bill = billingOfCustomer($c_id->client_id);
                $filteredBill = collect($bill)
                    ->filter(fn($item) => $item->isCollected == 'N')
                    ->sortByDesc('billProcessDt');

                $data[] = [
                    'due' => $filteredBill->sum('balanceAmt'),
                    'client_id' => $c_id->client_id,
                    'due_month' => $filteredBill->count(),
                    'in_charge' => $c_id->supportperson,
                    'billing' => $c_id->is_billing_in_charge,
                    'supervisor' => $c_id->is_supervisor,
                    'name' => $c_id->customer->cusname ?? '',
                ];
            }

            $data = array_filter($data);
            usort($data, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
            });

            $number_of_client= count($data);
            $total_due = array_sum(array_column($data, 'due'));

            return [
                "success" => true,
                "status" => 200,
                'message' => 'Customer Found',
                'data' => $data,
                'number_of_client' => $number_of_client,
                'total_due' => $total_due,
            ];
        });
    }

    public function supportGivenBySupportPerson()
    {
        $user = Auth::user();
        if ($user) {
$supports = DB::table('supportadmin_problems')
    ->leftJoin('addusers_customer as c', DB::raw('c.id::text'), '=', 'supportadmin_problems.client_id')
    ->leftJoin('client_support_admin_softwarelistall as s2', DB::raw('s2.id::text'), '=', 'supportadmin_problems.soft_id')
    ->where('supportadmin_problems.accepted_support_id', $user->id)
    ->where('supportadmin_problems.is_done', 1)
    ->select('c.cusname as customer', 'supportadmin_problems.shop_name as shop', 's2.soft_name as software', 'supportadmin_problems.p_description as description', 'supportadmin_problems.comment as comment')
    ->orderBy('c.cusname')
    ->paginate(10);

            if ($supports) {
                return JsonDataResponse($supports);
            }
        } else {
            return JsonDataResponse();
        }
    }

    public function customer_monitoring_by_support_person()
    {
        $support_person = Auth::user();
        $clients = SoftwareSupportPerson::where('support_person_id', $support_person->id)->get();
        $customers = [];
        foreach ($clients as $key => $client) {
            $customers[$key] = monitoringOfCustomer($client->client_id);
        }
        $data = [];
        foreach ($customers as $customer) {
            foreach ($customer as $c) {
                array_push($data, $c);
            }
        }
        return JsonDataResponse($data);
    }

    public function supportProfileDashboard(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        } else {
            $data = [];
            $query = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
                ->where('accepted_support_id', $user->id)
            // ->where('accepted_support_username', $user->username)
                ->select(
                    'supports.id as id',
                    'customers.cusname as client_name',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no as client_phone',
                    'software.soft_name as software_name',
                    'supports.is_helped as is_requested_help',
                    'supports.p_description as description',
                    'supports.comment as comment',
                    'supports.note_for_support as note_for_support',
                    'supports.prob_file_url as attachment',
                    'supports.support_accepted_time as accepted_time',
                    'supports.shop_name as shop_address',
                    'supports.requested_time as requested_time',
                    'supports.done_time as completed_time',
                    'supports.is_processing',
                    'supports.helped_by',
                    'supports.is_done',

                );

            $processingQuery = clone $query;
            $data['processing_table'] = $processingQuery
                ->where(function ($query) {
                    $query->whereNotNull('is_processing')
                        ->where('is_processing', 1);
                })
                ->where('is_done', 0)
                // ->orderBy('support_accepted_time', 'desc')
                ->orderBy('requested_time', 'desc')
                ->get();
            // ->where('is_processing', 1)
            // ->paginate(10, ['*'], 'processing_page');

            $solvedQuery = clone $query;
            $data['solved_table'] = $solvedQuery
                ->where(function ($query) {
                    $query->whereNotNull('is_done')
                        ->where('is_done', 1);
                })
                ->orderBy('done_time', 'desc')
                // ->where('is_done', 1)
                ->get();
            // ->paginate(10, ['*'], 'solved_page');

            // ->paginate(10, ['*'], 'cancelled_page');

            $data['pending_table'] =
            DB::table('supportadmin_problems as supports')
                ->select(
                    'supports.id AS id',
                    'supports.soft_id AS software_id',
                    'customers.cusname as client_name',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no AS client_phone',
                    'software.soft_name AS software_name',
                    'supports.shop_name AS shop_address',
                    'supports.p_description AS description',
                    'supports.comment as comment',
                    'supports.prob_file_url AS attachment',
                    'supports.requested_time AS requested_time',
                    'supports.is_processing',
                    'supports.is_refused',
                    'supports.is_transfer'
                )
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
                ->where(function ($query) {
                    $query->whereNull('supports.is_refused')
                        ->orWhere('supports.is_refused', '=', 0);
                })
                ->where(function ($query) {
                    $query->whereNull('supports.is_pending')
                        ->orWhere('supports.is_pending', '=', 1);
                })
            // ->where('supports.is_pending', '=', 1)
                ->where(function ($query) {
                    $query->whereNull('supports.is_helped')
                        ->orWhere('supports.is_helped', '=', 0);
                })
                ->orderBy('requested_time', 'desc')
                ->get();
            // ->paginate(10, ['*'], 'pending_page');
            $data['cancel_table'] = Support::where('canceled_by', $user->id)->orderBy('requested_time', 'desc')->get();

            $data['help_table']
            = DB::table('supportadmin_problems as supports')
                ->select(
                    'supports.id AS id',
                    'supports.soft_id AS software_id',
                    'customers.cusname AS client_name',
                    'customers.id AS customer_id',
                    'supports.shop_name as shop_address',
                    'customers.accountant_phone_no AS client_phone',
                    'software.soft_name AS software_name',
                    'supports.p_description AS description',
                    'supports.comment as comment',
                    'supports.prob_file_url AS attachment',
                    'supports.requested_time AS requested_time',
                    'supports.is_processing',
                    'supports.is_refused',
                    'supports.is_transfer',
                    'supports.is_helped'
                )
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
                ->where(function ($query) {
                    $query->whereNull('supports.is_refused')
                        ->orWhere('supports.is_refused', '=', false);
                })
                ->where('supports.helped_by_id', $user->id)
                ->where(function ($query) {
                    $query->whereNull('supports.is_pending')
                        ->orWhere('supports.is_pending', '=', false);
                })
                ->where(function ($query) {
                    $query->whereNotNull('supports.is_helped')
                        ->orWhere('supports.is_helped', '=', true);
                })
                ->where(function ($query) {
                    $query->whereNull('supports.is_helped_done')
                        ->orWhere('supports.is_helped_done', '=', false);
                })
                ->where(function ($query) {
                    $query->whereNull('supports.is_helped_done')
                        ->orWhere('supports.is_helped_done', '=', false);
                })
                ->where(function ($query) {
                    $query->whereNull('supports.is_done')
                        ->orWhere('supports.is_done', '=', false);
                })
                ->orderBy('requested_time', 'desc')
            // ->where('supports.is_done', '=', false)
            // ->paginate(10, ['*'], 'pending_page');
                ->get();

            $data['help_given_table']
            = DB::table('supportadmin_problems as supports')
                ->select(
                    'supports.id AS id',
                    'supports.soft_id AS software_id',
                    'customers.cusname AS client_name',
                    'supports.shop_name as shop_address',
                    'supports.accepted_support_username as accepted_support_username',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no AS client_phone',
                    'software.soft_name AS software_name',
                    'supports.p_description AS description',
                    'supports.comment as comment',
                    'supports.prob_file_url AS attachment',
                    'supports.requested_time AS requested_time',
                    'supports.is_processing',
                    'supports.is_refused',
                    'supports.is_transfer',
                    'supports.is_done',
                    'supports.is_helped_done'
                )
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))

                ->where('supports.helped_by_id', $user->id)
                ->where('supports.is_helped_done', true)
                ->where('supports.is_done', true)
                ->orderBy('requested_time', 'desc')
                ->get();

            $data['help_given_by_table']
            = DB::table('supportadmin_problems as supports')
                ->select(
                    'supports.id AS id',
                    'supports.soft_id AS software_id',
                    'customers.cusname AS client_name',
                    'supports.shop_name as shop_address',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no AS client_phone',
                    'software.soft_name AS software_name',
                    'supports.p_description AS description',
                    'supports.comment as comment',
                    'supports.prob_file_url AS attachment',
                    'supports.requested_time AS requested_time',
                    'supports.is_processing',
                    'supports.is_refused',
                    'supports.is_transfer',
                    'supports.is_done',
                    'supports.is_helped_done',
                    'supports.helped_by as support_person_name',
                )
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))

                ->where('supports.accepted_support_username', $user->username)
                ->where('supports.is_helped_done', true)
                ->where('supports.is_done', true)
                ->orderBy('requested_time', 'desc')
                ->get();

            $data['solved'] = Support::where('is_done', 1)->where('accepted_support_id', $user->id)->count();
            $data['processing'] = Support::where('is_processing', 1)->where('is_done', 0)->where('accepted_support_id', $user->id)->count();
            $data['pending'] = $data['pending_table']->count();
            $data['help_needed'] = $data['help_table']->count();
            $data['help_given'] = $data['help_given_table']->count();
            $data['help_taken'] = $data['help_given_by_table']->count();
            $data['cancel'] = $data['cancel_table']->count();
            return JsonDataResponse($data);
        }
    }

    public function allData()
    {
        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        } else {
            $data = [];

            $query = Support::query();

            // $data['pending_support'] =
            // DB::table('supportadmin_problems as supports')
            //     ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'supports.client_id')
            //     ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
            // ->where(function ($query) {
            //     $query->whereNull('supports.is_pending')
            //         ->orWhere('supports.is_pending', '=', 1);
            // })
            //     ->orderBy('requested_time', 'desc')
            //     ->count();
            $data['pending_support'] = $query->where('is_pending', 1)
                ->where(function ($query) {
                    $query->whereNull('is_pending')
                        ->orWhere('is_pending', '=', 1);
                })
            ->count();

            // $data['not_accepted_support'] =
            // DB::table('supportadmin_problems as supports')
            //     ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
            //     ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', DB::raw('software.id::text'))
            //     ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
            //     ->leftJoin('addusers_users as client_users', 'client_users.id', 'supports.client_user_id')
            //     ->where('is_transfer', 1)
            //     ->count();
            $data['not_accepted_support'] = $query->where('is_transfer', 1)->count();

            // Count pending inventory
            $data['pending_inventory'] = Inventory::query()
                ->select('id', 'is_assigned', 'is_approved', 'is_accept')
                ->where(function ($query) {
                    $query->whereNull('is_assigned')
                        ->orWhere('is_assigned', false);
                })
                ->where(function ($query) {
                    $query->whereNull('is_accept')
                        ->orWhere('is_accept', false);
                })
                ->whereNull('is_approved')
                ->count();

            // Count inventory that assigned to support person
            $data['assigned_inventory'] = Inventory::query()
                ->select('id', 'is_assigned', 'is_approved', 'is_accept')
                ->where('assigned_person_id', $user->id)
                ->where('is_approved', 1)
                ->where('is_assigned', true)
                ->whereNull('is_done')
                ->count();

            // Count pending training
            $data['pending_training'] = Training::query()
                ->select('id', 'is_assigned', 'is_approved', 'is_accept')
                ->where(function ($query) {
                    $query->whereNull('is_assigned')
                        ->orWhere('is_assigned', false);
                })
                ->where(function ($query) {
                    $query->whereNull('is_accept')
                        ->orWhere('is_accept', false);
                })
                ->whereNull('is_approved')
                ->count();

            // Count training that assigned to support person
            $data['assigned_training'] = Training::query()
                ->select('id', 'is_assigned', 'is_approved', 'is_accept')
                ->where('assigned_person_id', $user->id)
                ->where('is_approved', 1)
                ->where('is_assigned', true)
                ->whereNull('is_done')
                ->count();

            // Count pending payment
            $data['pending_payment'] = Payment::query()
                ->where(function ($query) {
                    $query->whereNull('is_assigned')
                        ->orWhere('is_assigned', false);
                })
                ->count();

            // Count payment that assigned to support person
            $data['assigned_payment'] = Payment::query()
                ->where('assigned_person_id', $user->id)
                ->whereNull('is_collected')
                ->where('is_assigned', true)
                ->count();

            // Count pending complain
            $data['pending_complain'] = Complain::query()
                ->where(function ($query) {
                    $query->whereNull('is_rejected')
                        ->orWhere('is_rejected', false);
                })
                ->where(function ($query) {
                    $query->whereNull('is_accepted')
                        ->orWhere('is_accepted', false);
                })
                ->where(function ($query) {
                    $query->whereNull('is_forward')
                        ->orWhere('is_forward', false);
                })
                ->count();

            // Count payment that assigned to support person
            $data['assigned_complain'] = Complain::query()
                ->where('assign_to_id', $user->id)
                ->where('is_forward', true)
                ->where(function ($query) {
                    $query->whereNull('is_rejected')
                        ->orWhere('is_rejected', false);
                })
                ->where(function ($query) {
                    $query->whereNull('is_accepted')
                        ->orWhere('is_accepted', false);
                })
                ->count();

            $data['pending_license'] = License::query()
                ->where('is_approved', -1)
                ->count();

            return JsonDataResponse($data);
        }
    }

    public function individualSupport(Request $request)
    {
        $support_person_id = $request->id;
        $client_id = $request->client_id;
        $soft_id = $request->soft_id;

        // Retrieve support profile
        $support_profile = User::select('id', 'username', 'email', 'phone_no', 'designation', 'pro_img_url')
            ->find($support_person_id);

        $data = [
            'support_taken_client' => [],
            'support_taken_software' => [],
        ];

        // Fetch support taken by client if client_id is provided
        if ($client_id !== null && $client_id !== 'null') {
            $data['support_taken_client'] = DB::table('supportadmin_problems as supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
                ->where('accepted_support_id', $support_person_id)
                ->where('client_id', $client_id)
                ->select(
                    'supports.id as id',
                    'customers.cusname as client_name',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no as client_phone',
                    'software.soft_name as software_name',
                    'supports.p_description as description',
                    'supports.prob_file_url as attachment',
                    'supports.shop_name as shop_address',
                    'supports.requested_time as requested_time',
                    'supports.done_time as completed_time',
                )
                ->get();
        }

        // Fetch supported software if soft_id is provided
        if ($soft_id !== null && $soft_id !== 'null') {
            // return 'ok';
            $data['support_taken_software'] = DB::table('supportadmin_problems AS supports')
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
                ->leftJoin('client_support_admin_softwarelistall AS software', DB::raw('supports.soft_id::numeric'), '=', 'software.id')

            // ->select(
            //     's.soft_id',
            //     's2.soft_name',
            //     'customers.cusname as client_name',
            //     'customers.id AS customer_id'
            // )
                ->select(
                    'supports.id as id',
                    'customers.cusname as client_name',
                    'customers.id AS customer_id',
                    'customers.accountant_phone_no as client_phone',
                    'software.soft_name as software_name',
                    'supports.p_description as description',
                    'supports.prob_file_url as attachment',
                    'supports.shop_name as shop_address',
                    'supports.requested_time as requested_time',
                    'supports.done_time as completed_time',
                )
                ->where('supports.accepted_support_id', $support_person_id)
                ->where('supports.soft_id', $soft_id)
                ->get();
        }

        // return $data;
        return JsonDataResponse($data);
    }

    public function customer_monitoring_for_billing_incharge()
    {
        $support_person = Auth::user();
        $clients = SoftwareSupportPerson::where('support_person_id', $support_person->id)
            ->where('is_billing_in_charge', true)
            ->get();

        $customers = [];
        foreach ($clients as $key => $client) {
            $customers[$key] = monitoringOfCustomer($client->client_id);
        }
        // return $customers;
        $data = [];
        foreach ($customers as $customer) {
            foreach ($customer as $c) {
                // if (Carbon::parse($c->promiseOrCallDate)->isToday()) {
                array_push($data, $c);
                // }
            }
        }

        // return $data;
        return JsonDataResponse($data);
    }
}
