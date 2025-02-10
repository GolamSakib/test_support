<?php

namespace App\Http\Controllers\Api\V2;

use Cache;
use App\Models\District;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\V2\Controller;
use Illuminate\Validation\ValidationException;

class DistrictController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * @OA\Get(
     *     path="/api/v2/districts",
     *     tags={"Districts"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a listing of the districts",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     * @OA\JsonContent()
     *     )
     * )
     */
    public function index(Request $request)
    {

        // $districts =District::with('areas_under_district', 'division')->orderBy('name', 'ASC')->get();
        $districts =District::orderBy('dist_name', 'ASC')->get();
        return response()->json([
            'success' => true,
            'data' => $districts,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v2/districts/{id}",
     *     tags={"Districts"},
     *     security={{"bearerAuth": {}}},
     *     summary="Get a specific district",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the district",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="District not found",
     *         @OA\JsonContent()
     *     )
     * )
     */

    public function show($id)
    {
        $district = Cache::remember('single_district', 86400, function () use ($id) {
            return District::with('areas_under_district')->find($id);
        });
        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }
        return response()->json(['data' => $district]);
    }

    /**
     * @OA\Post(
     *     path="/api/v2/districts",
     *     tags={"Districts"},
     *     security={{"bearerAuth": {}}},
     *     summary="Create a new district",
     *     @OA\RequestBody(
     *         required=true,
     *         description="District data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="division_id",
     *                 type="integer"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="District created",
     *          @OA\JsonContent()
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division not found",
     *         @OA\JsonContent()
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'name' => 'required|string|unique:districts',
                'division_id' => 'required|numeric'
            ]);
            $division = Division::find($validatedData['division_id']);
            if ($division) {
                $district = District::create($validatedData);
                return response()->json(['message' => 'District created Successfully', 'data' => $district], 200);
            }
        }
        catch (ValidationException $e) {
            $errors = $e->errors();
            return response()->json(['errors' => $errors], 422);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v2/districts/{id}",
     *     tags={"Districts"},
     *     security={{"bearerAuth": {}}},
     *     summary="Update a specific district",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the district",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="District data",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string"
     *             ),
     *             @OA\Property(
     *                 property="division_id",
     *                 type="integer"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="District updated",
     *          @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Division not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Division is not found"
     *             )
     *         )
     *     )
     * )
     */


    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'division_id' => 'required|numeric'
        ]);
        $division = Division::find($validatedData['division_id']);
        if (!$division) {
            return response()->json(['message' => 'Division is not found'], 404);
        } else {
            $district = District::find($id);
            $district->update($validatedData);
            Artisan::call('cache:clear');
            Artisan::call('view:clear');
            return response()->json(['message' => 'District is updated', 'data' => $district]);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v2/districts/{id}",
     *     tags={"Districts"},
     *     security={{"bearerAuth": {}}},
     *     summary="Delete a specific district",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the district",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="District deleted",
     *@OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="District not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="District not found"
     *             )
     *         )
     *     )
     * )
     */

    public function destroy($id)
    {
        $district = District::find($id);
        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }
        $district->delete();
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        return response()->json(['message' => 'District deleted']);
    }
}
