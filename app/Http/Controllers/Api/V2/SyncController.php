<?php
namespace App\Http\Controllers\Api\V2;

use App\Console\Commands\SupportAdminUserSyncWithPayroll;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerSoftware;
use App\Models\SoftwareSupportPerson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/syncCustomer",
     *     tags={"Sync Customer"},
     *     summary="Customer Sync",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(response="200", description="Customer Sync Successful",
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
     */

    public function syncCustomer(Request $request)
    {
        try {
            $count = $this->syncSoftware();
            if ($count > 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer Sync Successfully',
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'No new customer found',
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function syncSoftware()
    {
        $ch = curl_init();

        $url = 'http://software.mediasoftbd.com/msbill/api/BillCollection/GetCustomerList';

        // Corrected line for User-Agent
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Laravel Command';

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: ' . $userAgent, // Use variable here
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch), curl_getinfo($ch, CURLINFO_HTTP_CODE));
        }

        $customerArray = json_decode($response)->data ?? [];
        $count         = count($customerArray);

        if ($count > 0) {
            foreach ($customerArray as $customer) {
                $updatedCustomer = Customer::updateOrCreate([
                    'id' => $customer->cusID,
                ], [
                    'cusname'                      => $customer->cusName,
                    'accountant_phone_no'          => $customer->acctCPCellNo,
                    'is_active'                    => ($customer->discontinue == 'N') ? '1' : '0',
                    'is_registered'                => '1',
                    'sms_phone_no'                 => $customer->cusSMS,
                    'agreement_date'               => date('Y-m-d H:i:s', strtotime($customer->agreementDate)),
                    'operation_starting_date'      => date('Y-m-d H:i:s', strtotime($customer->operationStartingDate)),
                    'maxlicenseissueduration'      => $customer->maxlicenseIssueDuration ?? null,
                    'billing_support_manager_name' => $customer->msSupportManagerName ?? "",
                ]);
            }
        }
        curl_close($ch);
        return $count;
    }

public function createOrUpdateCustomer(Request $request)
{
    try {
        $validatedData = $this->validateRequest($request);

        // First create/update the customer
        $customer = $this->createOrUpdateCus($validatedData);

        // Then try to handle the bindings separately
        try {
            // Get or create sale and support users
            [$salePerson, $supportPerson] = $this->checkExistenceOfSaleAndSupportUser(
                $validatedData['salesBy'],
                $validatedData['msSupportManager']
            );

            if ($salePerson) {
                try {
                    $this->bindSalePerson($salePerson, $customer);
                } catch (\Exception $e) {
                    \Log::error("Failed to bind sale person: " . $e->getMessage());
                    // Continue execution, don't throw
                }
            }

            if ($supportPerson) {
                try {
                    $this->bindSupportPerson($supportPerson, $customer);
                } catch (\Exception $e) {
                    \Log::error("Failed to bind support person: " . $e->getMessage());
                    // Continue execution, don't throw
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to process user bindings: " . $e->getMessage());
            // Return success with warning
            return response()->json([
                'message'  => 'Customer created successfully but failed to bind users',
                'customer' => $customer,
                'warning' => $e->getMessage()
            ], 200);
        }

        return response()->json([
            'message'  => 'Customer synced successfully',
            'customer' => $customer,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to create customer',
            'error'   => $e->getMessage(),
        ], 500);
    }
}

public function bindSalePerson(User $salePerson, Customer $customer)
{
    CustomerSoftware::updateOrCreate(
        [
            'client_id' => $customer->id
        ],
        [
            'sell_by' => json_encode([$salePerson->username]),
            'sell_by_id' => json_encode([$salePerson->id]),
            'client_name' => $customer->cusname,
            'agreement_date' => $customer->agreement_date
                ? Carbon::parse($customer->agreement_date)->format('Y-m-d H:i:s.000')
                : null,
            'operation_start_date' => $customer->operation_starting_date
                ? Carbon::parse($customer->operation_starting_date)->format('Y-m-d H:i:s.000')
                : null,
        ]
    );
}

public function bindSupportPerson(User $supportPerson, Customer $customer)
{
    SoftwareSupportPerson::updateOrCreate([
        'support_person_id' => $supportPerson->id,
        'client_id' => $customer->id,
    ], [
        'supportperson'        => $supportPerson->username,
        'is_support'           => true,
        'is_billing_in_charge' => false,
        'is_supervisor'        => false,
    ]);
}

    public function checkExistenceOfSaleAndSupportUser($salesBy, $msSupportManager)
    {
        $salesByUser          = User::where('username', $salesBy)->first();
        $msSupportManagerUser = User::where('username', $msSupportManager)->first();

        $salePerson    = $salesByUser;
        $supportPerson = $msSupportManagerUser;

        if (! $salesByUser) {
            $usersForSale   = $this->getUserfromPayrollData(app(SupportAdminUserSyncWithPayroll::class));
            $salePersonData = collect($usersForSale)->firstWhere('UserName', $salesBy);
            $salePerson     = $this->createUserByRole($salePersonData, 'Marketing');
        }

        if (! $msSupportManagerUser) {
            $usersForSupport   = $this->getUserfromPayrollData(app(SupportAdminUserSyncWithPayroll::class));
            $supportPersonData = collect($usersForSupport)->firstWhere('UserName', $msSupportManager);
            $supportPerson     = $this->createUserByRole($supportPersonData, 'support');
        }

        return [$salePerson, $supportPerson];
    }

    public function createUserByRole($user, $role)
    {
        $user = User::create([
            'username'    => $user['UserName'],
            'email'       => $user['Email'],
            'password'    => '123456',
            'phone_no'    => $user['Phone'],
            'address'     => $user['Address'],
            'designation' => $user['Designation'],
            'payroll_id'  => $user['EID'],
            'isactive'    => true,
            'is_status_active' => true,
            'role'   => $role,
        ]);
        return $user;
    }

    public function getUserfromPayrollData(SupportAdminUserSyncWithPayroll $command)
    {
        $users = $command->getPayrollUsers();
        return $users;
    }

    public function validateRequest(Request $request)
    {
        $validatedData = $request->validate([
            'cusID'                   => 'required',
            'cusName'                 => 'required',
            'cusSMS'                  => 'required',
            'agreementDate'           => 'required',
            'operationStartingDate'   => 'required',
            'maxlicenseIssueDuration' => 'required',
            'salesBy'                 => 'required',
            'msSupportManager'        => 'required',
        ]);
        return $validatedData;
    }

    public function createOrUpdateCus($validatedData)
    {
        $customer = Customer::updateOrCreate(
            ['id' => $validatedData['cusID']],
            [
                'cusname'                 => $validatedData['cusName'],
                'accountant_phone_no'     => $validatedData['cusSMS'],
                'is_active'               => 1,
                'is_registered'           => 1,
                'sms_phone_no'            => $validatedData['cusSMS'],
                'agreement_date'          => date('Y-m-d H:i:s', strtotime($validatedData['agreementDate'])),
                'operation_starting_date' => date('Y-m-d H:i:s', strtotime($validatedData['operationStartingDate'])),
                'maxlicenseissueduration' => $validatedData['maxlicenseIssueDuration'],
                'updated_at'              => Carbon::now(),
            ]
        );

        return $customer;

    }

}
