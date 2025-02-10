<?php

namespace App\Http\Controllers\Api\v2;

use Carbon\Carbon;
use App\Models\Customer;
use App\Models\Software;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ClientUser;

class ChangeRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time);
            $end_time = Carbon::parse($request->end_time);
        }

        $changes = DB::table('clientlogin_changes as changes');

        if ($start_time && $end_time) {
            $changes = $changes
                ->whereBetween('created_time', [$start_time, $end_time]);
        }

        $changes =  $changes->select([
            'changes.id',
            'changes.client_id',
            'changes.client_name',
            'changes.client_user_name',
            'changes.client_user_phone',
            'changes.changes_group_id',
            'changes.shop_name',
            'changes.soft_name',
            'changes.created_time',
            DB::raw('STRING_AGG(groups.changes, \', \') as all_changes')
        ])
            ->join('clientlogin_changes_group as groups', 'groups.group_id', '=', 'changes.changes_group_id')  // Use a different alias for the group table
            ->groupBy(
                'changes.id',
                'changes.client_id',
                'changes.client_name',
                'changes.client_user_name',
                'changes.client_user_phone',
                'changes.changes_group_id',
                'changes.shop_name',
                'changes.soft_name',
                'changes.created_time',
            )
            ->orderBy('changes.created_time', 'desc')
            ->get();



        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $changes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $validatedData = $request->validate([
            'software_id' => 'required',
        ]);

        try {
            DB::beginTransaction();
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'message' => 'Unauthorized.',
                ], 401);
            }

            $validatedData['client_user_name'] = $user->username;
            $validatedData['client_user_phone'] = $user->phoneno;
            $validatedData['shop_name'] = $user->shopname;

            $client = Customer::find($user->client_id);
            if (!$client) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'Client not found.',
                ], 404);
            }
            $validatedData['client_id'] =  $client->id;
            $validatedData['client_name'] = $client->cusname;

            $software = Software::find($request->software_id);
            if (!$software) {
                return response()->json([
                    'success' => false,
                    'status' => 404,
                    'message' => 'Software not found.',
                ], 404);
            }
            $validatedData['soft_id'] = $software->id;
            $validatedData['soft_name'] = $software->soft_name;

            $largeValue = DB::table('clientlogin_changes')->max('changes_group_id');
            $increamtedValue = $largeValue + 1;

            DB::table('clientlogin_changes')->insert([
                'client_id' => $validatedData['client_id'],
                'client_name' => $validatedData['client_name'],
                "shop_name" => $validatedData['shop_name'],
                'client_user_name' => $validatedData['client_user_name'],
                'client_user_phone' => $validatedData['client_user_phone'],
                'created_time' => Carbon::now(),
                'soft_id' => $validatedData['soft_id'],
                'soft_name' =>  $validatedData['soft_name'],
                'changes_group_id' => $increamtedValue,
            ]);

            if ($request->has('allChange')) {
                foreach ($request->allChange as $item) {
                    DB::table('clientlogin_changes_group')->insert([
                        'changes' => $item['value'],
                        'group_id' => $increamtedValue,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'status' => 200,
                'message' => 'Change request submitted successfully.',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'An error occurred while submitting the change request.',
                'error' => $th->getMessage(),
            ], 500);
        }
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function changeRequestByClient(Request $request)
    {
        // return $request->all();
        $start_time = null;
        $end_time = null;

        if ($request->has('start_time') && $request->has('end_time')) {
            $start_time = Carbon::parse($request->start_time);
            $end_time = Carbon::parse($request->end_time);
        }

        $changes = DB::table('clientlogin_changes as changes');

        if ($start_time && $end_time) {
            $changes = $changes
                ->whereBetween('created_time', [$start_time, $end_time]);
        }

        $user = auth()->user();

        $changes =  $changes
            ->where('changes.client_user_name', $user->username)
            ->select([
                'changes.id',
                'changes.client_id',
                'changes.client_name',
                'changes.client_user_name',
                'changes.client_user_phone',
                'changes.changes_group_id',
                'changes.shop_name',
                'changes.soft_name',
                'changes.created_time',
                DB::raw('STRING_AGG(groups.changes, \', \') as all_changes')
            ])
            ->join('clientlogin_changes_group as groups', 'groups.group_id', '=', 'changes.changes_group_id')  // Use a different alias for the group table
            ->groupBy(
                'changes.id',
                'changes.client_id',
                'changes.client_name',
                'changes.client_user_name',
                'changes.client_user_phone',
                'changes.changes_group_id',
                'changes.shop_name',
                'changes.soft_name',
                'changes.created_time',
            )
            ->orderBy('changes.created_time', 'desc')
            ->get();



        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $changes,
        ]);
    }
}
