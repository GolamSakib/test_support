<?php

namespace App\Http\Controllers\Api\V2;

use Auth;
use Cache;
use App\Models\Division;
use App\Facades\AppFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\V2\Controller;

class DivisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/divisions",
     *     tags={"Divisions"},
     *     security={ {"bearerAuth": {} }},
     *     summary="Get a listing of the divisions",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     *
     *
     * )
     */
    public function index(Request $request)
    {
        $divisions = Cache::remember('divisions_data', 86400, function () {
            return Division::with('districts.areas_under_district')->orderBy('name', 'ASC')->get();
        });
        return response()->json(['data' => $divisions], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/divisions/{id}",
     *     tags={"Divisions"},
     *     summary="Get a specific division",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division not found"
     *     )
     * )
     */

    public function show($id)
    {
        $division = Cache::remember('single_division', 86400, function () use ($id) {
            return Division::with('districts.areas_under_district')->find($id);
        });
        if (!$division) {
            return response()->json(['message' => 'Division not found'], 404);
        }
        return response()->json(['data' => $division]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/divisions",
     *     tags={"Divisions"},
     *     summary="Create a new division",
     *     security={ {"bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Division data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Division created",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division is not created",
     *         @OA\JsonContent()
     *     )
     * )
     */


    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|unique:divisions',
        ]);
        $division = Division::create($validatedData);
        AppFacade::generateActivityLog('divisions', 'create', $division->id);
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json(['message' => 'Division created', 'data' => $division], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v2/divisions/{id}",
     *     tags={"Divisions"},
     *     summary="Update a specific division",
     *     security={ {"bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Division data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Division updated",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division not found",
     *         @OA\JsonContent()
     *     )
     * )
     */



    public function update(Request $request, $id)
    {
        $division = Division::find($id);
        if (!$division) {
            return response()->json(['message' => 'Division not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string',
        ]);

        $division->update($validatedData);
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return response()->json(['message' => 'Division updated', 'data' => $division]);
    }


    /**
     * @OA\Delete(
     *     path="/api/v2/divisions/{id}",
     *     tags={"Divisions"},
     *     summary="Delete a specific division",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the division",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Division is deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division not found",
     *         @OA\JsonContent()
     *     )
     * )
     */




    public function destroy($id)
    {
        $division = Division::find($id);
        if (!$division) {
            return response()->json(['message' => 'Division not found'], 404);
        }
        $division->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json(['message' => 'Division deleted']);
    }
}
