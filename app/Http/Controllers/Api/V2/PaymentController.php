<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\Payment;
use App\Models\Support;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Software;
use App\Models\ProblemType;
use Illuminate\Http\Request;
use App\Models\CustomerSoftware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V2\Controller;
use App\Models\ClientUser;
use App\Models\SoftwareSupportPerson;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    /**
     * @OA\Get(
     *     path="/api/v2/payment-alert/all",
     *     tags={"Payment Alert"},
     *     summary="Get all payment alert",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    //test here again
    public function paymentAlertAll(Request $request)
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
        } else if (strtolower($check_user->role) == 'admin' || strtolower($check_user->role) == 'super admin'  || strtolower($check_user->role) == 'super_admin') {
            $payment = DB::table('clientlogin_payment as payments')
                // ->leftJoin('clientlogin_inventory as inventories', 'payments.client_id', 'inventories.client_id')
                // // ->leftJoin('shops', 'payments.shop_id', 'shops.id')
                // ->leftJoin('supportadmin_support_user as user', 'user.username', '=', 'payments.assigned_person')
                // ->whereExists(function ($query) {
                //     $query->select(DB::raw(1))
                //           ->from('addusers_customer')
                //           ->whereColumn(DB::raw('addusers_customer.id::text'), 'payments.client_id')
                //           ->where('addusers_customer.is_active', true);
                // })
                ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('payments.payment_date', [$start_time, $end_time]);
                })
                ->select(
                    'payments.id',
                    'payments.client_name',
                    'payments.client_id',
                    'payments.shop_name as shop_name',
                    'payments.client_user_phone',
                    'payments.payment_type',
                    'payments.note',
                    'payments.assigned_person',
                    'payments.is_seen',
                    'payments.payment_date',
                    'payments.is_assigned',
                    'payments.is_collected',
                    'payments.is_accept',
                    'payments.collection_note')
                ->orderBy('payments.created_time', 'DESC')
                ->groupBy('payments.id')
                ->get();

            // $payment = DB::table('payments')
            //     ->leftJoin('inventories', 'payments.customer_id', 'inventories.customer_id')
            //     ->leftJoin('shops', 'payments.shop_id', 'shops.id')
            //     ->leftJoin('users as u', 'u.id', '=', 'payments.assigned_to')
            //     ->select('payments.id', 'inventories.client_name', 'payments.customer_id','u.name', 'shops.name as shop_name', 'payments.client_phone', 'payments.payment_type', 'payments.note', 'payments.assigned_to', 'payments.is_seen', 'payments.payment_date', 'payments.is_assigned', 'payments.is_collected', 'payments.is_accept', 'payments.collection_note')
            //     ->orderBy('payments.created_at','DESC')
            //     ->groupBy('payments.id')
            //     ->get();
            if (!$payment) {
                return JsonDataResponse($payment);
            } else {
                return JsonDataResponse($payment);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/paymentAlertByClient",
     *     tags={"Payment Alert By Client"},
     *     summary="Get all payment alert",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function paymentAlertByClient(Request $request)
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
        $payments
            = DB::table('clientlogin_payment as payments')
            ->where('payments.client_user_name', $user->username)
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'payments.client_id')
            // ->leftJoin('shops', 'shops.id', 'payments.shop_id')
            ->leftjoin('client_support_admin_softwarelistall as software', 'payments.soft_id', DB::raw('software.id::text'))
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('payments.payment_date', [$start_time, $end_time]);
            })
            ->select(
                'customers.cusname as client_name',
                'payments.shop_name as shop',
                'software.soft_name',
                'payments.note as note',
                'payments.created_time as created_date',
                'payments.payment_date as payment_date',
                'payments.is_assigned',
                'payments.is_collected'
            )
            ->orderBy('payments.id', 'desc')
            ->get();

        // $payments
        //     = DB::table('payments')->where('payments.user_id', $user->id)
        //     ->leftJoin('customers', 'customers.customer_id', 'payments.customer_id')
        //     ->leftJoin('shops', 'shops.id', 'payments.shop_id')
        //     ->leftjoin('software', 'payments.software_id', 'software.id')->select('customers.name as client_name', 'shops.name as shop', 'software.software_name', 'payments.note as note', 'payments.created_at as created_date')
        //     ->paginate(20);
        if (!$payments) {
            return JsonDataResponse($payments);
        } else {
            return JsonDataResponse($payments);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/paymentAlertbyAssignedPerson/{id}",
     *     tags={"Payment Alert By Assigned Person"},
     *     summary="Get all payment alert",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function paymentAlertbyAssignedPerson($id)
    {
        $check_user = User::find($id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        } else {
            $payment = Payment::with('software')->where('assigned_to', $check_user->id)->get();
            if (!$payment) {
                return JsonDataResponse($payment);
            } else {
                return JsonDataResponse($payment);
            }
        }
    }


    /**
     * @OA\Get(
     *     path="/api/v2/payment-alert/payment-for-support-person",
     *     tags={"Payment Alert"},
     *     summary="Get payment alert for support person",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function paymentAlertForSupport(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $user = Auth::user();
        // $user_id = $user->id;
        if (!$user) {
            return JsonDataResponse($user);
        }
        $payment_alert = DB::table("clientlogin_payment as payments")
            // ->where('payments.assigned_person', $user->username)
            ->where('payments.assigned_person_id', $user->id)
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'payments.client_id')
            // ->leftJoin('shops', 'shops.id', '=', 'payments.shop_id')
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('payments.payment_date', [$start_time, $end_time]);
            })
            ->select(
                'payments.id',
                'payments.is_collected',
                'customers.id as client_id',
                'customers.cusname as client_name',
                'payments.shop_name as shop_name',
                'customers.accountant_phone_no as client_phone',
                'payments.payment_type as payment_type',
                'payments.assigned_person_id',
                'payments.note as note',
                'payments.created_time',
                'payments.payment_date'
            )
            ->orderBy('payments.created_time', 'desc')
            ->get();

        // foreach($payment_alert as $key => $value){
        //     $value->payment_date = formatDate($value->payment_date);
        // }

        if (!$payment_alert) {
            return JsonDataResponse($payment_alert);
        } else {
            return JsonDataResponse($payment_alert);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v2/payment-alert/store",
     *     tags={"Payment Alert"},
     *     summary="Store a payment alert",
     *     operationId="storePaymentAlert",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="payment_type", type="string", description="The type of payment", example="Credit Card"),
     *                 @OA\Property(property="payment_amount", type="number", format="float", description="The payment amount", example=100.50),
     *                 @OA\Property(property="payment_date", type="string", format="date", description="The payment date", example="2023-05-31"),
     *                 @OA\Property(property="note", type="string", description="Optional note for the payment", example="Additional information")
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
        // dd($request->all());
        $validatedData = $request->validate([
            'payment_type' => 'required|string',
            'payment_amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'software_id' => 'required|numeric',
            'note' => 'sometimes|string',
        ]);
        $user = Auth::user();
        // $check_user = User::find($user->id);
        $check_user = ClientUser::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }

        $validatedData['client_user_name'] = $check_user->username;
        $validatedData['shop_name'] = $check_user->shopname;
        $validatedData['client_user_phone'] = $check_user->phoneno;

        $customer_id = $check_user->client_id;
        if (!$customer_id) {
            return JsonDataResponse($customer_id);
        }
        // $validatedData['customer_id'] = $customer_id;
        $client = Customer::find($customer_id);
        if (!$client) {
            return JsonDataResponse($client);
        }
        $validatedData['client_id'] = $client->id;
        $validatedData['client_name'] = $client->cusname;

        // $shop_id = Shop::where('user_id', $user->id)->where('customer_id', $customer_id)->first();
        // if (!$shop_id) {
        //     return JsonDataResponse($shop_id);
        // }
        // $validatedData['shop_id'] = $shop_id->id;
        $software = Software::where('id', $request->software_id)->first();
        if (!$software) {
            return JsonDataResponse($software);
        }
        $validatedData['soft_id'] = $software->id;
        $validatedData['soft_name'] = $software->soft_name;

        // if ($request->has('image') && $request->image !== null){
        //     $validatedData['image'] = $request['image']->store('uploads/payment');
        // }

        $payment = Payment::create([
            'client_id' => $validatedData['client_id'],
            'client_name' => $validatedData['client_name'],
            'client_user_name' => $validatedData['client_user_name'],
            'client_user_phone' => $validatedData['client_user_phone'],
            'shop_name' => $validatedData['shop_name'],
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'created_time' => now(),
            'payment_date' => $request->payment_date,
            // 'is_accept' => ,
            'soft_id' =>  $validatedData['soft_id'],
            'soft_name' => $validatedData['soft_name'],
            'payment_amount' => $request->payment_amount,
            // 'assigned_person' => ,
            // 'is_assigned' => ,
            // 'is_collected' => ,
            // 'collection_note' => ,
        ]);

        return saveDataResponse($payment);
    }

    public function paymentGeneratedBySupportPerson(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'required|numeric',
            'software_id' => 'required|numeric',
            'payment_type' => 'required|string',
            'payment_amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'note' => 'sometimes|string'
        ]);
        $user = Auth::user();
        $check_user = User::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        $validatedData['user_id'] = $check_user->id;
        $validatedData['user_name'] = $check_user->username;

        $softwareSupportCustomer = SoftwareSupportPerson::where('support_person_id', $check_user->id)
            ->where('client_id', $request->customer_id)
            ->first();
        if (!$softwareSupportCustomer) {
            return JsonDataResponse($softwareSupportCustomer);
        }

        $validatedData['client_id'] = $softwareSupportCustomer->client_id;
        $customer_info = Customer::where('id',  $validatedData['client_id'])->first();
        if (!$customer_info) {
            return JsonDataResponse($customer_info);
        }
        $validatedData['client_name'] = $customer_info->cusname;

        $validatedData['soft_id'] = $request->software_id;
        $software = Software::where('id', $validatedData['soft_id'])->first();
        if (!$software) {
            return JsonDataResponse($software);
        }
        $validatedData['soft_name'] = $software->soft_name;
        // $payment = Payment::create($validatedData);


        $payment = Payment::create([
            'client_id' => $validatedData['client_id'],
            'client_name' => $validatedData['client_name'],
            // 'client_user_name' => $validatedData['client_user_name'],
            // 'client_user_phone' => $validatedData['client_user_phone'],
            // 'shop_name' => $validatedData['shop_name'],
            'payment_type' => $request->payment_type,
            'note' => $request->note,
            'created_time' => now(),
            'payment_date' => $request->payment_date,
            // 'is_accept' => ,
            'soft_id' =>  $validatedData['soft_id'],
            'soft_name' => $validatedData['soft_name'],
            'payment_amount' => $request->payment_amount,
            'is_accept' => true,
            'assigned_person_id' => $validatedData['user_id'],
            'assigned_person' => $validatedData['user_name'],
            'is_assigned' => true,
            'is_collected' => true,
            'collection_note' => $request->note,
        ]);
        return saveDataResponse($payment);
    }


    public function paymentAlertAssign(Request $request)
    {
        $payment_id = $request->payment_id;
        $support_id = $request->support_id;

        $payment = Payment::where('id', $payment_id)->first();
        $supportPerson = User::find($support_id);

        if ($payment) {
            $payment->assigned_person = $supportPerson->username;
            $payment->assigned_person_id = $supportPerson->id;
            $payment->is_assigned = 1;
            $payment->is_accept = 1;
            // $payment->assigned_time = Carbon::now();
            $payment->save();
        }
        return updateDataResponse($payment);
    }


    /**
     * @OA\Post(
     *     path="/api/v2/payment-alert/seen",
     *     tags={"Payment Alert"},
     *     summary="Payment Alert seen",
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

    public function paymentAlertSeen(Request $request)
    {
        $ids = $request->input('id', []);
        $paymentSeen = Payment::whereIn('id', $ids)->update([
            'is_seen' => 1
        ]);
        return JsonDataResponse($paymentSeen);
    }

    public function updatePaymentAlertForSupportPerson(Request $request)
    {
        if ($request->has('id') && $request->has('collection_note')) {
            $payment = Payment::where('id', $request->id)->first();
            $payment->collection_note = $request->collection_note;
            $payment->is_collected = 1;
            $payment->save();
            if ($payment) {
                return updateDataResponse($payment);
            } else {
                return updateDataResponse($payment);
            }
        } else {
            return updateDataResponse($payment = null);
        }
    }
}
