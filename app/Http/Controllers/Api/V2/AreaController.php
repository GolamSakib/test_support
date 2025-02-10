<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\Area;
use App\Models\District;
use App\Models\Division;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;


class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Get(
     *     path="/api/v2/areas",
     *     tags={"Areas"},
     *     summary="Get all areas",
     *     operationId="getAreas",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index(Request $request)
    {
        if ($request->has('page')) {
            $page = $request->page;
        } else {
            $page = 1;
        }
        // $areas = Cache::remember('Areas' . $page, 86400, function () {
            // return Area::with('district', 'division')->orderBy('name', 'ASC')->get();
            $areas = Area::with('district')->orderBy('name', 'ASC')->get();
        // });
        return response()->json(['data' => $areas], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    /**
     * @OA\Post(
     *     path="/api/v2/areas",
     *     tags={"Areas"},
     *     summary="Create a new area",
     *     operationId="createArea",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "division_id", "district_id"},
     *                 @OA\Property(property="name", type="string", description="The name of the area"),
     *                 @OA\Property(property="division_id", type="integer", description="The ID of the division"),
     *                 @OA\Property(property="district_id", type="integer", description="The ID of the district")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Area created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message", example="Area is created"),
     *             @OA\Property(property="data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {

        
        $validatedData = $request->validate([
            'name' => 'required|string|unique:areas,name',
            // 'division_id' => 'required|numeric',
            'district_id' => 'required|numeric'
        ]);

        // $division = Division::find($validatedData['division_id']);
        // if (!$division) {
        //     return response()->json(['message' => 'Division is not found']);
        // }

        // $area = Area::where('name', $validatedData['name'])->first();
        // if ($area) {
        //     return response()->json(['message' => 'Area name '. $validatedData['name']  .'is already exist!']);
        // }

        $district = District::find($validatedData['district_id']);
        if (!$district) {
            return response()->json(['message' => 'District is not found']);
        }

        // Create the district if both division and district are found
        $area = Area::create([
            'name' => $validatedData['name'],
            'district_id' => $district->id
        ]);

        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        return response()->json([
            'message' => 'Area is created',
            'data' => $area
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/areas/{id}",
     *     tags={"Areas"},
     *     summary="Get a specific area",
     *     operationId="getArea",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the area",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="data"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function show($id)
    {
        $area = Cache::remember('single_area', 86400, function () use ($id) {
            return Area::find($id);
        });
        if (!$area) {
            return response()->json(['message' => 'Area not found'], 404);
        }
        return response()->json(['data' => $area]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Put(
     *     path="/api/v2/areas/{id}",
     *     tags={"Areas"},
     *     summary="Update an area",
     *     operationId="updateArea",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the area",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", description="The name of the area"),
     *                 @OA\Property(property="division_id", type="integer", description="The ID of the division"),
     *                 @OA\Property(property="district_id", type="integer", description="The ID of the district")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division or district not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string',
            'division_id' => 'required|numeric',
            'district_id' => 'required|numeric'
        ]);
        $division = Division::find($validatedData['division_id']);
        if (!$division) {
            return response()->json(['message' => 'Division is not found'], 404);
        }
        $district = District::find($validatedData['district_id']);
        if (!$district) {
            return response()->json(['message' => 'District is not found']);
        } else {
            $area = Area::find($id);
            $area->update($validatedData);
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return response()->json(['message' => 'Area is updated', 'data' => $area]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Delete(
     *     path="/api/v2/areas/{id}",
     *     tags={"Areas"},
     *     summary="Delete an area",
     *     operationId="deleteArea",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="The ID of the area",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Area not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */

    public function destroy($id)
    {
        //
        $area = Area::find($id);
        if (!$area) {
            return response()->json(['message' => 'Area not found'], 404);
        }

        $area->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json(['message' => 'Area deleted']);
    }
}
