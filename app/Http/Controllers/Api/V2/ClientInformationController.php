<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\Area;
use App\Models\shop;
use App\Models\District;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Models\CustomerSoftware;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ClientInformationController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/v2/client-search",
     *     tags={"Client Search"},
     *     summary="Customer List",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="district_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="area_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="division_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
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
    public function clientSearch(Request $request)
    {
        $client = shop::with('division', 'district', 'area', 'customer')
            ->where('division_id', '=', $request->division_id)
            ->where('district_id', '=', $request->district_id)
            ->where('area_id', '=', $request->area_id)
            ->where('customer_id', '=', $request->customer_id)
            ->get();
        return response()->json(['data' => $client], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/CustomerSoftware",
     *     tags={"software list for a client"},
     *     summary="Customer Software",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *      name="customer_id",
     *      in="query",
     *      required=false,
     *      @OA\Schema(
     *           type="integer"
     *      )
     *   ),
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

    public function clientAllSoftwares(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return JsonDataResponse($user);
        } else {
            $clientId = $user->client_id;
        }
        // $clientSoftwares = DB::table('customer_software')->leftjoin('customers', 'customer_software.customer_id', 'customers.customer_id')->where('customer_software.customer_id', $cusomer_id)->get();
        $clientSoftwares = CustomerSoftware::where('client_id', $clientId)
            ->select('software_id','software_name')
            ->get();
        if ($clientSoftwares) {
            return JsonDataResponse($clientSoftwares);
        } else {
            return JsonDataResponse($clientSoftwares);
        }
    }
}
