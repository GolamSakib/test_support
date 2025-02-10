<?php
namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCustomerRequest;
use App\Jobs\SendPushMessage;
use App\Models\Area;
use App\Models\ClientUser;
use App\Models\Customer;
use App\Models\CustomerSoftware;
use App\Models\District;
use App\Models\shop;
use App\Models\Software;
use App\Models\SoftwareSupportPerson;
use App\Models\Support;
use App\Models\User;
use App\Utility\GetLocation;
use App\Utility\SendSMSUtility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/customer/all",
     *     tags={"Get Customer"},
     *     summary="Customer List",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="status",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="accountant_phone",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="agreement_date_range",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="operation_date_range",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="is_registered",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="boolean"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="sms_phone_no",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function index(Request $request)
    {
        $query = DB::table('addusers_customer')
            ->whereNotNull('addusers_customer.id')            // Specify the table name
            ->orderByDesc('addusers_customer.is_active')      // Be explicit here too
            ->orderByDesc('addusers_customer.is_registered'); // And here

        if ($request->customer_id) {
            $query->where('addusers_customer.id', $request->customer_id);
        }

        if ($request->status !== null) {
            $status = (int) $request->status;
            if ($status !== 2) {
                $query->where('addusers_customer.is_active', $status);
            } else {
                $query->select('addusers_customer.*')
                    ->where('addusers_customer.is_active', true)
                    ->leftJoin('addusers_supportperson as support_person', 'support_person.client_id', '=', DB::raw('addusers_customer.id::text'))
                    ->whereNull('support_person.client_id');
            }
        }

        if ($request->accountant_phone) {
            $query->where('addusers_customer.accountant_phone_no', $request->accountant_phone);
        }

        if ($request->sms_phone_no) {
            $query->where('addusers_customer.sms_phone_no', $request->sms_phone_no);
        }

        if ($request->name) {
            $query->where('addusers_customer.cusname', $request->name);
        }

        if ($request->id) {
            $query->where('addusers_customer.id', $request->id);
        }

        if ($request->agreement_date_range) {
            $dates = explode(" to ", $request->agreement_date_range);
            $query->whereBetween('addusers_customer.agreement_date', [
                date('Y-m-d 00:00:00', strtotime($dates[0])),
                date('Y-m-d 23:59:59', strtotime($dates[1])),
            ]);
        }

        if ($request->operation_date_range) {
            $dates = explode(" to ", $request->operation_date_range);
            $query->whereBetween('addusers_customer.operation_starting_date', [
                date('Y-m-d 00:00:00', strtotime($dates[0])),
                date('Y-m-d 23:59:59', strtotime($dates[1])),
            ]);
        }

        if ($request->is_registered !== null) {
            $query->where('addusers_customer.is_registered', $request->is_registered);
        }

        $total     = $query->count();
        $customers = $query->paginate($total);

        return response()->json([
            'success' => $customers->count() > 0,
            'message' => $customers->count() > 0 ? 'Customer Found' : 'Customer Not Found',
            'data'    => $customers->count() > 0 ? $customers : null,
        ], $customers->count() > 0 ? 200 : 404);
    }

    public function allWithoutPaginate(Request $request)
    {
        $customer_id          = null;
        $status               = null;
        $accountant_phone     = null;
        $sms_phone_no         = null;
        $id                   = null;
        $registered           = null;
        $agreement_date_range = null;
        $operation_date_range = null;
        $customer             = Customer::where('id', '!=', null)->where('is_active', 1);
        if ($request->customer_id != null) {
            $customer_id = $request->customer_id;
            $customer    = $customer->where('id', $customer_id);
        }
        if ($request->status != null) {
            $status   = $request->status;
            $customer = $customer->where('status', $status);
        }
        if ($request->accountant_phone != null) {
            $accountant_phone = $request->accountant_phone;
            $customer         = $customer->where('accountant_phone_no', $accountant_phone);
        }
        if ($request->sms_phone_no != null) {
            $sms_phone_no = $request->sms_phone_no;
            $customer     = $customer->where('sms_phone_no', $sms_phone_no);
        }
        if ($request->id != null) {
            $id       = $request->id;
            $customer = $customer->where('id', $id);
        }
        if ($request->agreement_date_range != null) {
            $agreement_date_range = $request->agreement_date_range;
            $customer             = $customer->where('agreement_date', '>=', date('Y-m-d', strtotime(explode(" to ", $agreement_date_range)[0])) . '  00:00:00')
                ->where('agreement_date', '<=', date('Y-m-d', strtotime(explode(" to ", $agreement_date_range)[1])) . '  23:59:59');
        }
        if ($request->operation_date_range != null) {
            $operation_date_range = $request->operation_date_range;
            $customer             = $customer->where('operation_starting_date', '>=', date('Y-m-d', strtotime(explode(" to ", $operation_date_range)[0])) . '  00:00:00')
                ->where('operation_starting_date', '<=', date('Y-m-d', strtotime(explode(" to ", $operation_date_range)[1])) . '  23:59:59');
        }
        if ($request->is_registered != null) {
            $registered = $request->is_registered;
            $customer   = $customer->where('is_registered', $registered);
        }
        $customer = $customer->orderBy('cusname', 'asc')->get();
        if ($customer->count() > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Customer Found',
                'data'    => $customer,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Customer Not Found',
                'data'    => null,
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/customer/storeOrUpdate",
     *     tags={"Get Customer"},
     *     summary="Customer Sync",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="status",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function storeOrUpdate(Request $request)
    {
        $this->validate($request, [
            'customer_id'          => 'required|unique:customers,',
            'name'                 => 'required',
            'sms_phone_no'         => 'required',
            'agreement_date'       => 'required',
            'operation_start_date' => 'required',
        ]);
        try {
            $customer = Customer::updateOrCreate([
                'customer_id' => $request->customer_id,
            ], [
                'name'                 => $request->name,
                'accountant_phone'     => $request->accountant_phone,
                'status'               => $request->status,
                'sms_phone_no'         => $request->sms_phone_no,
                'agreement_date'       => date('Y-m-d H:i:s', strtotime($request->agreementDate)),
                'operation_start_date' => date('Y-m-d H:i:s', strtotime($request->operationStartingDate)),
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Customer Created or Updated Successfully',
                'data'    => $customer,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/customer/register",
     *     tags={"Get Customer"},
     *     summary="Customer Register",
     *     security={ {"bearerAuth": {} }},
     *     @OA\RequestBody(
     *         @OA\JsonContent(),
     *         @OA\MediaType(
     *            mediaType="multipart/form-data",
     *            @OA\Schema(
     *               type="object",
     *               required={"support_person","software","user","customer_id"},
     *               @OA\Property(property="customer_id", type="string"),
     *               @OA\Property(
     *               property="user",
     *               type="array",
     *               @OA\Items(
     *                 type="object",
     *                 example={
    "name":"a",
    "area_id":"1",
    "designation":"s",
    "email":"s@gmail.com",
    "phone":"01*********",
    "shop_address":"sss",
    "district_id":"1",
    "division_id":"1"
    },
     *                 )
     *              ),
     *     @OA\Property(
     *               property="software",
     *               type="array",
     *               @OA\Items(
     *                 type="object",
     *                 example={
    "id":"1",
    "name":"Cloud_POS",
    "lead_by":"3",
    "sale_by":"3"
    },
     *                 )
     *              ),
     *     @OA\Property(
     *               property="support_person",
     *               type="array",
     *               @OA\Items(
     *                 type="string",
     *                 example={"support_user_id"},
     *                 )
     *              ),
     *            ),
     *        ),
     *    ),
     *     @OA\Response(response="200", description="Successful",
     *      @OA\MediaType(
     *           mediaType="application/json",
     *      )
     * ),
     *     @OA\Response(
     *      response=401,
     *       description="Unauthenticated"
     *   ),
     *     @OA\Response(
     *      response=404,
     *       description="Not Found"
     *   ),
     *     @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     * )
     */
    // public function register(Request $request)
    // {
    //     // dd($request->all());

    //     $user = $request->user;
    //     $software = $request->software;
    //     $support_person = $request->support_person;
    //     $customer_id = $request->customer_id;
    //     $sales = $request->sales;
    //     $leads = $request->leads;
    //     $count = count($user);
    //     // dd($request->all());
    //     // dd($support_person);

    //     if ($count < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "User Missing",
    //             'data' => null,
    //         ], 404);
    //     }
    //     if (count($software) < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Software Missing",
    //             'data' => null,
    //         ], 404);
    //     }
    //     if ( !$request->has('support_person') || !is_array($request->input('support_person')) || count($request->input('support_person')) < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Support Person Missing",
    //             'data' => null,
    //         ], 404);
    //     }
    //     if (count($sales) < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Sales Person Missing",
    //             'data' => null,
    //         ], 404);
    //     }
    //     if (count($leads) < 1) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => "Leads Person Missing",
    //             'data' => null,
    //         ], 404);
    //     }

    //     // dd('ok');
    //     try {
    //         DB::beginTransaction();
    //         $customer = Customer::find($customer_id);
    //         if ($customer == null) {
    //             DB::rollBack();
    //         }
    //         $phone = null;
    //         $salesByName=[];
    //         $LeadsByName=[];
    //         foreach ($sales as $sale) {
    //             $saleByuser=User::where('id',$sale)->first();
    //             $salesByName[]=$saleByuser->username;
    //         }
    //         foreach ($leads as $lead) {
    //             $LeadByuser=User::where('id',$lead)->first();
    //             $LeadsByName[]=$LeadByuser->username;
    //         }
    //         $salesById=json_encode($sales);
    //         $salesByName=json_encode($salesByName);
    //         $leadsById=json_encode($leads);
    //         $leadsByName=json_encode($LeadsByName);
    //         foreach ($user as $key => $value) {
    //             $phoneWithCountryCode = '88' . substr($value['phone'], -11);
    //             if (($count - 1) == $key) {
    //                 $phone = $phone . $phoneWithCountryCode;
    //             } else {
    //                 $phone = $phone . $phoneWithCountryCode . ',';
    //             }

    //             $imagePath = null;
    //             if (isset($value['image'])) {
    //                 $fileExtension = $value['image']->extension();
    //                 $filename = "Client" . uniqid() . '.' . $fileExtension;  // Generate a unique filename
    //                 $imagePath = $value['image']->storeAs('uploads/users/profile', $filename);
    //             }
    //             $district = District::find($value['district_id']);
    //             $location = GetLocation::locationInfo();

    //             $areaId = $value['area'] ?? null;
    //             $area = $areaId ? Area::find($areaId) : null;

    //             $created_user = ClientUser::updateOrCreate([
    //                 'email' => $value['email'],
    //                 'phoneno' => $value['phone'],
    //                 'client_id' => $customer_id
    //             ], [
    //                 'username' => $value['name'],
    //                 'designation' => $value['designation'],
    //                 'shopname' => $value['shop_address'],
    //                 'district' => $district->dist_name,
    //                 'dist_id' => $district->id,
    //                 'area' => $area ? $area->name : null,
    //                 'area_id' => $area ? $area->id : null,
    //                 'client_name' => $customer->cusname,
    //                 'lead_by' => $leadsByName,
    //                 'lead_by_id' => $leadsById,
    //                 'sell_by' => $salesByName,
    //                 'sell_by_id' => $salesById,
    //                 'is_active' => true,
    //                 'is_approved' => true,
    //                 'is_status_active' => true,
    //                 'pro_img_url' => $imagePath,
    //                 // 'password_is_set' => true,
    //                 'user_created_time' => Carbon::now(),
    //                 // 'password' => Hash::make('123456'),
    //                 "latitude" => $location->latitude,
    //                 "longitude" => $location->longitude,
    //             ]);

    //             $created_user->assignRole('Client');
    //             // $created_shop = shop::updateOrCreate([
    //             //     'phone' => $value['phone'],
    //             //     'customer_id' => $customer_id,
    //             //     'user_id' => $created_user->id,
    //             // ], [
    //             //     'name' => $value['name'],
    //             //     'address' => $value['shop_address'],
    //             //     'division_id' => $value['division_id'],
    //             //     'district_id' => $value['district_id'],
    //             //     'area_id' => $value['area_id']
    //             // ]);
    //         }
    //         // return $software;
    //         foreach ($software as $softwareId) {
    //             // return $value;
    //             // $saleByuser=User::where('id',$value['sale_by'])->first();
    //             // $LeadByuser=User::where('id',$value['lead_by'])->first();
    //             $soft = Software::where('id',$softwareId)->first();

    //             $created_software = CustomerSoftware::updateOrCreate([
    //                 'client_id' => $customer_id,
    //                 'software_id' => $softwareId,
    //                 'software_name' => $soft->soft_name,
    //             ], [
    //                 'lead_by' => $leadsByName,
    //                 'lead_by_id' => $leadsById,
    //                 'sell_by' => $salesByName,
    //                 'sell_by_id' => $salesById,
    //                 'client_name' => $customer->cusname
    //             ]);

    //             foreach ($support_person as $support) {
    //                 // return $support['supportPerson'];
    //                 $user = User::where('id', $support['supportPerson'])->first();
    //                 SoftwareSupportPerson::updateOrCreate([
    //                     'support_person_id' => $user->id,
    //                     'supportperson' => $user->username,
    //                     'client_id' => $customer_id
    //                 ], [
    //                     'is_support' => 1,
    //                     'is_billing_in_charge' => $support['billingIncharge'],
    //                     'is_supervisor' => $support['supervisor'],
    //                 ]);
    //             }
    //         }
    //         Customer::where('id', $customer_id)->update([
    //             'is_registered' => 1
    //         ]);
    //         $text = 'Congratulations!!!!!Dear Customer,Now you are a registered user of Mediasoft Digital Customer Service Unit.Please download the App from the following link: https://play.google.com/store/apps/details?id=com.mediasoft.cm_client Or access our webportal from this link: http://support.mediasoftbd.com/';
    //         // SendSMSUtility::sendSMSToMany($phone, 'MEDIASOFTBD', $text);
    //         DB::commit();
    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Customer Registered Successfully',
    //             'data' => $request->all(),
    //         ], 200);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => $e->getMessage(),
    //             'data' => null,
    //         ], 505);
    //     }
    // }

    public function register(RegisterCustomerRequest $request)
    {
        // Validate input data
        // $this->validateRegistrationData($request);

        try {
            DB::beginTransaction();
            // Fetch customer
            $customer = $this->getCustomer($request->customer_id);

            // Process and format sales and leads data
            $salesData = $this->processSalesData($request->sales);
            $leadsData = $this->processLeadsData($request->leads);

            // Create or update client users

            // Create or update customer software
            $this->createOrUpdateCustomerSoftware(
                $request->software,
                $customer,
                $leadsData,
                $salesData
            );

            // Create or update software support persons
            $supportPersons = $this->createOrUpdateSupportPersons(
                $request->support_person,
                $request->customer_id
            );

            $clientUsers = $this->createOrUpdateClientUsers(
                $request->user,
                $customer,
                $leadsData,
                $salesData,
                $supportPersons
            );

            // Mark customer as registered
            $this->markCustomerAsRegistered($request->customer_id);

            // Send registration SMS (commented out in original code)
            // $this->sendRegistrationSMS($phone);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Customer Registered Successfully',
                'data'    => $request->all(),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }

    public function details($id)
    {
        // $customer = Customer::where('customer_id', $id)->first();
        $customer = Customer::where('id', $id)->first();
        if ($customer != null) {
            // $softwares = CustomerSoftware::where('customer_id', $id)->get();
            $softwares = CustomerSoftware::where('client_id', $id)->where('software_id', '!=', null)->orderBy('software_name', 'ASC')->get();
            $ids       = $softwares->pluck('id');
            // $saleBy = DB::table('customer_software as cs')
            //     ->leftJoin('users', 'users.id', '=', 'cs.sale_by')
            //     ->where('cs.customer_id', $id)
            //     ->groupBy('cs.sale_by')
            //     ->select('users.name')
            //     ->pluck('users.name');

            // $saleByUser = ClientUser::select('id','sell_by_id')
            //     ->with('saleBy:id,username')
            //     ->where('client_id', $id)
            //     ->first();
            $saleByUser = CustomerSoftware::select('id', 'sell_by', 'sell_by_id')
            // ->with('saleBy:id,username')
                ->where('client_id', $id)
                ->first();
            // ->pluck('saleBy.username');

            // $leadBy = DB::table('customer_software as cs')
            //     ->leftJoin('users', 'users.id', '=', 'cs.lead_by')
            //     ->where('cs.client_id', $id)
            //     ->groupBy('cs.lead_by')
            //     ->select('users.name')
            //     ->pluck('users.name');
            $leadByUser = CustomerSoftware::where('client_id', $id)
                ->select('id', 'lead_by', 'lead_by_id')
                ->first();

            // ->pluck('leadBy.username');

            // $supports = DB::table('software_support_people as ssp')
            //     ->leftJoin('users', 'users.id', '=', 'ssp.user_id')
            //     ->whereIn('ssp.customer_software_id', $ids)
            //     ->groupBy('ssp.user_id')
            //     ->select('ssp.*', 'users.name')
            //     ->get();

            $supports = SoftwareSupportPerson::with('support_person')
                ->where('client_id', $id)->get();

            $billInfo = billingOfCustomer($id);
            // $users = User::with('shop')->where('customer_id', $id)->get();
            $clientUsers    = ClientUser::where('client_id', $id)->orderBy('username', 'ASC')->get();
            $monitoringInfo = monitoringOfCustomer($id);
            return response()->json([
                "success" => true,
                "status"  => 200,
                'message' => 'Customer Found',
                'data'    => [
                    'customer'       => $customer,
                    'software'       => $softwares,
                    'users'          => $clientUsers,
                    'sale_by'        => $saleByUser ? json_decode($saleByUser->sell_by) : null,
                    'lead_by'        => $leadByUser ? json_decode($leadByUser->lead_by) : null,
                    'support'        => $supports,
                    'billInfo'       => $billInfo,
                    'monitoringInfo' => $monitoringInfo,
                ],
            ]);
        } else {
            return response()->json([
                "success" => false,
                "status"  => 200,
                'message' => 'Customer Not Found',
                'data'    => null,
            ]);
        }
    }

    public function pushMessage(Request $request)
    {
        $customers    = $request->customer_ids;
        $message      = $request->message;
        $phone_number = $request->phone_number;
        try {
            if ($phone_number) {
                SendSMSUtility::sendSMS($phone_number, $message);
            } else {
                foreach ($request->customer_ids as $customerId) {
                    $users = ClientUser::where('client_id', $customerId)
                        ->where('is_active', 1)
                        ->where('phoneno', '!=', null)
                        ->get();
                    foreach ($users as $user) {
                        SendPushMessage::dispatch($user, $message)
                            ->onQueue('messages');
                    }
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Messages queued for sending',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to queue messages',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function assign_role($id, Request $request)
    {
        // dd($id, $request->all());

        // $software = CustomerSoftware::where('customer_id', $id)->pluck('id');
        // $software = CustomerSoftware::where('client_id', $id)->pluck('id');
        // SoftwareSupportPerson::whereIn('customer_software_id', $software)->update([
        //     "is_billing_in_charge" => $request->is_billing_in_charge,
        //     "is_supervisor" => $request->is_supervisor,
        //     "is_support" => 1
        // ]);

        SoftwareSupportPerson::where('client_id', $id)
            ->where('support_person_id', $request->support_id)
            ->update([
                "is_billing_in_charge" => $request->is_billing_in_charge,
                "is_supervisor"        => $request->is_supervisor,
                "is_support"           => 1,
            ]);

        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Updated Successfully',
            'data'    => null,
        ]);
    }

    public function supportAdd($id, Request $request)
    {
        $supportpersons = $request->support_id;

        if (empty($supportpersons)) {
            return response()->json([
                'status'  => 400,
                'message' => 'No support persons were provided.',
            ], 400);
        }

        foreach ($supportpersons as $supportperson) {
            // Check if the user exists and is active
            $person = User::where('id', $supportperson['supportPerson'])
                ->where('isactive', 1)
                ->select('id', 'username')
                ->first();

            if (! $person) {
                return response()->json([
                    'status'  => 404,
                    'message' => "Support person with ID {$supportperson['supportPersonName']} not found or inactive.",
                ], 404);
            }

            // Create the support person entry
            SoftwareSupportPerson::create([
                "supportperson"        => $supportperson['supportPersonName'],
                "is_support"           => 1,
                "client_id"            => $id,
                'support_person_id'    => $supportperson['supportPerson'],
                'is_billing_in_charge' => $supportperson['billingIncharge'],
                'is_supervisor'        => $supportperson['supervisor'],
            ]);
        }

        return response()->json([
            'status'  => 200,
            'message' => 'Support persons successfully added.',
        ], 200);
    }
    public function salepersonAdd($id, Request $request)
    {

        // $customer_software = CustomerSoftware::where('customer_id', $id)->get();
        $sales_person      = $request->sale_id;
        $sales_person_id   = [];
        $sales_person_name = [];
        foreach ($sales_person as $sale_person) {
            $sp = User::where('id', $sale_person)
                ->select('id', 'username')
                ->first();
            $sales_person_id[]   = $sp->id;
            $sales_person_name[] = $sp->username;
        }
        $customer_software = CustomerSoftware::where('client_id', $id)->get();
        $customer          = Customer::where('id', $id)->first();
        if ($customer_software->count() > 0) {
            foreach ($customer_software as $s) {
                $s->sell_by              = json_encode($sales_person_name);
                $s->sell_by_id           = json_encode($sales_person_id);
                $s->client_name          = $customer->cusname;
                $s->agreement_date       = $customer->agreement_date ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000') : null;
                $s->operation_start_date = $customer->operation_starting_date ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000') : null;
                $s->save();
            }
        } else {
            $s                       = new CustomerSoftware();
            $s->client_id            = $customer->id; // Add this line
            $s->sell_by              = json_encode($sales_person_name);
            $s->sell_by_id           = json_encode($sales_person_id);
            $s->client_name          = $customer->cusname;
            $s->agreement_date       = $customer->agreement_date ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000') : null;
            $s->operation_start_date = $customer->operation_starting_date ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000') : null;
            $s->save();

        }
        $clients = ClientUser::where('client_id', $id)->get();
        foreach ($clients as $c) {
            $c->sell_by    = json_encode($sales_person_name);
            $c->sell_by_id = json_encode($sales_person_id);
            $c->save();
        }
        return saveDataResponse($customer_software);
    }

    public function leadpersonAdd($id, Request $request)
    {

        $leads_person      = $request->lead_id;
        $leads_person_id   = [];
        $leads_person_name = [];
        foreach ($leads_person as $lead_person) {
            $ld = User::where('id', $lead_person)
                ->select('id', 'username')
                ->first();
            $leads_person_id[]   = $ld->id;
            $leads_person_name[] = $ld->username;
        }

        $customer_software = CustomerSoftware::where('client_id', $id)->get();
        $customer          = Customer::where('id', $id)->first();
        $user              = User::where('id', $request->lead_id)
            ->select('id', 'username')
            ->first();

        foreach ($customer_software as $s) {
            $s->lead_by              = json_encode($leads_person_name);
            $s->lead_by_id           = json_encode($leads_person_id);
            $s->client_name          = $customer->cusname;
            $s->agreement_date       = $customer->agreement_date ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000') : null;
            $s->operation_start_date = $customer->operation_starting_date ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000') : null;
            $s->save();
        }
        return saveDataResponse($customer_software);
    }

    public function supportDelete($id, Request $request)
    {
        // dd($id, $request->all());
        // $software = CustomerSoftware::where('customer_id', $id)->pluck('id');
        // return $software = CustomerSoftware::where('client_id', $id)->pluck('software_id');
        // SoftwareSupportPerson::whereIn('customer_software_id', $software)->where('user_id', $request->support_id)->delete();
        SoftwareSupportPerson::where('client_id', $id)
            ->where('support_person_id', $request->support_id)
            ->delete();

        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Deleted Successfully',
            'data'    => null,
        ]);
    }

    public function softwareAdd($id, Request $request)
    {
        try {
            $saleLead   = CustomerSoftware::where('client_id', $id)->first();
            $sell_by_id = null;
            $sell_by    = null;

            // Only try to decode if $saleLead exists and has sell_by_id
            if ($saleLead && $saleLead->sell_by_id) {
                $sell_by_id = json_decode($saleLead->sell_by_id);
                $sell_by    = json_decode($saleLead->sell_by);
            }

            $softwares        = $request->software_id;
            $createdSoftwares = [];

            foreach ($softwares as $software) {
                $name            = Software::where('id', $software)->first();
                $createdSoftware = CustomerSoftware::create([
                    "client_id"     => $id,
                    "software_name" => $name->soft_name,
                    "software_id"   => $software,
                    "lead_by"       => $saleLead ? $saleLead->lead_by : null,
                    "lead_by_id"    => $saleLead ? $saleLead->lead_by_id : null,
                    "sell_by"       => json_encode($sell_by),
                    "sell_by_id"    => json_encode($sell_by_id),
                ]);
                $createdSoftwares[] = $createdSoftware;
            }

            CustomerSoftware::where('client_id', $id)->where('software_id', null)->delete();
            return saveDataResponse($createdSoftwares);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "status"  => 500,
                'message' => 'Failed to add software',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    public function softwareDelete($id, Request $request)
    {

        CustomerSoftware::where('client_id', $id)->where('software_id', $request->software_id)->delete();
        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Deleted Successfully',
            'data'    => null,
        ]);
    }

    public function usersAdd($id, Request $request, $update = null)
    {
        if ($request->has('dist_id')) {
            $request->merge(['district_id' => $request->dist_id]);
        }
        $validator = Validator::make(
            $request->all(),
            [
                'email'   => 'nullable|unique:addusers_users,email',
                'phoneno' => 'required|unique:addusers_users,phoneno',
            ],
            [
                'email.unique'   => 'The email has already been taken.',
                'phoneno.unique' => 'The phone number has already been taken.',
            ]
        );
        if ($validator->fails()) {
            $errors        = $validator->errors();
            $errorMessages = [];

            if ($errors->has('email')) {
                $errorMessages = $errors->first('email');
            }

            if ($errors->has('phoneno')) {
                $errorMessages = $errors->first('phoneno');
            }

            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => $errorMessages,
            ], 422);
        }
        $client         = Customer::where('id', $id)->first();
        $client_name    = $client->cusname;
        $district       = District::where('id', $request->district_id)->first()->dist_name;
        $location       = GetLocation::locationInfo();
        $user           = ClientUser::where('client_id', $id)->first();
        $lead_by_id     = $user->lead_by_id ?? null;
        $lead_by        = $user->lead_by ?? null;
        $sell_by_id     = $user->sale_by_id ?? null;
        $sell_by        = $user->sale_by ?? null;
        $support_person = SoftwareSupportPerson::with('support_person')
            ->where('client_id', $id)
            ->get();
        $support_person_name  = $support_person->pluck('support_person.username')->toArray();
        $support_person_phone = $support_person->pluck('support_person.phone_no')->toArray();

        $imagePath = null;
        if ($request->file('pro_img_url')) {
            $fileExtension = $request->file('pro_img_url')->extension();
            $filename      = "Client" . uniqid() . '.' . $fileExtension;
            $imagePath     = $request->file('pro_img_url')->storeAs('uploads/users/profile', $filename);
        }
        $created_user = ClientUser::updateOrCreate([
            'email'     => $request->email,
            'phoneno'   => $request->phoneno,
            'client_id' => $id,
        ], [
            'username'          => $request->username,
            'designation'       => $request->designation,
            'shopname'          => $request->shop,
            'is_active'         => 1,
            'is_status_active'  => 1,
            'is_approved'       => 1,
            // 'password_is_set' => 1,
            // 'password' => Hash::make('123456'),
            'area'              => $request->area,
            'client_name'       => $client_name,
            'dist_id'           => $request->district_id,
            'district'          => $district,
            'user_created_time' => Carbon::now(),
            "latitude"          => $location->latitude,
            "longitude"         => $location->longitude,
            "lead_by"           => $lead_by,
            "lead_by_id"        => $lead_by_id,
            "sell_by"           => $sell_by,
            "sell_by_id"        => $sell_by_id,
            "pro_img_url"       => $imagePath,
        ]);

        $this->sendRegistrationSMS($created_user->phoneno, $support_person_name, $support_person_phone);
        $created_user->update(['is_notification_sent' => 1]);

        $created_user->assignRole('Client');

        // $created_shop = shop::updateOrCreate([
        //     'phone' => $request->phone_no,
        //     'customer_id' => $id,
        //     'user_id' => $created_user->id,
        // ], [
        //     'name' => $request->name,
        //     'address' => $request->shop,
        //     'division_id' => $request->division_id,
        //     'district_id' => $request->district_id,
        //     'area_id' => $request->area_id
        // ]);
        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Saved Successfully',
            'data'    => [
                "user" => $created_user,
            ],
        ]);
    }
    public function usersUpdate(Request $request)
    {
        $user = ClientUser::find($request->id);
        if ($user) {

            if ($request->phoneno !== null) {
                $existingUserWithPhone = ClientUser::where('phoneno', $request->phoneno)
                    ->where('id', '<>', $user->id)
                    ->first();

                if ($existingUserWithPhone) {
                    return response()->json([
                        "success" => false,
                        "status"  => 401,
                        'message' => 'Phone number already exists for another user',
                    ], 401);

                }
            }
            if ($request->email !== null) {
                $existingUserWithEmail = ClientUser::where('email', $request->email)
                    ->where('id', '<>', $user->id)
                    ->first();

                if ($existingUserWithEmail) {
                    return response()->json([
                        "success" => false,
                        "status"  => 401,
                        'message' => 'Email already exists for another user',
                    ], 401);

                }
            }
        }
        $imagePath = $user->pro_img_url; // Use existing path as default

        if ($request->file('pro_img_url')) {
            $fileExtension = $request->file('pro_img_url')->extension();
            $filename      = "Client" . uniqid() . '.' . $fileExtension;
            $imagePath     = $request->file('pro_img_url')->storeAs('uploads/users/profile', $filename);
        }

        $data = $user->update($request->all());
        if ($user->is_notification_sent == "false") {
            $this->sendRegistrationSMS($user->phoneno);
            $user->update(['is_notification_sent' => 1]);
        }

        $user->pro_img_url = $imagePath;
        $user->save();

        return saveDataResponse($data);
    }
    public function usersDelete($id, Request $request)
    {
        // dd($id,$request->all());
        // User::where('customer_id', $id)->where('id', $request->id)->delete();
        ClientUser::where('id', $request->id)
            ->where('client_id', $id)
            ->delete();
        // shop::where('customer_id', $id)->where('user_id', $request->id)->delete();
        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Deleted Successfully',
            'data'    => null,
        ]);
    }

    public function supportList($id, Request $request)
    {
        $supportList = Support::with(['software', 'refused', 'helped', 'accepted_support', 'shop', 'user'])->where('customer_id', $id)->orderBy('id', 'desc')->get();
        return response()->json([
            "success" => true,
            "status"  => 200,
            'data'    => $supportList,
        ]);
    }

    public function moneyReceiptList(Request $request)
    {
        try {
            $ch  = curl_init();
            $id  = $request->id;
            $url = 'http://software.mediasoftbd.com/msbill/api/BillCollection/GetOMRCusWise?CusID=' . $id;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'],
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response      = curl_exec($ch);
            $customerArray = array_reverse(json_decode($response)->data);

            $count = count($customerArray);
            if ($count > 0) {
                foreach ($customerArray as $key => $val) {
                    $val->collectionDt = humanReadableDate($val->collectionDt);
                }
                return response()->json([
                    'data'    => $customerArray,
                    'success' => true,
                    'message' => 'Customer Data Found Successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer Not Found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function moneyReceiptTempale(Request $request)
    {
        try {
            $ch   = curl_init();
            $mrno = $request->mrno;
            $url  = 'http://software.mediasoftbd.com/msbill/api/BillCollection/GetMoneyReceiveData?MRNo=' . $mrno;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: ' . $_SERVER['HTTP_USER_AGENT'],
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $response      = curl_exec($ch);
            $customerArray = json_decode($response)->data;
            $count         = count($customerArray);
            if ($count > 0) {
                // foreach($customerArray as $key=>$val){
                //     $val->paidAmtWords=amountinWords($val->paidAmt);
                //     $val->chequeDt=humanReadableDate($val->chequeDt);
                // }
                return response()->json([
                    'data'    => $customerArray,
                    'success' => true,
                    'message' => 'Customer Data Found Successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer Not Found',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function allUsers(Request $request)
    {
        // dd($request->all());
        $clientUsers = DB::table('addusers_users as client_users')
            ->join('addusers_customer as customers', 'client_users.client_id', '=', DB::raw('customers.id::text'))
            ->leftJoin('addusers_softwarelist as customer_software', DB::raw('customers.id::text'), '=', 'customer_software.client_id')
            ->leftJoin('addusers_district as district', 'district.id', '=', 'client_users.dist_id')
            ->leftJoin('areas', 'areas.id', '=', 'client_users.area_id')
            ->select(
                'client_users.client_id',
                'customers.cusname as client_name',
                'client_users.username  as client_user_name',
                'customers.accountant_phone_no',
                'customers.agreement_date',
                DB::raw('STRING_AGG(customer_software.software_name, \', \') as software_names'),
                DB::raw('STRING_AGG(customer_software.software_id::text, \', \') as software_ids'),
                'district.dist_name',
                'district.id as district_id',
                'areas.name as area_name',
                'areas.id as area_id'
            )
            ->where('customers.is_active', true)
            ->groupBy(
                'client_users.client_id',
                'customers.cusname',
                'client_users.username',
                'customers.accountant_phone_no',
                'customers.agreement_date',
                'district.dist_name',
                'district.id',
                'areas.name',
                'areas.id'
            )
            ->orderBy('customers.cusname', 'desc')
            ->get();

        if ($clientUsers->count() > 0) {
            return response()->json([
                'success' => true,
                'message' => 'Customer Users Found',
                'data'    => $clientUsers,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Customer Users Not Found',
                'data'    => null,
            ], 404);
        }
    }

    private function validateRegistrationData(Request $request)
    {
        $request->validate([
            'user'           => 'required|array|min:1',
            'software'       => 'required|array|min:1',
            'support_person' => 'required|array|min:1',
            'customer_id'    => 'required|exists:addusers_customer,id',
            'sales'          => 'required|array|min:1',
            'leads'          => 'required|array|min:1',
        ], [
            'user.required'           => 'User information is missing.',
            'software.required'       => 'Software information is missing.',
            'support_person.required' => 'Support person information is missing.',
            'sales.required'          => 'Sales person information is missing.',
            'leads.required'          => 'Leads information is missing.',
        ]);
    }

    /**
     * Fetch customer by ID
     *
     * @param int $customerId
     * @return Customer
     * @throws \Exception
     */
    private function getCustomer(int $customerId)
    {
        $customer = Customer::find($customerId);

        if (! $customer) {
            throw new \Exception('Customer not found');
        }

        return $customer;
    }

    /**
     * Process sales data
     *
     * @param array $salesIds
     * @return array
     */
    private function processSalesData(array $salesIds)
    {
        $salesByName = User::whereIn('id', $salesIds)
            ->pluck('username')
            ->toArray();

        return [
            'ids'   => json_encode($salesIds),
            'names' => json_encode($salesByName),
        ];
    }

    /**
     * Process leads data
     *
     * @param array $leadsIds
     * @return array
     */
    private function processLeadsData(array $leadsIds)
    {
        $leadsByName = User::whereIn('id', $leadsIds)
            ->pluck('username')
            ->toArray();

        return [
            'ids'   => json_encode($leadsIds),
            'names' => json_encode($leadsByName),
        ];
    }

    /**
     * Create or update client users
     *
     * @param array $users
     * @param Customer $customer
     * @param array $leadsData
     * @param array $salesData
     * @return \Illuminate\Support\Collection
     */
    private function createOrUpdateClientUsers(
        array $users,
        Customer $customer,
        array $leadsData,
        array $salesData,
        $supportPersons
    ) {
        $createdUsers         = collect();
        $phone                = $this->formatPhoneNumbers($users);
        $location             = GetLocation::locationInfo();
        $support_person_name  = $supportPersons->pluck('support_person.username')->toArray();
        $support_person_phone = $supportPersons->pluck('support_person.phone_no')->toArray();

        foreach ($users as $userData) {
            $district = District::find($userData['district_id']);
            $area     = $userData['area'] ? Area::find($userData['area']) : null;

            $clientUser = ClientUser::updateOrCreate(
                [
                    'email'     => $userData['email'],
                    'phoneno'   => $userData['phone'],
                    'client_id' => $customer->id,
                ],
                [
                    'username'          => $userData['name'],
                    'designation'       => $userData['designation'],
                    'shopname'          => $userData['shop_address'],
                    'district'          => $district->dist_name,
                    'dist_id'           => $district->id,
                    'area'              => $area ? $area->name : null,
                    'area_id'           => $area ? $area->id : null,
                    'client_name'       => $customer->cusname,
                    'lead_by'           => $leadsData['names'],
                    'lead_by_id'        => $leadsData['ids'],
                    'sell_by'           => $salesData['names'],
                    'sell_by_id'        => $salesData['ids'],
                    'is_active'         => true,
                    'is_approved'       => true,
                    'is_status_active'  => true,
                    'pro_img_url'       => $this->uploadProfileImage($userData),
                    'user_created_time' => now(),
                    'latitude'          => $location->latitude,
                    'longitude'         => $location->longitude,
                ]
            );

            $clientUser->assignRole('Client');
            $createdUsers->push($clientUser);
            if (! $clientUser->is_notification_sent) {
                $this->sendRegistrationSMS($userData['phone'], $support_person_name, $support_person_phone);
                $clientUser->update(['is_notification_sent' => 1]);
            }
        }

        return $createdUsers;
    }

    public function checkUserExistence(Request $request)
    {
        $validationRules = [
            'phone' => 'required|string',
            'email' => 'nullable|email',
        ];

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 400);
        }

        $phone = $request->input('phone');
        $email = $request->input('email');

        // Check user by phone or email
        $user = ClientUser::where('phoneno', $phone)
            ->when($email, function ($query) use ($email) {
                return $query->orWhere('email', $email);
            })
            ->first();

        // Return appropriate response
        return response()->json([
            'success' => $user !== null,
            'exists'  => $user !== null,
            'message' => $user ? 'User Already Exists' : 'User Not Found',
            'data'    => $user,
        ], $user ? 200 : 404);
    }

    /**
     * Format phone numbers with country code
     *
     * @param array $users
     * @return string
     */
    private function formatPhoneNumbers(array $users)
    {
        return collect($users)
            ->map(fn($user) => '88' . substr($user['phone'], -11))
            ->implode(',');
    }

    /**
     * Upload profile image
     *
     * @param array $userData
     * @return string|null
     */
    private function uploadProfileImage(array $userData)
    {
        if (! isset($userData['image'])) {
            return null;
        }

        $fileExtension = $userData['image']->extension();
        $filename      = "Client" . uniqid() . '.' . $fileExtension;
        return $userData['image']->storeAs('uploads/users/profile', $filename);
    }

    /**
     * Create or update customer software
     *
     * @param array $softwareIds
     * @param Customer $customer
     * @param array $leadsData
     * @param array $salesData
     */
    private function createOrUpdateCustomerSoftware(
        array $softwareIds,
        Customer $customer,
        array $leadsData,
        array $salesData
    ) {
        foreach ($softwareIds as $softwareId) {
            $software = Software::findOrFail($softwareId);
            CustomerSoftware::updateOrCreate(
                [
                    'client_id'     => $customer->id,
                    'software_id'   => $softwareId,
                    'software_name' => $software->soft_name,
                ],
                [
                    'lead_by'              => $leadsData['names'],
                    'lead_by_id'           => $leadsData['ids'],
                    'sell_by'              => $salesData['names'],
                    'sell_by_id'           => $salesData['ids'],
                    'client_name'          => $customer->cusname,
                    'agreement_date'       => $customer->agreement_date ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000') : null,
                    'operation_start_date' => $customer->operation_starting_date ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000') : null,

                ]
            );
        }
    }

    /**
     * Create or update software support persons
     *
     * @param array $supportPersons
     * @param int $customerId
     */
    private function createOrUpdateSupportPersons(array $supportPersons, int $customerId)
    {
        foreach ($supportPersons as $support) {
            $user = User::findOrFail($support['supportPerson']);

            SoftwareSupportPerson::updateOrCreate(
                [
                    'support_person_id' => $user->id,
                    'supportperson'     => $user->username,
                    'client_id'         => $customerId,
                ],
                [
                    'is_support'           => 1,
                    'is_billing_in_charge' => $support['billingIncharge'],
                    'is_supervisor'        => $support['supervisor'],
                ]
            );
        }

        // Return all support persons for this customer
        return SoftwareSupportPerson::with('support_person')
            ->where('client_id', $customerId)
            ->get();
    }

    /**
     * Mark customer as registered
     *
     * @param int $customerId
     */
    private function markCustomerAsRegistered(int $customerId)
    {
        Customer::where('id', $customerId)->update([
            'is_registered' => 1,
        ]);
    }

    /**
     * Send registration SMS (Optional - currently commented out)
     *
     * @param string $phone
     */
    private function sendRegistrationSMS(string $phone, array $supportPersonsName = null, array $supportPersonsPhoneNo = null)
    {
        $supportPersonsText = '';
        if ($supportPersonsName && $supportPersonsPhoneNo) {
            $combinedInfo = array_map(function ($name, $phone) {
                return $name . ' (' . $phone . ')';
            }, $supportPersonsName, $supportPersonsPhoneNo);

            $supportPersonsText = 'Your support persons are: ' . implode(', ', $combinedInfo) . '. ';
        }

        $text = 'Congratulations!!!!!Dear Customer,Now you are a registered user of Mediasoft Digital Customer Service Unit. ' .
            $supportPersonsText .
            'Please download the App from the following link: https://play.google.com/store/apps/details?id=com.mediasoftbd.msclient&hl=en. ' .
            'Use ' . $phone . ' this phone number for login.';

        SendSMSUtility::sendSMS($phone, $text);
    }

}
