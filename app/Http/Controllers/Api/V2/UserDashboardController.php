<?php

namespace App\Http\Controllers\Api\V2;

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

class UserDashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
 * @OA\Get(
 *     path="/api/v2/user_dashboard",
 *     tags={"User Dashboard"},
 *     summary="Get user dashboard data",
 *     @OA\Response(
 *         response=200,
 *         description="Success response",
 *         @OA\JsonContent(
 *             @OA\Property(property="support_people", type="array", @OA\Items(type="string"), description="List of support people"),
 *             @OA\Property(property="done", type="array", @OA\Items(type="string"), description="List of support done"),
 *             @OA\Property(property="done_count", type="integer", description="Count of completed support requests"),
 *             @OA\Property(property="processing", type="array", @OA\Items(type="string"), description="List of support processing"),
 *             @OA\Property(property="processing_count", type="integer", description="Count of support requests in progress"),
 *             @OA\Property(property="pending", type="array", @OA\Items(type="string"), description="List of support pending"),
 *             @OA\Property(property="pending_count", type="integer", description="Count of pending support requests")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Error response",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while retrieving dashboard data.")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function user_dashboard()
    {
        $user = Auth::user();
        $check_user = User::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        // dd($check_user);
        $customer_id = $check_user->customer_id;
        if (!$customer_id) {
            return JsonDataResponse($customer_id);
        }
        // dd($customer_id);
        $support_people=SoftwareSupportPerson::with('support_person')->where('customer_software_id',$customer_id)->get()->pluck('support_person.name');
        if (!$support_people) {
            return JsonDataResponse($support_people);
        }

        $supports = Support::where('user_id', $check_user->id)
        ->whereIn('is_done', [1, 0])
        ->leftJoin('users', 'supports.accepted_support_id', '=', 'users.id')
        ->leftJoin('software', 'software.id', '=', 'supports.software_id')
        ->get();
        $done = $supports->where('is_done', 1);
        $processing = $supports->where('is_processing', 1);
        $pending = $supports->where('is_pending', 1);
        $done_count=$supports->where('is_done',1)->count();
        $processing_count=$supports->where('is_processing',1)->count();
        $pending_count=$supports->where('is_pending',1)->count();
        $data=[
            'support_people'=>$support_people,
            'done'=>$done,
            'processing'=>$processing,
            'pending'=>$pending,
            'done_count'=>$done_count,
            'processing_count'=>$processing_count,
            'pending_count'=>$pending_count,
        ];
        return JsonDataResponse($data);
    }
}
