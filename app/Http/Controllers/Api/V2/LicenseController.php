<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use Cache;
use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\License;
use App\Models\Support;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Software;
use App\Models\Training;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SoftwareSupportPerson;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\V2\Controller;
use App\Models\ClientUser;

class LicenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Post(
     *     path="/api/v2/license-request/store",
     *     tags={"License"},
     *     summary="Store a license request",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function licenseRequestStore(Request $request)
    {
        $validatedData = $request->validate([
            'phone_no' => 'required|numeric',
            'software_no' => 'required',
            // 'software_id' => 'required|numeric'
        ]);

        $user = Auth::user();
        $check_user = ClientUser::find($user->id);

        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        $validatedData['client_id'] = $check_user->client_id;

        // $customer_id = $check_user->customer_id;
        // if (!$customer_id) {
        //     return JsonDataResponse($customer_id);
        // }
        // $validatedData['customer_id'] = $customer_id;
        // $shop_id = Shop::where('user_id', $check_user->id)->select('id')->first();
        // if (!$shop_id) {
        //     return JsonDataResponse($shop_id);
        // }
        // $validatedData['shop_id'] = $shop_id->id;
        // $software_id = Software::where('id', $request->software_id)->first();
        // if (!$software_id) {
        //     return JsonDataResponse($software_id);
        // }
        // $validatedData['software_id'] = $software_id->id;

        $licenseRequest = License::create([
            'client_id' => $validatedData['client_id'],
            'client_number' => $request->phone_no,
            'software_number' => $request->software_no,
            // 'license_number' => ,
            // 'license_note' => ,
            'is_approved' => -1,
            'requested_time' => Carbon::now(),

        ]);

        if ($licenseRequest) {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return saveDataResponse($licenseRequest);
        } else {
            return JsonDataResponse($licenseRequest);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/license-request",
     *     tags={"License"},
     *     summary="Get all licenses",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function index(Request $request)
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
        }
        // else if (strtolower($check_user->role) == 'admin' || strtolower($check_user->role) == 'super admin' || strtolower($check_user->role) == 'super_admin') {
        else {
            // $licenses = DB::table('licenses')
            // ->leftJoin('customers', 'licenses.customer_id', 'customers.customer_id')
            // ->leftJoin('software', 'software.id', 'licenses.software_id')
            // ->select('licenses.*', 'customers.name as client_name', 'software.software_name as software_name')
            // ->orderBy('licenses.request_time', 'desc')
            // ->get();

            $licenses = DB::table('clientlogin_license as licenses')
                ->leftJoin('addusers_customer as customers', 'licenses.client_id', DB::raw('customers.id::text'))
                // ->leftJoin('software', 'software.id', 'licenses.software_id')
                ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('licenses.requested_time', [$start_time, $end_time]);
                })
                ->select(
                    'licenses.id',
                    'customers.id as client_id',
                    'customers.cusname as client_name',
                    'licenses.client_number as phone_no',
                    'licenses.software_number',
                    'licenses.requested_time as request_time',
                    'licenses.is_approved',
                    'licenses.is_done',
                    // 'software.software_name as software_name'
                )
                ->orderBy('licenses.id', 'desc')
                ->get();

            if (!$licenses) {
                return JsonDataResponse($licenses);
            } else {
                return JsonDataResponse($licenses);
            }
        }
    }
    /**
     * @OA\Get(
     *     path="/api/v2/license-request/LicenseRequestByClient/{id}",
     *     tags={"License"},
     *     summary="LicenseRequestByClient",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function LicenseRequestByClient(Request $request)
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
        $customer_id = $user->client_id;
        if (!$customer_id) {
            return JsonDataResponse($customer_id);
        } else {
            // $license = DB::table('licenses')->leftJoin('software', 'licenses.software_id', 'software.id')
            //     ->leftJoin('customers', 'customers.customer_id', 'licenses.customer_id')
            //     ->where('licenses.user_id', $user->id)->get();

            $license = DB::table('clientlogin_license as licenses')
                // ->leftJoin('software', 'licenses.software_id', 'software.id')
                ->where('licenses.client_id', $customer_id)
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'licenses.client_id')
                ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                    $query->whereBetween('licenses.requested_time', [$start_time, $end_time]);
                })
                ->select(
                    'licenses.id',
                    'customers.cusname as client_name',
                    'licenses.client_number',
                    'licenses.software_number as software_no',
                    'licenses.requested_time',
                    'licenses.license_number as license_no',
                    'licenses.is_approved',
                )
                // ->where('licenses.user_id', $user->id)
                ->orderBy('licenses.requested_time', 'desc')
                ->get();

            if (!$license) {
                return JsonDataResponse($license);
            } else {
                return JsonDataResponse($license);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/license-request/LicenseRequestByAssignedPerson/{id}",
     *     tags={"License"},
     *     summary="LicenseRequestByAssignedPerson",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the assigned person",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */


    public function LicenseRequestByAssignedPerson($id)
    {
        $check_user = User::find($id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        } else {
            $license = DB::table('licenses')->leftJoin('software', 'licenses.software_id', 'software.id')->where('licenses.assigned_to', $check_user->id)->get();
            if (!$license) {
                return JsonDataResponse($license);
            } else {
                return JsonDataResponse($license);
            }
        }
    }


    /**
     * @OA\Post(
     *     path="/api/v2/license-request/send",
     *     tags={"License"},
     *     summary="Update license request",
     *     operationId="updateLicenseRequest",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function licenseRequestUpdate(Request $request)
    {
        $validatedData = $request->validate(
            [
                'license_id' => 'numeric|required',
                // 'license_no' => 'required'
            ]
        );

        $license = License::find($request->license_id);
        if (!$license) {
            return JsonDataResponse($license);
        }

        // $license->license_number = $request->license_no;
        $license->is_seen = 1;
        $license->is_approved = 1;
        $license->is_accept = true;
        $license->save();

        if ($license) {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return saveDataResponse($license);
        } else {
            return JsonDataResponse($license);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/license-request/approve",
     *     tags={"License"},
     *     summary="Approve license request",
     *     operationId="approveLicenseRequest",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */


    public function licenseRequestApprove(Request $request)
    {
        $validatedData = $request->validate(
            [
                'id' => 'numeric|required'
            ]
        );
        $license = License::find($request->id);
        if (!$license) {
            return JsonDataResponse($license);
        }
        $license->is_approved = 1;
        $license->save();
        if ($license) {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return saveDataResponse($license);
        } else {
            return JsonDataResponse($license);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/license-request/disapprove",
     *     tags={"License"},
     *     summary="Disapprove license request",
     *     operationId="disapproveLicenseRequest",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent()
     *     ),
     *      @OA\Response(
     *         response=404,
     *         description="Failed response",
     *         @OA\JsonContent()
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */


    public function licenseRequestDisapprove(Request $request)
    {
        $validatedData = $request->validate(
            [
                'license_id' => 'numeric|required'
            ]
        );
        $license = License::find($request->license_id);
        if (!$license) {
            return JsonDataResponse($license);
        }
        $license->is_approved = 0;
        // $license->is_rejected = 1;
        // $license->rejected_note = $request->note;
        $license->save();

        if ($license) {
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return saveDataResponse($license);
        } else {
            return JsonDataResponse($license);
        }
    }
}
