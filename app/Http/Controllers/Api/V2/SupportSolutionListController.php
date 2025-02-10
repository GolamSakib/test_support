<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Models\Support;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupportSolutionListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/support-solution-list",
     *     operationId="index",
     *     tags={"Support Solution List"},
     *     summary="Retrieve the support solution list",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Data not found",
     *         @OA\JsonContent()
     *     )
     * )
     */

    public function index()
    {
        
        $user = Auth::user();
        $support = Support::where('user_id', $user->id)->get();
        if (!$support) {
            return JsonDataResponse($support);
        }
        $info = DB::table('supports')
            ->leftJoin('shops', 'supports.shop_id', 'shops.id')
            ->leftJoin('customers', 'shops.customer_id', 'customers.customer_id')
            ->leftJoin('software', 'supports.software_id', 'software.id')
            ->select('shops.name as shop_name', 'customers.name as customer_name', 'software.software_name as software_name', 'shops.address as shop_address', 'shops.phone as shop_phone', 'supports.description as support_description', 'shops.status as shop_status');
        $newRequest = $info->where('is_pending', 0)->get();
        $newRequestCount = $newRequest->count();

        $processRequest = $info->where('is_processing', 1)->get();
        $processRequestCount = $processRequest->count();

        $notAccepted = $info->where('is_accepted', 1)->get();
        $notAcceptedCount = $notAccepted->count();

        $helpedBySupport = $info->where('is_helped', 1)->get();
        $helpedBySupportCount = $helpedBySupport->count();
        $done = $info->where('is_done', 1)->get();
        $doneCount = $done->count();

        $supportProblemListInfo = [
            'new_request' => $newRequest,
            'new_request_count' => $newRequestCount,
            'processing_request' => $processRequest,
            'processing_request_count' => $processRequestCount,
            'not_accepted' => $notAccepted,
            'not_accepted count' => $notAcceptedCount,
            'helped_by_support' => $helpedBySupport,
            'helped_by_support_count' => $helpedBySupportCount,
            'done' => $done,
            'done_count' => $doneCount
        ];

        return  JsonDataResponse($supportProblemListInfo);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


    public function destroy($id)
    {
    }
}
