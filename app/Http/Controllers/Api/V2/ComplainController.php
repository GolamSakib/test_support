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
use App\Http\Controllers\Api\V2\Controller;
use App\Models\ClientUser;
use Carbon\Carbon;

class ComplainController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/complain/all",
     *     tags={"Complain"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get all complain",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function complainAlertAll(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $user = Auth::user();
        $check_user = User::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        } else if (strtolower($check_user->role) == 'admin' || strtolower($check_user->role) == 'super admin' || strtolower($check_user->role) == 'super_admin') {
            $complains =
            DB::table('clientlogin_complain as complains')
            ->leftJoin('supportadmin_support_user as users', 'users.id', 'complains.assign_to_id')
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'complains.client_id')
            ->leftjoin('client_support_admin_softwarelistall as software', 'complains.soft_id', DB::raw('software.id::text'))
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('complains.created_time', [$start_time, $end_time]);
            })
            ->select(
                'complains.id',
                'customers.cusname as client_name',
                'customers.id as customer_id',
                'software.soft_name as software_name',
                'complains.complain_title as title',
                'complains.complain_body as description',
                'complains.created_time as created_date',
                'complains.is_rejected',
                'complains.is_accepted',
                'complains.is_forward as is_assigned',
                'complains.assign_to as assigned_to',
                'complains.is_seen',
                'users.username as assigned_person')
            ->orderBy('complains.created_time', 'desc')
            // ->paginate(20);
            ->get();

            if (!$complains) {
                return JsonDataResponse($complains);
            } else {
                return JsonDataResponse($complains);
            }
        }
    }

    /**
     * @OA\Get(
     *  path="/api/v2/complain/complainAlertByClient",
     *     tags={"Complain"},
     *     security={{"bearerAuth": {}}},
     *     summary="complainAlertByClient",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function complainAlertByClient(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        }
        $complains
            = DB::table('clientlogin_complain as complains')
            ->where('complains.client_user_id', $user->id)
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'complains.client_id')
            ->leftjoin('client_support_admin_softwarelistall as software', 'complains.soft_id', DB::raw('software.id::text'))
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('complains.created_time', [$start_time, $end_time]);
            })
            ->select(
                'customers.cusname as client_name',
                'software.soft_name as software_name',
                'complains.complain_title as title',
                'complains.complain_body as description',
                'complains.is_forward as is_assigned',
                'complains.is_accepted as is_accepted',
                'complains.is_rejected',
                'complains.created_time as created_date')
            ->orderBy('complains.created_time', 'desc')
            // ->paginate(20);
            ->get();

        // $complains
        //     = DB::table('complains')->where('complains.user_id', $user->id)
        //     ->leftJoin('customers', 'customers.customer_id', 'complains.customer_id')
        //     ->leftjoin('software', 'complains.software_id', 'software.id')->select('customers.name as client_name', 'software.software_name', 'complains.title as title', 'complains.description as description', 'complains.created_at as created_date')
        //     ->orderBy('complains.created_at', 'desc')
        //     ->paginate(20);
        if (!$complains) {
            return JsonDataResponse($complains);
        } else {
            return JsonDataResponse($complains);
        }
    }

    /**
     * @OA\Get(
     *  path="/api/v2/complain/complainAlertbyAssignedPerson/{id}",
     *     tags={"Complain"},
     *     security={{"bearerAuth": {}}},
     *     summary="complainAlertbyAssignedPerson",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function complainAlertbyAssignedPerson($id)
    {
        $check_user = User::find($id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        } else {
            $complains = Complain::with('software')->where('assigned_to', $check_user->id)->get();
            if (!$complains) {
                return JsonDataResponse($complains);
            } else {
                return JsonDataResponse($complains);
            }
        }
    }



    /**
     * @OA\Post(
     *     path="/api/v2/complain/status/{id}",
     *     tags={"Complain"},
     *     summary="complain status",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function changeStatus($id)
    {
        $complain = Complain::findOrFail($id);
        $currentStatus = $complain->is_seen;
        $newStatus = $currentStatus == 0 ? 1 : 0;
        $complain->is_seen = $newStatus;
        $complain->save();
        return updateDataResponse($complain);
    }


    /**
     * @OA\Post(
     *     path="/api/v2/complain/assaign",
     *     tags={"Complain"},
     *     summary="complain assaign",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function complainAssaign(Request $request)
    {
        $complain_id = $request->id;
        $support_id = $request->user_id;
        $complain = Complain::where('id', $complain_id)->first();
        $support  = User::where('id', $support_id)->first();

        if ($complain && $support) {
            $complain->assign_to_id = $support->id;
            $complain->assign_to = $support->username;
            // $complain->is_assigned = 1;
            $complain->is_forward = 1;
            $complain->save();
        }
        return updateDataResponse($complain);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/complain/seen",
     *     tags={"Complain"},
     *     summary="complain assaign",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data found successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function complainSeen(Request $request)
    {
        $ids = $request->input('id', []);
        $complainSeen = Complain::whereIn('id', $ids)->update([
            'is_seen' => 1
        ]);
        return JsonDataResponse($complainSeen);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/complain/store",
     *     tags={"Complain"},
     *     summary="Store a complain",
     *     operationId="storeComplain",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="description", type="string", description="The description of the complain", example="Issue with the product"),
     *                 @OA\Property(property="title", type="string", description="The title of the complain", example="Product complaint")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data saved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Error message", example="An error occurred while saving the data.")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string',
            'title' => 'required|string',
            'software_id' => 'required|numeric',
        ]);

        $user = Auth::user();
        $check_user = ClientUser::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        // dd("1");
        $validatedData['client_user_id'] = $check_user->id;
        $validatedData['client_user_name'] = $check_user->username;
        $validatedData['shop_name'] = $check_user->shopname;

        $customer_id = $check_user->client_id;
        $client = Customer::find($check_user->client_id);
        if (!$client) {
            return JsonDataResponse($client);
        }
        $validatedData['client_id'] = $client->id;
        $validatedData['client_name'] = $client->cusname;
        $validatedData['client_phone'] = $client->accountant_phone_no;

        // dd("2");
        // $shop_id = Shop::where('user_id', $user->id)->where('customer_id', $customer_id)->first();
        // if (!$shop_id) {
        //     return JsonDataResponse($shop_id);
        // }
        // dd("3");
        // $validatedData['shop_id'] = $shop_id->id;
        // dd("4");
        $software = Software::where('id', $request->software_id)->first();
        if (!$software) {
            return JsonDataResponse($software);
        }
        $validatedData['soft_id'] = $software->id;
        $validatedData['soft_name'] = $software->soft_name;

        $support_request = Complain::create([
            'client_id' => $validatedData['client_id'],
            'client_name' => $validatedData['client_name'],
            'complain_title' => $request->title,
            'complain_body' => $request->description,
            'client_user_id' => $validatedData['client_user_id'],
            'client_user_name' => $validatedData['client_user_name'],
            'client_user_phone' => $validatedData['client_phone'],
            'created_time' => Carbon::now(),
            'shop_name' =>  $validatedData['shop_name'],
            'soft_id' =>  $validatedData['soft_id'] ,
            'soft_name' =>  $validatedData['soft_name'] ,

        ]);
        return saveDataResponse($support_request);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/complain/complainForSupportPerson",
     *     tags={"Complain"},
     *     summary="Retrieve complaints for support person",
     *     operationId="complainForSupportPerson",
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with complaints",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Support person not found or no complaints available",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function complainForSupportPerson(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $user = Auth::user();
        $check_user = User::where('id', $user->id)->first();
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }

        $complain = DB::table('clientlogin_complain as complains')
            ->where('complains.assign_to_id', $user->id)
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'complains.client_id')
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('complains.created_time', [$start_time, $end_time]);
            })
            ->select([
                'complains.id',
                'customers.id as client_id',
                'customers.cusname as client_name',
                'complains.client_user_name as complained_by',
                'complains.complain_title as title',
                'complains.complain_body as description',
                'complains.is_accepted',
                'complains.is_rejected',
                'complains.created_time as created_at'
            ])
            ->orderBy('complains.created_time', 'desc')
            ->get();


        // $complain = DB::table(function ($query) use ($user) {
        //     $query->selectRaw('DISTINCT customers.name as client_name,complains.id, complains.user_name as complained_by, complains.title, complains.description, complains.created_at,complains.is_seen,complains.is_assigned,complains.is_rejected,complains.is_accepted,complains.is_forward')
        //         ->from('software_support_people')
        //         ->where('software_support_people.user_id', $user->id)
        //         ->leftJoin('complains', 'complains.customer_id', 'software_support_people.customer_id')
        //         ->leftJoin('customers', 'customers.customer_id', 'complains.customer_id')
        //         ->leftJoin('shops', 'shops.id', 'complains.shop_id')
        //         ->whereNotNull('customers.name')
        //         ->whereNotNull('complains.user_name')
        //         ->whereNotNull('complains.title')
        //         ->whereNotNull('complains.description')
        //         ->whereNotNull('complains.created_at')
        //         ->orderBy('complains.created_at', 'desc');
        // }, 'sub')
        //     ->paginate(10);

        // Your code to handle the $complain data and pagination links goes here...


        if (!$complain) {
            return JsonDataResponse($complain);
        } else {
            return JsonDataResponse($complain);
        }
    }

    public function setCancelNote(Request $request){
        $id = $request->id;
        $cancel_note = $request->cancel_note;
        $complain = Complain::where('id', $id)->first();
        if (!$complain) {
            return JsonDataResponse($complain);
        }
        $complain->is_rejected = 1;
        $complain->cancel_note = $cancel_note;
        $data = $complain->save();
        return JsonDataResponse($data);
    }

    public function changeComplainAllertToAccepted(Request $request)
    {
        $user = Auth::user();
        $id = $request->id;
        $complain = Complain::where('id', $id)->first();
        if (!$complain) {
            return JsonDataResponse($complain);
        } else {
            $complain->is_accepted = 1;
            // $complain->is_seen = 1;
            $complain->is_rejected = 0;
            $data = $complain->save();
            return JsonDataResponse($data);
        }
    }

    public function changeComplainStatusToCancel(Request $request)
    {
        $id = $request->id;
        $cancel_note = $request->cancel_note;
        $complain = Complain::where('id', $id)->first();
        if (!$complain) {
            return JsonDataResponse($complain);
        }
        $complain->is_accepted = 0;
        $complain->is_rejected = 1;
        $complain->cancel_note = $cancel_note;
        $complain->is_seen = 1;
        $data = $complain->save();
        return JsonDataResponse($data);
    }
}
