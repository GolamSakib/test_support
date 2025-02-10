<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;
use App\Models\shop;
use App\Models\User;
use App\Models\Support;
use App\Models\Customer;
use App\Models\Division;
use App\Models\Software;
use App\Models\Inventory;
use Illuminate\Http\Request;
use MercadoPago\Config\Json;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V2\Controller;

class InventoryRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/all-inventory",
     *     tags={"Inventory Request"},
     *     summary="Inventory",
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
    public function index(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $info = Inventory::orderBy('created_time', 'desc')
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('request_date', [$start_time, $end_time]);
            })
            ->get();

        return JsonDataResponse($info);

        }

    /**
     * @OA\Get(
     *     path="/api/v2/inventory-request/inventoryRequestByClient",
     *     tags={"inventoryRequestByClient"},
     *     summary="Inventory",
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


    public function inventoryRequestByClient(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        $user = Auth::user();
        $client_id=$user->client_id;
        $inventories
        = Inventory::where('client_id', $client_id)->orderBy('created_time', 'desc')
        ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
            $query->whereBetween('request_date', [$start_time, $end_time]);
        })
        ->get();

        return JsonDataResponse($inventories);

        }

    /**
     * @OA\Get(
     *     path="/api/v2/inventory-request/InventoryRequestbyAssignedPerson/{id}",
     *     tags={"InventoryRequestByAssignedPerson"},
     *     summary="Inventory",
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

    public function InventoryRequestbyAssignedPerson(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time)->startOfDay();
            $end_time = Carbon::parse($request->end_time)->endOfDay();
        }

        if ($request->has('id')) {
            $check_user = User::find($request->id);
        } else {
            $check_user = Auth::user();
        }
        if (!$check_user) {
            return JsonDataResponse($check_user);
        } else {
            $inventories = Inventory::where('assigned_person_id', $check_user->id)->orderBy('created_time', 'desc')
            ->when($start_time && $end_time, function ($query) use ($start_time, $end_time) {
                $query->whereBetween('request_date', [$start_time, $end_time]);
            })
            ->get();

            foreach ($inventories as $key => $value) {
                $value->request_date = formatDate($value->request_date);
            }


            if (!$inventories) {
                return JsonDataResponse($inventories);
            } else {
                return JsonDataResponse($inventories);
            }
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v2/inventory-request/inventory_requests",
     *     tags={"Inventory"},
     *     summary="Get Inventory Requests for support person",
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
    public function inventory_requests(Request $request)
    {
        $user = Auth::user();
        $check_user = User::where('user_type', 'Support')->first();
        if (!$check_user) {
            return JsonDataResponse($check_user);
        }
        $inventory_request = DB::table("software_support_people")
            ->where('software_support_people.user_id', $user->id)
            ->leftJoin('inventories', 'inventories.customer_id', 'software_support_people.customer_software_id')
            ->leftjoin('shops', 'shops.id', 'inventories.shop_id')
            ->get();
        if (!$inventory_request) {
            return JsonDataResponse($inventory_request);
        } else {
            return JsonDataResponse($inventory_request);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v2/inventory-request/store",
     *     tags={"Inventory"},
     *     summary="Store an inventory request",
     *     operationId="storeInventoryRequest",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="software_id", type="integer", description="The ID of the software", example=1),
     *                 @OA\Property(property="note", type="string", description="Optional note for the request", example="Additional information"),
     *                 @OA\Property(property="inventory_date", type="string", format="date", description="The inventory date", example="2023-05-31")
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
            'soft_id' => 'required|numeric',
            'note' => 'sometimes|string',
            'request_date' => 'required|date',
        ]);
        $user = Auth::user();
        $validatedData['client_user_name'] = $user->username;
        $customer_id = $user->client_id;
        $customer_info = Customer::where('id', $customer_id)->first();
        $validatedData['client_id'] = $customer_id;
        $validatedData['client_user_id'] = $user->id;
        $validatedData['client_name'] = $customer_info->cusname;
        $validatedData['shop_name'] = $user->shopname;
        $validatedData['client_user_phone'] = $user->phoneno;
        $validatedData['created_time'] = Carbon::now();
        $software_info=Software::where('id',$request->soft_id)->first();
        $validatedData['soft_name']=$software_info->softname;
        $inventory_request = Inventory::create($validatedData);
        return saveDataResponse($inventory_request);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/inventory-request/assign",
     *     tags={"Inventory"},
     *     security={{"bearerAuth": {}}},
     *     summary="Assign inventory",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"inventory_id", "assigned_id"},
     *             @OA\Property(property="inventory_id", type="integer", description="ID of the inventory to assign"),
     *             @OA\Property(property="assigned_id", type="integer", description="ID of the user to assign the inventory to")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function inventoryAssign(Request $request)
    {

        $inventory_id = $request->inventory_id;
        if ($request->is_approved == 1) {
            $inventory = Inventory::find($inventory_id);
            if (!$inventory_id) {
                return JsonDataResponse($inventory_id);
            } else {
                $inventory->assigned_person_id = $request->assigned_id;
                $assigned_person_info=User::find($request->assigned_id);
                $inventory->assigned_person = $assigned_person_info->username;
                $inventory->is_approved = 1;
                $inventory->is_assigned = 1;
                $inventory->save();
                return JsonDataResponse($inventory);
            }
        } else {
            $inventory = Inventory::find($inventory_id);
            if (!$inventory_id) {
                return JsonDataResponse($inventory_id);
            } else {
                $inventory->is_approved = 0;
                $inventory->rejected_note = $request->note;
                $inventory->save();
                return JsonDataResponse($inventory);
            }
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v2/inventory-request/seen",
     *     tags={"Inventory"},
     *     security={{"bearerAuth": {}}},
     *     summary="Mark inventory requests as seen",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"id"},
     *             @OA\Property(property="id", type="array", @OA\Items(type="integer"), description="Array of inventory request IDs to mark as seen")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function inventoryRequestSeen(Request $request)
    {
        $ids = $request->input('id', []);
        $inventory = Inventory::whereIn('id', $ids)->update([
            'is_seen' => 1
        ]);
        return JsonDataResponse($inventory);
    }

    public function inventoryDoneBySupportPerson(Request $request)
    {
        if ($request->has('id') && $request->has('completed_note')) {
            $inventory = Inventory::where('id', $request->id)->first();
            $inventory->completed_note = $request->completed_note;
            $inventory->is_done = 1;
            $inventory->save();
            if ($inventory) {
                return updateDataResponse($inventory);
            } else {
                return updateDataResponse($inventory);
            }
        } else {
            return updateDataResponse($inventory = null);
        }
    }
}
