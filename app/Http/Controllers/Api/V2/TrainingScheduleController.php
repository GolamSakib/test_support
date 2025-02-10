<?php

namespace App\Http\Controllers\Api\V2;

use Hash;
use DateTime;
use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\Support;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Software;
use App\Models\Training;
use App\Models\Inventory;
use App\Models\ClientUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\SoftwareSupportPerson;
use App\Http\Controllers\Api\V2\Controller;

class TrainingScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/training-scheduling/training_requests_for_client",
     *     tags={"Training Scheduling for client person"},
     *     summary="Get training requests",
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
    public function training_request_for_client(Request $request)
    {
        // dd($request->all());
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay()->format('Y-d-m H:i:s');
            $end_time = Carbon::parse($request->end_time)->endOfDay()->format('Y-d-m H:i:s');
        }

        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        } else {
            $training_request = DB::table('clientlogin_training as trainings')
                ->where(function ($query) use ($user) {
                    $query->whereNotNull('trainings.client_user_id')
                        ->where('trainings.client_user_id', $user->id);
                })
                ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), '=', 'trainings.client_id')
                ->leftJoin('client_support_admin_softwarelistall as software', DB::raw('software.id::text'), '=', 'trainings.soft_id');

            if ($start_time && $end_time) {
                $training_request = $training_request
                ->whereBetween('trainings.training_start_date', [$start_time, $end_time]);
            }

            $training_request = $training_request
                ->select('customers.cusname as client_name', 'software.soft_name', 'trainings.*')
                ->orderBy('trainings.id', 'desc')
                ->get();

            return JsonDataResponse($training_request);
        }

    }

    /**
     * @OA\Get(
     *     path="/api/v2/training-scheduling/training_requests_for_support",
     *     tags={"Training Scheduling for support person"},
     *     summary="Get training requests",
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
    public function training_requests_for_support(Request $request)
    {
        $user = Auth::user();
        $check_user = User::where('user_type', 'Support')->first();
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        $training_request = DB::table('software_support_people')->where('software_support_people.user_id', $user->id)
            ->leftJoin('trainings', 'trainings.customer_id', 'software_support_people.customer_software_id')
            ->leftJoin('customers', 'customers.customer_id', 'trainings.customer_id')
            ->leftjoin('shops', 'shops.id', 'trainings.shop_id')->select('customers.name as client name', 'shops.name as shop name', 'shops.address', 'customers.accountant_phone')
            ->distinct()
            ->get();
        if (!$training_request) {
            return JsonDataResponse($training_request);
        } else {
            return JsonDataResponse($training_request);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/training-scheduling/store",
     *     tags={"Training Scheduling"},
     *     summary="Store a training schedule",
     *     operationId="storeTrainingSchedule",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="training_type", type="string", description="The type of training", example="Workshop"),
     *                 @OA\Property(property="training_start_time", type="string", format="date-time", description="The start time of the training", example="2023-05-31T09:00:00"),
     *                 @OA\Property(property="training_end_time", type="string", format="date-time", description="The end time of the training", example="2023-05-31T17:00:00"),
     *                 @OA\Property(property="no_of_person", type="integer", description="The number of persons attending the training", example=10),
     *                 @OA\Property(property="note", type="string", description="Optional note for the training", example="Additional information")
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
            'training_type' => 'sometimes|string',
            'training_start_time' => 'required|date',
            'training_end_time' => 'required|date',
            'no_of_person' => 'required|numeric',
            'note' => 'sometimes|string',
            'software_id' => 'required|numeric'
        ]);
        $user = Auth::user();

        // dd(Carbon::parse($request->training_start_time)->format('Y-d-m H:i:s'), Carbon::parse($request->training_end_time)->format('Y-d-m H:i:s'));
        // $check_user = User::find($user->id);

        $check_user = ClientUser::find($user->id);
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        $validatedData['client_user_id'] = $check_user->id;
        $validatedData['client_user_name'] = $check_user->username;
        $validatedData['shop_name'] = $check_user->shopname;
        $validatedData['client_user_phone'] = $check_user->phone_no;

        // $customer_id = $check_user->client_id;
        // if (!$customer_id) {
        //     return JsonDataResponse($customer_id);
        // }
        // $validatedData['customer_id'] = $customer_id;

        $client = Customer::find($check_user->client_id);
        if (!$client) {
            return JsonDataResponse($client);
        }

        $validatedData['client_id'] = $client->id;
        $validatedData['client_name'] = $client->cusname;

        $software = Software::find($request->software_id);
        if(!$software){
            return JsonDataResponse($software);
        }

        $validatedData['soft_id'] = $software->id;
        $validatedData['soft_name'] = $software->soft_name;


        // $shop_id = Shop::where('user_id', $user->id)->where('customer_id', $customer_id)->first();
        // if (!$shop_id) {
        //     return JsonDataResponse($shop_id);
        // }
        // $validatedData['shop_id'] = $shop_id->id;
        $training = Training::create([
            'client_id' => $validatedData['client_id'],
            'client_name' =>  $validatedData['client_name'],
            'client_user_id' => $validatedData['client_user_id'],
            'client_user_name' => $validatedData['client_user_name'],
            'shop_name' => $validatedData['shop_name'],
            'client_user_phone' =>  $validatedData['client_user_phone'],
            'note' => $request->note,
            'training_start_date' => Carbon::parse($request->training_start_time)->format('Y-d-m H:i:s'),
            'training_end_date' =>  Carbon::parse($request->training_end_date)->format('Y-d-m H:i:s'),
            'created_time' => now(),
            'soft_id' => $validatedData['soft_id'],
            'soft_name' => $validatedData['soft_name'],
            'person_number' => $request->no_of_person,
            'training_type' => $request->training_type,

        ]);
        return saveDataResponse($training);
    }


    /**
     * @OA\Get(
     *     path="/api/v2/training-scheduling/index",
     *     tags={"Training Scheduling"},
     *     summary="Training Scheduling List",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="See All Types.")
     *         )
     *     ),
     * )
     * )
     */
    public function index(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay()->format('Y-d-m H:i:s');
            $end_time = Carbon::parse($request->end_time)->endOfDay()->format('Y-d-m H:i:s');
        }

        // $training = Training::with(['customer', 'shop', 'assigned', 'software'])->orderBy('created_at','desc')->get();
        $training = Training::with(['customer','assigned','software']);

        if ($start_time && $end_time) {
            $training = $training
            ->whereBetween('training_start_date', [$start_time, $end_time]);
        }

        $training= $training->orderBy('created_time','desc')
            ->get();

            foreach ($training as $key => $value) {
                try {
                    // Try parsing the training_start_date and training_end_date
                    $value->training_start_date = $value->training_start_date
                        ? Carbon::parse($value->training_start_date)->format('Y-m-d')
                        : "";
                } catch (\Exception $e) {
                    // If parsing fails, set it to an empty string or a default value
                    $value->training_start_date = "";
                }

                try {
                    $value->training_end_date = $value->training_end_date
                        ? Carbon::parse($value->training_end_date)->format('Y-m-d')
                        : "";
                } catch (\Exception $e) {
                    $value->training_end_date = "";
                }
            }

        return JsonDataResponse($training);
    }

    public function alertForSupportPerson(Request $request)
    {

        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay()->format('Y-d-m H:i:s');
            $end_time = Carbon::parse($request->end_time)->endOfDay()->format('Y-d-m H:i:s');
        }

        // dd($start_time, $end_time);

        $user = Auth::user();

        $trainings = DB::table('clientlogin_training as trainings')
            ->where('trainings.assigned_person_id', $user->id)
            ->leftJoin('addusers_customer as customers', DB::raw('customers.id::text'), 'trainings.client_id')
            // ->leftJoin('shops', 'shops.id', 'trainings.shop_id')
            ->leftJoin('client_support_admin_softwarelistall as software', DB::raw('software.id::text'), 'trainings.soft_id');


        if ($start_time && $end_time) {
            $trainings = $trainings
            ->whereBetween('trainings.training_start_date', [$start_time, $end_time]);
        }

        $trainings = $trainings
            ->select('trainings.*', 'customers.cusname as customer_name', 'trainings.shop_name as shop_address', 'customers.accountant_phone_no as client_phone')
            ->orderBy('trainings.created_time', 'desc')
            // ->paginate(20);
            ->get();

        foreach ($trainings as $key => $value) {
             $value->training_start_date = formatDate($value->training_start_date);
             $value->training_end_date = formatDate($value->training_end_date);
        }


        return JsonDataResponse($trainings);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/training-status",
     *     tags={"Training Scheduling"},
     *     summary="Training Status Change",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Training Status Changed.")
     *         )
     *     ),
     * )
     * )
     */
    public function trainingstatus(Request $request)
    {
        $training_id = $request->training_id;
        $training = Training::find($training_id);
        $training->is_seen = $training->is_seen == 1 ? 0 : 1;
        $training->save();
    }

    /**
     * @OA\Get(
     *     path="/api/v2/training-assign",
     *     tags={"Training Scheduling"},
     *     summary="Training Assign",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Training Assign.")
     *         )
     *     ),
     * )
     * )
     */
    public function trainingassign(Request $request)
    {
        $training_id = $request->training_id;
        if ($request->is_approved == 1) {
            $support_id = $request->support_id;
            $training = Training::find($training_id);
            $support = User::find($support_id);

            if ($training_id = $training->id) {
                $training->assigned_person_id = $support->id;
                $training->assigned_person = $support->username;
                $training->is_approved = 1;
                $training->is_assigned = 1;
            }
            $training->save();
            return JsonDataResponse($training);
        } else {
            $training = Training::find($training_id);
            if ($training_id = $training->id) {
                $training->is_approved = 0;
                $training->rejected_note = $request->note;
            }
            $training->save();
            return JsonDataResponse($training);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/training-seen",
     *     tags={"Training Scheduling"},
     *     summary="Training Assign",
     *     security={{"bearerAuth": {} }},
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Training Seen.")
     *         )
     *     ),
     * )
     * )
     */
    public function trainingSeen(Request $request)
    {
        $ids = $request->input('id', []);
        $trainingSeen = Training::whereIn('id', $ids)->update([
            'is_seen' => 1
        ]);
        return JsonDataResponse($trainingSeen);
    }

    public function updateTrainingAlertForSupportPerson(Request $request)
    {
        if ($request->has('id') && $request->has('note_by_support_person')) {
            $training = Training::where('id', $request->id)->first();
            // $training->note_by_support_person = $request->note_by_support_person;
            $training->completed_note = $request->note_by_support_person;
            $training->is_done = 1;
            $training->save();
            if ($training) {
                return updateDataResponse($training);
            } else {
                return updateDataResponse($training);
            }
        } else {
            return updateDataResponse($training = null);
        }
    }
}
