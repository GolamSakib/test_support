<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\Support;
use App\Models\Complain;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Software;
use Illuminate\Http\Request;
use App\Models\CustomerSoftware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SoftwareSupportPerson;
use App\Http\Controllers\Api\V2\Controller;


class ClientDashboardController extends Controller
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
    public function clientDashBoard()
    {

        $user = Auth::user();
        $customer_id = $user->client_id;
        $customer = Customer::find($customer_id);

        if (!$customer) {
            return JsonDataResponse($customer);
        }


        $data['client_info'] = [
            'client_name' =>   $customer->cusname,
            'client_phone' =>  $customer->accountant_phone_no,
            'client_profile_url' => $user->pro_img_url ? asset($user->pro_img_url) : null,
        ];

        $query = DB::table('supportadmin_problems as supports')
            ->leftJoin(
                'addusers_customer as customers',
                DB::raw('customers.id::text'),
                '=',
                'supports.client_id'
            )
            ->leftJoin(
                'client_support_admin_softwarelistall as software',
                'supports.soft_id',
                '=',
                DB::raw('software.id::text')
            )
            ->where('supports.client_id', $customer_id);

        $solvedQuery = clone $query;
        $solved = $solvedQuery
            ->where('supports.is_done', true)
            ->leftJoin(
                'supportadmin_support_user as users',
                'users.id',
                '=',
                'supports.accepted_support_id'
            )
            ->leftJoin(
                'supportadmin_rating_review as ratings',
                DB::raw('supports.id::text'),
                '=',
                'ratings.problem_id'
            )
            ->select(
                'supports.id as id',
                'customers.cusname as client_name',
                'customers.id AS customer_id',
                'software.soft_name as software_name',
                'supports.is_helped as is_requested_help',
                'supports.p_description as description',
                'supports.support_accepted_time as accepted_time',
                'supports.requested_time as requested_time',
                'supports.done_time as completed_time',
                'supports.comment as comment',
                'supports.is_processing',
                'supports.is_done',
                'supports.helped_by',
                'supports.rating',
                'supports.rating_comment',
                'supports.accepted_support_id',
                'users.username as support_username',
                'ratings.solved_text',
                'ratings.suggestion_text',
                'supports.is_rated',
                'supports.rating_time'
            )
            ->orderBy('done_time', 'desc')
            ->get();

        $pendingQuery = clone $query;
        $pending = $pendingQuery
            ->where('is_pending', 1)
            ->select(
                'supports.id as id',
                'customers.cusname as client_name',
                'customers.id AS customer_id',
                'software.soft_name as software_name',
                'supports.is_helped as is_requested_help',
                'supports.p_description as description',
                'supports.support_accepted_time as accepted_time',
                'supports.requested_time as requested_time',
                'supports.done_time as completed_time',
                'supports.is_processing',
                'supports.helped_by',
            )
            ->get();


        $processingQuery = clone $query;
        $processing = $processingQuery
            ->where('is_processing', 1)
            ->select(
                'supports.id as id',
                'customers.cusname as client_name',
                'customers.id AS customer_id',
                'software.soft_name as software_name',
                'supports.is_helped as is_requested_help',
                'supports.p_description as description',
                'supports.support_accepted_time as accepted_time',
                'supports.requested_time as requested_time',
                'supports.done_time as completed_time',
                'supports.is_processing',
                'supports.accepted_support_username',
                'supports.accepted_support_id',
                'supports.helped_by',
                // 'users.username as support_username',
            )
            ->get();

        $softwares = CustomerSoftware::where('client_id', $customer_id)->get();
        $ids = $softwares->pluck('id')->toArray();

        $supports = DB::table('supportadmin_problems as support')
            ->leftJoin('client_support_admin_softwarelistall as software', 'support.soft_id', DB::raw('software.id::text'))
            ->leftJoin('supportadmin_support_user as users', 'users.id', 'support.accepted_support_id')
            ->whereNotNull('accepted_support_id')
            ->groupBy('support.accepted_support_id', 'users.username')
            ->select('users.username')
            ->get();



        // $supports = DB::table('software_support_people as ssp')
        // ->leftJoin('users', 'users.id', '=', 'ssp.user_id')
        // ->whereIn('ssp.customer_software_id', $ids)
        // ->groupBy('ssp.user_id')
        // ->select('users.name')
        // ->get();
        $total_support_person = $supports->count();
        $data['solved'] = count($solved);
        $data['pending'] = count($pending);
        $data['processing'] = count($processing);

        $data['solved_table'] = $solved;
        $data['pending_table'] = $pending;
        $data['processing_table'] = $processing;

        $data['supports'] = $supports;
        $data['total_support_person'] = $total_support_person;

        return JsonDataResponse($data);
    }

    public function clientReview(Request $request)
    {
        // dd('hlw');
        $validatedData = $request->validate([
            'rating' => 'required',
            'comment' => 'required',
            'supportId' => 'required',
        ]);

        $support = Support::where('id', $request->supportId)->first();
        if (!$support) {
            return JsonDataResponse($support);
        }

        $support->is_rated = true;
        $support->rating = $request->rating;
        $support->rating_comment = $request->comment;
        $support->rating_time = Carbon::now();
        $support->save();

        // $support->update([
        //     'rating' => $request->rating,
        //     'rating_comment' => $request->comment,
        // ]);

        return response()->json([
            "success" => true,
            "status" => 200,
            "message" => "Review was successfully updated"
            // 'data' => $support,
        ], 200);
    }

    public function reviewedSupport()
    {

        $user = Auth::user();
        $customer_id = $user->client_id;

        $customer = Customer::find($customer_id);

        if (!$customer) {
            return JsonDataResponse($customer);
        }

        $data = DB::table('supportadmin_problems as supports')
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'supports.client_id')
            ->leftJoin('client_support_admin_softwarelistall as software', 'supports.soft_id', '=', DB::raw('software.id::text'))
            ->where('supports.client_id', $customer_id)
            ->where('supports.is_done', true)
            ->whereNotNull('supports.is_rated')
            ->where('supports.is_rated', true)
            ->whereNotNull('supports.accepted_support_id')
            ->leftJoin('supportadmin_support_user as users', 'users.id', 'supports.accepted_support_id')
            ->select(
                'supports.id as id',
                'customers.cusname as client_name',
                'customers.id AS customer_id',
                'software.soft_name as software_name',
                'supports.is_helped as is_requested_help',
                'supports.p_description as description',
                'supports.support_accepted_time as accepted_time',
                'supports.requested_time as requested_time',
                'supports.done_time as completed_time',
                'supports.is_done',
                'supports.helped_by',
                'supports.rating',
                'supports.rating_comment',
                'supports.accepted_support_id',
                'users.username as support_username',
                'supports.is_rated',
            )
            // ->orderBy('completed_time','desc')
            ->orderBy('id', 'desc')
            ->get();

        return JsonDataResponse($data);
    }
}
