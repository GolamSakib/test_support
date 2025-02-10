<?php
namespace App\Http\Controllers\Api\V2;

use App\Models\Support;
use App\Models\Software;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CustomerSoftware;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\SoftwareSupportPerson;

class SoftwareController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v2/software",
     *     tags={"Software"},
     *     summary="Software List",
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="software_name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *      @OA\Parameter(
     *      name="software_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
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
    public function index()
    {
        $software = Software::with('softwaretypes')
            ->orderBy('soft_name', 'asc')
            ->get();
        return response()->json([
            'success' => true,
            'data'    => $software,
        ], 200);
    }

    public function clientWiseSoftware(Request $request): JsonResponse
    {
        try {
            $query = DB::table('addusers_softwarelist')
                ->leftJoin('addusers_customer as customer',
                    DB::raw('customer.id::text'),
                    '=',
                    'addusers_softwarelist.client_id'
                )
                ->where('customer.is_active', true)
                ->select([
                    'addusers_softwarelist.id',
                    'addusers_softwarelist.software_id',
                    'addusers_softwarelist.client_id',
                    'addusers_softwarelist.software_name',
                    'customer.cusname',
                    'customer.id as customer_id',
                    'customer.accountant_phone_no',
                    'customer.sms_phone_no',
                    'customer.is_active',
                ]);

            if ($request->filled('software_id')) {
                $query->where('software_id', $request->software_id);
            }

            // Get paginated results
            $perPage        = $request->get('per_page', 10);
            $clientSoftware = $query->orderBy('software_name', 'asc')
                ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data'    => $clientSoftware,
                'message' => 'Client software data retrieved successfully',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving client software data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/software/store",
     *     tags={"Software"},
     *     summary="Software Create",
     *     security={{"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="software_name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="software_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data saved successfully.")
     *         )
     *     ),
     * )
     * )
     */

    public function store(Request $request)
    {

        $data = $this->validate($request, [
            'software_name'    => 'required|unique:client_support_admin_softwarelistall,soft_name',
            'software_type_id' => 'required',
        ]);
        try {
            if (Software::create([
                'soft_name'        => $data['software_name'],
                'software_type_id' => $data['software_type_id'],
            ])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Created Successfully',
                    'data'    => $data,
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Something went wrong',
                    'data'    => $data,
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => $data,
            ], $e->getCode());
        }

    }
    /**
     * @OA\Get(
     *     path="/api/v2/software/edit/{id}",
     *     tags={"Software"},
     *     summary="Software Edit",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="software_name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="software_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data saved successfully.")
     *         )
     *     ),
     * )
     * )
     */
    public function edit($id)
    {
        $software = Software::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Edit Successfully',
            'data'    => $software,
        ], 200);

    }

    /**
     * @OA\Put(
     *     path="/api/v2/software/update/{id}",
     *     tags={"Software"},
     *     summary="Software Update",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="software_name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="software_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data Update successfully.")
     *         )
     *     ),
     * )
     * )
     */

    public function update(Request $request, $id)
    {
        $data = $this->validate($request, [
            'software_name' => [
                'required',
                Rule::unique('client_support_admin_softwarelistall', 'soft_name')->ignore($id, 'id'),
            ],
        ]);
        $software                   = Software::findOrFail($id);
        $software->soft_name        = $data['software_name'];
        $software->software_type_id = $request->software_type_id;
        $software->save();
        return response()->json([
            'success' => true,
            'message' => 'Update Successfully',
            'data'    => $software,
        ], 200);
    }
    /**
     * @OA\Get(
     *     path="/api/v2/software/destroy/{id}",
     *     tags={"Software"},
     *     summary="software Delete",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="software_name",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     *   @OA\Parameter(
     *      name="software_type_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="string"
     *      )
     *   ),
     * @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Data delete successfully.")
     *         )
     *     ),
     * )
     * )
     */
    public function destroy($id)
    {
        $software = Software::find($id);
        $software->delete();
        return response()->json([
            'success' => true,
            'message' => 'Delete Successfully',
            'data'    => $software,
        ], 200);
    }

    public function show($id)
    {

    }

    public function details($id, Request $request)
    {
        $software      = Software::where('id', $id)->first();
        $support       = Support::where('software_id', $id)->get();
        $software_user = CustomerSoftware::where('software_id', $id)->groupBy('software_id')->pluck('customer_id');
        foreach ($software_user as $user) {
            $count[] = Support::where('software_id', $id)->where('customer_id', $user)->count();
        }
        $top_5_suport_taken_client = $count;
        $software_support          = SoftwareSupportPerson::where('software_id', $id)->groupBy('user_id')->pluck('user_id');
        foreach ($software_support as $support) {
            $count[] = Support::where('software_id', $id)->where('accepted_support_id', $support)->count();
        }
        $top_5_suport_given_user = $count;
        return response()->json([
            "success" => true,
            "status"  => 200,
            'message' => 'Customer Found',
            'data'    => [
                'software'                  => $software,
                'top_5_suport_taken_client' => $top_5_suport_taken_client,
                'top_5_suport_given_user'   => $top_5_suport_given_user,
            ],
        ]);
    }
}
